<?php
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set("Europe/Berlin");
require_once ('db_connect.php');

function weatherNewClient ($homepageID, $cityId, $db){
	//Abfrage, ob es bereits einen DB Eintrag mit der HomepageID XY gibt, wenn ja zusätzlich prüfen, wie alt dieser ist.
	$result = $db->query("SELECT '$homepageID' FROM 5_d_3_h_forecast");

	//Es wird gezählt, wie viele Ergebnisse die DB Abfrage liefert
	$row_cnt = $result->num_rows;

	//Überprüfen ob es überhaupt Einträge zur Homepage ID gibt
	if ($row_cnt != 0) {
		//ToDo -> echo Löschen
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
	 	
	 	//Überprüfen, ob das aktuelle Datum älter als eine Stunde ist. Wenn ja sollen die Daten geupdatet werden
		if ($date1 >= $date2)
		{
			//Daten sollen neu abgeholt und in die Datenbank geschrieben werden, die alten Einträge sollen geupdatet werden.
			//Darauf achten, dass die API abfrage immer gleich aufgebaut ist
		 	$request = 'http://api.openweathermap.org/data/2.5/forecast/city?id='.$cityId.'&lang=de&units=metric&APPID=3a9e4969aec4da208074af93275d4309';
			$response = file_get_contents($request);

			$sql = "UPDATE 5_d_3_h_forecast SET data='$response', date='$aktuelleZeitJetzt' WHERE homepageId='$homepageID'";
			$dbEintragen = $db->query($sql);

			//Ab hier kann dann die Ausgabe beginnen
			//ToDo -> echo Löschen
			echo "Daten wurden in der DB aktualisiert";
			$wetter = ausgabeWetter($db, $homepageID);
			return $wetter;
		}
		else
		{
			//Daten sind aktuell und können ausgegeben werden
			//ToDo -> echo Löschen
			echo "Wenn die Daten aktuell sind können Sie direkt aus der DB ausgegeben werden";
			$wetter = ausgabeWetter($db, $homepageID);
			return $wetter;
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

		$sql = "INSERT INTO 5_d_3_h_forecast (homepageId, cityId, data) VALUES ('$homepageID', '$cityId', '$response')";
		$dbEintragen = $db->query($sql);

		//ToDo -> echo Löschen
		echo "ToDo - Ab hier könnte auch gleich die Ausgabe erfolgen";
		$wetter = ausgabeWetter($db, $homepageID);
		return $wetter;
	}
	$db->Close();
}

//Funktion der Wetterausgabe
function ausgabeWetter($db, $homepageID)
{
	//Daten werden aus der DB geladen
	$sql = "SELECT data FROM 5_d_3_h_forecast WHERE homepageId = '$homepageID'";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	$data = $row["data"];

	$jsonarray = json_decode($data, true);

	//Jeder Index müsste auch überprüft werden ob er befüllt ist oder nicht.
	echo $jsonarray['city']['name']."<br>";
	$cnt = $jsonarray['cnt']; //Anzahl der enthalten Datensätze
	for ($i = 1 ; $i < $cnt; $i++ ) 
	{
		$wetter = array();
		$wetter['Temp'] = $jsonarray['list'][$i]['main']['temp'];
		$wetter['pressure'] =  $jsonarray['list'][$i]['main']['pressure'];
		$wetter['humidity'] =  $jsonarray['list'][$i]['main']['humidity'];
		$wetter['weather'] =  $jsonarray['list'][$i]['weather']['0']['main'];
		$wetter['description'] =  $jsonarray['list'][$i]['weather']['0']['description'];
		$wetter['icon'] =  $jsonarray['list'][$i]['weather']['0']['icon'];
		$wetter['clouds'] =  $jsonarray['list'][$i]['clouds']['all'];
		$wetter['windSpeed'] =  $jsonarray['list'][$i]['wind']['speed'];
		$wetter['winddeg'] =  $jsonarray['list'][$i]['wind']['deg'];
		$wetter['date'] =  $jsonarray['list'][$i]['dt_txt'];

		$wetterVorschau[] = $wetter;
	};

	$threeDayForecast = getThreeDayForecast($wetterVorschau);
	
	return $threeDayForecast;
}

function getThreeDayForecast($wetterVorschau) 
{
	$date = time();
	$datum = date("Y-m-d",$date);

	//tomorrow
	$tomorrow = time() + 60*60*24;
	$tomorrowDate = date("Y-m-d",$tomorrow);

	//Uhrzeiten für den morgigen Tag definieren
	$tomorrow9AM = $tomorrowDate." 09:00:00";
	$tomorrow12AM = $tomorrowDate." 12:00:00";
	$tomorrow15AM = $tomorrowDate." 15:00:00";
	$tomorrow18AM = $tomorrowDate." 18:00:00";

	//the day after tomorrow
	$dayAfterTomorrow = time() + 60*60*24*2;
	$dayAfterTomorrowDate = date("Y-m-d",$dayAfterTomorrow);

	//Uhrzeiten für übermorgen definieren
	$dayAfterTomorrowDate9AM = $dayAfterTomorrowDate." 09:00:00";
	$dayAfterTomorrowDate12AM = $dayAfterTomorrowDate." 12:00:00";
	$dayAfterTomorrowDate15AM = $dayAfterTomorrowDate." 15:00:00";
	$dayAfterTomorrowDate18AM = $dayAfterTomorrowDate." 18:00:00";

	//3_day_forecast
	$threeDayForecastD = time() + 60*60*24*3;
	$threeDayForecastDate = date("Y-m-d",$threeDayForecastD);

	//Uhrzeiten für den 3.Tag definieren
	$threeDayForecastDate9AM = $threeDayForecastDate." 09:00:00";
	$threeDayForecastDate12AM = $threeDayForecastDate." 12:00:00";
	$threeDayForecastDate15AM = $threeDayForecastDate." 15:00:00";
	$threeDayForecastDate18AM = $threeDayForecastDate." 18:00:00";

	//Vorschau-Auswahl wird in ein Array geschrieben und zurückgegeben
	$threeDayForecast  = array();
	
	$threeDayForecast[] = array_multi_search($tomorrow9AM, $wetterVorschau); //Position im Array 0

	$threeDayForecast[] = array_multi_search($tomorrow12AM, $wetterVorschau); //Position im Array 1

	$threeDayForecast[] = array_multi_search($tomorrow15AM, $wetterVorschau); //Position im Array 2

	$threeDayForecast[] = array_multi_search($tomorrow18AM, $wetterVorschau); //Position im Array 3

	$threeDayForecast[] = array_multi_search($dayAfterTomorrowDate9AM, $wetterVorschau); //Position im Array 4

	$threeDayForecast[] = array_multi_search($dayAfterTomorrowDate12AM, $wetterVorschau); //Position im Array 5

	$threeDayForecast[] = array_multi_search($dayAfterTomorrowDate15AM, $wetterVorschau); //Position im Array 6

	$threeDayForecast[] = array_multi_search($dayAfterTomorrowDate18AM, $wetterVorschau); //Position im Array 7

	$threeDayForecast[] = array_multi_search($threeDayForecastDate9AM, $wetterVorschau); //Position im Array 8

	$threeDayForecast[] = array_multi_search($threeDayForecastDate12AM, $wetterVorschau); //Position im Array 9

	$threeDayForecast[] = array_multi_search($threeDayForecastDate15AM, $wetterVorschau); //Position im Array 10

	$threeDayForecast[] = array_multi_search($threeDayForecastDate18AM, $wetterVorschau); //Position im Array 11

	//var_dump($threeDayForecast);

	return $threeDayForecast;

}

function array_multi_search($mSearch, $aArray, $sKey = "")
	{
	    $aResult = array();
	   
	    foreach( (array) $aArray as $aValues)
	    {
	        if($sKey === "" && in_array($mSearch, $aValues)) $aResult[] = $aValues;
	        else
	        if(isset($aValues[$sKey]) && $aValues[$sKey] == $mSearch) $aResult[] = $aValues;
	    }
	   
	    return $aResult;
	}

?>