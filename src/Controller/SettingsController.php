<?php
namespace App\Controller;

use App\Entity\User;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SettingsController extends ApiController {
    /**
     * User submitted bug
     */
    public function bug(\Swift_Mailer $mailer) {
        if (array_key_exists('description', $_POST) && !empty($_POST['description'])) {
            $this->sendRequest($mailer, 'Bug', $_POST['description'], $_POST['user']);
            $response = array('success' => 'Successfully submitted bug');
            return $this->jsonEncodedResponse($response);
        }
        else {
            $response = array('error' => 'No bug description provided');
            return $this->jsonEncodedResponse($response);
        }
    }

    /**
     * User submitted feature
     */
    public function feature(\Swift_Mailer $mailer) {
        if (array_key_exists('description', $_POST) && !empty($_POST['description'])) {
            $this->sendRequest($mailer, 'Feature', $_POST['description'], $_POST['user']);
            $response = array('success' => 'Successfully submitted feature request');
            return $this->jsonEncodedResponse($response);
        }
        else {
            $response = array('error' => 'No feature description provided');
            return $this->jsonEncodedResponse($response);
        }
    }

    private function sendRequest(\Swift_Mailer $mailer, $title, $description, $userId) {
        $user = $this->getDoctrine()->getRepository(User::class)->find($userId);
        $email = $user->getEmail();
        $message = new \Swift_Message("Story Companion " . $title);
        // We set from/to ourselves so we don't have to set up receiving - we will include user email in email.
        $message->setFrom('isjustgamedev@gmail.com', "Story Companion");
        $message->setTo('isjustgamedev@gmail.com');
        $message->setBody(
            $this->renderView(
                './BugOrFeature.html.twig',
                array(
                    'title' => $title,
                    'email' => $email,
                    'description' => $description,
                )
            ),
            'text/html'
        );
        $mailer->send($message);
    }
}