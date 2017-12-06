<?php

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);


	$filename = "spaceEngineers.sqlite3";
	$database = $filename;
	$tabella = "coordinate";




if(isset($_GET["what"])){

$what = $_GET["what"];

if($what == "serverList"){















	//Connessione al database
	class MyDB extends SQLite3
	{
		function __construct($filename)
		{
			$this->open($filename);
		}
	}

	$db = new MyDB($filename);
	if(!$db){
		echo '{"status":"error", "cause":"impossible to open the database"}';
	}




$ret = $db->query("SELECT * FROM servers;");

$data = [];
$data["status"] = "ok";
$data["user"] = [];
$data["user"]["id"] = 1;
$data["user"]["name"] = "Mario";
$data["team"] = [];



$data["team"][] = array("name" => "Team Rocket", "servers" => []);



while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
	$data["team"][0]["servers"][] =  array("id" => $row["id"], "name" => $row["server"], "address" => $row["indirizzo"], "ping" => 15, "maxPlayers" => 16, "players" => 9, "description" => $row["descrizione"], "owner" => "Rebel Gamers", "timestamp" => $row["timestamp"]);
}



#$data["team"]["servers"] = array_values();

echo json_encode($data);















}else{


echo "RIP";


}




} else{



	//Connessione al database
	class MyDB extends SQLite3
	{
		function __construct($filename)
		{
			$this->open($filename);
		}
	}

	$db = new MyDB($filename);
	if(!$db){
		echo '{"status":"error", "cause":"impossible to open the database"}';
	}



echo "AAA";
if (($_SERVER['REQUEST_METHOD'] === 'POST')) {


	$name = SQLite3::escapeString($_POST['name']);
	$ipaddress = SQLite3::escapeString($_POST['ipaddress']);
	$port = SQLite3::escapeString($_POST['port']);
	$description = SQLite3::escapeString($_POST['description']);
	$game = SQLite3::escapeString($_POST['game']);

echo "BBB";

if(isset($name, $ipaddress, $port, $description, $game)){
	$indirizzo = $ipaddress . ":" . $port;
	$ret = $db->query("INSERT INTO servers (server, indirizzo, descrizione) VALUES('" . $name . "', '" . $indirizzo . "', '" . $description . "');");
echo "oi";
}
}



}






?>
