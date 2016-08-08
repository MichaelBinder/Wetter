<?php
// Verbindungs-Objekt samt Zugangsdaten festlegen
@$db = new mysqli(MYSQL_HOST, MYSQL_BENUTZER, MYSQL_KENNWORT, MYSQL_DATENBANK);
//Bestimmt, dass die DB Verbindung UTF-8 ist.
mysqli_query($db, "SET NAMES 'utf8'");

// Verbindung überprüfen
if (mysqli_connect_errno()) {
  printf("Verbindung fehlgeschlagen: %s\n", mysqli_connect_error());
  exit();
}
?>