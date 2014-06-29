<?php
namespace Radmiraal\CouchDB\Persistence;

/*                                                                        *
 * This script belongs to the Flow package "Radmiraal.CouchDB".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\Package;
use TYPO3\Flow\Reflection\ObjectAccess;
use TYPO3\Flow\Persistence\Exception\IllegalObjectTypeException;
use Doctrine\Common\EventManager;
use Doctrine\ODM\CouchDB\DocumentManager;
use TYPO3\Flow\Utility\Files;

/**
 * Factory for creating Doctrine ODM DocumentManager instances
 * @Flow\Scope("singleton")
 */
class DocumentManagerFactory {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\Flow\Package\PackageManagerInterface
	 * @Flow\Inject
	 */
	protected $packageManager;

	/**
	 * @var \TYPO3\Flow\Utility\Environment
	 * @Flow\Inject
	 */
	protected $environment;

	/**
	 * @var array
	 * @Flow\Inject(setting="persistence.backendOptions", package="Radmiraal.CouchDB")
	 */
	protected $backendOptions;

	/**
	 * @var array
	 * @Flow\Inject(setting="persistence.doctrine.eventListeners", package="Radmiraal.CouchDB")
	 */
	protected $eventListeners = array();

	/**
	 * @var \TYPO3\Flow\Configuration\ConfigurationManager
	 * @Flow\Inject
	 */
	protected $configurationManager;

	/**
	 * @var array<\Doctrine\ODM\CouchDB\DocumentManager>
	 */
	protected $documentManagers;

	/**
	 * Creates a Doctrine ODM DocumentManager
	 *
	 * @param string $instanceIdentifier
	 * @return \Doctrine\ODM\CouchDB\DocumentManager
	 */
	public function create($instanceIdentifier) {
		$initializedDocumentManager = ObjectAccess::getPropertyPath($this->documentManagers, $instanceIdentifier);
		if ($initializedDocumentManager instanceof \Doctrine\ODM\CouchDB\DocumentManager) {
			return $initializedDocumentManager;
		}

		// Get backend options, with fallback for default identifier for backwards compatibility
		$persistenceBackendOptions = ObjectAccess::getPropertyPath($this->backendOptions, 'instances.' . $instanceIdentifier);
		if ($persistenceBackendOptions === NULL && $instanceIdentifier === 'default') {
			$persistenceBackendOptions = $this->backendOptions;
		}

		return $this->createDocumentManager($instanceIdentifier, $persistenceBackendOptions);
	}

	/**
	 * @param $instanceIdentifier
	 * @param array $persistenceBackendOptions
	 * @throws \Radmiraal\CouchDB\Exception
	 * @return \Doctrine\ODM\CouchDB\DocumentManager
	 */
	protected function createDocumentManager($instanceIdentifier, array $persistenceBackendOptions) {
		if (isset($this->documentManagers[$instanceIdentifier])) {
			return $this->documentManagers[$instanceIdentifier];
		}

		if (empty($persistenceBackendOptions['databaseName'])) {
			throw new \Radmiraal\CouchDB\Exception('No databaseName set for instance ' . $instanceIdentifier);
		}
		$httpClient = new \Doctrine\CouchDB\HTTP\SocketClient(
			isset($persistenceBackendOptions['host']) ? $persistenceBackendOptions['host'] : 'localhost',
			isset($persistenceBackendOptions['port']) ? $persistenceBackendOptions['port'] : 5984,
			isset($persistenceBackendOptions['username']) ? $persistenceBackendOptions['username'] : '',
			isset($persistenceBackendOptions['password']) ? $persistenceBackendOptions['password'] : '',
			isset($persistenceBackendOptions['ip']) ? $persistenceBackendOptions['ip'] : NULL
		);

		$reader = new \Doctrine\Common\Annotations\AnnotationReader();
		$metaDriver = new \Doctrine\ODM\CouchDB\Mapping\Driver\AnnotationDriver($reader);

		$config = new \Doctrine\ODM\CouchDB\Configuration();
		$config->setMetadataDriverImpl($metaDriver);

		$designDocumentFolders = array(
			'_all' => array(),
			$instanceIdentifier => array()
		);

		/**
		 * TODO: Move creation of the design documents to the migrate command and add a cache for design documents to load
		 */
		$packages = $this->packageManager->getActivePackages();
		foreach ($packages as $package) {
			$this->importDesignDocuments($config, $package, $instanceIdentifier);
		}

		$proxyDirectory = \TYPO3\Flow\Utility\Files::concatenatePaths(array($this->environment->getPathToTemporaryDirectory(), 'DoctrineODM/Proxies'));
		\TYPO3\Flow\Utility\Files::createDirectoryRecursively($proxyDirectory);
		$config->setProxyDir($proxyDirectory);

		$config->setProxyNamespace('TYPO3\Flow\Persistence\DoctrineODM\Proxies');
		$config->setAutoGenerateProxyClasses(TRUE);

		$couchClient = new \Doctrine\CouchDB\CouchDBClient($httpClient, $persistenceBackendOptions['databaseName']);
		/** @var DocumentManager $documentManager */
		$documentManager = DocumentManager::create($couchClient, $config, $this->buildEventManager());

		if(!$documentManager->getHttpClient()->request('GET', '/' . $persistenceBackendOptions['databaseName'])->status === 200) {
			throw new \Radmiraal\CouchDB\Exception('Database ' . $persistenceBackendOptions['databaseName'] . ' for instance ' . $instanceIdentifier . ' does not exist');
		}

		$this->documentManagers[$instanceIdentifier] = $documentManager;
		return $documentManager;
	}

	/**
	 * @param \Doctrine\ODM\CouchDB\Configuration $config
	 * @param Package $package
	 * @param string $instanceIdentifier
	 * @return void
	 */
	protected function importDesignDocuments(\Doctrine\ODM\CouchDB\Configuration &$config, Package $package, $instanceIdentifier) {
		$designDocumentRootFolder = Files::concatenatePaths(array($package->getPackagePath(), 'Migrations/CouchDB/DesignDocuments'));
		if (!is_dir($designDocumentRootFolder)) {
			return;
		}

		$designDocumentFolders = glob(Files::concatenatePaths(array($designDocumentRootFolder, '_all')) . '/*');
		$designDocumentFolders = array_merge($designDocumentFolders, glob(Files::concatenatePaths(array($designDocumentRootFolder, $instanceIdentifier)) . '/*'));

		foreach ($designDocumentFolders as $designDocumentFolder) {
			if (is_dir($designDocumentFolder)) {
				$config->addDesignDocument(
					strtolower(basename($designDocumentFolder)),
					'Radmiraal\CouchDB\View\Migration',
					array(
						'packageKey' => $package->getPackageKey(),
						'path' => $designDocumentFolder
					)
				);
			}
		}
	}

	/**
	 * @return void
	 */
	public function instantiateAllDocumentManagersFromConfiguration() {
		$instances = isset($this->backendOptions['instances']) ? $this->backendOptions['instances'] : array();
		if (!isset($instances['default']) && empty($instances)) {
			$instances['default'] = $this->backendOptions;
		}

		foreach ($instances as $instanceIdentifier => $persistenceBackendOptions) {
			$this->createDocumentManager($instanceIdentifier, $persistenceBackendOptions);
		}
	}

	/**
	 * @return array<\Doctrine\ODM\CouchDB\DocumentManager>
	 */
	public function getInstantiatedDocumentManagers() {
		return $this->documentManagers;
	}

	/**
	 * Add configured event subscribers and listeners to the event manager
	 *
	 * @return EventManager
	 * @throws IllegalObjectTypeException
	 */
	protected function buildEventManager() {
		$eventManager = new EventManager();
		if (is_array($this->eventListeners)) {
			foreach ($this->eventListeners as $listenerOptions) {
				$listener = $this->objectManager->get($listenerOptions['listener']);
				$eventManager->addEventListener($listenerOptions['events'], $listener);
			}
		}
		return $eventManager;
	}
}
