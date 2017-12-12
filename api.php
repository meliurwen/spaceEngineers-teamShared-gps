 <?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$filename = "spaceEngineersGps.sqlite3";
$database = $filename;
$tabella  = "coordinate";



if (file_exists($filename)) {
    $database_exists = True;
} else {
    $database_exists = False;
}





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
} else {
    $db->exec('PRAGMA foreign_keys = ON;');
}

if (!$database_exists) {
    
    $ret = $db->query("CREATE TABLE `settings` (
    `setting_id`    TEXT NOT NULL,
    `value_text`    TEXT,
    `value_int`    INTEGER,
    PRIMARY KEY(`setting_id`)
);");
    $ret = $db->query("CREATE TABLE `categories` (
    `category_id`    INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `name`    TEXT NOT NULL
);");
    $ret = $db->query("CREATE TABLE `owners` (
    `owner_id`    INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `name`    TEXT NOT NULL
);");
    $ret = $db->query("CREATE TABLE `servers` (
    `server_id`    INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `address`    TEXT NOT NULL,
    `port`    INTEGER NOT NULL,
    `name`    TEXT NOT NULL,
    `description`    TEXT,
    `timestamp`    INTEGER NOT NULL
);");
    $ret = $db->query("CREATE TABLE `coordinates` (
    `coordinate_id`    INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `raw`    TEXT NOT NULL,
    `timestamp`    INTEGER NOT NULL,
    `server_id`    INTEGER NOT NULL,
    `owner_id`    INTEGER NOT NULL,
    FOREIGN KEY(`owner_id`) REFERENCES `owners`(`owner_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(`server_id`) REFERENCES `servers`(`server_id`) ON DELETE CASCADE ON UPDATE CASCADE
);");
    $ret = $db->query("CREATE TABLE `categories_coordinates` (
    `category_id`    INTEGER NOT NULL,
    `coordinate_id`    INTEGER NOT NULL,
    FOREIGN KEY(`category_id`) REFERENCES `categories`(`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY(`coordinate_id`) REFERENCES `coordinates`(`coordinate_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY(`category_id`,`coordinate_id`)
);");
    
    $description_home = '<b>Info globali</b><br /> Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.<br /><br /><b>oreinfo</b><br /> Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>';
    $description_home = SQLite3::escapeString($description_home);
    
    $ret = $db->query("INSERT INTO `main`.`settings`(`setting_id`,`value_text`,`value_int`) VALUES ('title_big_home','Space Engineers',NULL);");
    $ret = $db->query("INSERT INTO `main`.`settings`(`setting_id`,`value_text`,`value_int`) VALUES ('title_small_home','Server List',NULL);");
    $ret = $db->query("INSERT INTO `main`.`settings`(`setting_id`,`value_text`,`value_int`) VALUES ('description_home','" . $description_home . "',NULL);");
    $ret = $db->query("INSERT INTO `main`.`categories`(`name`) VALUES ('Generic');");
    $ret = $db->query("INSERT INTO `main`.`owners`(`name`) VALUES ('Anonymous');");
    
    
}







if (isset($_GET["operation"])) {
    
    $operation = $_GET["operation"];
    
    if ($operation == "get_serverList") {
        
        
        
        $ret = $db->query("SELECT * FROM servers ORDER BY server_id ASC;");
        $pet = $db->query("SELECT * FROM settings;");
        
        $data                 = array();
        $data["status"]       = "ok";
        $data["user"]         = array();
        $data["user"]["id"]   = 1;
        $data["user"]["name"] = "Mario";
        $data["items"]        = array();
        
        $data["items"]["settings"] = array();
        while ($row = $pet->fetchArray(SQLITE3_ASSOC)) {
            $data["items"]["settings"][$row["setting_id"]] = $row["value_text"];
        }
        
        
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
        
        
        
    } elseif ($operation == "get_serverCoords" && isset($_GET["server_id"])) {
        
        $server_id = $_GET["server_id"];
        if (ctype_digit($_GET["server_id"])) {
            
            $ret          = $db->query("SELECT coordinates.coordinate_id, coordinates.raw, coordinates.timestamp, owners.name as owner_name FROM coordinates, owners WHERE coordinates.server_id = " . $server_id . " ORDER BY coordinates.coordinate_id ASC;");
            $server_q     = $db->query("SELECT * FROM servers WHERE server_id = " . $server_id . " LIMIT 1;");
            $categories_q = $db->query("SELECT * FROM categories;");
            $owners_q     = $db->query("SELECT * FROM owners;");
            
            
            $data                        = array();
            $data["status"]              = "ok";
            $data["user"]                = array();
            $data["user"]["id"]          = 1;
            $data["user"]["name"]        = "Mario";
            $data["items"]               = array();
            $data["items"]["categories"] = array();
            while ($row = $categories_q->fetchArray(SQLITE3_ASSOC)) {
                $data["items"]["categories"][] = array(
                    "id" => $row["category_id"],
                    "name" => $row["name"]
                );
            }
            $data["items"]["owners"] = array();
            while ($row = $owners_q->fetchArray(SQLITE3_ASSOC)) {
                $data["items"]["owners"][] = array(
                    "id" => $row["owner_id"],
                    "name" => $row["name"]
                );
            }
            $data["items"]["server_coords"]           = array();
            $data["items"]["server_coords"]["server"] = array();
            while ($row = $server_q->fetchArray(SQLITE3_ASSOC)) {
                $data["items"]["server_coords"]["server"]["name"]        = $row["name"];
                $data["items"]["server_coords"]["server"]["address"]     = $row["address"];
                $data["items"]["server_coords"]["server"]["port"]        = $row["port"];
                $data["items"]["server_coords"]["server"]["description"] = $row["description"];
            }
            $data["items"]["server_coords"]["coords"] = array();
            
            while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
                $set = $db->query("SELECT name FROM categories WHERE category_id IN (SELECT category_id FROM categories_coordinates WHERE coordinate_id = " . $row["coordinate_id"] . ");");
                
                $coords_catergories = array();
                
                while ($rew = $set->fetchArray(SQLITE3_ASSOC)) {
                    $coords_catergories[] = $rew["name"];
                }
                
                $data["items"]["server_coords"]["coords"][] = array(
                    "coordinate_id" => $row["coordinate_id"],
                    "raw" => $row["raw"],
                    "owner_name" => $row["owner_name"],
                    "timestamp" => $row["timestamp"],
                    "categories" => $coords_catergories
                );
            }
            
            
            
            #$data["team"]["servers"] = array_values();
            
            echo json_encode($data);
            
        } else {
            echo '{"status":"error", "message":"server_id must be an integer"}';
        }
        
        
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
            
            
        } elseif ($operation === "add_coordinates") {
            
            
            
            
            
            $raw        = SQLite3::escapeString($_POST['raw']);
            $timestamp  = time();
            $server_id  = SQLite3::escapeString($_POST['server_id']);
            $owner_id   = SQLite3::escapeString($_POST['owner_id']);
            $categories = $_POST['categories'];
            
            
            if (isset($raw, $server_id, $owner_id, $categories)) {
                $ret    = $db->query("INSERT INTO coordinates (raw, timestamp, server_id, owner_id) VALUES('" . $raw . "', " . $timestamp . ", " . $server_id . ", " . $owner_id . ");");
                $lastid = $db->lastInsertRowid();
                foreach ($categories as &$category_id) {
                    if (ctype_digit($category_id)) {
                        $ret = $db->query("INSERT INTO categories_coordinates (category_id, coordinate_id) VALUES(" . $category_id . ", " . $lastid . ");");
                    }
                }
                echo '{"status":"ok", "message":"Coordinates (maybe) added!"}';
                
            }
        } elseif ($operation === "del_coordinates") {
            if (isset($_POST['coordinate_id'])) {
                if (is_array($_POST['coordinate_id'])) {
                    $server_id = $_POST['coordinate_id'];
                } else {
                    $server_id = array();
                }
            } else {
                $server_id = array();
            }
            
            foreach ($server_id as &$value) {
                if (ctype_digit($value)) {
                    $ret = $db->query("DELETE FROM coordinates WHERE coordinate_id = " . $value . ";");
                }
            }
            echo '{"status":"ok", "message":"All selected coordinates has been deleted"}';
            
        }
        
        
        
        
        
    }
    
    
    
}




$db->close();



?> 
