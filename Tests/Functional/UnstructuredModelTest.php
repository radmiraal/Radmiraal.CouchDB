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
class UnstructuredModelTest extends AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function aModelExtendingTheAbstractModelCanBePersisted() {
		$model = new \Radmiraal\CouchDB\Tests\Functional\Fixtures\Domain\Model\UnstructuredModel(array(
			'title' => 'Foo'
		));

		$this->getDefaultDocumentManager()->persist($model);
		$this->getDefaultDocumentManager()->flush();

		$result = $this->getDefaultDocumentManager()->getRepository('\Radmiraal\CouchDB\Tests\Functional\Fixtures\Domain\Model\UnstructuredModel')->findAll();

		$this->assertEquals(1, count($result));
		$this->assertInstanceOf('Radmiraal\CouchDB\Tests\Functional\Fixtures\Domain\Model\UnstructuredModel', $result[0]);
		$this->assertEquals('Foo', $result[0]->getTitle());
	}

}

?>