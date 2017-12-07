 <?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$filename = "test.sqlite3";
$database = $filename;
$tabella  = "coordinate";








//Connessione al database
class MyDB extends SQLite3
{
    function __construct($filename)
    {
        $this->open($filename);
    }
}

$db = new MyDB($filename);
if (!$db) {
    echo '{"status":"error", "message":"impossible to open the database"}';
}








if (isset($_GET["operation"])) {
    
    $operation = $_GET["operation"];
    
    if ($operation == "get_serverList") {
        
        
        
        $ret = $db->query("SELECT * FROM servers;");
        
        $data                          = array();
        $data["status"]                = "ok";
        $data["user"]                  = array();
        $data["user"]["id"]            = 1;
        $data["user"]["name"]          = "Mario";
        $data["items"]                 = array();
        $data["items"]["servers_list"] = array();
        
        
        while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
            $data["items"]["servers_list"][] = array(
                "server_id" => $row["server_id"],
                "address" => $row["address"],
                "port" => $row["port"],
                "name" => $row["name"],
                "description" => $row["description"],
                "timestamp" => $row["timestamp"],
                "ping" => 15,
                "maxPlayers" => 16,
                "players" => 9
            );
        }
        
        
        
        #$data["team"]["servers"] = array_values();
        
        echo json_encode($data);
        
        
        
        
    } else {
        
        
        echo "RIP";
        
        
    }
    
    
    
    
} else {
    
    
    
    
    
    
    
    if (($_SERVER['REQUEST_METHOD'] === 'POST')) {
        
        
        $operation = $_POST['operation'];
        
        if ($operation === "add_server") {
            
            
            
            
            
            $address     = SQLite3::escapeString($_POST['address']);
            $port        = SQLite3::escapeString($_POST['port']);
            $name        = SQLite3::escapeString($_POST['name']);
            $description = SQLite3::escapeString($_POST['description']);
            $timestamp   = time();
            
            
            
            if (isset($address, $port, $name, $description)) {
                $ret = $db->query("INSERT INTO servers (address, port, name, description, timestamp) VALUES('" . $address . "', " . $port . ", '" . $name . "', '" . $description . "', " . $timestamp . ");");
                
                echo '{"status":"ok", "message":"Server (maybe) added!"}';
                
            }
            
            
            
        } elseif ($operation === "del_server") {
            
            if (isset($_POST['server_id'])) {
                if (is_array($_POST['server_id'])) {
                    $server_id = $_POST['server_id'];
                } else {
                    $server_id = array();
                }
            } else {
                $server_id = array();
            }
            
            foreach ($server_id as &$value) {
                if (ctype_digit($value)) {
                    $ret = $db->query("DELETE FROM servers WHERE server_id = " . $value . ";");
                }
            }
            echo '{"status":"ok", "message":"All selected servers has been deleted"}';
            
            
        }
        
        
        
        
        
    }
    
    
    
}






?> 
