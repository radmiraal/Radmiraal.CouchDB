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

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Aspect
 */
class PersistenceManagerAspect {

	/**
	 * @var \Doctrine\ODM\CouchDB\DocumentManager
	 */
	protected $documentManager;

	/**
	 * @var \Radmiraal\CouchDB\Persistence\DocumentManagerFactory
	 */
	protected $documentManagementFactory;

	/**
	 * @param \Radmiraal\CouchDB\Persistence\DocumentManagerFactory $documentManagerFactory
	 * @return void
	 */
	public function injectDocumentManagerFactory(\Radmiraal\CouchDB\Persistence\DocumentManagerFactory $documentManagerFactory) {
		$this->documentManagementFactory = $documentManagerFactory;
		$this->documentManager = $this->documentManagementFactory->create();
	}

	/**
	 * @Flow\Around("within(TYPO3\Flow\Persistence\PersistenceManagerInterface) && method(.*->convertObjectToIdentityArray())")
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint The current join point
	 * @throws \TYPO3\Flow\Persistence\Exception\UnknownObjectException
	 * @return array
	 */
	public function convertDocumentToIdentityArray(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		try {
			$identityArray = $joinPoint->getAdviceChain()->proceed($joinPoint);
			return $identityArray;
		} catch (\TYPO3\Flow\Persistence\Exception\UnknownObjectException $exception) {
			$object = $joinPoint->getMethodArgument('object');
			if (method_exists($object, 'getId')) {
				$objectIdentifier = $object->getId();
				if (!empty($objectIdentifier)) {
					return array('__identity' => $objectIdentifier);
				}
			}
		}
		throw new \TYPO3\Flow\Persistence\Exception\UnknownObjectException('The given object is unknown to the Persistence Manager.', 1302628242);
	}

	/**
	 * @Flow\Around("within(TYPO3\Flow\Persistence\PersistenceManagerInterface) && method(.*->getIdentifierByObject())")
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint The current join point
	 * @return string
	 */
	public function getIdentifierByObject(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$identifier = $joinPoint->getAdviceChain()->proceed($joinPoint);

		if ($identifier === NULL) {
			if (method_exists($joinPoint->getMethodArgument('object'), 'getId')) {
				return $joinPoint->getMethodArgument('object')->getId();
			}
		}

		return $identifier;
	}

	/**
	 * @Flow\Around("within(TYPO3\Flow\Persistence\PersistenceManagerInterface) && method(.*->getObjectByIdentifier())")
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint The current join point
	 * @return object
	 */
	public function getObjectByIdentifier(\TYPO3\Flow\Aop\JoinPointInterface $joinPoint) {
		$object = $joinPoint->getAdviceChain()->proceed($joinPoint);

		if ($object === NULL) {
			try {
				$document = $this->documentManager->find($joinPoint->getMethodArgument('objectType'), $joinPoint->getMethodArgument('identifier'));
				if ($document !== NULL) {
					return $document;
				}
			} catch (\Doctrine\ODM\CouchDB\Mapping\MappingException $exception) {
				// probably not a valid document, so ignore it
			}
		}

		return $object;
	}

}

?>