<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Story;
use App\Entity\Prompt;
use App\Entity\DownVote;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PromptController extends ApiController
{
    private function isBlacklisted($string)
    {
        $blacklist = file_get_contents('./blacklist.txt');
        $blacklist = explode(',', $blacklist);
        foreach ($blacklist as $blacklistedWord) {
            if (!empty($blacklistedWord)) {
                $pattern = "/\b$blacklistedWord\b/i";
                if (preg_match($pattern, $string)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function creation()
    {
        $valid = $this->validateRequest(array('name', 'description', 'user'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $this->fillEmptyPostIndexes(Prompt::fields());

        $isBlacklisted = $this->isBlacklisted($_POST['name'] . " " . $_POST['description']);
        if ($isBlacklisted) {
            $response = ['error' => 'Try to keep the prompt free of vulgar language'];
            return $this->jsonEncodedResponse($response);
        }

        $user = $this->find(User::class, (int) $_POST['user']);
        $prompt = new Prompt();
        $prompt->setUser($user);
        $prompt->setName($_POST['name']);
        $prompt->setDescription($_POST['description']);
        $this->saveEntity($prompt);
        $response = array("success" => "Created a new prompt. Thanks for contributing!");
        return $this->jsonEncodedResponse($response);
    }

    /**
     * 1 Random prompt that the user hasn't down voted
     */
    public function view()
    {
        $valid = $this->validateApplication(array('user'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $userId = (int) $_POST['user'];

        // Queries for a prompt this user hasn't created/downvoted
        $queryString = "
            SELECT p.id FROM prompt AS p
            WHERE p.user_id != $userId AND p.id NOT IN(
                SELECT d.prompt_id FROM down_vote AS d
                WHERE d.user_id = :downVoteId AND d.prompt_id = p.id
            )
            ORDER BY RAND()
            LIMIT 1;
        ";

        $parameters = array('downVoteId' => $userId);
        $promptId = $this->query($queryString, $parameters);
        $response = array();
        if (array_key_exists(0, $promptId)) {
            $promptId = $promptId[0]['id'];
            $prompt = $this->find(Prompt::class, (int) $promptId);
            $response = array('success' => $prompt->describe());
        }
        return $this->jsonEncodedResponse($response);
    }

    public function downVote()
    {
        $valid = $this->validateRequest(array('prompt', 'user'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }
        $this->fillEmptyPostIndexes(Prompt::fields());

        $user = $this->find(User::class, (int) $_POST['user']);
        $prompt = $this->find(Prompt::class, (int) $_POST['prompt']);
        $downVote = new DownVote();
        $downVote->setPrompt($prompt);
        $downVote->setUser($user);
        $this->saveEntity($downVote);
        $response = array("success" => "Successfully Down Voted Prompt");
        return $this->jsonEncodedResponse($response);
    }

    public function toStory()
    {
        $valid = $this->validateRequest(array('prompt', 'user'));
        if (gettype($valid) !== "boolean") {
            return $valid;
        }

        $user = $this->find(User::class, (int) $_POST['user']);
        $prompt = $this->find(Prompt::class, (int) $_POST['prompt']);
        $newStory = new Story();
        $newStory->setUser($user);
        $newStory->setName($prompt->getName());
        $newStory->setDescription($prompt->getDescription());
        $this->saveEntity($newStory);
        $response = array('success' => $newStory->describe());
        return $this->jsonEncodedResponse($response);
    }
}
