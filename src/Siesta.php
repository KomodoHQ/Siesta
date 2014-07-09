<?php

namespace Siesta;

use GuzzleHttp,
    GuzzleHttp\Exception\ClientException,
    GuzzleHttp\Exception\ServerException;

/**
 * Custom Exception class for any general erros
 */
class SiestaGeneralException extends \Exception
{

    /**
     * Assoc array representing the JSON response from the server
     */
    protected $response;

    public function __construct($message = null, $code = 0, $e = null)
    {
        parent::__construct($message,$code,$e);
        if($e->getResponse()) {
            $this->response = json_decode((string)$e->getResponse()->getBody(),true);
        }
    }

    final public function getResponse()
    {
        return $this->response;
    }

}

/**
 * Custom Exception class for when an HTTP Status Code in the 400 range is returned
 */
class SiestaClientException extends SiestaGeneralException
{
}

/**
 * Custom Exception class for when an HTTP Status Code in the 500 range is returned
 */
class SiestaServerException extends SiestaGeneralException
{
}

/**
 * Collection of methods that map to REST API calls
 *
 * Provides common database type methods to a class which map to REST Api CRUD calls, allowing a
 * REST API to be used as a data source.
 */
trait Siesta
{
    /**********************
     * Private Properties *
     **********************/

    /**
     * Stores the GuzzleHttp\Client instance used for all requests.
     */
    protected static $client;


    /*************************
     * Public Static Methods *
     *************************/

    /**
     * Takes the data from the API and turns it into an instance of the current class.
     *
     * @param array $item An associative array representing the JSON decoded data for a single item in the
     * API response.
     *
     * @return class New instance of current class.
     */
    public static function populate($item)
    {
        return new static($item);
    }

    /**
     * Performs an API GET request to the configured endpoint.
     *
     * @param array $queryParams Associative array containing query paramters to be appended to the
     * API call.
     * @param array $options Optional configuration for this request. Supported keys are:
     *
     * * endpoint - Overrides the default API endpoint for this request.
     * * token - Specifies the OAuth Token to use for this request. Uses the $_SESSION one by default.
     *
     * @return array Array of instances of the current class.
     */
    public static function find($queryParams = [], $options = [])
    {

        static::siestaSetup();

        if (!static::$client) {
            static::initClient();
        }

        $items = [];

        $endpoint = (array_key_exists('endpoint',$options)) ? $options['endpoint'] : static::$siestaConfig['endpoint'];

        $request = static::$client->createRequest('GET','/' . $endpoint,[
                'query' => $queryParams
            ]);

        $token = static::getSiestaOauthToken($options);

        if ($token) {
            $request->setHeader('Authorization','Bearer ' . $token);
        }

        /*
         * If GuzzleHttp Exceptions occur, convert them to Siesta exceptions, better for future
         * proofing if we ever replace Guzzle
         */
        try {

            $response = static::$client->send($request);
            $results = static::siestaReadBody($response);

        } catch (ClientException $e) {

            throw new SiestaClientException($e->getMessage(),$e->getCode(),$e);

        } catch (ServerException $e) {

            throw new SiestaServerException($e->getMessage(),$e->getCode(),$e);

        } catch (\Exception $e) {

            throw new SiestaGeneralException($e->getMessage(),$e->getCode(),$e);

        }

        foreach ($results as $result) {
            $items[] = static::populate($result);
        }

        return $items;
    }

    /**
     * Performs an API GET request to the configured endpoint to get a single result.
     *
     * @param array $queryParams Associative array containing query paramters to be appended to the
     * API call.
     * @param array $options Optional configuration for this request. Supported keys are:
     *
     * * endpoint - Overrides the default API endpoint for this request.
     * * token - Specifies the OAuth Token to use for this request. Uses the $_SESSION one by default.
     *
     * @return class Instance of the current class.
     */
    public static function findOne($queryParams = [], $options = [])
    {
        $queryParams['limit'] = 1;

        $results = static::find($queryParams,$options);

        return (count($results) >= 1) ? $results[0] : null;
    }

    /**
     * Performs an API GET request to the configured endpoint for a specific result
     *
     * @param string|int $id id of resource you wish to retrieve
     * @param array $options Optional configuration for this request. Supported keys are:
     *
     * * endpoint - Overrides the default API endpoint for this request
     * * token - Specifies the OAuth Token to use for this request. Uses the $_SESSION one by default
     *
     * @return class Instance of the current class that represents the requested resource.
     */
    public static function findById($id, $options = [])
    {
        static::siestaSetup();

        if (!static::$client) {
            static::initClient();
        }

        $endpoint = (array_key_exists('endpoint',$options)) ? $options['endpoint'] : static::$siestaConfig['endpoint'];

        $request = static::$client->createRequest('GET','/' . $endpoint . '/'  . (string)$id);

        $token = static::getSiestaOauthToken($options);

        if ($token) {
            $request->setHeader('Authorization','Bearer ' . $token);
        }

        /*
         * If GuzzleHttp Exceptions occur, convert them to Siesta exceptions, better for future
         * proofing if we ever replace Guzzle
         */
        try {

            $response = static::$client->send($request);
            $result = static::siestaReadBody($response);

        } catch (ClientException $e) {

            throw new SiestaClientException($e->getMessage(),$e->getCode(),$e);

        } catch (ServerException $e) {

            throw new SiestaServerException($e->getMessage(),$e->getCode(),$e);

        } catch (\Exception $e) {

            throw new SiestaGeneralException($e->getMessage(),$e->getCode(),$e);

        }

        return ($result) ? static::populate($result) : null;

    }

    /**
     * Performs an API POST request to the configured endpoint to create a new resource with the
     * supplied data.
     *
     * @param array $data Associative array containing the data to send to the API to create a new
     * resource with.
     * @param array $options Optional configuration for this request. Supported keys are:
     *
     * * endpoint - Overrides the default API endpoint for this request
     * * token - Specifies the OAuth Token to use for this request. Uses the $_SESSION one by default
     *
     * @return class Instance of the current class that represents the newly created resource.
     */
    public static function create($data, $options = [])
    {
        static::siestaSetup();

        if (!static::$client) {
            static::initClient();
        }

        $endpoint = (array_key_exists('endpoint',$options)) ? $options['endpoint'] : static::$siestaConfig['endpoint'];

        $request = static::$client->createRequest('POST','/' . $endpoint,[
            'body' => json_encode($data),
            'headers' => [
                    'Content-Type' => 'application/json'
                ]
        ]);

        $token = static::getSiestaOauthToken($options);

        if ($token) {
            $request->setHeader('Authorization','Bearer ' . $token);
        }

        /*
         * If GuzzleHttp Exceptions occur, convert them to Siesta exceptions, better for future
         * proofing if we ever replace Guzzle
         */
        try {

            $response = static::$client->send($request);
            $result = static::siestaReadBody($response);

        } catch (ClientException $e) {

            throw new SiestaClientException($e->getMessage(),$e->getCode(),$e);

        } catch (ServerException $e) {

            throw new SiestaServerException($e->getMessage(),$e->getCode(),$e);

        } catch (\Exception $e) {

            throw new SiestaGeneralException($e->getMessage(),$e->getCode(),$e);

        }

        return static::populate($result);

    }

    /***************************
     * Public Instance Methods *
     ***************************/

    /**
     * Alias method for save. Only difference is the $data param is required.
     *
     * @see save()
     *
     * @param array|null $data Associative array containing the data to send to the API to update the
     * resource with. If not supplied Siesta will call its toArray() method to serialize the current
     * instance.
     * @param array $options Optional configuration for this request. Supported keys are:
     *
     * * token - Specifies the OAuth Token to use for this request. Uses the $_SESSION one by default
     *
     */
    public function update($data, $options = [])
    {
        return $this->save($data,$options);
    }

    /**
     * Performs an API PUT request to the configured endpoint to update a resource with the new
     * properties.
     *
     * @param array|null $data Associative array containing the data to send to the API to update the
     * resource with. If not supplied Siesta will call its toArray() method to serialize the current
     * instance.
     * @param array $options Optional configuration for this request. Supported keys are:
     *
     * * token - Specifies the OAuth Token to use for this request. Uses the $_SESSION one by default
     *
     * @return class The current instance of the class.
     */
    public function save($data = null,$options = [])
    {
        static::siestaSetup();

        if (!static::$client) {
            static::initClient();
        }

        $data = $data ?: $this->toArray();

        $idProperty = static::$siestaConfig["idProperty"];
        $isNew = !isset($this->$idProperty);
        $request = static::$client->createRequest(
            (!$isNew) ? 'PUT' : 'POST',
            '/' . static::$siestaConfig['endpoint'] . ((!$isNew) ? '/' . $this->$idProperty : ''),
            [
                'body' => json_encode($data),
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]
        );

        $token = static::getSiestaOauthToken($options);

        if ($token) {
            $request->setHeader('Authorization','Bearer ' . $token);
        }


        /*
         * If GuzzleHttp Exceptions occur, convert them to Siesta exceptions, better for future
         * proofing if we ever replace Guzzle
         */
        try {

            $response = static::$client->send($request);
            $result = static::siestaReadBody($response);

        } catch (ClientException $e) {

            throw new SiestaClientException($e->getMessage(),$e->getCode(),$e);

        } catch (ServerException $e) {

            throw new SiestaServerException($e->getMessage(),$e->getCode(),$e);

        } catch (\Exception $e) {

            throw new SiestaGeneralException($e->getMessage(),$e->getCode(),$e);

        }

        if($isNew && array_key_exists(static::$siestaConfig["idField"],$result)) {
            $this->$idProperty = $result[static::$siestaConfig["idField"]];
        }

        foreach ($result as $key => $value) {
            if(property_exists($this,$key)) {
                $this->setValue($key, $result[$key]);
            }
        }

        return $this;

    }

    /**
     * Performs an API DELETE request to the configured endpoint to delete a resource.
     *
     * @param array $options Optional configuration for this request. Supported keys are:
     *
     * * token - Specifies the OAuth Token to use for this request. Uses the $_SESSION one by default
     *
     * @return array The API response.
     */
    public function delete($options = [])
    {

        static::siestaSetup();

        if (!static::$client) {
            static::initClient();
        }

        $idProperty = static::$siestaConfig["idProperty"];
        $request = static::$client->createRequest('DELETE','/' . static::$siestaConfig['endpoint'] . '/' . $this->$idProperty);

        $token = static::getSiestaOauthToken($options);

        if ($token) {
            $request->setHeader('Authorization','Bearer ' . $token);
        }

        /*
         * If GuzzleHttp Exceptions occur, convert them to Siesta exceptions, better for future
         * proofing if we ever replace Guzzle
         */
        try {

            $response = static::$client->send($request);

        } catch (ClientException $e) {

            throw new SiestaClientException($e->getMessage(),$e->getCode(),$e);

        } catch (ServerException $e) {

            throw new SiestaServerException($e->getMessage(),$e->getCode(),$e);

        } catch (\Exception $e) {

            throw new SiestaGeneralException($e->getMessage(),$e->getCode(),$e);

        }

        return static::siestaReadBody($response);

    }

    /**
     * Serializes the current instance's properties into an array.
     *
     * @return array The serialized class
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    public function setValue($property, $value)
    {
        $this->$property = $value;
    }

    /**************************
     * Private Static Methods *
     **************************/

    /**
     * Sets up the GuzzleHttp\Client instance and extends the default config with the new values
     * specified in the class.
     */
    protected static function siestaSetup()
    {
        static::$siestaConfig = array_merge([
            "url" => null,
            "endpoint" => "",
            "idProperty" => "id",
            "idField" => "_id",
            "resultField" => "result",
            "tokenField" => 'SIESTA_OAUTH_TOKEN',
            "requestContentType" => "application/json"
        ],static::$siestaConfig ?: []);
    }

    protected static function initClient()
    {
        if (!static::$siestaConfig["url"]) {

            /**
             * If we're in Laravel environment get the url from the Config. Workaround for PHP's
             * inadequate closure implementation
             */
            if(class_exists('Config') && is_callable(['\Config','get']) && \Config::get('api.url')) {
                static::$siestaConfig["url"] = \Config::get('api.url');
            } else {
                throw new Exception("You Must Specify A URL For The API!");
            }
        }

        static::$client = new GuzzleHttp\Client(["base_url" => static::$siestaConfig["url"]]);
    }

    /**
     * Extracts the results of the API query from the GuzzleHttp\Stream
     *
     * @param GuzzleHttp\Stream $response The response from an API request.
     *
     * @return array The results of the request
     */
    protected static function siestaReadBody($response)
    {
        $obj = json_decode((string)$response->getBody(),true);

        return (static::$siestaConfig["resultField"]) ? $obj[static::$siestaConfig["resultField"]] : $obj;
    }

    /**
     * Checks all the possible places and finds the OAuth Token to use for a request
     *
     * @param array $options The array of options passed to the request method
     *
     * @return string|null The OAuth Token to use for the request
     */
    protected static function getSiestaOauthToken($options = [])
    {

        if (array_key_exists('token',$options)) {
            return $options['token'];
        } else if (class_exists('Session') && is_callable(['\Session','get']) && \Session::has(static::$siestaConfig['tokenField'])) {
            return \Session::get(static::$siestaConfig['tokenField']);
        } else if (isset($_SESSION) && array_key_exists(static::$siestaConfig['tokenField'],$_SESSION)) {
            return $_SESSION[static::$siestaConfig['tokenField']];
        } else {
            return null;
        }
    }

}
