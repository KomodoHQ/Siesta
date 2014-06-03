<?php

include ("src/Siesta.php");

class User {

    use Siesta;

    private static $siestaConfig = [
            "url" => "http://localhost:9999",
            "endpoint" => "users"
        ];

    function __construct($data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->email = $data['email'];
    }

}
