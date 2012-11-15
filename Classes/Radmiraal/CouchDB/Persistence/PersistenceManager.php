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
 * @Flow\Scope("prototype")
 */
class PersistenceManager extends \TYPO3\Flow\Persistence\AbstractPersistenceManager implements \TYPO3\Flow\Persistence\PersistenceManagerInterface {

	/**
	 * @var \TYPO3\CouchDB\Client
	 */
	protected $client;

	/**
	 * Create new instance
	 */
	public function __construct() {
		$this->addedObjects = new \SplObjectStorage();
		$this->removedObjects = new \SplObjectStorage();
		$this->changedObjects = new \SplObjectStorage();
	}

	/**
	 * Initialize CouchDB client to use for persistence
	 *
	 * @throws \Radmiraal\CouchDB\Exception
	 * @return void
	 */
	public function initialize() {
		try {
			$this->client = new \TYPO3\CouchDB\Client($this->settings['backendOptions']['dataSourceName'], $this->settings['backendOptions']['options']);

			if (!$this->client->databaseExists($this->settings['backendOptions']['databaseName'])) {
				$this->client->createDatabase($this->settings['backendOptions']['databaseName']);
			}

			$this->client->setDatabaseName($this->settings['backendOptions']['databaseName']);
		} catch (\Exception $exception) {
			throw new \Radmiraal\CouchDB\Exception('Could not connect to CouchDB server');
		}
	}

	/**
	 * Returns the CouchDB client object in use
	 *
	 * @return \TYPO3\CouchDB\Client
	 */
	public function getClient() {
		return $this->client;
	}

	/**
	 * Update document
	 *
	 * TODO: Implement
	 * @param \Radmiraal\CouchDB\Persistence\AbstractDocument $object
	 * @return void
	 */
	public function update($object) {
		$this->validate($object);
		if (!$this->changedObjects->contains($object) && $object->getDocumentId() !== NULL) {
			$this->changedObjects->attach($object);
		}
	}

	/**
	 * Add document to the database
	 *
	 * @param \Radmiraal\CouchDB\Persistence\AbstractDocument $object
	 * @return void
	 */
	public function add($object) {
		$this->validate($object);
		if (!$this->addedObjects->contains($object)) {
			$this->addedObjects->attach($object);
		}
	}

	/**
	 * Remove document
	 *
	 * TODO: Implement
	 * @param \Radmiraal\CouchDB\Persistence\AbstractDocument $object
	 * @return void
	 */
	public function remove($object) {
		$this->validate($object);
		if (!$this->removedObjects->contains($object) && $object->getDocumentId() !== NULL) {
			$this->removedObjects->attach($object);
		}
	}

	/**
	 * Checks if an object is known to be a newly added object in the persistence manager
	 *
	 * @param \Radmiraal\CouchDB\Persistence\AbstractDocument $object
	 * @return boolean
	 */
	public function isNewObject($object) {
		$this->validate($object);
		return $this->addedObjects->contains($object);
	}

	/**
	 * Fetches a document from the database by identifier, and returns the object representation
	 *
	 * TODO: Add support for $objectType
	 * @param string $identifier
	 * @param string $objectType
	 * @param boolean $useLazyLoading Not used by this package
	 * @return \Radmiraal\CouchDB\Persistence\AbstractDocument
	 */
	public function getObjectByIdentifier($identifier, $objectType = NULL, $useLazyLoading = FALSE) {
		try {
			$document = $this->client->getDocument($identifier);
		} catch (\TYPO3\CouchDB\Client\NotFoundException $exception) {
			return NULL;
		}

		if (isset($document->persistence_objectType)) {
			return new $document->persistence_objectType((array)$document);
		}

		return new \Radmiraal\CouchDB\Document((array)$document);
	}

	/**
	 * Returns the identifier of a document. If not already set in the object
	 * it will try to search over the entire database and return the identifier
	 * of the first document matching the object.
	 *
	 * TODO: Optimize if performance issues occur
	 * @param \Radmiraal\CouchDB\Persistence\AbstractDocument $object
	 * @return string
	 */
	public function getIdentifierByObject($object) {
		$this->validate($object);

		if ($object->getDocumentId() !== NULL) {
			return $object->getDocumentId();
		}

			// Find the document in the database
		$result = $this->client->listDocuments();
		$objectArray = $object->__toArray();
		unset($objectArray['_id'], $objectArray['_rev']);
		foreach ($result->rows as $documentResult) {
			$document = $this->getObjectByIdentifier($documentResult->id);

			$matchValue = $document->__toArray();
			unset($matchValue['_id'], $matchValue['_rev']);

			if ($objectArray === $matchValue) {
				return $documentResult->id;
			}
		}

		return NULL;
	}

	/**
	 * @param string $type
	 * @return \TYPO3\Flow\Persistence\QueryInterface|void
	 */
	public function createQueryForType($type) {

	}

	/**
	 * Stores all objects known in the peristence manager and saves
	 * them to the database.
	 *
	 * @return void
	 */
	public function persistAll() {
		foreach ($this->removedObjects as $removedObject) {
			$this->client->deleteDocument($removedObject->getDocumentId(), $removedObject->getDocumentRevision());
			$this->removedObjects->detach($removedObject);
		}

		foreach ($this->changedObjects as $changedObject) {
			$this->client->updateDocument((string)$changedObject, $changedObject->getDocumentId());
			$this->changedObjects->detach($changedObject);
		}

		foreach ($this->addedObjects as $addedObject) {
			$this->client->createDocument((string)$addedObject);
			$this->addedObjects->detach($addedObject);
		}
	}

	/**
	 * Validates if the object is manageable by CouchDB
	 *
	 * @param $object
	 * @throws \Radmiraal\CouchDB\Exception if the object is not manageable
	 * @return void
	 */
	protected function validate($object) {
		if (!$object instanceof \Radmiraal\CouchDB\Persistence\AbstractDocument) {
			throw new \Radmiraal\CouchDB\Exception('Object is not a valid document');
		}
	}
}

?>