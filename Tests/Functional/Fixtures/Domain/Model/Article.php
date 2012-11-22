<?php
namespace Radmiraal\CouchDB\Tests\Functional\Fixtures\Domain\Model;

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

use Doctrine\ODM\CouchDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(indexed=true)
 */
class Article {

	/**
	 * @var string
	 * @ODM\Id(type="string")
	 */
	protected $id;

	/**
	 * @var string
	 * @ODM\Field(type="string")
	 */
	protected $title;

	/**
	 * @var string
	 * @ODM\Field(type="string")
	 */
	protected $slug;

	/**
	 * @var string
	 * @ODM\Field(type="string")
	 */
	protected $body;

	/**
	 * @var \DateTime
	 * @ODM\Field(type="datetime")
	 */
	protected $created;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ODM\EmbedMany(targetDocument="Radmiraal\CouchDB\Tests\Functional\Fixtures\Domain\Model\Tag")
	 */
	protected $tags;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->tags = new \Doctrine\Common\Collections\ArrayCollection();
		$this->created = new \DateTime('now');
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $body
	 * @return void
	 */
	public function setBody($body) {
		$this->body = $body;
	}

	/**
	 * @return string
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * @param \DateTime $created
	 * @return void
	 */
	public function setCreated(\DateTime $created) {
		$this->created = $created;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreated() {
		return $this->created;
	}

	/**
	 * @param string $id
	 * @return void
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param string $slug
	 * @return void
	 */
	public function setSlug($slug) {
		$this->slug = $slug;
	}

	/**
	 * @return string
	 */
	public function getSlug() {
		return $this->slug;
	}

	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getTags() {
		return $this->tags;
	}

	/**
	 * @param string $tag
	 * @return void
	 */
	public function addTag($tag) {
		$this->tags[] = new \Radmiraal\CouchDB\Tests\Functional\Fixtures\Domain\Model\Tag($tag);
	}

}

?>