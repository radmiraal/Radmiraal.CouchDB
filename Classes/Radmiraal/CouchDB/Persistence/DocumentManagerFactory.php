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

/**
 * Factory for creating Doctrine ODM DocumentManager instances
 */
class DocumentManagerFactory {

	/**
	 * @var \TYPO3\Flow\Utility\Environment
	 */
	protected $environment;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param \TYPO3\Flow\Utility\Environment $environment
	 * @return void
	 */
	public function injectEnvironment(\TYPO3\Flow\Utility\Environment $environment) {
		$this->environment = $environment;
	}

	/**
	 * @param array $settings
	 */
	public function __construct(array $settings) {
		$this->settings = array_merge(array('host' => 'localhost', 'port' => 5984), $settings);
	}

	/**
	 * Creates a Doctrine ODM DocumentManager
	 *
	 * @return \Doctrine\ODM\CouchDB\DocumentManager
	 */
	public function create() {
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
		return \Doctrine\ODM\CouchDB\DocumentManager::create($couchClient, $config);
	}

}

?>