<?php
require_once('StoryCompanionTestFunctions.php');

use PHPUnit\Framework\TestCase;

class DraftTest extends StoryCompanionTestFunctions {
    private $mockDraft = array(
        'story' => 11,
        'description' => 'I am merely but a test draft',
        'apiKey' => 'b172df49e6e5233026f3c63a5cc41060eafc835c8af757c7b23d5c8a5931c3a32b010cf5e6cb5b8f30554abeb2efd81823745940315c8393d9413521679bbcad',
        'image' => '',
    );

    function testCreation() {
        $response = $this->apiPost('/draft/creation', $this->mockDraft);
        $this->assertTrue(array_key_exists('success', $response));
        $draft = $this->query("SELECT * FROM draft WHERE `description` = 'I am merely but a test draft';");
        $this->assertTrue($draft['description'] === $this->mockDraft['description']);
        $this->assertTrue($draft['description'] === $this->mockDraft['description']);
    }

    function testView() {
        $draft = $this->query("SELECT * FROM draft WHERE `description` = 'I am merely but a test draft';");
        $response = $this->apiPost('/draft/view', $this->mockDraft);
        $this->assertTrue(array_key_exists('success', $response));
        $this->assertTrue($response['success']['description'] === $this->mockDraft['description']);
    }

    function testEdit() {
        $draft = $this->query("SELECT * FROM draft WHERE `description` = 'I am merely but a test draft';");
        $draft['description'] = 'Iwillbechanged!';
        $draft['apiKey'] = $this->mockDraft['apiKey'];
        $draft['draft'] = $draft['id'];
        $response = $this->apiPost("/draft/edit", $draft);
        $draft = $this->query("SELECT * FROM draft WHERE `id` = " . $draft['id'] . ";");
        $this->assertTrue($draft['description'] === 'Iwillbechanged!');
    }

    function testDelete() {
        $draft = $this->query("SELECT * FROM draft WHERE `description` = 'Iwillbechanged!';");
        $draft['apiKey'] = $this->mockDraft['apiKey'];
        $draft['draft'] = $draft['id'];
        $response = $this->apiPost("/draft/delete", $draft);
        $draft = $this->query("SELECT * FROM draft WHERE id = " . $draft['id'] . ";");
        $this->assertTrue($draft === null);
    }
}