<?php
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set("Europe/Berlin");
require_once ('db_connect.php');

function weatherNewClient ($homepageID, $cityId, $db) {


//ToDo: Get HomepageID aus externer Datei einrichten
//$homepageID = 100;
//ToDo: Get cityId aus externer Datei einrichten
//$cityId = 6555548;

//Abfrage, ob es bereits einen DB Eintrag mit der HomepageID XY gibt, wenn ja zusätzlich prüfen, wie alt dieser ist.
$result = $db->query("SELECT '$homepageID' FROM 5_d_3_h_forecast");
var_dump($result);
//ToDo: Hier wird die Meldung Notice: Trying to get property of non-object in C:\xampp\htdocs\weather\index.php on line 18  ausgegeben
$row_cnt = $result->num_rows;
echo "TEST";
echo $row_cnt;
//Überprüfen ob es überhaupt Einträge zur Homepage ID gibt
if ($row_cnt != 0) {
	echo "Es gibt einen Datensatz mit der hinterlegten HomepageID in der DB jetzt wird geprüft ob dieser aktuell ist oder nicht";
	//Datum aus der Datenbank auslesen und in eine Variable schreiben.
	$sql = "SELECT date FROM 5_d_3_h_forecast WHERE homepageId = '$homepageID'";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	$dateEintrag = $row["date"];

	//Aktuelle Uhrzeit ermitteln und Formatieren anschließend mit der Uhrzeit aus der Datenbank vergleichen.
	$date = time() - 60*60; //-1Stunde
	$datum = date("Y-m-d",$date);
	$uhrzeit = date("H:i:s",$date);
	$aktuelleZeit = $datum." ".$uhrzeit; //-1Stunde

	$date1 = new DateTime($aktuelleZeit);
	$date2 = new DateTime($dateEintrag);

	$datumJetzt = date("Y-m-d");
	$uhrzeitJetzt = date("H:i:s");
	$aktuelleZeitJetzt = $datumJetzt." ".$uhrzeitJetzt;
 
	if ($date1 >= $date2)
	{
		//Daten sollen neu abgeholt und in die Datenbank geschrieben werden, die alten Einträge sollen geupdatet werden.
		//Darauf achten, dass die API abfrage immer gleich aufgebaut ist
	 	$request = 'http://api.openweathermap.org/data/2.5/forecast/city?id='.$cityId.'&lang=de&units=metric&APPID=3a9e4969aec4da208074af93275d4309';
		$response = file_get_contents($request);
		//ToDo Wird das benötigt?
		//$results = json_decode($response, TRUE);
		$sql = "UPDATE 5_d_3_h_forecast SET data='$response', date='$aktuelleZeitJetzt' WHERE homepageId='$homepageID'";
		$dbEintragen = $db->query($sql);
		//Ab hier kann dann die Ausgabe beginnen
		echo "Daten wurden in der DB aktualisiert";
		ausgabeWetter($db, $homepageID);
		//ToDo -> aktualisierung der Daten funktioniert derzeit nicht so genau wie es sollte. Evtl. hängt dies mit dem Hinweis zusammen, dass nur ein mal alle 10 Minuten Daten abgeholt werden sollen.
		//Die Meldungen erscheinen aber in der DB tut sich nichts und die Ausgabe wird ebenfalls nicht erreicht
		//evtl. eine andere Lösung als "file_get_contents" finden
	}
	else
	{
		//Daten sind aktuell und können ausgegeben werden
		echo "Wenn die Daten aktuell sind können Sie direkt aus der DB ausgegeben werden";
		$wetter = ausgabeWetter($db, $homepageID);
		return $wetter;
		//var_dump($wetter);
		//var_dump($wetter[0]);
		//echo $wetter[0]['Temp'];
		//ausgabeWetter($db, $homepageID);
	}
	
}
else{
	echo "Es gibt keine Daten mit der hinterlegten HomepageID in der Datenbank, die Daten werden ganz neu über die API abgeholt und in die DB eingetragen";
	//Die Daten müssen über die Schnittstelle abgerufne und in díe Datenbank eingetragen werden.
	//ToDo -> Überwachen ob die Anzahl der aufrufe des API Keys ausreichend sind
	//$request = 'http://api.openweathermap.org/data/2.5/forecast/city?id='.$cityId.'&lang=de&units=metric&APPID=3a9e4969aec4da208074af93275d4309';
	//$request = 'http://api.openweathermap.org/data/2.5/weather?id=6553292&lang=de&units=metric&appid=3a9e4969aec4da208074af93275d4309';
	$request = 'http://api.openweathermap.org/data/2.5/forecast/city?id='.$cityId.'&lang=de&units=metric&APPID=3a9e4969aec4da208074af93275d4309';
	$response = file_get_contents($request);
	//ToDo Wird das benötigt?
	//$results = json_decode($response, TRUE);

	$sql = "INSERT INTO 5_d_3_h_forecast (homepageId, cityId, data) VALUES ('$homepageID', '$cityId', '$response')";
	$dbEintragen = $db->query($sql);

	echo "ToDo - Ab hier könnte auch gleich die Ausgabe erfolgen";
	ausgabeWetter($db, $homepageID);
  
}
$db->Close();

}
//Funktion der Wetterausgabe
function ausgabeWetter($db, $homepageID)
{
	$sql = "SELECT data FROM 5_d_3_h_forecast WHERE homepageId = '$homepageID'";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	$data = $row["data"];

	//print_r(json_decode($data));

	$jsonarray = json_decode($data, true);
	//var_dump($jsonarray[ 'list' ]);
	//Jeder Index müsste auch überprüft werden ob er befüllt ist oder nicht.
	echo $jsonarray['city']['name']."<br>";
	$cnt = $jsonarray['cnt']; //Anzahl der enthalten Datensätze
	for ($i = 1 ; $i < $cnt; $i++ ) 
	{
		$wetter = array();
		$wetter['Temp'] = "Temp:".$jsonarray['list'][$i]['main']['temp']."<br>";
		$wetter['pressure'] =  "pressure:".$jsonarray['list'][$i]['main']['pressure']."<br>";
		$wetter['humidity'] =  "humidity:".$jsonarray['list'][$i]['main']['humidity']."<br>";
		$wetter['weather'] =  "weather:".$jsonarray['list'][$i]['weather']['0']['main']."<br>";
		$wetter['description'] =  "description:".$jsonarray['list'][$i]['weather']['0']['description']."<br>";
		$wetter['icon'] =  "icon:".$jsonarray['list'][$i]['weather']['0']['icon']."<br>";
		$wetter['clouds'] =  "clouds:".$jsonarray['list'][$i]['clouds']['all']."<br>";
		$wetter['windSpeed'] =  "wind Speed:".$jsonarray['list'][$i]['wind']['speed']."<br>";
		$wetter['winddeg'] =  "wind deg:".$jsonarray['list'][$i]['wind']['deg']."<br>";
		$wetter['date'] =  "Datum:".$jsonarray['list'][$i]['dt_txt']."<br><br>";

		/*echo "Temp:".$jsonarray['list'][$i]['main']['temp']."<br>";
		echo "pressure:".$jsonarray['list'][$i]['main']['pressure']."<br>";
		echo "humidity:".$jsonarray['list'][$i]['main']['humidity']."<br>";
		echo "weather:".$jsonarray['list'][$i]['weather']['0']['main']."<br>";
		echo "description:".$jsonarray['list'][$i]['weather']['0']['description']."<br>";
		echo "icon:".$jsonarray['list'][$i]['weather']['0']['icon']."<br>";
		echo "clouds:".$jsonarray['list'][$i]['clouds']['all']."<br>";
		echo "wind Speed:".$jsonarray['list'][$i]['wind']['speed']."<br>";
		echo "wind deg:".$jsonarray['list'][$i]['wind']['deg']."<br>";
		//echo "rain:".$jsonarray['list'][$i]['rain']['3h']."<br>"; Gefallene Regenmenge der letzten 3 Stunden
		//echo "snow:".$jsonarray['list'][$i]['snow']['3h']."<br>"; Gefallene Schneemenge der letzten 3 Stunden
		echo "Datum:".$jsonarray['list'][$i]['dt_txt']."<br><br>";*/
		$wetterVorschau[] = $wetter;
	};
	
	return $wetterVorschau;
}

// Verbindung zum Datenbankserver beenden
//$db->Close();

?>