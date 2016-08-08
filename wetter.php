<?php
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set("Europe/Berlin");
//ToDo Richtige WetterClient einfügen
include('wetterClient.php');
include('db_connection.php');

//Hier werden immer die HomepageID und CityId des Projektes definiert
$homepageID = 100;
$cityId = 6555548;
//$params = array(23700, 12835747, 'wetter.htm', 64);

$wetter = weatherNewClient($homepageID, $cityId, $db);

include('wetter.html');

?>