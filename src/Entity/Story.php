<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StoryRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Story extends BaseEntity
{
    /**
     * These are the doctrine generated names, not the mysql column names
     */
    public static function fields()
    {
        return array(
            'id' => 'integer',
            'user' => 'entity',
            'description' => 'text',
            'name' => 'text',
            'tag' => 'id',
            'image' => 'text',
            'genre' => 'text',
        );
    }
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(type="text", length=200)
     */
    private $name;

    /**
     * @ORM\Column(type="text", length=5000)
     */
    private $description;

    /**
     * @ORM\Column(type="text", length=500, nullable=true)
     */
    private $image;

    /**
     * @ORM\ManyToOne(targetEntity="Tag")
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $tag;

    /**
     * @ORM\Column(type="text", length=500, nullable=true)
     */
    private $genre;

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

    public function getImage()
    {
        return $this->image;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function setGenre($genre)
    {
        $this->genre = $genre;
    }

    public function setTag($tag)
    {
        $this->tag = $tag;
    }
    
    public function describe()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'genre' => $this->genre,
            'user' => $this->user->describe(),
            'tag' => isset($this->tag) ? $this->tag->getId() : null,
        );
    }
}
