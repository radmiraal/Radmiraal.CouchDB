<?php
namespace Radmiraal\CouchDB\Tests\Functional\Persistence;

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

use \TYPO3\Flow\Annotations as Flow;

/**
 *
 */
class PersistenceManagerTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var \Radmiraal\CouchDB\Persistence\PersistenceManager
	 */
	protected $persistenceManager;

	/**
	 * @var array
	 */
	protected $settings;

	public function setUp() {
		parent::setUp();
		$this->persistenceManager = $this->objectManager->get('\Radmiraal\CouchDB\Persistence\PersistenceManager');

		$configurationManager = $this->objectManager->get('TYPO3\Flow\Configuration\ConfigurationManager');
		$this->settings = $this->objectManager->getSettingsByPath(array('Radmiraal', 'CouchDB'));

		$this->inject($this->persistenceManager, 'settings', $this->settings);

		try {
			$this->persistenceManager->initialize();
		} catch (\Radmiraal\CouchDB\Exception $exception) {
			$this->markTestSkipped('No connection to CouchDB');
		}
	}

	public function tearDown() {
		$this->persistenceManager->getClient()->deleteDatabase($this->settings['persistence']['backendOptions']['databaseName']);
	}

	/**
	 * @test
	 * @expectedException \Radmiraal\CouchDB\Exception
	 */
	public function initializeThrowsExceptionIfConnectionFails() {
			// Inject incorrect settings
		$this->inject($this->persistenceManager, 'settings', array(
			'persistence' => array(
				'backendOptions' => array(
					'databaseName' => '',
					'dataSourceName' => 'http://1.1.1.1:9999'
				)
			)
		));

		$this->persistenceManager->initialize();
	}

	/**
	 * @test
	 * @expectedException \Radmiraal\CouchDB\Exception
	 */
	public function persistenceManagerAddMethodDoesNotAcceptObjectsThatDontExtendAbstractDocuments() {
		$this->persistenceManager->add((object)array());
	}

	/**
	 * @test
	 * @expectedException \Radmiraal\CouchDB\Exception
	 */
	public function persistenceManagerRemoveMethodDoesNotAcceptObjectsThatDontExtendAbstractDocuments() {
		$this->persistenceManager->remove((object)array());
	}

	/**
	 * @test
	 * @expectedException \Radmiraal\CouchDB\Exception
	 */
	public function persistenceManagerUpdateMethodDoesNotAcceptObjectsThatDontExtendAbstractDocuments() {
		$this->persistenceManager->update((object)array());
	}

	/**
	 * @test
	 */
	public function aDocumentCanBePersistedAndRetrieved() {
		$testObject = new \Radmiraal\CouchDB\Document();
		$testObject->foo = 'bar';
		$testObject->baz = array('test');

		$this->persistenceManager->add($testObject);

		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		$identifier = $this->persistenceManager->getIdentifierByObject($testObject);

		$this->assertNotNull($identifier);

		$matchObject = $this->persistenceManager->getObjectByIdentifier($identifier);
		$this->assertNotNull($matchObject->getDocumentId());
		$this->assertNotNull($matchObject->getDocumentRevision());
		$this->assertNotNull($matchObject->getDocumentType());
		$this->assertEquals($testObject->foo, $matchObject->foo);
		$this->assertEquals($testObject->baz, $matchObject->baz);
	}

	/**
	 * @test
	 */
	public function anObjectCanBeRemovedFromTheDatabase() {
		$testObject = new \Radmiraal\CouchDB\Document(array('_id' => 'object-to-be-removed'));
		$this->persistenceManager->add($testObject);

		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		$matchObject = $this->persistenceManager->getObjectByIdentifier('object-to-be-removed');

		$this->assertEquals('Radmiraal\\CouchDB\\Document', $matchObject->getDocumentType());

		$this->persistenceManager->remove($matchObject);
		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		$matchObject = $this->persistenceManager->getObjectByIdentifier('object-to-be-removed');

		$this->assertNull($matchObject);
	}

	/**
	 * @test
	 */
	public function anObjectCanBeUpdatedAndRetreivedCorrectly() {
		$this->persistenceManager->add(
			new \Radmiraal\CouchDB\Document(array(
				'_id' => 'object-to-be-updated',
				'title' => 'Some title'
			))
		);

		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		$matchObject = $this->persistenceManager->getObjectByIdentifier('object-to-be-updated');

		$this->assertEquals('Some title', $matchObject->getTitle('foo', 'bar'));

		$matchObject->setTitle('Some other title');
		$this->persistenceManager->update($matchObject);

		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		$secondMatchObject = $this->persistenceManager->getObjectByIdentifier('object-to-be-updated');
		$this->assertEquals('Some other title', $secondMatchObject->getTitle());
	}

}

?>