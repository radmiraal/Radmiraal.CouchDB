<?php
namespace Radmiraal\CouchDB;

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
 * @Flow\Scope("singleton")
 */
class CouchDBHelper {

	/**
	 * @Flow\Inject
	 * @var \Radmiraal\CouchDB\Persistence\DocumentManagerFactory
	 */
	protected $documentManagerFactory;

	/**
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface $systemLogger
	 */
	protected $systemLogger;

	/**
	 * @param \TYPO3\Flow\Log\SystemLoggerInterface $systemLogger
	 * @return void
	 */
	public function injectSystemLogger(\TYPO3\Flow\Log\SystemLoggerInterface $systemLogger) {
		$this->systemLogger = $systemLogger;
	}

	/**
	 * @param string $databaseName
	 * @param \Doctrine\ODM\CouchDB\DocumentManager $documentManager
	 * @return void
	 */
	public function createDatabaseIfNotExists(\Doctrine\ODM\CouchDB\DocumentManager $documentManager, $databaseName) {
		if($documentManager->getHttpClient()->request('GET', '/' . $databaseName)->status === 404) {
			$documentManager->getHttpClient()->request('PUT', '/' . $databaseName);
		}
	}

	/**
	 * @return array<\Doctrine\ODM\CouchDB\DocumentManager>
	 */
	public function getAllDocumentManagers() {
		$this->documentManagerFactory->instantiateAllDocumentManagersFromConfiguration();
		return $this->documentManagerFactory->getInstantiatedDocumentManagers();
	}

	/**
	 * @return void
	 */
	public function createDatabasesIfNotExist() {
		foreach ($this->getAllDocumentManagers() as $documentManager) {
			$databaseName = $this->getClient($documentManager)->getDatabase();
			if (!empty($databaseName)) {
				$this->createDatabaseIfNotExists($documentManager, $databaseName);
			}
		}
	}

	/**
	 * @param string $databaseName
	 * @param \Doctrine\ODM\CouchDB\DocumentManager $documentManager
	 * @return void
	 */
	public function deleteDatabaseIfExists(\Doctrine\ODM\CouchDB\DocumentManager $documentManager, $databaseName) {
		if($documentManager->getHttpClient()->request('GET', '/' . $databaseName)->status === 200) {
			$documentManager->getHttpClient()->request('DELETE', '/' . $databaseName);
		}
	}

	/**
	 * @param \Doctrine\ODM\CouchDB\DocumentManager $documentManager
	 * @return array
	 */
	public function createOrUpdateDesignDocuments(\Doctrine\ODM\CouchDB\DocumentManager $documentManager = NULL) {
		if ($documentManager === NULL) {
			$documentManagers = $this->getAllDocumentManagers();
		} else {
			$documentManagers = array($documentManager);
		}

		$result = array('success' => array(), 'error' => array());

		foreach ($documentManagers as $documentManager) {
			$designDocumentNames = $documentManager->getConfiguration()->getDesignDocumentNames();

			foreach ($designDocumentNames as $docName) {
				$designDocData = $documentManager->getConfiguration()->getDesignDocument($docName);

				$localDesignDoc = new $designDocData['className']($designDocData['options']);
				$localDocBody = $localDesignDoc->getData();

				$remoteDocBody = $documentManager->getCouchDBClient()->findDocument('_design/' . $docName)->body;
				if ($this->isMissingOrDifferent($localDocBody, $remoteDocBody)) {
					$response = $documentManager->getCouchDBClient()->createDesignDocument($docName, $localDesignDoc);

					if ($response->status < 300) {
						$result['success'][] = $docName;
					} else {
						$result['error'][$docName] = $response->body['reason'];
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param \Doctrine\ODM\CouchDB\DocumentManager $documentManager
	 * @return \Doctrine\CouchDB\CouchDBClient
	 */
	public function getClient(\Doctrine\ODM\CouchDB\DocumentManager $documentManager) {
		return $documentManager->getCouchDBClient();
	}

	/**
	 * @param array $local
	 * @param array $remote
	 * @return boolean
	 */
	protected function isMissingOrDifferent(array $local, array $remote) {
		if (is_null($remote) || (isset($remote['error']) && $remote['error'] == 'not_found')) {
			return TRUE;
		}
		foreach ($local as $key => $val) {
			if (!isset($remote[$key]) || $remote[$key] != $val) {
				return TRUE;
			}
			unset($remote[$key]);
		}
			// If any items remain (excluding _id and _rev) the remote is different.
		if (count($remote) > 2) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @return void
	 */
	public function flush() {
		try {
			$documentManagers = $this->documentManagerFactory->getInstantiatedDocumentManagers();
			if (is_array($documentManagers)) {
				foreach ($this->documentManagerFactory->getInstantiatedDocumentManagers() as $documentManager) {
					$documentManager->flush();
				}
			}
		} catch (\Exception $exception) {
			$this->systemLogger->log('Could not flush ODM unit of work, error: ' . $exception->getMessage());
		}
	}

}
