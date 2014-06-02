<?php

include ("src/Siesta.php");

class User {

    use Siesta;

    const SIESTA_URL = "http://localhost:9999";
    const SIESTA_ENDPOINT = "users";

    function __construct($data)
    {
        $this->_id = $data['id'];
        $this->name = $data['name'];
        $this->email = $data['email'];
    }

}
