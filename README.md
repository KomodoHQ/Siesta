# Siesta

Easily add API Consumption to your PHP Classes.

Siesta will add methods to consume your JSON REST API and convert the results to instances of your
PHP models.

## Installation

composer.json:

``` json
{
    "require": {
        "komodohq/siesta": "dev-master"
    }
}
```

```
$ composer install
```

## Usage

``` php

class User {

    use Siesta\Siesta;

    private static $siestaConfig = [
            "url" => "http://localhost:9999",
            "endpoint" => "users"
        ];

    // constructor takes assoc array of properties
    function __construct($data) {
        $this->_id = $data['id'];
        $this->name = $data['name'];
    }
}

$users = User::find();

```

## Methods

### `Siesta::find`

``` php
User::find([$queryParams[, $options]]);

User::find();
User::find(["username" => "OiNutter"]);
User::find(["username" => "OiNutter"], ["endpoint" => "administrators"]);
```

Performs a GET request to `/<endpoint>` that returns a collection of items matching the supplied
query parameters.

NB. This method is also used by the findOne method so be aware if you override this method it will
also affect that method.

### `Siesta::findOne`

``` php
User::findOne([$queryParams[, options]]);

User:find();
User::findOne(["username" => "OiNutter"]);
User::findOne(["username" => "OiNutter"], ["endpoint" => "administrators"]);
```

Performs a GET request to `/{{endpoint}}/{{this->id}}` and returns the first result to match the supplied query parameters. Sets an
additional `limit` parameter to 1 so if the API being consumed supports that it will limit the amount
of responses returned and save work on your end.

### `Siesta::findById`

``` php
User::findById($id[, $options]);

User::findById(1);
User::findById(1, ["endpoint" => "administrators"]);
```

Performs a GET request to `/{{endpoint}}/{{this->id}}` and returns a single result with the matching id.

### `Siesta::create`

``` php
User::create($data[, $options]);

User::create(["username" => "Bob", "email" => "bob@komododigital.co.uk"]);
User::create(
    ["username" => "Bob", "email" => "bob@komododigital.co.uk"],
    ["endpoint" => "administrators"]
);
```

Performs a POST request to `/{{endpoint}}` with the supplied data and returns the newly created resource.

### `$siestaItem->update`

``` php
$user->update($data[, $options]);

$user->update(["location" => "Newcastle Upon Tyne"]);
$user->update(["location" => "Newcastle Upon Tyne"], ["endpoint" => "administrators"]);
```

Performs a PUT request to `/{{endpoint}}/{{this->id}}` with the supplied data and returns the updated
resource. It will also update the local resource with any fields that have been updated on the server.

### `$siestaItem->save`

``` php
$user->save([$options]);

$user->save();
$user->save(["endpoint" => "administrators"]);
```

Performs a PUT request to `/{{endpoint}}/{{this->id}}` with the current resource and returns the updated
resource. It will also update the local resource with any fields that have been updated on the server.

NB. This method is also used by the update method so be aware if you override this method it will
also affect that method.

### `$siestaItem->delete()`

``` php
$user->delete([$options]);

$user->delete(["endpoint" => "administarots"]);
```

Performs a DELETE request to `/{{endpoint}}/{{this->id}}` and returns an associative array representing
the HTTP response body returned from the server.


## Customisation

### `Siesta::$siestaConfig`

To configure your class to use Siesta you need to add a `private static $siestaConfig` variable to
your class. This is an `associative array` which can contain the following keys:

* `url` - The FQD of the API you are going to be interacting with. **Required**
* `endpoint` - The endpoint on the API that maps to the resource your class represents. Defaults to
    `''`.
* `idProperty`- The primary key field for your class. Can be different from the primary key on the
    API Response. Defaults to `id`.
* `resultField` - The property of the API response that contains the result of the query or action.
    Defaults to `result`
* `tokenField` - The `$_SESSION` key under which your oauth token is stored. Defaults to
    `SIESTA_OAUTH_TOKEN`.
* `requestContentType` - The body content type that is sent in PUT and POST requests. Defaults to
    `application/json`.


### `Siesta::populate`

To change how a new model is created from the returned API data you will need to override the
`populate` method. The default passes the returned data as an `associative array` to the class'
constructor method.

``` php
public static function populate($item)
{
    return new static($item);
}
```

But say you wanted to pass the properties as individual arguments to the constructor, you could
override it in your class definition so it looked like this:

```php
public static function populate($item)
{
    $reflect  = new ReflectionClass($class);
    return $reflect->newInstanceArgs($item);
}
```


### `Siesta->toArray`

Siesta adds a `private` `toArray` method to your class. This is used to serialize the class for
sending in a save request. The default merely calls `get_object_vars` on itself, like so:

``` php
public function toArray()
{
    return get_object_vars($this);
}
```

but you can override that to provide your own serialisation.

## Exceptions

### `SiestaGeneralException`

Base class for any general exceptions. Stores the response body which can be retrieved like so:

``` php
$e->getResponse();
```

### `SiestaClientException`

Exception class for HTTP error codes in the 400 range. Extends `SiestaGeneralException`.

### `SiestaServerException`

Exception class for HTTP error codes in the 500 range. Extends `SiestaGeneralException`.


## Laravel

If using Laravel you can omit the URL config variable for Siesta and it will look for
`Config::get('api.url')` instead.

## TODO

* Error Handling
* More Tests
* Tidy up tests
