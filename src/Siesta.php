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
    public static function find($queryParams = [], $options = [])
    {
        if (!self::$client)
            self::siestaSetup();

        $items = [];

        $endpoint = (array_key_exists('endpoint',$options)) ? $options['endpoint'] : self::$siestaConfig['endpoint'];

        $request = self::$client->createRequest('GET','/' . $endpoint,[
                'query' => $queryParams
            ]);

        $token = self::getSiestaOauthToken($options);

        if ($token)
            $request->setHeader('Authorization','Bearer ' . $token);

        $response = self::$client->send($request);
        $results = self::siestaReadBody($response);

        foreach ($results as $result) {
            $items[] = self::populate($result);
        }

        return $items;
    }

    public static function findOne($queryParams = [], $options = [])
    {
        $queryParams['limit'] = 1;

        $results = self::find($queryParams,$options);

        return $results[0];
    }

    public static function findById($id, $options = [])
    {
        if (!self::$client)
            self::siestaSetup();

        $endpoint = (array_key_exists('endpoint',$options)) ? $options['endpoint'] : self::$siestaConfig['endpoint'];

        $request = self::$client->createRequest('GET','/' . $endpoint . '/'  . (string)$id);

        $token = self::getSiestaOauthToken($options);

        if ($token)
            $request->setHeader('Authorization','Bearer ' . $token);

        $response = self::$client->send($request);

        $result = self::siestaReadBody($response);

        return self::populate($result);
    }

    public static function create($data, $options = [])
    {
        if (!self::$client)
            self::siestaSetup();

        $endpoint = (array_key_exists('endpoint',$options)) ? $options['endpoint'] : self::$siestaConfig['endpoint'];

        $request = self::$client->createRequest('POST','/' . $endpoint,[
            'body' => $data,
            'headers' => [
                    'Content-Type' => 'application/json'
                ]
        ]);

        $token = self::getSiestaOauthToken($options);

        if ($token)
            $request->setHeader('Authorization','Bearer ' . $token);

        $response = self::$client->send($request);
        $result = self::siestaReadBody($response);

        return new self($result);

    }

    /******************
     * Instance Methods *
     ******************/
    public function update($data, $options = [])
    {
        return $this->save($data,$options);
    }

    public function save($data = NULL,$options = [])
    {
        if (!self::$client)
            self::siestaSetup();

        $data = $data ?: self::siestaToArray();

        $idProperty = self::$siestaConfig["idProperty"];

        $request = self::$client->createRequest('PUT','/' . self::$siestaConfig['endpoint'] . '/' . $this->$idProperty,[
            'body' => $data,
            'headers' => [
                    'Content-Type' => 'application/json'
                ]
        ]);

        $token = self::getSiestaOauthToken($options);

        if ($token)
            $request->setHeader('Authorization','Bearer ' . $token);

        $response = self::$client->send($request);
        $result = self::siestaReadBody($response);

        foreach ($data as $key => $value) {
            if(array_key_exists($key,$result))
                $this->$key = $result[$key];
        }

        return $this;

    }

    public function delete($options = [])
    {

        if (!self::$client)
            self::siestaSetup();

        $idProperty = self::$siestaConfig["idProperty"];
        $request = self::$client->createRequest('DELETE','/' . self::$siestaConfig['endpoint'] . '/' . $this->$idProperty);

        $token = self::getSiestaOauthToken($options);

        if ($token)
            $request->setHeader('Authorization','Bearer ' . $token);

        $response = self::$client->send($request);

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
            "tokenField" => 'SIESTA_OAUTH_TOKEN',
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

    private static function getSiestaOauthToken($options = [])
    {
        if (array_key_exists('token',$options)) {
            return $options['token'];
        } else if (isset($_SESSION) && array_key_exists(self::$config['tokenField'],$_SESSION)) {
            return $_SESSION[self::$config['tokenField']];
        } else {
            return NULL;
        }
    }

    private function siestaToArray()
    {
        return get_object_vars($this);
    }
}
