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

}

?>