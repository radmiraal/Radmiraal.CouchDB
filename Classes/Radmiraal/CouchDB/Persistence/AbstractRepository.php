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

use \TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
abstract class AbstractRepository implements \TYPO3\Flow\Persistence\RepositoryInterface {

	/**
	 * @var \Radmiraal\CouchDB\Persistence\PersistenceManager
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	public function __construct() {
		if (static::ENTITY_CLASSNAME === NULL) {
			$this->objectType = str_replace(array('\\Repository\\', 'Repository'), array('\\Model\\', ''), get_class($this));
		} else {
			$this->objectType = static::ENTITY_CLASSNAME;
		}
	}

	/**
	 * @return string
	 */
	public function getEntityClassName() {
		return $this->getDocumentClassName();
	}

	/**
	 * @return string
	 */
	public function getDocumentClassName() {
		return $this->objectType;
	}

	/**
	 * @param object $object
	 * @throws \Radmiraal\CouchDB\Exception
	 */
	public function add($object) {
		if (get_class($object) !== $this->getDocumentClassName()) {
			throw new \Radmiraal\CouchDB\Exception('Wrong document class');
		}
		$this->persistenceManager->add($object);
	}

	public function findByUid($uid) {
		return $this->persistenceManager->getObjectByIdentifier($uid);
	}

	public function remove($object) {
		if (get_class($object) !== $this->getDocumentClassName()) {
			throw new \Radmiraal\CouchDB\Exception('Wrong document class');
		}
		$this->persistenceManager->remove($object);
	}

	public function update($object) {
		if (get_class($object) !== $this->getDocumentClassName()) {
			throw new \Radmiraal\CouchDB\Exception('Wrong document class');
		}
		$this->persistenceManager->update($object);
	}

	public function countAll() {

	}

	public function findAll() {

	}

	public function removeAll() {

	}

	public function __call($method, $arguments) {

	}

	public function createQuery() {

	}

	public function setDefaultOrderings(array $defaultOrderings) {

	}

	public function findByIdentifier($identifier) {
		return $this->persistenceManager->getObjectByIdentifier($identifier);
	}
}

?>