<?php
require_once('StoryCompanionTestFunctions.php');

use PHPUnit\Framework\TestCase;

class tagTest extends StoryCompanionTestFunctions
{
    private $mockTag = array(
        'user' => 1,
        'name' => "Test tag",
        'description' => 'I am merely a test tag',
        'apiKey' => 'b172df49e6e5233026f3c63a5cc41060eafc835c8af757c7b23d5c8a5931c3a32b010cf5e6cb5b8f30554abeb2efd81823745940315c8393d9413521679bbcad',
        'type' => 'person',
    );

    private $mockTag2 = array(
        'user' => 1,
        'name' => "Test tag 2",
        'description' => 'I am merely a test tag 2',
        'apiKey' => 'b172df49e6e5233026f3c63a5cc41060eafc835c8af757c7b23d5c8a5931c3a32b010cf5e6cb5b8f30554abeb2efd81823745940315c8393d9413521679bbcad',
        'type' => 'story',
    );

    public function testCreationPersonTag()
    {
        $response = $this->apiPost('/tag/creation', $this->mockTag);
        $this->assertTrue(array_key_exists('success', $response));
        $tag = $this->query("SELECT * FROM tag WHERE `name` = 'Test tag';");
        $this->assertTrue($tag['name'] === 'Test tag');
        $this->assertTrue($tag['description'] === 'I am merely a test tag');
    }

    public function testCreationStoryTag()
    {
        $response = $this->apiPost('/tag/creation', $this->mockTag2);
        $this->assertTrue(array_key_exists('success', $response));
        $tag = $this->query("SELECT * FROM tag WHERE `name` = 'Test tag 2';");
        $this->assertTrue($tag['name'] === 'Test tag 2');
        $this->assertTrue($tag['description'] === 'I am merely a test tag 2');
    }

    public function testView()
    {
        $params = $this->mockTag;
        unset($params['type']);
        $response = $this->apiPost('/tag/view', $params);
        $this->assertTrue(array_key_exists('success', $response));
        $this->assertTrue(gettype($response['success']) === 'array');
    }

    public function testEdit()
    {
        $tag = $this->query("SELECT * FROM tag WHERE `name` = 'Test tag';");
        $tag['description'] = 'Iwillbechanged!';
        $tag['tag'] = $tag['id'];
        $tag['apiKey'] = $this->mockTag['apiKey'];
        $response = $this->apiPost('/tag/edit', $tag);
        $tag = $this->query("SELECT * FROM tag WHERE id = " . $tag['id'] . ";");
        $this->assertTrue($tag['description'] === 'Iwillbechanged!');
    }

    public function testDelete()
    {
        $tag1 = $this->query("SELECT * FROM tag WHERE `name` = 'Test tag';");
        $tag1['apiKey'] = $this->mockTag['apiKey'];
        $tag1['tag'] = $tag1['id'];
        $tag2 = $this->query("SELECT * FROM tag WHERE `name` = 'Test tag 2';");
        $tag2['apiKey'] = $this->mockTag2['apiKey'];
        $tag2['tag'] = $tag2['id'];
        $response = $this->apiPost('/tag/delete', $tag1);
        $response2 = $this->apiPost('/tag/delete', $tag2);
        $this->assertTrue(array_key_exists('success', $response));
        $this->assertTrue(array_key_exists('success', $response2));
        $tagTest = $this->query("SELECT * FROM tag WHERE id = " . $tag1['id'] . ";");
        $tagTest2 = $this->query("SELECT * FROM tag where id = " . $tag2['id'] . ";");
        $this->assertTrue($tagTest === null);
        $this->assertTrue($tagTest2 === null);
    }
}
