<?php

$verb = strtolower($_SERVER['REQUEST_METHOD']);

function output($data) {
	echo json_encode([
			"result" => $data
		]);
}

header('Content-Type: application/json');

$users = [
			[
				"id" => 0,
				"name" => "Will McKenzie",
				"email" => "will@komododigital.co.uk"
			],
			[
				"id" => 1,
				"name" => "Alan Mitchell",
				"email" => "alan@komododigital.co.uk"
			]
	];


if (preg_match('/^\/users$/', $_SERVER["REQUEST_URI"])) {

	switch ($verb) {
		case 'get':
			output($users);
			break;
	}

} else {
    echo "<p>Siesta Test API</p>";
}
