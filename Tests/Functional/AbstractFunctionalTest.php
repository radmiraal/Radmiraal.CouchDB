<?php
namespace Radmiraal\CouchDB\Tests\Functional;

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
use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\Reflection\ObjectAccess;

/**
 * Abstract functional test class, setting up a DocumentManager and httpClient
 * object for usage in functional tests.
 */
abstract class AbstractFunctionalTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var \Radmiraal\CouchDB\Persistence\DocumentManagerFactory
	 */
	protected $documentManagerFactory;

	/**
	 * @var array
	 */
	protected $instances;

	/**
	 * Set up test
	 */
	public function setUp() {
		parent::setUp();
		/** @var \TYPO3\Flow\Configuration\ConfigurationManager $configurationManager */
		$configurationManager = $this->objectManager->get('TYPO3\Flow\Configuration\ConfigurationManager');
		$settings = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Radmiraal.CouchDB.persistence.backendOptions');

		$this->documentManagerFactory = $this->objectManager->get('\Radmiraal\CouchDB\Persistence\DocumentManagerFactory');

		$this->instances = ObjectAccess::getPropertyPath($settings, 'instances');
		if ($this->instances === NULL) {
			$this->instances = array('default' => $settings);
		}

		$couchDbHelper = new \Radmiraal\CouchDB\CouchDBHelper();
		$this->inject($couchDbHelper, 'documentManagerFactory', $this->documentManagerFactory);

		foreach ($this->instances as $instanceIdentifier => $instanceConfiguration) {
			if (!isset($instanceConfiguration['databaseName'])) {
				continue;
			}
			$documentManager = $this->documentManagerFactory->create($instanceIdentifier);
			$couchDbHelper->createDatabaseIfNotExists($documentManager, $instanceConfiguration['databaseName']);
		}

		$couchDbHelper->createOrUpdateDesignDocuments();
	}

	/**
	 * Clean up database after running tests
	 */
	public function tearDown() {
		parent::tearDown();

		$documentManagers = $this->documentManagerFactory->getInstantiatedDocumentManagers();
		/** @var \Doctrine\ODM\CouchDB\DocumentManager $documentManager */
		foreach ($documentManagers as $documentManager) {
			$documentManager->getHttpClient()->request('DELETE', '/' . $documentManager->getCouchDBClient()->getDatabase());
		}
	}

	/**
	 * @return \Doctrine\ODM\CouchDB\DocumentManager
	 */
	protected function getDefaultDocumentManager() {
		$documentManagers = $this->documentManagerFactory->getInstantiatedDocumentManagers();
		return $documentManagers['default'];
	}

}

?>