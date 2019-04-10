<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PromptRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Prompt extends BaseEntity
{
    public static function fields()
    {
        return array(
            'id' => 'integer',
            'user' => 'entity',
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
     * @ORM\ManyToOne(targetEntity="User")
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
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function describe()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'user' => $this->user->getId(),
        );
    }
}
