<?php
require_once('StoryCompanionTestFunctions.php');

use PHPUnit\Framework\TestCase;

class StoryTest extends StoryCompanionTestFunctions
{
    private $mockStory = array(
        'user' => 1,
        'name' => "Test Story",
        'genre' => 'Fiction',
        'description' => 'I am merely a test story',
        'tag' => '',
        'apiKey' => 'b172df49e6e5233026f3c63a5cc41060eafc835c8af757c7b23d5c8a5931c3a32b010cf5e6cb5b8f30554abeb2efd81823745940315c8393d9413521679bbcad',
        'image' => '',
    );

    public function testCreation()
    {
        $response = $this->apiPost('/story/creation', $this->mockStory);
        $story = $this->query("SELECT * FROM story WHERE name = 'Test Story';");
        $this->assertEquals($story['name'], $this->mockStory['name']);
        $this->assertEquals($story['genre'], $this->mockStory['genre']);
    }

    public function testView()
    {
        $story = $this->query("SELECT * FROM story WHERE name = 'Test Story';");
        $response = $this->apiPost('/story/view', $this->mockStory);
        $this->assertEquals($response['success'][$story['id']]['name'], $this->mockStory['name']);
        $this->assertEquals($response['success'][$story['id']]['genre'], $this->mockStory['genre']);
    }

    public function testEdit()
    {
        $story = $this->query("SELECT * FROM story WHERE name = 'Test Story';");
        $story['description'] = 'Iwillbechanged!';
        $story['genre'] = 'ChangedGenre';
        $story['story'] = $story['id'];
        $story['apiKey'] = $this->mockStory['apiKey'];
        $story['tag'] = '';
        $response = $this->apiPost('/story/edit', $story);
        $story = $this->query("SELECT * FROM story WHERE id = " . $story['id'] . ";");
        $this->assertEquals($story['description'], 'Iwillbechanged!');
        $this->assertEquals($story['genre'], 'ChangedGenre');
    }

    public function testDelete()
    {
        $story = $this->query("SELECT * FROM story WHERE name = 'Test Story';");
        $this->mockStory['story'] = $story['id'];
        $response = $this->apiPost('/story/delete', $this->mockStory);
        $story = $this->query("SELECT * FROM story WHERE name = 'Test Story';");
        $this->assertTrue($story === null);
    }
}
