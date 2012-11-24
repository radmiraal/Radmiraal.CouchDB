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

use TYPO3\Flow\Annotations as Flow;

/**
 *
 */
abstract class AbstractRepository implements \TYPO3\Flow\Persistence\RepositoryInterface {

	/**
	 * @var \Doctrine\ODM\CouchDB\DocumentRepository
	 */
	protected $backend;

	/**
	 * @var \Doctrine\ODM\CouchDB\DocumentManager
	 */
	protected $documentManager;

	/**
	 * Warning: if you think you want to set this,
	 * look at RepositoryInterface::ENTITY_CLASSNAME first!
	 *
	 * @var string
	 */
	protected $entityClassName;

	/**
	 * @var \Radmiraal\CouchDB\Persistence\DocumentManagerFactory
	 */
	protected $documentManagementFactory;

	/**
	 * @var array
	 */
	protected $defaultOrderings = array();

	/**
	 * @param \Radmiraal\CouchDB\Persistence\DocumentManagerFactory $documentManagerFactory
	 * @return void
	 */
	public function injectDocumentManagerFactory(\Radmiraal\CouchDB\Persistence\DocumentManagerFactory $documentManagerFactory) {
		$this->documentManagementFactory = $documentManagerFactory;
		$this->documentManager = $this->documentManagementFactory->create();
		$this->backend = $this->documentManager->getRepository($this->getEntityClassName());
	}

	/**
	 * Initializes a new Repository.
	 */
	public function __construct() {
		if (static::ENTITY_CLASSNAME === NULL) {
			$this->entityClassName = preg_replace(array('/\\\Repository\\\/', '/Repository$/'), array('\\Model\\', ''), get_class($this));
		} else {
			$this->entityClassName = static::ENTITY_CLASSNAME;
		}
	}

	/**
	 * @param object $object
	 * @throws \TYPO3\Flow\Persistence\Exception\IllegalObjectTypeException
	 * @return void
	 */
	public function add($object) {
		if (!is_object($object) || !($object instanceof $this->entityClassName)) {
			$type = (is_object($object) ? get_class($object) : gettype($object));
			throw new \TYPO3\Flow\Persistence\Exception\IllegalObjectTypeException('The value given to add() was ' . $type . ' , however the ' . get_class($this) . ' can only store ' . $this->entityClassName . ' instances.', 1298403438);
		}

		$this->documentManager->persist($object);
	}

	/**
	 * @param object $object
	 * @throws \TYPO3\Flow\Persistence\Exception\IllegalObjectTypeException
	 * @return void
	 */
	public function update($object) {
		if (!is_object($object) || !($object instanceof $this->entityClassName)) {
			$type = (is_object($object) ? get_class($object) : gettype($object));
			throw new \TYPO3\Flow\Persistence\Exception\IllegalObjectTypeException('The value given to update() was ' . $type . ' , however the ' . get_class($this) . ' can only store ' . $this->entityClassName . ' instances.', 1249479625);
		}

		$this->documentManager->persist($object);
	}

	/**
	 * @param object $object
	 * @throws \TYPO3\Flow\Persistence\Exception\IllegalObjectTypeException
	 * @return void
	 */
	public function remove($object) {
		if (!is_object($object) || !($object instanceof $this->entityClassName)) {
			$type = (is_object($object) ? get_class($object) : gettype($object));
			throw new \TYPO3\Flow\Persistence\Exception\IllegalObjectTypeException('The value given to remove() was ' . $type . ' , however the ' . get_class($this) . ' can only handle ' . $this->entityClassName . ' instances.', 1298403442);
		}

		$this->documentManager->remove($object);
	}

	/**
	 * @return void
	 */
	public function removeAll() {
		$objects = $this->backend->findAll();
		foreach($objects as $object) {
			$this->remove($object);
		}
	}

	/**
	 * @return array
	 */
	public function findAll() {
		return $this->backend->findAll();
	}

	/**
	 * @param string $identifier
	 * @return object
	 */
	public function findByIdentifier($identifier) {
		return $this->backend->find($identifier);
	}

	/**
	 * @return string
	 */
	public function getEntityClassName() {
		return $this->entityClassName;
	}

	/**
	 * @return integer
	 */
	public function countAll() {
		return count($this->backend->findAll());
	}

	public function __call($methodName, $arguments) {
		if (substr($methodName, 0, 6) === 'findBy') {
			$propertyName = lcfirst(substr($methodName, 6));
			return $this->backend->findBy(array($propertyName => $arguments[0]));
		} elseif (substr($methodName, 0, 9) === 'findOneBy') {
			$propertyName = lcfirst(substr($methodName, 9));
				// Use findBy instead of findOneBy as that method does not use a limit
			$result = $this->backend->findBy(array($propertyName => $arguments[0]), NULL, 1);
			if (count($result) === 1) {
				return $result[0];
			}
			return NULL;
		} elseif (substr($methodName, 0, 7) === 'countBy') {
			$propertyName = lcfirst(substr($methodName, 7));
			$result = $this->backend->findBy(array($propertyName => $arguments[0]));
			return count($result);
		}
	}

	public function createQuery() {
	}

	public function setDefaultOrderings(array $defaultOrderings) {
	}

}

?>