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

/**
 *
 */
class RepositoryTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var \Radmiraal\CouchDB\Tests\Functional\Fixtures\Repository\TestDocumentRepository
	 */
	protected $testRepository;

	/**
	 * @var \Radmiraal\CouchDB\Persistence\PersistenceManager
	 */
	protected $persistenceManager;

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

		$this->testRepository = $this->objectManager->get('Radmiraal\CouchDB\Tests\Functional\Fixtures\Repository\TestDocumentRepository');
		$this->inject($this->testRepository, 'persistenceManager', $this->persistenceManager);
	}

	/**
	 * @test
	 */
	public function getDocumentClassNameReturnsTheCorrectDocumentClassName() {
		$this->assertEquals(
			'Radmiraal\CouchDB\Tests\Functional\Fixtures\Model\TestDocument',
			$this->testRepository->getDocumentClassName()
		);
	}

	/**
	 * @test
	 * @expectedException \Radmiraal\CouchDB\Exception
	 */
	public function addingADocumentToTheRepositoryThrowsAnErrorIfItHasAWrongType() {
		$document = new \Radmiraal\CouchDB\Document();
		$this->testRepository->add($document);
	}

	/**
	 * @test
	 * @expectedException \Radmiraal\CouchDB\Exception
	 */
	public function updatingADocumentToTheRepositoryThrowsAnErrorIfItHasAWrongType() {
		$document = new \Radmiraal\CouchDB\Document();
		$this->testRepository->update($document);
	}

	/**
	 * @test
	 * @expectedException \Radmiraal\CouchDB\Exception
	 */
	public function removingADocumentToTheRepositoryThrowsAnErrorIfItHasAWrongType() {
		$document = new \Radmiraal\CouchDB\Document();
		$this->testRepository->remove($document);
	}

	/**
	 * @test
	 */
	public function aDocumentCanBeAddedPersistedAndRetrieved() {
		$document = new \Radmiraal\CouchDB\Tests\Functional\Fixtures\Model\TestDocument(array(
			'_id' => 'some-object',
			'title' => 'test'
		));

		$this->testRepository->add($document);

		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		$testObject = $this->testRepository->findByUid('some-object');
		$this->assertEquals('test', $testObject->getTitle());
	}

	/**
	 * @test
	 */
	public function aDocumentCanBeUpdated() {
		$document = new \Radmiraal\CouchDB\Tests\Functional\Fixtures\Model\TestDocument(array(
			'_id' => 'some-object-to-update',
			'title' => 'test'
		));

		$this->testRepository->add($document);

		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		$document = $this->testRepository->findByUid('some-object-to-update');
		$document->setTitle('Some other title');

		$this->testRepository->update($document);

		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		$testObject = $this->testRepository->findByUid('some-object-to-update');
		$this->assertEquals('Some other title', $testObject->getTitle());
	}

	/**
	 * @test
	 */
	public function aDocumentCanBeRemoved() {
		$document = new \Radmiraal\CouchDB\Tests\Functional\Fixtures\Model\TestDocument(array(
			'_id' => 'some-object-to-be-removed'
		));

		$this->testRepository->add($document);

		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		$testObject = $this->testRepository->findByUid('some-object-to-be-removed');
		$this->assertNotNull($testObject);
		$this->testRepository->remove($testObject);

		$this->persistenceManager->persistAll();
		$this->persistenceManager->clearState();

		$testObject = $this->testRepository->findByUid('some-object-to-be-removed');
		$this->assertNull($testObject);
	}

}

?>