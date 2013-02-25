<?php
namespace Radmiraal\CouchDB\Tests\Unit;

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
class DocumentTest extends \TYPO3\Flow\Tests\UnitTestCase {

	protected $document;

	public function setUp() {
		$this->document = new \Radmiraal\CouchDB\Tests\Unit\Fixtures\Document();
	}

	/**
	 * @return array
	 */
	public function propertyCollectionProvider() {
		return array(
			array('foo', 'bar'),
			array('baz', (object)array('test')),
			array('bar', array('test')),
			array('cab', new \Exception('test'))
		);
	}

	/**
	 * @test
	 * @dataProvider propertyCollectionProvider
	 */
	public function aDocumentAcceptsUnknownPropertiesByDirectAccess($property, $value) {
		$this->document->$property = $value;
		$this->assertEquals($value, $this->document->$property);
	}

	/**
	 * @test
	 * @dataProvider propertyCollectionProvider
	 */
	public function aDocumentAcceptsUnknownPropertiesByGetterSetterMethods($property, $value) {
		$setter = 'set' . ucfirst($property);
		$getter = 'get' . ucfirst($property);

		$this->document->$setter($value);
		$this->assertEquals($value, $this->document->$getter($property));
	}

}

?>