<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DraftRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Draft extends BaseEntity
{
    public static function fields()
    {
        return array(
            'id' => 'integer',
            'story' => 'entity',
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
     * @ORM\ManyToOne(targetEntity="Story")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $story;

    /**
    * @ORM\Column(type="text")
    */
    private $description;

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

    public function getStory()
    {
        return $this->story;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setStory($story)
    {
        $this->story = $story;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function describe()
    {
        return array(
            'id' => $this->id,
            'description' => $this->description,
        );
    }
}
