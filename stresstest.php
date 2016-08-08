<?php
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set("Europe/Berlin");
require_once ('db_connect.php');

// Verbindungs-Objekt samt Zugangsdaten festlegen
@$db = new mysqli(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT, MYSQL_DATENBANK);
//Bestimmt, dass die DB Verbindung UTF-8 ist.
mysqli_query($db, "SET NAMES 'utf8'");

// Verbindung überprüfen
if (mysqli_connect_errno()) {
  printf("Verbindung fehlgeschlagen: %s\n", mysqli_connect_error());
  exit();
}

//ToDo: Get HomepageID aus externer Datei einrichten
$homepageID = 100;
//ToDo: Get cityId aus externer Datei einrichten
$cityId = 6555548;
$cityIds = array(2875558,2817610,2854700,6547883,2913335,2864705,2807106,6551493,2842372,6551404,2956985,2880399,2880400,6551469,2883270,6551465,2886278,6551423,2939293,6551629,2807864,6551852,2911010,2944892,6551909,2846922,6551937,2813647,6552642,3209070,6552622,2951115,2877203,2877204,2840399,2897875,2904870,2913296,6556474,3205010,6556528,2890499,2855050,2828438,2918284,2904317,2926973,2883782,6556814,2808657,2825429,6557122,2837809,6556067,2898458,6556087,2879335,6555548,2941923,2807790,6553665,2944146,6554357,3207532,6555324,2909129,2859756,2867094,6555026,2952934,6555125,2867098,6554956,2896156,2835304,6552363,2917991,2931182,2859916,2908570,2959508,2878351,2848541,6554244,2869222,6553781,2842438,6554147,2897518,6553335,2848914,6553354,2813787,6554644,2892104,2950083,6548615,2885742,2864402,2949857,2882361,2855956,2805623,2874652,6550337,2818426,6550315,2934921,6549288,2806343,2949103,2895536,2913449,2911725,2908726,2851450,2825372,2946357,2918092,2883895);

foreach ($cityIds as &$cityId) {

	echo "Es gibt keine Daten mit der hinterlegten HomepageID in der Datenbank, die Daten werden ganz neu über die API abgeholt und in die DB eingetragen";
	//Die Daten müssen über die Schnittstelle abgerufne und in díe Datenbank eingetragen werden.
	//ToDo -> Überwachen ob die Anzahl der aufrufe des API Keys ausreichend sind
	$request = 'http://api.openweathermap.org/data/2.5/forecast/city?id='.$cityId.'&units=metric&APPID=3a9e4969aec4da208074af93275d4309';
	$response = file_get_contents($request);
	//ToDo Wird das benötigt?
	//$results = json_decode($response, TRUE);

	$sql = "INSERT INTO 5_d_3_h_forecast (homepageId, cityId, data) VALUES ('$homepageID', '$cityId', '$response')";
	$dbEintragen = $db->query($sql);
}


// Verbindung zum Datenbankserver beenden
$db->Close();
?>