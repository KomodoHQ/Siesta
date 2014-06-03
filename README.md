# Siesta

Easily add API Consumption to your PHP Classes.

## Installation

composer.json:

``` json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "hg@bitbucket.org/komodohq/komodo-siesta"
        }
    ],
    "require": {
        "komodohq/komodo-siesta": "dev-master"
    }
}
```

```
$ composer install
```

## Usage

``` php

class User {

    use Siesta;

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
    return new self($item);
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


## TODO

* Error Handling
* More Tests
* Tidy up tests
