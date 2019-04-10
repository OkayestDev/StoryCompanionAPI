<?php
require_once('StoryCompanionTestFunctions.php');

use PHPUnit\Framework\TestCase;

class PromptTest extends StoryCompanionTestFunctions
{
    private $mockPrompt = array(
        'user' => 2,
        'name' => 'Test Prompt',
        'description' => 'test description'
    );
    private $vulgarPrompt = array(
        'user' => 2,
        'name' => 'fuck',
        'description' => 'fuck',
    );

    public function testCreate()
    {
        $response = $this->apiPost('/prompt/creation', $this->mockPrompt);
        $prompt = $this->query("SELECT * FROM prompt WHERE `name` = 'Test Prompt';");
        $this->assertTrue($prompt['description'] === 'test description');
        $this->assertTrue($prompt['name'] === 'Test Prompt');
        $this->assertTrue(array_key_exists('success', $response));
    }

    public function testVulgarCreate()
    {
        $response = $this->apiPost('/prompt/creation', $this->vulgarPrompt);
        $this->assertTrue(array_key_exists('error', $response));
        $prompt = $this->query("SELECT * FROM prompt WHERE `name` = 'fuck';");
        $this->assertTrue($prompt === null);
    }

    public function testView()
    {
        $response = $this->apiPost('/prompt/view', ['user' => 1]);
        $this->assertTrue(array_key_exists('success', $response));
        $this->assertTrue(array_key_exists('name', $response['success']));
    }

    public function testDownVote()
    {
        $prompt = $this->apiPost('/prompt/view', ['user' => 1]);
        $downVoteParams = [
            'user' => 1,
            'prompt' => $prompt['success']['id']
        ];
        $response = $this->apiPost('/prompt/down_vote', $downVoteParams);
        $this->assertTrue(array_key_exists('success', $response));
        $newDownVote = $this->query("SELECT * FROM down_vote WHERE `user_id` = " . $downVoteParams['user']);
        $this->assertTrue($newDownVote['user_id'] == 1);
    }

    /**
     * We create a prompt and then down vote it. View should return an empty response
     */
    public function testViewAfterDownVote()
    {
        $viewParam = [
            'user' => 1
        ];
        $response = $this->apiPost('/prompt/view', $viewParam);
        $this->assertTrue(empty($response));
    }

    public function testToStory()
    {
        $prompt = $this->query("SELECT * FROM prompt WHERE `name` = 'Test Prompt';");
        $params = [
            'user' => 1,
            'prompt' => $prompt['id'],
        ];
        $response = $this->apiPost('/prompt/to_story', $params);
        $this->assertTrue(array_key_exists('success', $response));
        $story = $this->query("SELECT * FROM story WHERE `name` = 'Test Prompt';");
        $this->assertTrue($story['name'] === 'Test Prompt');
    }

    public function testCleanUp()
    {
        $this->query("DELETE FROM prompt WHERE `name` = 'Test Prompt'");
        $this->query("DELETE FROM story WHERE `name` = 'Test Prompt'");
        $promptCheck = $this->query("SELECT * FROM prompt WHERE `name` = 'Test Prompt");
        $this->assertFalse($promptCheck);
    }
}
