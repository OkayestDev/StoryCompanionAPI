<?php
require_once('StoryCompanionTestFunctions.php');

use PHPUnit\Framework\TestCase;

class NoteTest extends StoryCompanionTestFunctions
{
    private $mockNote = array(
        'story' => 11,
        'name' => "Test note",
        'description' => 'I am merely a test note',
        'apiKey' => 'b172df49e6e5233026f3c63a5cc41060eafc835c8af757c7b23d5c8a5931c3a32b010cf5e6cb5b8f30554abeb2efd81823745940315c8393d9413521679bbcad',
        'image' => '',
    );

    public function testCreation()
    {
        $response = $this->apiPost('/note/creation', $this->mockNote);
        $this->assertTrue(array_key_exists('success', $response));
        $note = $this->query("SELECT * FROM note WHERE `name` = 'Test note';");
        $this->assertTrue($note['name'] === $this->mockNote['name']);
        $this->assertTrue($note['description'] === $this->mockNote['description']);
    }

    public function testView()
    {
        $note = $this->query("SELECT * FROM note WHERE `name` = 'Test note';");
        $response = $this->apiPost('/note/view', $this->mockNote);
        $this->assertTrue(array_key_exists('success', $response));
        $this->assertTrue($response['success'][$note['id']]['name'] === $this->mockNote['name']);
    }

    public function testEdit()
    {
        $note = $this->query("SELECT * FROM note WHERE `name` = 'Test note';");
        $note['description'] = 'Iwillbechanged!';
        $note['apiKey'] = $this->mockNote['apiKey'];
        $note['note'] = $note['id'];
        $response = $this->apiPost("/note/edit", $note);
        $note = $this->query("SELECT * FROM note WHERE `name` = 'Test note';");
        $this->assertTrue($note['description'] === 'Iwillbechanged!');
    }

    public function testExport()
    {
        $response = $this->apiPost("/note/export", $this->mockNote);
        $this->assertTrue(array_key_exists('success', $response));
    }

    public function testDelete()
    {
        $note = $this->query("SELECT * FROM note WHERE `name` = 'Test note';");
        $note['apiKey'] = $this->mockNote['apiKey'];
        $note['note'] = $note['id'];
        $response = $this->apiPost("/note/delete", $note);
        $note = $this->query("SELECT * FROM note WHERE id = " . $note['id'] . ";");
        $this->assertTrue($note === null);
    }
}
