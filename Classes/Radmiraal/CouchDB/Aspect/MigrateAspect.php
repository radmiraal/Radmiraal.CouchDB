<?php
namespace Radmiraal\CouchDB\Aspect;

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
 * @Flow\Aspect
 */
class MigrateAspect {

	/**
	 * @var \Radmiraal\CouchDB\CouchDBHelper
	 * @Flow\Inject
	 */
	protected $couchDbHelper;

	/**
	 * @Flow\AfterReturning("method(TYPO3\Flow\Command\DoctrineCommandController->migrateCommand())")
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint The current join point
	 * @return void
	 */
	public function callMigrateCommand(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
			// TODO: Make this less magically quiet and stuff like that ;-)
		$this->couchDbHelper->createDatabaseIfNotExists();
		$this->couchDbHelper->createOrUpdateDesignDocuments(array());
	}

}

?>