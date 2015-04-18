<?php
namespace Radmiraal\CouchDB\Testing\Behavior;

trait CouchDBContextTrait {

	/**
	 * @BeforeFeature
	 */
	static public function createCouchDBDatabasesBeforeFeature() {
		$couchDBHelper = new \Radmiraal\CouchDB\CouchDBHelper();
		$couchDBHelper->createDatabasesIfNotExist();
		$couchDBHelper->createOrUpdateDesignDocuments();
	}

	/**
	 * @AfterFeature
	 */
	static public function removeCouchDBDatabasesAfterFeature() {
		self::removeCouchDBDatabases();
	}

	/**
	 * @return void
	 * @BeforeScenario @removeCouchDBDatabases
	 */
	static public function removeCouchDBDatabases() {
		$couchDBHelper = new \Radmiraal\CouchDB\CouchDBHelper();
		/** @var \Doctrine\ODM\CouchDB\DocumentManager $documentManager */
		foreach ($couchDBHelper->getAllDocumentManagers() as $documentManager) {
			$couchDBHelper->deleteDatabaseIfExists($documentManager, $documentManager->getCouchDBClient()->getDatabase());
		}
	}

	/**
	 * @BeforeFeature
	 */
	static public function createCouchDBDatabases() {
		$couchDBHelper = new \Radmiraal\CouchDB\CouchDBHelper();
		$couchDBHelper->createDatabasesIfNotExist();
		$couchDBHelper->createOrUpdateDesignDocuments();
	}

	/**
	 * @BeforeScenario @resetCouchDBDatabases
	 */
	static public function resetCouchDBDatabases() {
		self::removeCouchDBDatabases();
		self::createCouchDBDatabases();
	}

}
