<?php

trait Siesta
{
    /**********************
     * Private Properties *
     **********************/

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

        $results = [];

        $endpoint = $endpoint ?: self::SIESTA_ENDPOINT;

        $response = GuzzleHttp\get(self::SIESTA_URL . '/' . $endpoint);

        $response = self::readBody($response);

        foreach ($response['result'] as $result) {
            $results[] = new self($result);
        }

        return $results;
    }

    public static function findOne($queryParams = [], $endpoint = NULL)
    {
        $queryParams['limit'] = 1;

        $results = self::find($queryParams,$endpoint);

        return $results[0];
    }

    public static function findById($id, $endpoint = NULL)
    {
        $endpoint = $endpoint ?: self::SIESTA_ENDPOINT;

        $response = GuzzleHttp\get(self::SIESTA_URL . '/' . $endpoint . "/"  . (string)$id);

        $response = self::readBody($response);

        return new self($response['result']);
    }

    public static function create($data, $endpoint = NULL)
    {        

    }

    /******************
     * Instance Methods *
     ******************/
    public function update($data)
    {

    }

    public function save()
    {

    }

    public function delete()
    {

    }

    /******************
     * Private Methods *
     ******************/

    private static function readBody($response)
    {
        return json_decode((string)$response->getBody(),true);
    }

    private function toJSON()
    {

    }
}
