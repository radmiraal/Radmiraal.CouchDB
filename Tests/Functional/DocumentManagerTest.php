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
 *
 */
class DocumentManagerTest extends AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function checkIfHttpClientIsInstantiated() {
		$this->assertInstanceOf('Doctrine\CouchDB\HTTP\SocketClient', $this->httpClient);
	}

	/**
	 * @test
	 */
	public function checkIfDatabaseIsCreated() {
		$res = $this->httpClient->request('GET', '/' . $this->settings['databaseName']);
		$this->assertEquals(200, $res->status);
	}

	/**
	 * @test
	 */
	public function doctrineOdmAnnotationsCanBeLoaded() {
		$annotation = new \Doctrine\ODM\CouchDB\Mapping\Annotations\Document();
		$this->assertInstanceOf('Doctrine\ODM\CouchDB\Mapping\Annotations\Document', $annotation);
	}

}

?>