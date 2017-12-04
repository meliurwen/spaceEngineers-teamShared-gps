<html>

<head>
	<title>Space Engineers Shared GPS</title>
</head>

<body>
	<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

	$filename = "spaceEngineers.sqlite3";
	$database = $filename;
	$tabella = "coordinate";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$coordinate = $_POST['coordinate'];
	$descrizione = $_POST['descrizione'];
	$idServer = $_POST['idServer'];
	$idUtente = $_POST['idUtente'];

}
else{

	$deleteId = $_GET["delete"];

}


	//phpinfo();
	//exit;

	echo "<p><a href='" . $_SERVER['PHP_SELF'] . "?database=".$database."'>Indietro</a></p>";

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
		echo "Impossibile aprire il database<BR />";
	} else {
		echo "Database aperto con successo<BR />";
	}



if (($_SERVER['REQUEST_METHOD'] === 'POST') & isset($coordinate, $descrizione, $idServer, $idUtente)) {
	$ret = $db->query("INSERT INTO " . $tabella . " (coordinate, descrizione, idServer, idUtente) VALUES('" . $coordinate . "', '" . $descrizione . "', '" . $idServer . "', '" . $idUtente . "');");
}




	if(isset($deleteId) & !empty($deleteId)){
		$existsId = $db->querySingle("SELECT 1 FROM '" . $tabella . "' WHERE id = '" . $deleteId . "';");
		if ($existsId){
			$deletino = $db->query("DELETE FROM '" . $tabella . "' WHERE id = '" . $deleteId . "';");
			echo "<p>Elemento con id=" . $deleteId . " eliminato con successo</p>";
		}
		else{
			echo "<p>L'elemento con id=" . $deleteId . " NON esiste!</p>";
		}
	}
	else{
		echo "<p>Inserisci od elimina un elemento</p>";
	}

?>


<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
  <table>
  <td>
  <tr>
  <input type="text" name="coordinate" value="coordinate">
  </tr>
  <tr>
  <input type="text" name="descrizione" value="descrizione">
  </tr>
  <tr>
  <select name="idServer">
    <option value="0">Andromeda</option>
    <option value="1">Harambe</option>
  </select>
  </tr>
  <tr>
  <select name="idUtente">
    <option value="0">Mario</option>
    <option value="1">Dario</option>
    <option value="2">Alessio</option>
    <option value="3">Francesco</option>
  </select>
  </tr>
  <tr>
  <input type="submit" value="Aggiungi">
  </tr>
  </table>
</form>



<?php
	//INIZIO TABELLA
	//Numero Colonne Tabella
	$tablesquery = $db->query("PRAGMA table_info(" . $tabella . ");");
	$numColonne = 0;
	while($column = $tablesquery->fetchArray(SQLITE3_ASSOC)){
		$numColonne = $numColonne+1;
	};
	$numColonne = $numColonne + 1;	//Colonna in più per il taasto elimina
	echo "<TABLE border = '1' width='100%'>";
	//Nome tabella
	echo "<TR><TD colspan='" . $numColonne . "' align='center'><B>" . $tabella . "</B></TD></TR><TR>";
	echo "<TD></TD>";
	//Prima riga con i nomi delle colonne
	while ($nomeColonna = $tablesquery->fetchArray(SQLITE3_ASSOC)) {
		echo '<TD><B>' . $nomeColonna['name'] . '</B></TD>';
	}
	echo "</TR>";
	//Il resto delle righe con il contenuto della tabella
	$ret = $db->query("SELECT * FROM " . $tabella . ";");
	while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
		echo "<TR>";
		echo "<TD><a href='" . $_SERVER['PHP_SELF'] . "?database=" . $database . "&table=" . $tabella . "&delete=" . $row['id'] . "'><img src='delete.png' alt='Delete'></a><a href='#'><img src='modify.png' alt='Modify'></a></TD>";
		while ($nomeColonna = $tablesquery->fetchArray(SQLITE3_ASSOC)){
			echo "<TD>" . $row[$nomeColonna['name']] . "</TD>";
		}
		echo "</TR>";
	}
	echo "</TABLE>";
	//FINE TABELLA


	echo "Operazione completata con successo<BR />";
	//Chiusura connessione con il database
	$db->close();
	?>
</body>

</html>
