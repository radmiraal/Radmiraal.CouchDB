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
	 * @var \Radmiraal\CouchDB\Persistence\DocumentManagerFactory
	 */
	protected $documentManagementFactory;

	/**
	 * @var \Doctrine\ODM\CouchDB\DocumentManager
	 */
	protected $documentManager;

	/**
	 * @var array
	 */
	protected $settings;

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
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings['persistence']['backendOptions'];
	}

	/**
	 * @param \Radmiraal\CouchDB\Persistence\DocumentManagerFactory $documentManagerFactory
	 * @return void
	 */
	public function injectDocumentManagerFactory(\Radmiraal\CouchDB\Persistence\DocumentManagerFactory $documentManagerFactory) {
		$this->documentManagementFactory = $documentManagerFactory;
		$this->documentManager = $this->documentManagementFactory->create();
	}

	/**
	 * @param string $databaseName
	 * @return void
	 */
	public function createDatabaseIfNotExists($databaseName = NULL) {
		if ($databaseName === NULL) {
			$databaseName = $this->settings['databaseName'];
		}
		if($this->documentManager->getHttpClient()->request('GET', '/' . $databaseName)->status === 404) {
			$this->documentManager->getHttpClient()->request('PUT', '/' . $databaseName);
		}
	}

	/**
	 * @return array
	 */
	public function createOrUpdateDesignDocuments() {
		$result = array('success' => array(), 'error' => array());

		$designDocumentNames = $this->documentManager->getConfiguration()->getDesignDocumentNames();

		foreach ($designDocumentNames as $docName) {
			$designDocData = $this->documentManager->getConfiguration()->getDesignDocument($docName);

			$localDesignDoc = new $designDocData['className']($designDocData['options']);
			$localDocBody = $localDesignDoc->getData();

			$remoteDocBody = $this->documentManager->getCouchDBClient()->findDocument('_design/' . $docName)->body;
			if ($this->isMissingOrDifferent($localDocBody, $remoteDocBody)) {
				$response = $this->documentManager->getCouchDBClient()->createDesignDocument($docName, $localDesignDoc);

				if ($response->status < 300) {
					$result['success'][] = $docName;
				} else {
					$result['error'][$docName] = $response->body['reason'];
				}
			}
		}

		return $result;
	}

	/**
	 * @return \Doctrine\CouchDB\CouchDBClient
	 */
	public function getClient() {
		return $this->documentManager->getCouchDBClient();
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
			$this->documentManager->flush();
		} catch (\Exception $exception) {
			$this->systemLogger->log('Could not flush ODM unit of work, error: ' . $exception->getMessage());
		}
	}

}

?>