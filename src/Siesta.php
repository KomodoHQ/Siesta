<?php

trait Siesta
{
    /**********************
     * Private Properties *
     **********************/
    private static $client;

    public static function populate($item)
    {
        return new self($item);
    }

    /**
     * Performs an API GET request to the configured endpoint
     *
     * @param array $queryParams Associative array containing query paramters to be appended to the
     * API call
     * @param string $endpoint Alternative endpoint to use in place of the endpoint for the class
     *
     * @returns array Array of instances of the current class
     */
    public static function find($queryParams = [], $endpoint = NULL)
    {
        if (!self::$client)
            self::siestaSetup();

        $items = [];

        $endpoint = $endpoint ?: self::$siestaConfig['endpoint'];

        $response = self::$client->get('/' . $endpoint,[
                'query' => $queryParams
            ]);

        $results = self::siestaReadBody($response);

        foreach ($results as $result) {
            $items[] = self::populate($result);
        }

        return $items;
    }

    public static function findOne($queryParams = [], $endpoint = NULL)
    {
        $queryParams['limit'] = 1;

        $results = self::find($queryParams,$endpoint);

        return $results[0];
    }

    public static function findById($id, $endpoint = NULL)
    {
        if (!self::$client)
            self::siestaSetup();

        $endpoint = $endpoint ?: self::$siestaConfig['endpoint'];

        $response = self::$client->get('/' . $endpoint . "/"  . (string)$id);

        $result = self::siestaReadBody($response);

        return self::populate($result);
    }

    public static function create($data, $endpoint = NULL)
    {
        if (!self::$client)
            self::siestaSetup();

        $endpoint = $endpoint ?: self::$siestaConfig['endpoint'];

        $response = self::$client->post("/" . $endpoint,[
            'body' => $data,
            'headers' => [
                    'Content-Type' => 'application/json'
                ]
        ]);

        $result = self::siestaReadBody($response);

        return new self($result);

    }

    /******************
     * Instance Methods *
     ******************/
    public function update($data)
    {
        return $this->save($data);
    }

    public function save($data = NULL)
    {
        if (!self::$client)
            self::siestaSetup();

        $data = $data ?: self::siestaToArray();

        $idProperty = self::$siestaConfig["idProperty"];

        $response = self::$client->put("/" . self::$siestaConfig['endpoint'] . "/" . $this->$idProperty,[
            'body' => $data,
            'headers' => [
                    'Content-Type' => 'application/json'
                ]
        ]);

        $result = self::siestaReadBody($response);

        foreach ($data as $key => $value) {
            if(array_key_exists($key,$result))
                $this->$key = $result[$key];
        }

        return $this;

    }

    public function delete()
    {

        if (!self::$client)
            self::siestaSetup();

        $idProperty = self::$siestaConfig["idProperty"];
        $response = self::$client->delete("/" . self::$siestaConfig['endpoint'] . "/" . $this->$idProperty);

        return self::siestaReadBody($response);

    }

    /******************
     * Private Methods *
     ******************/

    private static function siestaSetup()
    {
        self::$siestaConfig = array_merge([
            "url" => NULL,
            "endpoint" => "",
            "idProperty" => "id",
            "resultField" => "result",
            "requestContentType" => "application/json"
        ],self::$siestaConfig ?: []);

        if (!self::$siestaConfig["url"])
            throw new Exception("You Must Specify A URL For The API!");

        self::$client = new GuzzleHttp\Client(["base_url" => self::$siestaConfig["url"]]);

    }

    private static function siestaReadBody($response)
    {
        $obj = json_decode((string)$response->getBody(),true);

        return (self::$siestaConfig["resultField"]) ? $obj[self::$siestaConfig["resultField"]] : $obj;
    }

    private function siestaToArray()
    {
        return get_object_vars($this);
    }
}
