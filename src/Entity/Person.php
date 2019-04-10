<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="person")
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Person extends BaseEntity
{
    public static function fields()
    {
        return array(
            'id' => 'integer',
            'story' => 'entity',
            'name' => 'text',
            'tag' => 'entity',
            'description' => 'text',
            'attribute' => 'text',
            'image' => 'text',
            'number' => 'integer',
            'age' => 'text',
            'storyRole' => 'text',
            'goal' => 'text',
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
     * @ORM\Column(type="text", length=200)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Tag")
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $tag;

    /**
     * @ORM\Column(type="text", length=5000)
     */
    private $description;

    /**
     * @ORM\Column(type="text", length=5000)
     */
    private $attribute;

    /**
     * @ORM\Column(type="text", length=500)
     */
    private $image;

    /**
     * Used to order the characters in a view
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $number;

    /**
     * @ORM\Column(type="text", length=500)
     */
    private $age;

    /**
     * @ORM\Column(type="text", length=500)
     */
    private $storyRole;

    /**
     * @ORM\Column(type="text", length=500)
     */
    private $goal;

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

    public function getNumber()
    {
        return $this->number;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function setGoal($goal)
    {
        $this->goal = $goal;
    }

    public function setAge($age)
    {
        $this->age = $age;
    }

    public function setStoryRole($storyRole)
    {
        $this->storyRole = $storyRole;
    }

    public function setStory($story)
    {
        $this->story = $story;
    }

    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
    }

    public function setNumber($number)
    {
        $this->number = $number;
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
            'attribute' => $this->attribute,
            'image' => $this->image,
            'number' => $this->number,
            'age' => $this->age,
            'goal' => $this->goal,
            'storyRole' => $this->storyRole,
            'story' => $this->story->getId(),
            'tag' => isset($this->tag) ? $this->tag->getId() : null,
        );
    }
}
