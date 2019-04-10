<?php
require_once('StoryCompanionTestFunctions.php');

use PHPUnit\Framework\TestCase;

class PersonTest extends StoryCompanionTestFunctions
{
    private $mockPerson = array(
        'story' => 11,
        'name' => "Test Person",
        'description' => 'I am merely a test person',
        'attribute' => '',
        'number' => '0',
        'storyRole' => 'Test story role',
        'age' => '11 years',
        'goal' => 'World Domination',
        'tag' => '',
        'apiKey' => 'b172df49e6e5233026f3c63a5cc41060eafc835c8af757c7b23d5c8a5931c3a32b010cf5e6cb5b8f30554abeb2efd81823745940315c8393d9413521679bbcad',
        'image' => '',
    );

    public function testCreation()
    {
        $response = $this->apiPost('/person/creation', $this->mockPerson);
        $this->assertTrue(array_key_exists('success', $response));
        $person = $this->query("SELECT * FROM person WHERE `name` = 'Test Person';");
        $this->assertEquals($person['name'], $this->mockPerson['name']);
        $this->assertEquals($person['description'], $this->mockPerson['description']);
        $this->assertEquals($person['story_role'], $this->mockPerson['storyRole']);
        $this->assertEquals($person['age'], $this->mockPerson['age']);
        $this->assertEquals($person['goal'], $this->mockPerson['goal']);
    }

    public function testView()
    {
        $person = $this->query("SELECT * FROM person WHERE `name` = 'Test Person';");
        $response = $this->apiPost('/person/view', $this->mockPerson);
        $this->assertTrue(array_key_exists('success', $response));
        $this->assertEquals($response['success'][$person['id']]['name'], $this->mockPerson['name']);
        $this->assertEquals($response['success'][$person['id']]['goal'], $this->mockPerson['goal']);
        $this->assertEquals($response['success'][$person['id']]['age'], $this->mockPerson['age']);
    }

    public function testEdit()
    {
        $person = $this->query("SELECT * FROM person WHERE `name` = 'Test Person';");
        $person['description'] = 'Iwillbechanged!';
        $person['attribute'] = "Iwillbechanged!";
        $person['storyRole'] = 'AlteredStoryRole';
        $person['goal'] = 'Still world domination?';
        $person['age'] = '12 years old';
        $person['apiKey'] = $this->mockPerson['apiKey'];
        $person['tag'] = '';
        $person['character'] = $person['id'];
        $response = $this->apiPost("/person/edit", $person);
        $person = $this->query("SELECT * FROM person WHERE `name` = 'Test Person';");
        $this->assertEquals($person['description'], 'Iwillbechanged!');
        $this->assertEquals($person['attribute'], 'Iwillbechanged!');
        $this->assertEquals($person['age'], '12 years old');
        $this->assertEquals($person['goal'], 'Still world domination?');
        $this->assertEquals($person['story_role'], 'AlteredStoryRole');
    }

    public function testMoveUp()
    {
        $person = $this->query("SELECT * FROM person WHERE `name` = 'Test Person';");
        $person['apiKey'] = $this->mockPerson['apiKey'];
        $person['character'] = $person['id'];
        $currentNumber = $person['number'];
        $response = $this->apiPost('/person/move_up', $person);
        $updatedPerson = $this->query("SELECT * FROM person WHERE `name` = 'Test Person';");
        $this->assertEquals(intval($updatedPerson['number']), $currentNumber + 1);
    }

    public function testMoveDown()
    {
        $person = $this->query("SELECT * FROM person WHERE `name` = 'Test Person';");
        $person['apiKey'] = $this->mockPerson['apiKey'];
        $person['character'] = $person['id'];
        $currentNumber = $person['number'];
        $response = $this->apiPost('/person/move_down', $person);
        $updatedPerson = $this->query("SELECT * FROM person WHERE `name` = 'Test Person';");
        $this->assertEquals(intval($updatedPerson['number']), $currentNumber - 1);
    }

    public function testExport()
    {
        
    }

    public function testDelete()
    {
        $person = $this->query("SELECT * FROM person WHERE `name` = 'Test Person';");
        $person['apiKey'] = $this->mockPerson['apiKey'];
        $person['character'] = $person['id'];
        $response = $this->apiPost("/person/delete", $person);
        $person = $this->query("SELECT * FROM person WHERE id = " . $person['id'] . ";");
        $this->assertTrue($person === null);
    }
}
