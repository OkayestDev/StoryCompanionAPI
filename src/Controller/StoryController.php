<?php
namespace App\Controller;

use App\Entity\Story;
use App\Entity\User;
use App\Entity\Tag;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StoryController extends ApiController
{
    private $table = 'story';

    public function creation()
    {
        $valid = $this->validateRequest(array('user', 'name'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $this->fillEmptyPostIndexes(Story::fields());

        $story = new Story();
        $story->setName($_POST['name']);
        $story->setDescription($_POST['description']);
        $story->setImage($_POST['image']);
        $story->setGenre($_POST['genre']);
        $tag = $this->find(Tag::class, (int) $_POST['tag']);
        $user = $this->find(User::class, (int) $_POST['user']);
        $story->setUser($user);
        $story->setTag($tag);
        $this->saveEntity($story);
        return $this->view();
    }

    public function view()
    {
        $valid = $this->validateRequest(array('user'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        
        $user = $this->find(User::class, (int) $_POST['user']);
        $stories = $this->getDoctrine()->getRepository(Story::class)->findBy(array('user' => $user));
        $response = array();
        foreach ($stories as $index => $story) {
            $response[$story->getId()] = $story->describe();
        }
        return $this->jsonEncodedResponse(array('success' => $response));
    }

    public function edit()
    {
        $valid = $this->validateRequest(array('story', 'name'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $this->fillEmptyPostIndexes(Story::fields());

        $story = $this->find(Story::class, (int) $_POST['story']);
        $story->setName($_POST['name']);
        $story->setDescription($_POST['description']);
        $story->setGenre($_POST['genre']);
        $tag = $this->find(Tag::class, (int) $_POST['tag']);
        $story->setTag($tag);
        if ($_POST['image'] !== $story->getImage()) {
            if (!empty($story->getImage())) {
                $this->deleteFileFromS3($story->getImage());
            }
            $story->setImage($_POST['image']);
        }
        $this->saveEntity($story);
        $response = array('success' => $story->describe());
        return $this->jsonEncodedResponse($response);
    }

    public function delete()
    {
        $valid = $this->validateRequest(array('story'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        $story = $this->find(Story::class, (int) $_POST['story']);
        if (!empty($story->getImage())) {
            $this->deleteFileFromS3($story->getImage());
        }
        $this->removeEntity($story);
        return $this->jsonEncodedResponse(array('success' => 'Removed story successfully'));
    }
}
