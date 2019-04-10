<?php
namespace App\Controller;

use App\Entity\Chapter;
use App\Entity\Story;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ChapterController extends ApiController
{
    private $table = 'chapter';

    public function creation()
    {
        $valid = $this->validateRequest(array('story', 'description', 'name', 'number'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $this->fillEmptyPostIndexes(Chapter::fields());
        
        $chapter = new Chapter();
        $chapter->setDescription($_POST['description']);
        $chapter->setName($_POST['name']);
        $chapter->setNumber($this->ensureNumberFormat($_POST['number']));
        $story = $this->getDoctrine()->getRepository(Story::class)->find((int) $_POST['story']);
        $chapter->setStory($story);
        $this->saveEntity($chapter);
        return $this->view();
    }

    public function view()
    {
        $valid = $this->validateRequest(array('story'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        $story = $this->getDoctrine()->getRepository(Story::class)->find($_POST['story']);
        $chapters = $this->getDoctrine()->getRepository(Chapter::class)->findBy(array('story' => $story));
        $response = array();
        foreach ($chapters as $index => $chapter) {
            $response[$chapter->getId()] = $chapter->describe();
        }
        return $this->jsonEncodedResponse(array('success' => $response));
    }

    public function edit()
    {
        $valid = $this->validateRequest(array('chapter', 'description', 'number', 'name'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $this->fillEmptyPostIndexes(Chapter::fields());

        $chapter = $this->find(Chapter::class, (int) $_POST['chapter']);
        $chapter->setName($_POST['name']);
        $chapter->setDescription($_POST['description']);
        $chapter->setNumber($_POST['number']);
        $chapter->setContent($_POST['content']);
        $this->saveEntity($chapter);
        $response = array('success' => $chapter->describe());
        return $this->jsonEncodedResponse($response);
    }

    private function getExportChaptersContent($chapters)
    {
        $chaptersFileContent = '';
        foreach ($chapters as $index => $chapter) {
            $chaptersFileContent .= $chapter->getNumber() . '. ' . $chapter->getName() . "\n";
            $chaptersFileContent .= "\t" . $chapter->getContent() . "\n\n";
        }
        return $chaptersFileContent;
    }

    public function export(\Swift_Mailer $mailer)
    {
        $valid = $this->validateRequest(array('story'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        $story = $this->find(Story::class, (int) $_POST['story']);
        $chapters = $this->findBy(
            Chapter::class,
            array('story' => $story),
            array('number' => 'ASC')
        );
        $storyName = $story->getName();

        $toEmail = $story->getUser()->getEmail();
        if (!$this->isEmailValid($toEmail)) {
            $response = array('error' => 'The email address associated with this account is invalid');
            return $this->jsonEncodedResponse($response);
        }

        $escapedChaptersFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $storyName);
        $escapedChaptersFilename .= "_Chapters";
        $handle = fopen("$escapedChaptersFilename.txt", 'w');
        fwrite($handle, $this->getExportChaptersContent($chapters));

        $this->exportEmail(
            $mailer, 
            $escapedChaptersFilename, 
            $toEmail, 
            $storyName, 
            'ExportedChapters',
            "Story Companion: '$storyName' Chapters Export"
        );

        fclose($handle);
        unlink("./$escapedChaptersFilename.txt");
        return $this->jsonEncodedResponse(array('success' => "Sent chapters of $storyName to $toEmail"));
    }

    public function write()
    {
        $valid = $this->validateRequest(array('chapter', 'content'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        $chapter = $this->find(Chapter::class, (int) $_POST['chapter']);
        $chapter->setContent($_POST['content']);
        $this->saveEntity($chapter);
        $response = array('success' => $chapter->describe());
        return $this->jsonEncodedResponse($response);
    }

    public function delete()
    {
        $valid = $this->validateRequest(array('chapter'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        $chapter = $this->find(Chapter::class, (int) $_POST['chapter']);
        $this->removeEntity($chapter);
        return $this->jsonEncodedResponse(array('success' => 'Removed story successfully'));
    }

    private function ensureNumberFormat($number)
    {
        $number = str_replace('.', '', $number);
        $number = str_replace(' ', '', $number);
        $number = str_replace(',', '', $number);
        $number = str_replace('-', '', $number);
        return $number;
    }
}
