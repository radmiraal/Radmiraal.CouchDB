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
class BasicPersistenceTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var string
	 */
	protected $dataSourceName = 'http://127.0.0.1:5984';

	/**
	 * @var string
	 */
	protected $databaseName = 'radmiraal-couchdb-test';

	/**
	 * @var \TYPO3\CouchDB\Client
	 */
	protected $client;

	public function setUp() {
		parent::setUp();

		$this->client = new \TYPO3\CouchDB\Client($this->dataSourceName);
		$this->client->setDatabaseName($this->databaseName);

		if (!$this->client->databaseExists($this->databaseName)) {
			$this->client->createDatabase($this->databaseName);
		}
	}

	public function tearDown() {
		if ($this->client->databaseExists($this->databaseName)) {
			$this->client->deleteDatabase($this->databaseName);
		}
	}

	/**
	 * @test
	 */
	public function aDocumentCanBePersistedAndRetrieved() {
		$testObject = (object)array(
			'foo' => 'bar',
			'baz' => array('test')
		);

		$result = $this->client->createDocument($testObject);
		$this->assertTrue($result->isSuccess());

		$matchValue = $this->client->getDocument($result->getId());

		$this->assertEquals($testObject->foo, $matchValue->foo);
		$this->assertEquals($testObject->baz, $matchValue->baz);
	}

}

?>