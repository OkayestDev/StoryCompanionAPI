<?php
namespace App\Controller;

use App\Entity\User;

use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Aws\Credentials\Credentials;

class ApiController extends Controller
{
    /**
     * Fill in post indexes so no PHP errors are thrown when editing, creating entities
     */
    public function fillEmptyPostIndexes($fields)
    {
        foreach ($fields as $field => $type) {
            if (!array_key_exists($field, $_POST)) {
                switch ($type) {
                    case 'integer':
                        $_POST[$field] = 0;
                        break;
                    case 'text':
                        $_POST[$field] = '';
                        break;
                }
            }
        }
    }

    public function saveEntity($entity)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($entity);
        $entityManager->flush();
    }

    public function removeEntity($entity)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($entity);
        $entityManager->flush();
    }

    public function find($class, $id)
    {
        $entity = $this->getDoctrine()->getRepository($class)->find($id);
        return $entity;
    }

    public function findBy($class, $conditions, $order = array())
    {
        $entities = $this->getDoctrine()->getRepository($class)->findBy($conditions, $order);
        return $entities;
    }

    public function query($queryString, $parameters)
    {
        $em = $this->getDoctrine()->getManager();
        $connection = $em->getConnection();
        $statement = $connection->prepare($queryString);
        foreach ($parameters as $key => $value) {
            $statement->bindValue($key, $value);
        }
        $statement->execute();
        $results = $statement->fetchAll();
        return $results;
    }

    /**
     * Given an $apiKey, ensure a user exists
     */
    public function validateApplication($apiKey)
    {
        if ($apiKey === null) {
            return false;
        }
        $user = $this->getDoctrine()->getRepository(User::class)->findBy(array('apiKey' => $apiKey));
        return count($user) !== 0;
    }

    /** @TODO implement regex */
    public function isEmailValid($email)
    {
        return true;
    }

    /**
     * Give $requiredKeys, check to see if each key exists in $_POST
     * returns a Response on fail and a boolean on success
     */
    public function validateRequest($requiredKeys, $validateApplication = true)
    {
        if ($validateApplication) {
            if (!$this->validateApplication(@$_POST['apiKey'])) {
                $response = array('error' => "Invalid instance. Try Relogging");
                return $this->jsonEncodedResponse($response);
            }
        }

        foreach ($requiredKeys as $index => $key) {
            if (!array_key_exists($key, $_POST) && !empty($_POST[$key])) {
                $response = array("error" => "No $key provided");
                return $this->jsonEncodedResponse($response);
            }
        }
        return true;
    }

    /**
     * Given url to file in S3 bucket, delete it
     * example URL: https://s3.amazonaws.com/story-companion/new-story-1538937516109.jpg
     */
    public function deleteFileFromS3($fileUrl)
    {
        if (empty($fileUrl)) {
            return;
        }
        try {
            $urlParts = explode("/", $fileUrl);
            $key = $urlParts[count($urlParts) - 1];
            $credentials = new Credentials("AKIAJXWONLPYBWQPEAKQ", "NpbCL8Le4o7TQbznnQ8W6pUVMoEa2nsR0BFrd/G4");
            $options = array(
                'version' => 'latest',
                'region' => "us-east-1",
                'credentials' => $credentials,
            );
            $bucket = 'story-companion';
            $s3Client = new S3Client($options);
            $s3Client->deleteObject([
                'Bucket' => $bucket,
                'Key' => $key,
            ]);
            return true;
        } catch (Exception $e) {
            echo json_encode(array('error' => $e));
            return false;
        }
    }

    public function jsonEncodedResponse($response)
    {
        // These headers are critical for CORS so that clients from any valid subdomain or a local dev environment can access the API
        if (array_key_exists('HTTP_ORIGIN', $_SERVER) &&
        !empty($_SERVER['HTTP_ORIGIN']) &&
        (stripos($_SERVER['HTTP_ORIGIN'], 'story-companion') !== false || stripos($_SERVER['HTTP_ORIGIN'], 'localhost') !== false || stripos($_SERVER['HTTP_ORIGIN'], '127.0.0.1') !== false)) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        }
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

        return new Response(json_encode($response));
    }

    public function exportEmail($mailer, $escapedFilename, $toEmail, $storyName, $twig, $subject)
    {
        $message = (new \Swift_Message($subject));
        $swiftImage = new \Swift_Image();

        // Embed heart for email use
        $heartEmbedded = $message->embed($swiftImage->fromPath('./heart.png'));

        $message->setSubject($subject);
        $message->setFrom('isjustgamedev@gmail.com', "Story Companion");
        $message->setTo($toEmail);
        $message->setBody(
            $this->renderView(
                "./$twig.html.twig",
                array(
                    'story' => $storyName,
                    'heart' => $heartEmbedded,
                )
            ),
            'text/html'
        );
        
        $swiftAttachment = new \Swift_Attachment();
        $message->attach($swiftAttachment->fromPath("./$escapedFilename.txt"));
        $mailer->send($message);
    }
}
