<?php
namespace App\Controller;

use App\Entity\Story;
use App\Entity\Person;
use App\Entity\Tag;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PersonController extends ApiController
{
    private $table = 'person';

    public function creation()
    {
        $valid = $this->validateRequest(array('story', 'name', 'description', 'tag', 'attribute', 'image', 'number'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $this->fillEmptyPostIndexes(Person::fields());
   
        $story = $this->find(Story::class, (int) $_POST['story']);
        $person = new Person();
        $person->setStory($story);
        $person->setNumber($_POST['number']);
        $person->setName($_POST['name']);
        $person->setDescription($_POST['description']);
        $person->setAttribute($_POST['attribute']);
        $person->setImage($_POST['image']);
        $person->setAge($_POST['age']);
        $person->setStoryRole($_POST['storyRole']);
        $person->setGoal($_POST['goal']);
        $tag = $this->find(Tag::class, (int) $_POST['tag']);
        $person->setTag($tag);
        $this->saveEntity($person);
        return $this->view();
    }

    public function view()
    {
        $valid = $this->validateRequest(array('story'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $story = $this->find(Story::class, (int) $_POST['story']);
        $persons = $this->getDoctrine()->getRepository(Person::class)->findBy(array('story' => $story));
        $response = array();
        foreach ($persons as $index => $person) {
            $response[$person->getId()] = $person->describe();
        }
        return $this->jsonEncodedResponse(array('success' => $response));
    }

    public function edit()
    {
        $valid = $this->validateRequest(array('character', 'description', 'name', 'image', 'attribute', 'number'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $this->fillEmptyPostIndexes(Person::fields());

        $person = $this->find(Person::class, (int) $_POST['character']);
        if ($_POST['image'] !== $person->getImage() && $person->getImage() !== null) {
            $this->deleteFileFromS3($person->getImage());
            $person->setImage($_POST['image']);
        }
        $person->setName($_POST['name']);
        $person->setDescription($_POST['description']);
        $person->setNumber($_POST['number']);
        $person->setAttribute($_POST['attribute']);
        $person->setAge($_POST['age']);
        $person->setStoryRole($_POST['storyRole']);
        $person->setGoal($_POST['goal']);
        $tag = $this->find(Tag::class, (int) $_POST['tag']);
        $person->setTag($tag);
        $this->saveEntity($person);
        $response = array('success' => $person->describe());
        return $this->jsonEncodedResponse($response);
    }

    public function delete()
    {
        $valid = $this->validateRequest(array('character'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        
        $person = $this->find(Person::class, (int) $_POST['character']);
        if (!empty($person->getImage())) {
            $this->deleteFileFromS3($person->getImage());
        }
        $this->removeEntity($person);
        return $this->jsonEncodedResponse(array('success' => 'Removed character successfully'));
    }

    public function moveUp()
    {
        $valid = $this->validateRequest(array('character'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        $person = $this->find(Person::class, (int) $_POST['character']);
        $newNumber = $person->getNumber() + 1;
        $person->setNumber($newNumber);
        $this->saveEntity($person);
        $response = array("success" => $person->describe());
        return $this->jsonEncodedResponse($response);
    }

    public function moveDown()
    {
        $valid = $this->validateRequest(array('character'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $person = $this->find(Person::class, (int) $_POST['character']);
        $newNumber = $person->getNumber() - 1;
        // Prevent negative numbers
        if ($newNumber < 0) {
            $newNumber = 0;
        }
        $person->setNumber($newNumber);
        $this->saveEntity($person);
        $response = array("success" => $person->describe());
        return $this->jsonEncodedResponse($response);
    }

    public function export(\Swift_Mailer $mailer)
    {
        $valid = $this->validateRequest(array('story'));
        if (gettype($valid) !== 'boolean') {
            return $valid;
        }

        $story = $this->find(Story::class, (int) $_POST['story']);
        $storyName = $story->getName();
        $toEmail = $story->getUser()->getEmail();
        $persons = $this->findBy(Person::class, array('story' => $story));
        $escapedPersonsFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $storyName);
        $escapedPersonsFilename .= '_Characters';
        $handle = fopen("$escapedPersonsFilename.txt", 'w');
        $personsFileContent = $this->getPersonsFileContent($persons);
        fwrite($handle, $personsFileContent);

        $this->exportEmail(
            $mailer,
            $escapedPersonsFilename,
            $toEmail,
            $storyName,
            'ExportedPersons',
            "Story Companion: '$storyName' Characters Export"
        );

        fclose($handle);
        unlink("./$escapedPersonsFilename.txt");
        return $this->jsonEncodedResponse(array('success' => "Sent characters of $storyName to $toEmail"));
    }

    private function getPersonsFileContent($persons)
    {
        $personsFileContent = '';
        foreach ($persons as $index => $person) {
            $described = $person->describe();
            $listNumber = $index + 1;
            $personsFileContent .=
                "$listNumber) " . $described['name'] ."\n
                  Image: " . $described['image'] . "\n
                  Age: " . $described['age'] . "\n
                  Story Role: " . $described['storyRole'] . "\n
                  Goal: " . $described['goal'] . "\n
                  Description: " . $described['description'] . "\n
                  Attributes: " . $described['attribute'] . "\n
            ";
        }
        return $personsFileContent;
    }
}
