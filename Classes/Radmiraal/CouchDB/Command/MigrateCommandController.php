<?php
namespace Radmiraal\CouchDB\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Radmiraal.CouchDB".     *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * migrate command controller for the Radmiraal.CouchDB package
 *
 * @Flow\Scope("singleton")
 */
class MigrateCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @var \Radmiraal\CouchDB\CouchDBHelper
	 * @Flow\Inject
	 */
	protected $couchDbHelper;

	/**
	 * @param string $documentName
	 * @return void
	 */
	public function designsCommand($documentName = NULL) {
		$this->couchDbHelper->createDatabasesIfNotExist();

		$changesFound = FALSE;

		// TODO: make sure the migrations are database dependent
		foreach ($this->couchDbHelper->getAllDocumentManagers() as $documentManager) {
			$result = $this->couchDbHelper->createOrUpdateDesignDocuments($documentManager, $documentName === NULL ? array() : array($documentName));

			foreach ($result['success'] as $documentName) {
				$this->outputLine('Succesfully updated: %s', array($documentName));
				$changesFound = TRUE;
			}
			foreach ($result['error'] as $documentName => $reason) {
				$this->outputLine('Error updating %s: %s', array($documentName, $reason));
				$changesFound = TRUE;
			}
		}

		if (!$changesFound) {
			$this->outputLine('No changes found; nothing to do.');
		}
	}

}
