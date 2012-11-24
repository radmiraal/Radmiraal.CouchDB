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

use \Radmiraal\CouchDB\Tests\Functional\Fixtures\Domain\Model\Article;

/**
 *
 */
class RepositoryTest extends AbstractFunctionalTest {

	/**
	 * @var \Radmiraal\CouchDB\Tests\Functional\Fixtures\Domain\Repository\ArticleRepository
	 */
	protected $articleRepository;

	public function setUp() {
		parent::setUp();
		$this->articleRepository = $this->objectManager->get('\Radmiraal\CouchDB\Tests\Functional\Fixtures\Domain\Repository\ArticleRepository');
		$this->articleRepository->injectDocumentManagerFactory($this->documentManagerFactory);
	}

	/**
	 * @test
	 */
	public function repositoryGetEntityClassNameReturnsCorrectModelName() {
		$this->assertEquals(
			'Radmiraal\CouchDB\Tests\Functional\Fixtures\Domain\Model\Article',
			$this->articleRepository->getEntityClassName()
		);
	}

	/**
	 * @test
	 */
	public function aModelCanBeAdded() {
		$model = new Article(array('title' => 'foo'));
		$this->articleRepository->add($model);
		$this->documentManager->flush();
		$this->assertEquals(1, $this->articleRepository->countAll());
	}

	/**
	 * @test
	 */
	public function aModelCanBeRemoved() {
		$model = new Article(array('title' => 'foo'));
		$this->articleRepository->add($model);
		$this->documentManager->flush();
		$this->assertEquals(1, $this->articleRepository->countAll());
		$this->articleRepository->remove($model);
		$this->documentManager->flush();
		$this->assertEquals(0, $this->articleRepository->countAll());
	}

	/**
	 * @test
	 */
	public function removeAllRemovesAllDocuments() {
		$model = new Article(array('title' => 'foo'));
		$this->articleRepository->add($model);
		$this->documentManager->flush();
		$this->assertEquals(1, $this->articleRepository->countAll());
		$this->articleRepository->removeAll();
		$this->documentManager->flush();
		$this->assertEquals(0, $this->articleRepository->countAll());
	}

	/**
	 * @test
	 */
	public function findByIdentifierReturnsTheDocument() {
		$model = new Article();
		$model->setTitle('foo');
		$this->articleRepository->add($model);
		$this->documentManager->flush();
		$result = $this->articleRepository->findAll();
		$this->assertNotNull($result[0]->getId());
		$result2 = $this->articleRepository->findByIdentifier($result[0]->getId());
		$this->assertEquals('foo', $result2->getTitle());
	}

	/**
	 * @test
	 */
	public function findByPropertyReturnsTheDocument() {
		$model = new Article();
		$model->setTitle('foo');
		$this->articleRepository->add($model);
		$this->documentManager->flush();
		$result = $this->articleRepository->findByTitle('foo');
		$this->assertNotNull($result[0]->getId());
	}

	/**
	 * @test
	 */
	public function findOneByPropertyReturnsTheDocument() {
		$model = new Article();
		$model->setTitle('foo');
		$this->articleRepository->add($model);
		$this->documentManager->flush();

		$result = $this->articleRepository->findOneByTitle('foo');
		$this->assertNotNull($result->getId());
	}

}

?>