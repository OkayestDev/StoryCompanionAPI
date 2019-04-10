<?php
require_once('StoryCompanionTestFunctions.php');

use PHPUnit\Framework\TestCase;

class ChapterTest extends StoryCompanionTestFunctions
{
    private $chapter = array(
        'story' => 11,
        'name' => "Test chapter",
        'description' => 'I am merely a test chapter',
        'number' => '1',
        'attribute' => '',
        'apiKey' => 'b172df49e6e5233026f3c63a5cc41060eafc835c8af757c7b23d5c8a5931c3a32b010cf5e6cb5b8f30554abeb2efd81823745940315c8393d9413521679bbcad',
        'image' => '',
        'content' => '',
    );

    public function testCreation()
    {
        $response = $this->apiPost('/chapter/creation', $this->chapter);
        $this->assertTrue(array_key_exists('success', $response));
        $chapter = $this->query("SELECT * FROM chapter WHERE `name` = 'Test chapter';");
        $this->assertTrue($chapter['name'] === $this->chapter['name']);
        $this->assertTrue($chapter['description'] === $this->chapter['description']);
    }

    public function testWrite()
    {
        $chapter = $this->query("SELECT * FROM chapter WHERE `name` = 'Test chapter';");
        $this->chapter['chapter'] = $chapter['id'];
        $this->chapter['content'] = 'Testing writing a chapter';
        $response = $this->apiPost('/chapter/write', $this->chapter);
        $this->assertTrue(array_key_exists('success', $response));
        $chapter = $this->query("SELECT * FROM chapter WHERE `name` = 'Test chapter';");
        $this->assertTrue($chapter['content'] === 'Testing writing a chapter');
    }

    public function testView()
    {
        $chapter = $this->query("SELECT * FROM chapter WHERE `name` = 'Test chapter';");
        $response = $this->apiPost('/chapter/view', $this->chapter);
        $this->assertTrue(array_key_exists('success', $response));
        $this->assertTrue($response['success'][$chapter['id']]['name'] === $this->chapter['name']);
    }

    public function testEdit()
    {
        $chapter = $this->query("SELECT * FROM chapter WHERE `name` = 'Test chapter';");
        $chapter['description'] = 'Iwillbechanged!';
        $chapter['apiKey'] = $this->chapter['apiKey'];
        $chapter['chapter'] = $chapter['id'];
        $response = $this->apiPost("/chapter/edit", $chapter);
        $chapter = $this->query("SELECT * FROM chapter WHERE `name` = 'Test chapter';");
        $this->assertTrue($chapter['description'] === 'Iwillbechanged!');
    }

    public function testDelete()
    {
        $chapter = $this->query("SELECT * FROM chapter WHERE `name` = 'Test chapter';");
        $chapter['apiKey'] = $this->chapter['apiKey'];
        $chapter['chapter'] = $chapter['id'];
        $response = $this->apiPost("/chapter/delete", $chapter);
        $chapter = $this->query("SELECT * FROM chapter WHERE id = " . $chapter['id'] . ";");
        $this->assertTrue($chapter === null);
    }
}
