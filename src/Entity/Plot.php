<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PlotRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Plot extends BaseEntity
{
    public static function fields()
    {
        return array(
            'id' => 'integer',
            'plot' => 'entity',
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
     * @ORM\ManyToOne(targetEntity="Plot")
     * @ORM\JoinColumn(onDelete="CASCADE")     *
     */
    private $plot;

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

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getParentPlot()
    {
        return $this->plot;
    }

    public function setPlot($plot)
    {
        $this->plot = $plot;
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

    public function describe()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'story' => $this->story->getId(),
            'plot' => $this->plot === null ? '' : $this->plot->getId(),
        );
    }
}
