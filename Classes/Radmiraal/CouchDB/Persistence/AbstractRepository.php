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
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 * @Flow\Inject
	 */
	protected $reflectionService;

	/**
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	 * @Flow\Inject
	 * @var \Radmiraal\CouchDB\Persistence\DocumentManagerInterface
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
	 * @var array
	 */
	protected $defaultOrderings = array();

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

		$this->documentManager->merge($object);
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
		$objects = $this->getBackend()->findAll();
		foreach ($objects as $object) {
			$this->remove($object);
		}
	}

	/**
	 * @return array
	 */
	public function findAll() {
		return $this->getBackend()->findAll();
	}

	/**
	 * @param string $identifier
	 * @return object
	 */
	public function findByIdentifier($identifier) {
		return $this->getBackend()->find($identifier);
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
		return count($this->getBackend()->findAll());
	}

	public function __call($methodName, $arguments) {
		if (substr($methodName, 0, 6) === 'findBy') {
			$propertyName = lcfirst(substr($methodName, 6));
			return $this->getBackend()->findBy(array($propertyName => $this->getQueryMatchValue($arguments[0])));
		} elseif (substr($methodName, 0, 9) === 'findOneBy') {
			$propertyName = lcfirst(substr($methodName, 9));
				// Use findBy instead of findOneBy as that method does not use a limit
			$result = $this->getBackend()->findBy(array($propertyName => $this->getQueryMatchValue($arguments[0])), NULL, 1);
			if (count($result) === 1) {
				return $result[0];
			}
			return NULL;
		} elseif (substr($methodName, 0, 7) === 'countBy') {
			$propertyName = lcfirst(substr($methodName, 7));
			$result = $this->getBackend()->findBy(array($propertyName => $this->getQueryMatchValue($arguments[0])));
			return count($result);
		}
	}

	/**
	 * @param mixed $value
	 * @return mixed object
	 */
	protected function getQueryMatchValue($value) {
		if (is_object($value)
			&& $this->reflectionService->isClassAnnotatedWith(get_class($value), 'TYPO3\Flow\Annotations\Entity')) {
				return $this->persistenceManager->getIdentifierByObject($value);
		}
		return $value;
	}

	/**
	 * @param string $designDocumentName
	 * @param string $constraint
	 * @param string $type
	 * @return \Doctrine\CouchDB\View\AbstractQuery
	 */
	public function createQuery($designDocumentName = 'doctrine_repositories', $constraint = 'equal_constraint', $type = NULL) {
		switch($type) {
			case 'lucene':
				return $this->documentManager->createLuceneQuery($designDocumentName, $constraint);
			case 'native':
				return $this->documentManager->createNativeQuery($designDocumentName, $constraint);
			default:
				return $this->documentManager->createQuery($designDocumentName, $constraint);
		}
	}

	public function setDefaultOrderings(array $defaultOrderings) {
	}

	/**
	 * Convenience method for flushing the document manager
	 *
	 * @return void
	 */
	public function flushDocumentManager() {
		$this->documentManager->flush();
	}

	/**
	 * @return \Doctrine\ODM\CouchDB\DocumentRepository
	 */
	protected function getBackend() {
		return $this->documentManager->getRepository($this->getEntityClassName());
	}

}
