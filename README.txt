##############
Weather 
Daten werden über http://openweathermap.org/ bezogen

API aufruf erfolgt nach dem Schema
http://api.openweathermap.org/data/2.5/forecast/city?id=12345&APPID={APIKEY} 

ToDo
# ist die Abfrage via file_get_contents in Ordnung oder muss hier etwas anderes gefunden werden?

Datenbank Struktur
DB Name
weather

DB Tabelle
5_d_3_h_forecast

DB Spalten 
1 id 		Primärschlüssel	int(11)	AUTO_INCREMENT	
2 homepageId			int(11)		
3 cityId			int(11)		
4 data		text		utf8_general_ci		
5 date		timestamp	on update CURRENT_TIMESTAMP	
