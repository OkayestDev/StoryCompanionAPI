<?php
namespace App\Controller;

use App\Entity\Draft;
use App\Entity\Story;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DraftController extends ApiController {
    private $table = 'draft';

    public function creation() {
        $valid = $this->validateRequest(array('story', 'description'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $this->fillEmptyPostIndexes(Draft::fields());

        $draft = new Draft();
        $draft->setDescription($_POST['description']);
        $story = $this->find(Story::class, (int) $_POST['story']);
        $draft->setStory($story);
        $this->saveEntity($draft);
        return $this->view();
    }

    public function view() {
        $valid = $this->validateRequest(array("story"));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        $story = $this->find(Story::class, (int) $_POST['story']);
        $draft = $this->getDoctrine()->getRepository(Draft::class)->findBy(array('story' => $story));
        if (count($draft) === 0) {
            return $this->jsonEncodedResponse(array('success' => []));
        }
        return $this->jsonEncodedResponse(array('success' => $draft[0]->describe()));
    }

    public function edit() {
        $valid = $this->validateRequest(array('draft', 'description'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $this->fillEmptyPostIndexes(Draft::fields());

        $draft = $this->find(Draft::class, (int) $_POST['draft']);
        $draft->setDescription($_POST['description']);
        $this->saveEntity($draft);
        $response = array('success' => $draft->describe());
        return $this->jsonEncodedResponse($response);
    }

    public function delete() {
        $valid = $this->validateRequest(array('draft'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        $draft = $this->find(Draft::class, (int) $_POST['draft']);
        $this->removeEntity($draft);
        return $this->jsonEncodedResponse(array('success' => 'Removed draft successfully'));
    }

    /**
     * Exports the given draft into a .txt file and emails it to the corresponding user
     */
    public function export(\Swift_Mailer $mailer) {
        $valid = $this->validateRequest(array('draft'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        
        $draft = $this->find(Draft::class, (int) $_POST['draft']);
        $draftDescription = $draft->getDescription();
        $story = $draft->getStory();
        $storyName = $story->getName();
        $toEmail = $story->getUser()->getEmail();

        if (!$this->isEmailValid($toEmail)) {
            $response = array('error' => 'The email address associated with this account is invalid');
            return $this->jsonEncodedResponse($response);
        }

        // Replace all invalid characters for a filename
        $escapedStoryName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $storyName);
        $handle = fopen($escapedStoryName . '.txt', 'w');
        fwrite($handle, $draftDescription);

        $message = (new \Swift_Message("Story Companion: '$storyName' Export"));
        $swiftImage = new \Swift_Image();

        // Embed heart for email use
        $heartEmbedded = $message->embed($swiftImage->fromPath('./heart.png'));

        $message->setSubject("Story Companion: '$storyName' Export");
        $message->setFrom('isjustgamedev@gmail.com', "Story Companion");
        $message->setTo($toEmail);
        $message->setBody(
            $this->renderView(
                './ExportedDraft.html.twig',
                array(
                    'story' => $storyName,
                    'heart' => $heartEmbedded,
                )
            ),
            'text/html'
        );
        
        $swiftAttachment = new \Swift_Attachment();
        $message->attach($swiftAttachment->fromPath("./$escapedStoryName.txt"));
        $mailer->send($message);
        fclose($handle);
        unlink("./$escapedStoryName.txt");
        return $this->jsonEncodedResponse(array('success' => 'Sent exported draft to ' . $toEmail));
    }
}