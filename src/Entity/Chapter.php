<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChapterRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Chapter extends BaseEntity
{
    public static function fields()
    {
        return array(
            'id' => 'integer',
            'number' => 'integer',
            'story' => 'entity',
            'name' => 'text',
            'description' => 'text',
        );
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @ORM\ManyToOne(targetEntity="Story")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $story;

    /**
     * @ORM\Column(type="text", length=200)
     */
    private $name;

    /**
     * @ORM\Column(type="text", length=5000)
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    public function getId()
    {
        return $this->id;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setNumber($number)
    {
        $this->number = $number;
    }

    public function setStory($story)
    {
        $this->story = $story;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function describe()
    {
        return array(
            'id' => $this->id,
            'number' => $this->number,
            'name' => $this->name,
            'description' => $this->description,
            'content' => $this->content,
            'story' => $this->story->describe(),
        );
    }
}
