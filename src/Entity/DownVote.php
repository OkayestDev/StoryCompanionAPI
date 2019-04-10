<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DownVoteRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class DownVote extends BaseEntity
{
    public static function fields()
    {
        return array(
            'id' => 'integer',
            'prompt' => 'entity',
            'user' => 'entity',
        );
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Prompt")
     * @ORM\JoinColumn(name="prompt_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $prompt;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    public function getId()
    {
        return $this->id;
    }

    public function setPrompt($prompt)
    {
        $this->prompt = $prompt;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }
}
