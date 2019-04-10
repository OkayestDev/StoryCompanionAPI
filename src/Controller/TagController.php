<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Tag;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TagController extends ApiController
{
    private $table = 'tag';

    public function creation()
    {
        $valid = $this->validateRequest(array('description', 'name', 'type', 'user'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $this->fillEmptyPostIndexes(Tag::fields());

        $tag = new Tag();
        $tag->setDescription($_POST['description']);
        $tag->setName($_POST['name']);
        $tag->setType($_POST['type']);
        $user = $this->find(User::class, (int) $_POST['user']);
        $tag->setUser($user);
        $this->saveEntity($tag);
        return $this->view();
    }

    public function view()
    {
        $valid = $this->validateRequest(array('user'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        $user = $this->find(User::class, (int) $_POST['user']);
        $tags = array();
        $tags = $this->getDoctrine()->getRepository(Tag::class)->findBy(array('user' => $user));
        $response = array();
        foreach ($tags as $index => $tag) {
            $response[$tag->getId()] = $tag->describe();
        }
        return $this->jsonEncodedResponse(array('success' => $response));
    }

    public function edit()
    {
        $valid = $this->validateRequest(array('description', 'name', 'type', 'tag'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $this->fillEmptyPostIndexes(Tag::fields());

        $tag = $this->find(Tag::class, (int) $_POST['tag']);
        $tag->setName($_POST['name']);
        $tag->setDescription($_POST['description']);
        $tag->setType($_POST['type']);
        $this->saveEntity($tag);
        $response = array('success' => $tag->describe());
        return $this->jsonEncodedResponse($response);
    }

    public function delete()
    {
        $valid = $this->validateRequest(array('tag'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        $tag = $this->find(Tag::class, (int) $_POST['tag']);
        $this->removeEntity($tag);
        return $this->jsonEncodedResponse(array('success' => 'Removed tag successfully'));
    }
}
