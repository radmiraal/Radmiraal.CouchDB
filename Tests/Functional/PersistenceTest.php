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

use \Radmiraal\CouchDB\Tests\Functional\Fixtures\Domain\Model\Article as Article;

/**
 *
 */
class PersistenceTest extends AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function anObjectCanBePersisted() {
		$document1 = new Article();
		$this->documentManager->persist($document1);
		$this->documentManager->flush();

		$this->assertTrue($this->documentManager->contains($document1));
	}

	/**
	 * @test
	 */
	public function aListOfObjectsWithRelationsCanBePersisted() {
		$article1 = new Article();
		$article1->setTitle('Who is John Galt?');
		$article1->setBody('Find out!');
		$article1->addTag('Philosophy');

		$article2 = new Article();
		$article2->setTitle('Human Action');
		$article2->setBody('Find out!');
		$article2->addTag('Philosophy');
		$article2->addTag('Economics');

		$article3 = new Article();
		$article3->setTitle('Design Patterns');
		$article3->setBody('Find out!');
		$article3->addTag('Computer Science');

		$this->documentManager->persist($article1);
		$this->documentManager->persist($article2);
		$this->documentManager->persist($article3);
		$this->documentManager->flush();

		$this->assertTrue($this->documentManager->contains($article1));
		$this->assertTrue($this->documentManager->contains($article2));
		$this->assertTrue($this->documentManager->contains($article3));
	}

	/**
	 * @test
	 */
	public function anObjectWithRelationsCanBeRetrieved() {
		$article = new Article();
		$article->setTitle('Who is John Galt?');
		$article->setBody('Find out!');
		$article->addTag('Philosophy');

		$this->documentManager->persist($article);
		$this->documentManager->flush();

		$articles = $this->documentManager->getRepository('\Radmiraal\CouchDB\Tests\Functional\Fixtures\Domain\Model\Article')->findAll();

		$this->assertEquals(1, count($articles));
		$this->assertInstanceOf('DateTime', $articles[0]->getCreated());
		$this->assertNotNull($articles[0]->getId());
		$this->assertEquals('Who is John Galt?', $articles[0]->getTitle());
		$this->assertEquals('Find out!', $articles[0]->getBody());

		$this->assertEquals(1, $articles[0]->getTags()->count());
		$this->assertEquals('Philosophy', $articles[0]->getTags()->first()->getName());
	}

}

?>