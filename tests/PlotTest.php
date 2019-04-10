<?php
require_once('StoryCompanionTestFunctions.php');

use PHPUnit\Framework\TestCase;

class PlotTest extends StoryCompanionTestFunctions
{
    private $mockPlot = array(
        'story' => 11,
        'name' => "Test Plot",
        'description' => 'I am merely a test plot',
        'apiKey' => 'b172df49e6e5233026f3c63a5cc41060eafc835c8af757c7b23d5c8a5931c3a32b010cf5e6cb5b8f30554abeb2efd81823745940315c8393d9413521679bbcad',
        'plotParent' => '',
    );

    public function testCreation()
    {
        $response = $this->apiPost('/plot/creation', $this->mockPlot);
        $this->assertTrue(array_key_exists('success', $response));
        $plot = $this->query("SELECT * FROM plot WHERE name = 'Test Plot';");
        $this->assertTrue($plot['name'] === 'Test Plot');
        $this->assertTrue($plot['description'] === 'I am merely a test plot');
    }

    public function testView()
    {
        $response = $this->apiPost('/plot/view', $this->mockPlot);
        $plot = $this->query("SELECT * FROM plot WHERE name = 'Test Plot';");
        $this->assertTrue(array_key_exists($plot['id'], $response['success']));
        $this->assertTrue($response['success'][$plot['id']]['name'] === 'Test Plot');
    }

    public function testEdit()
    {
        $plot = $this->query("SELECT * FROM plot WHERE name = 'Test Plot';");
        $plot['description'] = 'Iwillbechanged!';
        $plot['plot'] = $plot['id'];
        $plot['apiKey'] = $this->mockPlot['apiKey'];
        $plot['plotParent'] = '';
        $response = $this->apiPost('/plot/edit', $plot);
        $plot = $this->query("SELECT * FROM plot WHERE id = " . $plot['id'] . ";");
        $this->assertTrue($plot['description'] === 'Iwillbechanged!');
    }

    public function testExport()
    {
        $response = $this->apiPost("/plot/export", $this->mockPlot);
        $this->assertTrue(array_key_exists('success', $response));
    }

    public function testDelete()
    {
        $plot = $this->query("SELECT * FROM plot WHERE name = 'Test Plot';");
        $plot['apiKey'] = $this->mockPlot['apiKey'];
        $plot['plot'] = $plot['id'];
        $plot['plotParent'] = '';
        $response = $this->apiPost('/plot/delete', $plot);
        $this->assertTrue(array_key_exists('success', $response));
        $plot = $this->query("SELECT * FROM plot WHERE id = " . $plot['id'] . ";");
        $this->assertTrue($plot === null);
    }
}
