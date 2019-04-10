<?php
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StoryCompanionTestFunctions extends TestCase
{
    protected $testApiKey = 'b172df49e6e5233026f3c63a5cc41060eafc835c8af757c7b23d5c8a5931c3a32b010cf5e6cb5b8f30554abeb2efd81823745940315c8393d9413521679bbcad';
    protected $devUrl = "127.0.0.1:8000";

    protected $connection = null;
    private $username = 'root';
    private $password = '';
    private $host = "127.0.0.1:3306";
    private $db = "story_companion";

    public function beforeQuery()
    {
        if ($this->connection === null) {
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->db);
        }
    }

    public function query($query)
    {
        $this->beforeQuery();
        $result = $this->connection->query($query);
        if ($result === null) {
            return null;
        } elseif (gettype($result) === "boolean") {
            return $result;
        }
        return $result->fetch_assoc();
    }

    public function deleteUserWithEmail($email)
    {
        $this->beforeQuery();
        $query = "DELETE FROM user WHERE email = '" . $email . "';";
        $result = $this->connection->query($query);
        return $result;
    }

    public function apiPost($route, $params)
    {
        $params['apiKey'] = $this->testApiKey;
        $route = $this->devUrl . $route;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $route,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query($params),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }

    public function apiGet($route, $params)
    {
        $params['apiKey'] = $this->testApiKey;
        $route = $this->devUrl . $route . http_build_query($params);
        // $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $route,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }
}
