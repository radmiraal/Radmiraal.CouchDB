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

/**
 * Factory for creating Doctrine ODM DocumentManager instances
 * @Flow\Scope("singleton")
 */
class DocumentManagerFactory {

	/**
	 * @var \TYPO3\Flow\Utility\Environment
	 * @Flow\Inject
	 */
	protected $environment;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var \TYPO3\Flow\Configuration\ConfigurationManager
	 * @Flow\Inject
	 */
	protected $configurationManager;

	/**
	 * @var \Doctrine\ODM\CouchDB\DocumentManager
	 */
	protected $documentManager;

	/**
	 * @return void
	 */
	public function initializeObject() {
		$settings = $this->configurationManager->getConfiguration(
			\TYPO3\Flow\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
			'Radmiraal.CouchDB'
		);
		$this->settings = array_merge(array('host' => 'localhost', 'port' => 5984), $settings['persistence']['backendOptions']);
	}

	/**
	 * Creates a Doctrine ODM DocumentManager
	 *
	 * @return \Doctrine\ODM\CouchDB\DocumentManager
	 */
	public function create() {
		if (isset($this->documentManager)) {
			return $this->documentManager;
		}

		$httpClient = new \Doctrine\CouchDB\HTTP\SocketClient(
			$this->settings['host'],
			$this->settings['port'],
			$this->settings['username'],
			$this->settings['password'],
			$this->settings['ip']
		);

		$reader = new \Doctrine\Common\Annotations\AnnotationReader();
		$metaDriver = new \Doctrine\ODM\CouchDB\Mapping\Driver\AnnotationDriver($reader);

		$config = new \Doctrine\ODM\CouchDB\Configuration();
		$config->setMetadataDriverImpl($metaDriver);

		$proxyDirectory = \TYPO3\Flow\Utility\Files::concatenatePaths(array($this->environment->getPathToTemporaryDirectory(), 'DoctrineODM/Proxies'));
		\TYPO3\Flow\Utility\Files::createDirectoryRecursively($proxyDirectory);
		$config->setProxyDir($proxyDirectory);

		$config->setProxyNamespace('TYPO3\Flow\Persistence\DoctrineODM\Proxies');
		$config->setAutoGenerateProxyClasses(TRUE);

		$couchClient = new \Doctrine\CouchDB\CouchDBClient($httpClient, $this->settings['databaseName']);
		$this->documentManager = \Doctrine\ODM\CouchDB\DocumentManager::create($couchClient, $config);

		return $this->documentManager;
	}

}

?>