<?php
namespace Radmiraal\CouchDB\Tests\Functional;

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

use Radmiraal\CouchDB\Validation\Validator\DocumentValidator;
use TYPO3\Flow\Annotations as Flow;

/**
 *
 */
class DocumentValidatorTest extends AbstractFunctionalTest {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Validation\ValidatorResolver
	 */
	protected $validatorResolver;

	/**
	 *
	 */
	public function setUp() {
		parent::setUp();
		$this->validatorResolver = $this->objectManager->get('TYPO3\Flow\Validation\ValidatorResolver');
	}

	/**
	 * @test
	 */
	public function theDocumentValidatorIsInTheBaseValidatorConjunctionForUnstructuredModel() {
		$model = new \Radmiraal\CouchDB\Tests\Functional\Fixtures\Domain\Model\UnstructuredModel();

		$baseValidatorConjunction = $this->validatorResolver->getBaseValidatorConjunction(get_class($model));
		$documentValidatorFound = FALSE;
		$validators = $baseValidatorConjunction->getValidators();
		foreach ($validators as $validator) {
			if ($validator instanceof DocumentValidator) {
				$documentValidatorFound = TRUE;
				break;
			}
		}

		$this->assertTrue($documentValidatorFound);
	}

	/**
	 * @test
	 */
	public function aValidModelPassesValidation() {
		$model = new \Radmiraal\CouchDB\Tests\Functional\Fixtures\Domain\Model\UnstructuredModel(array(
			'notEmptyProperty' => '1234'
		));

		$baseValidatorConjunction = $this->validatorResolver->getBaseValidatorConjunction(get_class($model));
		$result = $baseValidatorConjunction->validate($model);

		$this->assertFalse($result->hasErrors());
	}

	/**
	 * @test
	 */
	public function anInvalidModelFailsValidation() {
		$model = new \Radmiraal\CouchDB\Tests\Functional\Fixtures\Domain\Model\UnstructuredModel();

		$baseValidatorConjunction = $this->validatorResolver->getBaseValidatorConjunction(get_class($model));
		$result = $baseValidatorConjunction->validate($model);

		$this->assertTrue($result->hasErrors());
	}

}

?>