<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class User extends BaseEntity
{
    public static function fields()
    {
        return array(
            'apiKey' => 'text',
            'id' => 'integer',
            'email' => 'text',
            'password' => 'text',
        );
    }

    /**
     * a hash of password & email used to access api calls
     * @ORM\Column(type="text", length=128);
     */
    private $apiKey;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", length=100)
     */
    private $email;

    /**
     * @ORM\Column(type="text")
     */
    private $password;

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

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $email = str_replace(" ", "", $email);
        $email = strtolower($email);
        $this->email = $email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = hash('sha512', $password);
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function describe()
    {
        return array(
            'email' => $this->email,
            'id' => $this->id,
            'apiKey' => $this->apiKey,
        );
    }
}
