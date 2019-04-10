<?php
namespace App\Controller;

use App\Entity\Note;
use App\Entity\Story;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NoteController extends ApiController
{
    private $table = 'note';

    public function creation()
    {
        $valid = $this->validateRequest(array('story', 'description', 'name'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $this->fillEmptyPostIndexes(Note::fields());

        $note = new Note();
        $story = $this->find(Story::class, (int) $_POST['story']);
        $note->setStory($story);
        $note->setDescription($_POST['description']);
        $note->setName($_POST['name']);
        $this->saveEntity($note);
        return $this->view();
    }

    public function view()
    {
        $valid = $this->validateRequest(array('story'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        $story = $this->find(Story::class, (int) $_POST['story']);
        $notes = $this->getDoctrine()->getRepository(Note::class)->findBy(array('story' => $story));
        $response = array();
        foreach ($notes as $index => $note) {
            $response[$note->getId()] = $note->describe();
        }
        return $this->jsonEncodedResponse(array('success' => $response));
    }

    public function edit()
    {
        $valid = $this->validateRequest(array('note', 'name', 'description'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $this->fillEmptyPostIndexes(Note::fields());

        $note = $this->find(Note::class, (int) $_POST['note']);
        $note->setName($_POST['name']);
        $note->setDescription($_POST['description']);
        $this->saveEntity($note);
        $response = array('success' => $note->describe());
        return $this->jsonEncodedResponse($response);
    }

    public function delete()
    {
        $valid = $this->validateRequest(array('note'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        $note = $this->find(Note::class, (int) $_POST['note']);
        $this->removeEntity($note);
        return $this->jsonEncodedResponse(array('success' => 'Removed note successfully'));
    }

    private function getExportNotesContent($notes)
    {
        $notesFileContent = '';
        foreach ($notes as $index => $note) {
            $notesFileContent .= $index + 1 . ") " . $note->getName() . "\n";
            $notesFileContent .= "\t" . $note->getDescription() . "\n\n";
        }
        return $notesFileContent;
    }

    public function export(\Swift_Mailer $mailer)
    {
        $valid = $this->validateRequest(array('story'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        $story = $this->find(Story::class, (int) $_POST['story']);
        $storyName = $story->getName();
        $notes = $this->findBy(Note::class, array('story' => $story));

        $toEmail = $story->getUser()->getEmail();
        if (!$this->isEmailValid($toEmail)) {
            $response = array('error' => 'The email address associated with this account is invalid');
            return $this->jsonEncodedResponse($response);
        }

        $escapedNotesFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $storyName);
        $escapedNotesFilename .= "_Notes";
        $handle = fopen("$escapedNotesFilename.txt", 'w');
        fwrite($handle, $this->getExportNotesContent($notes));

        $message = (new \Swift_Message());
        $this->exportEmail(
            $mailer,
            $escapedNotesFilename,
            $toEmail,
            $storyName,
            'ExportedNotes',
            "Story Companion: '$storyName' Notes Export"
        );
        
        fclose($handle);
        unlink("./$escapedNotesFilename.txt");
        return $this->jsonEncodedResponse(array('success' => "Sent notes of $storyName to $toEmail"));
    }
}
