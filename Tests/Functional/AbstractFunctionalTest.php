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

/**
 * Abstract functional test class, setting up a DocumentManager and httpClient
 * object for usage in functional tests.
 */
abstract class AbstractFunctionalTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var \Doctrine\CouchDB\HTTP\SocketClient
	 */
	protected $httpClient;

	/**
	 * @var \Radmiraal\CouchDB\Persistence\DocumentManagerFactory
	 */
	protected $documentManagerFactory;

	/**
	 * @var \Doctrine\ODM\CouchDB\DocumentManager
	 */
	protected $documentManager;

	/**
	 * @var string
	 */
	protected $databaseName = 'doctrine_sandbox';

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * Set up test
	 */
	public function setUp() {
		parent::setUp();

		$configurationManager = $this->objectManager->get('TYPO3\Flow\Configuration\ConfigurationManager');
		$this->settings = $this->objectManager->getSettingsByPath(array('Radmiraal', 'CouchDB', 'persistence', 'backendOptions'));

		$this->httpClient = new \Doctrine\CouchDB\HTTP\SocketClient(
			$this->settings['host'],
			$this->settings['port'],
			$this->settings['username'],
			$this->settings['password'],
			$this->settings['ip']
		);

		$this->httpClient->request('PUT', '/' . $this->settings['databaseName']);

		$this->documentManagerFactory = $this->objectManager->get('\Radmiraal\CouchDB\Persistence\DocumentManagerFactory');
		$this->documentManager = $this->documentManagerFactory->create();

		$couchDbHelper = new \Radmiraal\CouchDB\CouchDBHelper();
		$couchDbHelper->injectSettings($this->objectManager->getSettingsByPath(array('Radmiraal', 'CouchDB')));
		$couchDbHelper->injectDocumentManagerFactory($this->documentManagerFactory);
	}

	/**
	 * Clean up database after running tests
	 */
	public function tearDown() {
		$this->httpClient->request('DELETE', '/' . $this->settings['databaseName']);
	}

}

?>