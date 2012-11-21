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
 *
 */
abstract class AbstractDocument {

	/**
	 * @var string
	 */
	protected $_id;

	/**
	 * @var string
	 */
	protected $_rev;

	/**
	 * @var string
	 */
	protected $persistence_objectType;

	/**
	 * @param array $data
	 */
	public function __construct(array $data = NULL) {
		if (!isset($data['persistence_objectType'])) {
			$this->persistence_objectType = get_class($this);
		}

		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$this->$key = $value;
			}
		}
	}

	/**
	 * @return string
	 */
	public function getDocumentId() {
		return $this->_id;
	}

	/**
	 * @return string
	 */
	public function getDocumentRevision() {
		return $this->_rev;
	}

	/**
	 * @return string
	 */
	public function getDocumentType() {
		return $this->persistence_objectType;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name) {
		if (isset($this->$name)) {
			return $this->$name;
		}
		return NULL;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function __set($name, $value) {
		if (substr($name, 0, 1) !== '_') {
			$this->$name = $value;
		}
	}

	/**
	 * Converts the object to an array
	 *
	 * @return array
	 */
	public function __toArray() {
		return get_object_vars($this);
	}

	/**
	 * Converts the object to a string representation (in JSON format)
	 * @return string
	 */
	public function __toString() {
		$values = array();
		foreach (get_object_vars($this) as $property => $value) {
			if (substr($property, 0, 1) !== '_' || !empty($value)) {
				$values[$property] = $value;
			}
		}
		return json_encode((object)$values);
	}

	/**
	 * Magic get* / set* method
	 *
	 * @param string $method
	 * @param array $arguments
	 * @return mixed
	 */
	function __call($method, array $arguments) {
		if (strlen($method) <= 3) {
			return NULL;
		}

		$methodName = '__' . substr($method, 0, 3);

		if ($methodName !== '__set' && $methodName !== '__get') {
			return NULL;
		}

		$var = lcfirst(substr($method, 3));
		return call_user_func_array(array($this, $methodName), array_merge(array($var), $arguments));
	}

}

?>