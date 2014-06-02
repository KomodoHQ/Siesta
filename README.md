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
    const SIESTA_URL = "<your api url>";
    const SIESTA_ENDPOINT = "<api endpoint for this resource>";

    // constructor takes assoc array of properties
    function __construct($data) {
        $this->_id = $data['id'];
        $this->name = $data['name'];
    }
}

$users = User::find();

```
