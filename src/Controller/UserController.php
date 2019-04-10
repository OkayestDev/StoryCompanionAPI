<?php
namespace App\Controller;

use App\Entity\User;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends ApiController
{
    private $table = 'user';

    private function createApiKey($email, $password)
    {
        $apiKey = hash('sha512', $email . $password);
        return $apiKey;
    }

    private function sendWelcomeEmail(\Swift_Mailer $mailer, $email, $password)
    {
        /** Prevents welcome emails during tests */
        if (strpos($email, '@example.com') !== false) {
            return false;
        }

        // Prevent api tests from sending welcome email
        if (!$this->isEmailValid($email)) {
            return false;
        }

        $message = new \Swift_Message("Story Companion: Welcome");
        $swiftImage = new \Swift_Image();
        // Embed heart for email use
        $heartEmbedded = $message->embed($swiftImage->fromPath('./heart.png'));

        // Send to user
        $message->setSubject("Story Companion: Welcome");
        $message->setFrom('isjustgamedev@gmail.com', "Story Companion");
        $message->setTo($email);
        $message->setBody(
            $this->renderView(
                './WelcomeUser.html.twig',
                array(
                    'email' => $email,
                    'password' => $password,
                    'heart' => $heartEmbedded,
                )
            ),
            'text/html'
        );
        $mailer->send($message);

        // Send to support
        $message->setTo('isjustgamedev@gmail.com');
        $mailer->send($message);
    }

    public function creation(\Swift_Mailer $mailer)
    {
        $valid = $this->validateRequest(array('email', 'password'), false);
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        if (!$this->doesUserExist($_POST['email'])) {
            $user = new User();
            $user->setPassword($_POST['password']);
            $user->setEmail($_POST['email']);
            $user->setApiKey($this->createApiKey($_POST['email'], $_POST['password']));
            $this->saveEntity($user);
            $this->sendWelcomeEmail($mailer, $_POST['email'], $_POST['password']);
            $response = array('success' => $user->describe());
            return $this->jsonEncodedResponse($response);
        } else {
            $response = array('error' => 'User with provided email already exists');
            return $this->jsonEncodedResponse($response);
        }
    }

    public function login()
    {
        $valid = $this->validateRequest(array('email', 'password'), false);
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        $password = $_POST['password'];
        $password = hash('sha512', $password);
        $email = $_POST['email'];
        $user = $this->getDoctrine()->getRepository(User::class)->findBy(array('email' => $email, 'password' => $password));
        if (count($user) !== 0) {
            $response = array('success' => $user[0]->describe());
            return $this->jsonEncodedResponse($response);
        } else {
            $response = array('error' => 'Credentials do not match any user');
            return $this->jsonEncodedResponse($response);
        }
    }

    public function passwordReset(\Swift_Mailer $mailer)
    {
        $valid = $this->validateRequest(array('email'), false);
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        if (!$this->doesUserExist($_POST['email'])) {
            $response = array('error' => "Email doesn't match any user");
            return $this->jsonEncodedResponse($response);
        }

        $user = $this->getDoctrine()->getRepository(User::class)->findBy(array('email' => $_POST['email']));
        $tempPassword = mt_rand(100, 999) . mt_rand(100, 999);
        $user[0]->setPassword($tempPassword);
        $this->saveEntity($user[0]);
        
        if ($this->isEmailValid($user[0]->getEmail())) {
            $message = new \Swift_Message("Story Companion: Password Reset");
            $swiftImage = new \Swift_Image();
            // Embed heart for email use
            $heartEmbedded = $message->embed($swiftImage->fromPath('./heart.png'));
            $message->setFrom('isjustgamedev@gmail.com', "Story Companion");
            $message->setTo($user[0]->getEmail());
            $message->setBody(
                $this->renderView(
                    './PasswordReset.html.twig',
                    array(
                        'tempPassword' => $tempPassword,
                        'heart' => $heartEmbedded,
                    )
                ),
                'text/html'
            );
            $mailer->send($message);
            $response = array('success' => 'Sent temporary password to user');
            return $this->jsonEncodedResponse($response);
        } else {
            $response = array('error' => 'Email is invalid');
            return $this->jsonEncodedResponse($response);
        }
    }

    public function changePassword()
    {
        $valid = $this->validateRequest(array('email', 'password', 'confirmPassword'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirmPassword'];
        if ($password === $confirmPassword) {
            if (!empty($password)) {
                $user = $this->find(User::class, (int) $_POST['user']);
                $user->setPassword($_POST['password']);
                $this->saveEntity($user);
                $response = array('success' => 'Successfully changed password');
                return $this->jsonEncodedResponse($response);
            } else {
                $response = array('error' => 'Passwords cannot be empty');
                return $this->jsonEncodedResponse($response);
            }
        } else {
            $response = array('error' => 'Passwords do not match');
            return $this->jsonEncodedResponse($response);
        }
    }

    public function changeEmail()
    {
        $valid = $this->validateRequest(array('email', 'user'), true);
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        $user = $this->find(User::class, (int) $_POST['user']);
        $user->setEmail($_POST['email']);
        $this->saveEntity($user);
        $response = array('success' => 'Successfully updated email to ' . $_POST['email']) ;
        return $this->jsonEncodedResponse($response);
    }

    private function doesUserExist($email)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findBy(array('email' => $email));
        return count($user) !== 0;
    }
}
