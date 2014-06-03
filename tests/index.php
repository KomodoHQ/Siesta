<?php

session_start();

$verb = strtolower($_SERVER['REQUEST_METHOD']);

function output($data) {
	echo json_encode([
			"result" => $data
		]);
}

header('Content-Type: application/json');

if (!isset($_SESSION['users']) || $_SERVER["REQUEST_URI"] == "/reset") {
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

	$_SESSION['users'] = $users;
} else {
	$users = $_SESSION['users'];
}

$matches;

if (preg_match('/^\/users\/([0-9]+)$/', $_SERVER["REQUEST_URI"],$matches)) {

	switch ($verb) {
		case 'get':
			output($users[$matches[1]]);
			break;
		case 'put':
			parse_str(file_get_contents('php://input'),$putVars);

			foreach ($putVars as $field => $value) {
				if (array_key_exists($field,$users[$matches[1]]))
					$users[$matches[1]][$field] = $value;
			}

			$_SESSION['users'] = $users;
			output($users[$matches[1]]);
			break;
		case 'delete':
			unset($users[$matches[1]]);
			$_SESSION['users'] = $users;
			output([
				"id"=> $matches[1],
				"deleted"=>1
				]);
			break;
	}
} else if (preg_match('/^\/users/', $_SERVER["REQUEST_URI"])) {

	switch ($verb) {
		case 'get':

			$results = [];
			if (array_key_exists("name",$_GET)) {
				foreach ($users as $user) {
					if ($user['name'] == $_GET['name'])
						$results[] = $user;
				}
			} else {
				$results = $users;
			}

			output($results);
			break;
		case 'post':
			$users[] = $_POST;
			$users[count($users)-1]["id"] = (int)$users[count($users)-1]["id"];
			$_SESSION['users'] = $users;
			output($users[count($users)-1]);
			break;
	}

} else {
    output(["message" => "Siesta Test API"]);
}
