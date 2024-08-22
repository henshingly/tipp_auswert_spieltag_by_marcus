<?php
/*------------------------------------------------------------------------------
// Individuelle Auswertung der Tippspielligen für LMO 4
//     mit Einstellmöglichkeit, dass nur die X-besten Spieltage gewertet werden
// Autor: Marcus - www.bcerlbach.de
//------------------------------------------------------------------------------
  *
  * This program is free software; you can redistribute it and/or
  * modify it under the terms of the GNU General Public License as
  * published by the Free Software Foundation; either version 2 of
  * the License, or (at your option) any later version.
  * 
  * This program is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
  * General Public License for more details.
  *
  * REMOVING OR CHANGING THE COPYRIGHT NOTICES IS NOT ALLOWED!
  *
------------------------------------------------------------------------------*/



/***** ab hier können Einstellungen vorgenommen werden ************************/
	
// Schriftart - bei Standardschrift Feld leer lassen -> $fontfamily = "";
$fontfamily = "Verdana, Arial, Courier, MS Serif";

// Größe der Tabellenschrift in Punkt
$fontsize = "10";

// Schriftfarbe
$fontcolor = "#000000";

// Schriftfarbe bei Fehlermeldungenim
$fontfehl = "#800000";

//Schriftgröße der Überschrift in Pixel
$headlinefontsize = "20";

// Farbe des Tabellenkopfes
$tablehaedercolor = "#C7CFD6";

// Farbe des Tabellenschriftfarbe
$tablefontcolor = "#123456";

// Farbe des Tabellenintergrundes
$tablebackcolor = "#C7CFD6";

//Tabellenschriftgröße
$tablefontsize = "10";

// Zellenhintergrundfarbe für Platz 1 bis 3
$colorplatz1 = "#efed25"; // wenn nicht dann frei lassen -> $colorplatz1="";
$colorplatz2 = "#bab4a2"; // wenn nicht dann frei lassen -> $colorplatz2="";
$colorplatz3 = "#cc9b18"; // wenn nicht dann frei lassen -> $colorplatz3="";

// Anzahl der anzuzeigenden Tipper festlegen
$showtipper = -1; // -1=keine Begrenzung

//sollen tipper die noch keinen tipp abgegeben haben angezeigt werden?
$shownichttipper = 0; // 0=nein - 1=ja

//was soll bei der auswertung angezeigt werden?  1 = anzeigen; 0 nicht anzeigen
$show_sp_ges = 1;//Anzahl Spiele getippt
$show_sp_proz = 1;//quote richtiger tipps - oder punkte pro spiel
$show_joker = 1;//jokerpunkte 
$show_punkte = 1;//anzahl punkte -> hier ist die 1 empfohlen
$show_team = 1;//teamnamen anzeigen

//zeichen im tabellenkopf bei der ausgabe einstellen - Variablen anpassen
$var_spiele = "Sp";//Anzahl Spiele getippt - Standard "Sp"
$var_joker = "JP";//durch Joker dazugewonnene Punkte - standard "JP"

$var_tippsrichtig = "Pkt";//Anzahl Tipps richtig - Standard "P"
$var_team = "MS";//Team Mannschaft der man angehört

// seitentitel
$title = "www.bcerlbach.de - Individuelle Auswertung der Tippspielligen";

// statusleistentext - falls nicht gewünscht frei lassen -> $status = "";
$status = "www.bcerlbach.de - Individuelle Auswertung der Tippspielligen";


/***** ab hier nichts mehr ändern *********************************************/


require_once(dirname(__FILE__).'/init.php');

//datei gesamt.aus in array einlesen... evtl. Pfad anpassen
$auswertdatei = PATH_TO_ADDONDIR."/tipp/tipps/auswert/gesamt.aus";

//prüfen ob Datei vorhanden ist
if (is_file($auswertdatei)) {
    $array = @file($auswertdatei);
}
else {
	 //Skript abbrechen wenn Datei nicht vorhanden
	 die("Datei $auswertdatei nicht vorhanden - Tippspiel neu auswerten!");
	 }

//tippmodus aus congig-datei auslesen
$tippmodus = @file(PATH_TO_LMO."/config/tipp/cfg.txt");
$tippmodus = substr($tippmodus[34], 10, 1); // 0=Tendenz  1=Ergebnis

if ($tippmodus == 0) {
    $var_prozrichtig = "Sp%"; // Prozent Spieltipp richtig - Standard "Sp%"
}
else {
     $var_prozrichtig = "Sp&Oslash;"; // Punkte pro Spiel bei Ergebnistipp
     }

/* anzahl der tipp-ligen ermitteln */
$zeile = trim($array[1]); // unnötige zeilenumbrüche ... entfernen
$anzligen = substr($zeile, 9, strlen($zeile));//->eigentlich immer ab 10. stelle

//anzahl der sportsfreunde
$anzahl_tipper = count( file( PATH_TO_ADDONDIR."/tipp/".$tipp_tippauthtxt ));

//version
$ver = "1.0"; 

//zurück-button
$zurueck = "<b><a href=\"javascript:history.go(-1);\">zur&uuml;ck</a></b><br>";

		
//------------------------------------------------------------------------------
/* eigene funktion zum ermitteln des dateinames */
function dateiname($zeile) {
	//$zeile = trim($datei); 
	$pos = strpos($zeile, "="); // suche nach dem =
	if ($pos++ !== false) {
		$dateiname = substr($zeile, $pos++, strlen($zeile)); //ligenname
	}    
		 
	$dateiname = str_replace('.aus', '.l98', $dateiname);
    return $dateiname; // z.b. liga1.l98
}//end-function    
//------------------------------------------------------------------------------


//------------------------------------------------------------------------------
/* eigene funktion zum ermitteln der ligen-info */
function dateiinfo($datei) {
    $dateiname = $datei;//wird benötigt, falls datei nicht vorhanden
	$datei = getcwd() . "/ligen/". $datei; // ligen-pfad
	
	//überprüfen, ob ligen-datei existiert 
	if (is_file($datei)) {
	    $liga = file($datei);//file wird in array eingelesen
	    $dateiinfo = str_replace('Name=', '', trim($liga[2]));//liga-info in 3ter zeile
		$liga = ''; 
	}
	else {
 	     //wenn datei nicht vorhanden -> dateiname als info verwenden
         $dateiinfo = $dateiname;
         $dateiname = "";
    }
		
    return $dateiinfo; 
}//end-function    
//------------------------------------------------------------------------------


//------------------------------------------------------------------------------
/* eigene funktion zum ermitteln max tipptages */
function maxtipptag($datei) {
    
	$auswertdatei = PATH_TO_ADDONDIR ."/tipp/tipps/auswert/" . $datei;
    $auswertdatei = str_replace('.l98', '.aus', $auswertdatei);
    
	$tmp = 0;	
	$max = 0;
	
	//überprüfen, ob ligen-datei existiert 
	if (is_file($auswertdatei)) {
	    $array = file($auswertdatei);//file wird in array eingelesen

    	/* for durchläuft jede zeile der auswertungsdatei */
		for ($i = 1; $i < sizeof($array); $i++) {
			$zeile = trim($array[$i]);
			//SG ermitteln
			$pos = strpos($zeile, "SG"); 
			if (($pos !== false) and ($pos == 0)) {
				$tmp = substr($zeile, 2, strpos($zeile, "=")-2);
				if ($tmp > $max) { $max = $tmp; }
			}//if
		}//for
	}
	else {
 	     //wenn auswertdatei nicht vorhanden -> meldung ausgeben
         $max = "<acronym title=\"Benötigte Auswertdatei nicht gefunden - Tippspielliga neu auswerten\">Fehler";
    }
		
    return $max; 
}//end-function    
//------------------------------------------------------------------------------


//------------------------------------------------------------------------------
/* sortiert das array und übernimmt nur die x-besten tippspieltage */
/* Funktion liefert Punkte und Spiele für einen Tipper */
function sortieren_abschneiden($array, $userwerte) {
// Paramter:
//	$array - alle verfügbaren werte von einem user
//	$userwerte - dient zur rückgabe von summierten punkten und anzahl der spiele

	$anzahl_elemente = count($array); //Anzahl der Elemente ermitteln. -1 da Arrays mit 0 beginnen! ;o) 
	
	//Schleife wird entsprechend der Anzahl der Elemente im Array $zahlen wiederholt 
	for($y = 0; $y < $anzahl_elemente; $y++) 
	     { 
	     //Jedes Element wird einzelen angesprochen und verschoben wenn das linke Element grösser ist als der rechte 
	     for($x = 0; $x < $anzahl_elemente; $x++) 
	          { 
	          //In diesem Beispiel aufsteigend. 
	          //Möchte man absteigend sortieren, einfach das grösser Zeichen mit einem kleiner Zeichen tauschen 
	
			// tauschen wenn:
			// 1. erzielte punkte unterschiedlich  oder
			// 2. erzielte pkte gleich + erzielte pkte>0 + anz. tipp unterschiedlich
	          if (($array[$x][1] < $array[$x+1][1])
	          	 or (($array[$x][1] == $array[$x+1][1])
					and ($array[$x][1] > 0)
					and ($array[$x][2] > $array[$x+1][2]))) { 
					    
	              /* Werte werden zwischengespeichert... */
	              
		              //-anzahl punkte
		              $grosser_wert = $array[$x][1]; 
		              $kleiner_wert = $array[$x+1][1];
		              //-anzahl getippter spiele
		              $grosse_anz = $array[$x][2];
					  $kleine_anz = $array[$x+1][2];
					  /*/-joker
		              $grosse_joker = $array[$x][3];
					  $kleine_joker = $array[$x+1][3];
					  //-team
		              $grosse_team = $array[$x][4];
					  $kleine_team = $array[$x+1][4];
					  */
				  
	              /* ... und anschließend vertauscht */
	              
		              //anzahl punkte
		              $array[$x][1] = $kleiner_wert; 
		              $array[$x+1][1] = $grosser_wert; 
		              //-anzahl getippter spiele tauschen
					  $array[$x][2] = $kleine_anz;
					  $array[$x+1][2] = $grosse_anz;              
					  /*/-joker
					  $array[$x][3] = $kleine_joker;
					  $array[$x+1][3] = $grosse_joker; 
		  			  //-team
					  $array[$x][4] = $kleine_team;
					  $array[$x+1][4] = $grosse_team;   			  
					  */
			}//if
		}//for2 
	}//for1
	

	//wenn eingabewert eine zahl ist, dann array auf gewünschte länge schneiden
	if (is_numeric($_POST['spieltage'])) {
		$array = array_slice ($array, 0, $_POST['spieltage']);   
	}

	$userwerte[0] = 0;
	$userwerte[1] = 0;
	
	//summiere anzahl getippter spiele und erzielte punkte
	for ($i = 0; $i < count($array); $i++) {
	    $userwerte[0] += $array[$i][2];
	    $userwerte[1] += $array[$i][1];
	}
	
/*		echo "<pre>";
		print_r(array_values($array));
		echo "</pre>";
*/
		
    return $userwerte;
}//end-function    
//------------------------------------------------------------------------------



/* Eingabemaske zusammenbasteln und ausgeben */
$htmlhead = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
      "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
 <title>'.$title.'</title>
<style type="text/css">
body {
font-size: '. $fontsize .'pt;
font-family: '. $fontfamily .';
color: '. $fontcolor .';
background-color: #B9C4CC;
	/* Farbe der Scrollbalken */
	scrollbar-face-color: #B9C4CC;
	scrollbar-track-color: #B9C4CC;
	scrollbar-highlight-color: #B9C4CC;
	scrollbar-3dlight-color: #B9C4CC;
	scrollbar-darkshadow-color: #B9C4CC;
	scrollbar-base-color:#B9C4CC; 
	scrollbar-arrow-color:  #889CB0;	
	scrollbar-shadow-color: #889CB0;	
}

p {
	font-size: '. $fontsize .'pt;
}

h2 {
	margin-top: 5px; 
	margin-bottom: '. $headlinefontsize .'px;
	color: #D8E4EC;
	background-color: #889CB0;
	text-align: center;
}

a { text-decoration: overline,underline; color: #3E4753; font-size: 10pt;}
a:visited	{ text-decoration: underline; color: #3E4753; }
a:hover		{ text-decoration: underline; color: #104E8B; }
a:active	{ text-decoration: underline; color: #D8E4EC; }

table.auswert {
	font-size: '. $tablefontsize .'pt;
	color: '. $tablefontcolor .';
	background-color: '. $tablebackcolor .';
	text-align: center; 
	BORDER: #8CA0B4 1px dotted; 
}
th.auswert {
	background-color: '. $tablehaedercolor .';
}

hr { 
	height: 0px; 
	border: dashed #525E6E 0px; 
	border-top-width: 1px;
}

input {
	color : #000000;
	background-color: #B9C4CC;
	font-size: 10pt;
}

acronym {
	cursor:help;
	border-bottom:1px dotted;
}

font.foot {
	font-size: 8pt;
}

a.foot { text-decoration: overline,underline; color: #3E4753; font-size: 8pt;}

</style>
';

if (strlen($status) != 0) { 
	$htmlhead .= '
<script type="text/javascript" language="javascript1.2">
<!--
	window.status=\' '.$status.' \' 
// -->
</script>
';
}

$htmlhead .= '</head>';


$htmlfoot = '<hr width="195" align="right" />
<p style="line-height:8px; margin-top:0px" align="right">
<font class="foot"><a href="http://forum.bcerlbach.de/downloads.php?cat=7" class="foot" target="_blank" title="zum Download">Auswertskript2</a> v'. $ver .' &copy; by <a href="http://www.bcerlbach.de" class="foot" target="_blank" title="zur Homepage">Marcus</a></font></p>
</body></html>';


/*------------------------------------------------------------------------------
/* formular anzeigen, noch nichts geklickt
/*-------------------------------------------*/
if (!$_POST["iswas"]) {
  
  $htmlbody .= '
  <body>
  <h2>Individuelle Auswertung</h2>
	<table border="1" cellspacing="0" cellpadding="5" align="center"><tr><td align="center">
  	<form name="formular" method="post" action="'.$_SERVER["REQUEST_URI"].'">
	<table border="0" cellspacing="0" cellpadding="0" align="center">
	  <tr> <td colspan="2" align="left"><br />Die gewünschte Liga markieren.<br>Anschließend Button klicken.<br />&nbsp;
		</td> </tr>
	  ';
	  
//for stellt die form zusammen wo die ligen ausgewählt werden können
for ($i = 0; $i < $anzligen; $i++) {
    
    $z = $i+1; //for beginnt mit 0, die ausgabe aber mit 1 
    $zl = $i+2; //wird benötigt, da die ligen in der datei ab der 3ten zeile stehen
    
    $dateiname = dateiname(trim($array[$zl])); // liefert ligen-name

	$htmlbody .= '	<tr><td align="left" height="30"> <input type="radio" value="'.$dateiname.'" name="liga"  ';
	
	if  ($i == 0) { $htmlbody .= 'checked /'; }

	//den im moment max. tippspieltag der jeweiligen tippspielliga ermitteln
	$maxtt = maxtipptag($dateiname);

	$htmlbody .= '>&nbsp;'.dateiinfo($dateiname)/*funktionsaufruf um ligeninfo zu ermittlen und auszugeben*/.' </td><td>&nbsp;&nbsp;(<acronym title="max. Tippspieltag">'. $maxtt .'</acronym>)
	<input type="hidden" name="maxtipptag'.$i.'" value='.$maxtt.' /></td></tr>
	';
}//end-for

 
$htmlbody .= '<tr><td colspan="2"><br /><hr /><input type="checkbox" value="1" name="bestespieltage" class="checkbox">&nbsp;nur die besten <input type="input"  name="spieltage" id="spieltage" size="2" maxlength="2" value=""> Spieltage werten<hr /></td></tr>
<tr><td colspan="2" align="center" nowrap="nowrap"><br><input type="submit"  value="zur Auswertung" name="submit" /><input type="hidden" name="iswas" value="1" /></td></tr>
</table></form></table>
<p align="center"><a href="lmo.php?action=tipp">zurück zum Tippspiel</a></p>';


//******************************************************************************
}else /* wenn der anzeige-button geklickt wurde */  
	{
	$zeit1 = microtime(); //zeit nehmen start
	    
	/* eingeloggten user ermitteln */
	$username = "";
	if ( (isset($_SESSION['lmotippername']) && $_SESSION['lmotippername'] != "") && (isset($_SESSION['lmotipperok']) && $_SESSION['lmotipperok'] > 0) ) { 	
	    //echo "...mach dies, wenn eingeloggt... ". $_SESSION['lmotippername'];
	    $username = "[". $_SESSION['lmotippername'] ."]";
	} 


	/* prüfen ob die eingabe kleiner gleich dem max. tippspieltag ist */
	$spieltage = $_POST['spieltage'];
	$maxtt = maxtipptag($_POST['liga']);
	//checkbox markiert und wert eingetragen
	if (($_POST['bestespieltage'] == 1) and (!empty($spieltage)) and ($spieltage > 0)) {

		if (($spieltage != 1) and ($spieltage <= $maxtt)) {
		    $info = '<p align="center">&bull;&nbsp;die ' .$spieltage.' besten Spieltage wurden gewertet</p>';
		}
		else if (($spieltage == 1) and ($spieltage <= $maxtt)) {
		        $info = '<p align="center">&bull;&nbsp;der beste Spieltag wurde gewertet</p>';
			}
			else { // wenn eingabe maxtipptag größer ist als max tippspieltage
			    $info = '<p align="center">&bull;&nbsp;Eingabewert ist größer als der momentan verfügbare Tippspieltag, deshalb wurden ' .$maxtt.' Spieltage gewertet!</p>';	    
			}
	}//if
	
	

	$goal = array(array(),array()); // 2dimensionales array anlegen
	$tmp = array(array(),array());
	
	$userwerte = array(); // beinhaltet spiele und punkte
	
	$anztipper = -1; // zählvariable
	$z = 0; 
	
	$auswertdatei = PATH_TO_ADDONDIR ."/tipp/tipps/auswert/" . $_POST['liga'];
    $auswertdatei = str_replace('.l98', '.aus', $auswertdatei);
			
	//überprüfen, ob ligen-datei existiert 
	if (is_file($auswertdatei)) {
	    
	    $array = file($auswertdatei); //file in array einlesen	    
	    
		//wenn spieltagelimit eingegeben wurde 
	    if ($_POST['bestespieltage'] == 1) { //checkbox markiert
	    
	   		/* for durchläuft jede zeile der auswertungsdatei */
			for ($i = 1; $i <= sizeof($array); $i++) {
				
				$zeile = trim($array[$i]); // entfernt überflüssige zeichen 
				
				// leere zeile bedeutet, dass neuer tipper folgt
				if ($zeile == '') {
	
					$z = 0; 
					
					// funktion
					sortieren_abschneiden($tmp, &$userwerte);
					
					$goal[$anztipper][2] = $userwerte[0]; // anzahl getippter spiele
					$goal[$anztipper][1] = $userwerte[1]; // erzielte punkte
					
					//array muss gelöscht und dann wieder neu angelegt werden - um den inhalt sowie die länge zu löschen
					// = initilisieren  ... da es keinen array-inhalt-lösche-befehl gibt
					//unset($tmp); <- muss nicht unbedingt stehen, array neu anlegen genügt
					$tmp = array(array(),array());
					
				}//if
	
				//name ermitteln
				$pos1 = strpos($zeile, "["); 
				$pos2 = strpos($zeile, "]"); 
				if (($pos1 !== false) and ($pos2 !== false)) { 
				    $goal[++$anztipper][0] = $zeile;
				    
				}
							
				//SG (getippte spiele) ermitteln
				$pos = strpos($zeile, "SG"); 
				if (($pos !== false) and ($pos == 0)) {
				    $tmp[$z][2] = substr($zeile, strpos($zeile, "=")+1, strlen($zeile));
				}//if
				
				//TP (erzielte punkte) ermitteln
				$pos = strpos($zeile, "TP"); 
				if (($pos !== false) and ($pos == 0)) {
				    $tmp[$z++][1] = substr($zeile, strpos($zeile, "=")+1, strlen($zeile));
				}//if			
				
			}//for	    

		}//bestespieltage=1
		
	    else //erbniss der tippspielliga wird normal ausgegeben
	    {
   			/* for durchläuft jede zeile der auswertungsdatei */
			for ($i = 1; $i <= sizeof($array); $i++) {
				
				$zeile = trim($array[$i]);
				
				//name ermitteln
				$pos1 = strpos($zeile, "["); 
				$pos2 = strpos($zeile, "]"); 
				if (($pos1 !== false) and ($pos2 !== false)) { 
				    $goal[++$anztipper][0] = $zeile;
				}
							
				//SG (getippte spiele) ermitteln
				$pos = strpos($zeile, "SG"); 
				if (($pos !== false) and ($pos == 0)) {
				    $goal[$anztipper][2] += substr($zeile, strpos($zeile, "=")+1, strlen($zeile));
				}//if
				
				//TP (erzielte punkte) ermitteln
				$pos = strpos($zeile, "TP"); 
				if (($pos !== false) and ($pos == 0)) {
				    $goal[$anztipper][1] += substr($zeile, strpos($zeile, "=")+1, strlen($zeile));
				}//if			
				
				//falls liga aboniert, aber nicht getippt wurde
				if ($goal[$anztipper][2] == '') { $goal[$anztipper][2] = 0; }			
				if ($goal[$anztipper][1] == '') { $goal[$anztipper][1] = 0; }
				
			}//for		
		    
		}//else bestespieltage=1
 
	}//isfile
	
	

/*/ test
echo "<pre>";
print_r(array_values($goal));
echo "</pre>";



	
//	$anzgoal = -1;
    
	/* for durchläuft jede zeile von der auswertungsdatei */
/*	for ($i = $anzligen+3; $i < sizeof($array); $i++) 
		{
		//usernamen ermitteln, wenn gefunden in array speichern
		$posname = strpos($array[$i], "["); 
		if ($posname !== false) {
			//gefundenen namen ins array speichern
			$goal[++$anzgoal][0] = $array[$i];
		}

 	    //foreach1 ermittelt die erzielten punkte
		foreach ($ligenkurz as $value) 
		{
		    $value = $value."="; // = muss stehen da bei TP1 auch TP10 TP11 erfasst
			$pos1 = strpos($array[$i], $value); 
			if ($pos1 !== false) {
			     //punkte gleich array dazu addieren
			     $goal[$anzgoal][1] += ltrim(strrchr($array[$i],'='),'=');
		 	}
		}//foreach1 end
			
		//foreach2 ermittelt die anzahl an getippten spielen
		foreach ($anzgetipptkurz as $value) 
		{
		    $value = $value."="; // = muss stehen da bei TP1 auch TP10 TP11 erfasst
			$pos1 = strpos($array[$i], $value); 
			if ($pos1 !== false) {
				//anzahl getippter spiele gleich array dazu addieren
				$goal[$anzgoal][2] += ltrim(strrchr($array[$i],'='),'=');
		 	}
		}//foreach2 end
		
		//wird nur benötigt, wenn die joker-punkte angezeigt werden sollen
		if ($show_joker == 1)
			{
			//foreach3 ermittelt jokerpunkte
			foreach ($jokerkurz as $value) 
			{
			    $value = $value."="; // = muss stehen da bei TP1 auch TP10 TP11 erfasst
				$pos1 = strpos($array[$i], $value); 
				if ($pos1 !== false) {
			    	//anzahl getippter spiele gleich array dazu addieren
			    	 $goal[$anzgoal][3] += ltrim(strrchr($array[$i],'='),'=');
			    	//var. zeigt ob jokerpunkte genutzt werden, wenn ja joker=1
			    	 $joker = 1;
			 	}
			}//foreach3 end
		}//if joker		

		//wird nur benötigt, wenn teams angezeigt werden sollen
		if ($show_team == 1)
			{				
			//teamname ermitteln, wenn gefunden in array speichern
			$posname = strpos($array[$i], "Team="); 
			if ($posname !== false)	{
				//gefundenen namen ins array speichern
				$goal[$anzgoal][4] = ltrim(strrchr($array[$i],'='),'=');
				//var. zeigt ob teams genutzt werden muss, wenn ja team=1
				if (strlen($goal[$anzgoal][4]) != 1) { 
				    $team = 1; 
				}
			}
		}//if			
							
	}//end for	
	
/*for ($i=0; $i < count($goal); $i++){
	echo "$i  ".$goal[$i][0]."  ".$goal[$i][1]." ".$goal[$i][2]."  ".$goal[$i][3]."  ".$goal[$i][4]."<br>";
	}*/



/*------------------------------------------------------------------------------
/* BUBBLE SORT  des zweidimensionalen arrays */
/*-------------------------------------------*/

$anzahl_elemente = count($goal); //Anzal der Elemente ermittlen. -1 da Arrays mit 0 beginnen! ;o) 

//Schleife wird entsprechend der Anzahl der Elemente im Array $zahlen wiederholt 
for($y = 0; $y < $anzahl_elemente; $y++) 
     { 
     //Jedes Element wird einzelen angesprochen und verschoben wenn das linke Element grösser ist als der rechte 
     for($x = 0; $x < $anzahl_elemente; $x++) 
          { 
          //In diesem Beispiel aufsteigend. 
          //Möchte man absteigend sortieren, einfach das grösser Zeichen mit einem kleiner Zeichen tauschen 

		// tauschen wenn:
		// 1. erzielte punkte unterschiedlich  oder
		// 2. erzielte pkte gleich + erzielte pkte>0 + anz. tipp unterschiedlich
          if (($goal[$x][1] < $goal[$x+1][1])
          	 or (($goal[$x][1] == $goal[$x+1][1])
				and ($goal[$x][1] > 0)
				and ($goal[$x][2] > $goal[$x+1][2]))) { 
				    
              //Werte werden zwischengespeichert... 
              $grosser_wert = $goal[$x][1]; 
              $kleiner_wert = $goal[$x+1][1];
			  //-namen ebenfalls
		  	  $grosser_name = $goal[$x][0]; 
              $kleiner_name = $goal[$x+1][0];
              //-anzahl getippter spiele
              $grosse_anz = $goal[$x][2];
			  $kleine_anz = $goal[$x+1][2];
			  //-joker
              $grosse_joker = $goal[$x][3];
			  $kleine_joker = $goal[$x+1][3];
			  //-team
              $grosse_team = $goal[$x][4];
			  $kleine_team = $goal[$x+1][4];
			  
              //... und anschließen werte vertauschen
              $goal[$x][1] = $kleiner_wert; 
              $goal[$x+1][1] = $grosser_wert; 
              //-namen tauschen
              $goal[$x][0] = $kleiner_name; 
              $goal[$x+1][0] = $grosser_name; 
              //-anzahl getippter spiele tauschen
			  $goal[$x][2] = $kleine_anz;
			  $goal[$x+1][2] = $grosse_anz;              
			  //-joker
			  $goal[$x][3] = $kleine_joker;
			  $goal[$x+1][3] = $grosse_joker; 
  			  //-team
			  $goal[$x][4] = $kleine_team;
			  $goal[$x+1][4] = $grosse_team;   			  
          }//if
          
     /*         //erzielte punkte sind gleich -> anzahl tipps entscheidet
			  else if (($goal[$x][1] == $goal[$x+1][1]) and ($goal[$x][1] > 0))
			  		  {
			  		  if ($goal[$x][2] > $goal[$x+1][2])//Anzahl Tipps auswerten
			  			 {
			              //Werte werden zwischengespeichert... 
			              $grosser_wert = $goal[$x][1]; 
			              $kleiner_wert = $goal[$x+1][1];
						  //-namen ebenfalls
					  	  $grosser_name = $goal[$x][0]; 
			              $kleiner_name = $goal[$x+1][0];
			              //-anzahl getippter spiele
			              $grosse_anz = $goal[$x][2];
						  $kleine_anz = $goal[$x+1][2];
						  //-joker
			              $grosse_joker = $goal[$x][3];
						  $kleine_joker = $goal[$x+1][3];
			  			  //-team
			              $grosse_team = $goal[$x][4];
						  $kleine_team = $goal[$x+1][4];
			
			              //... und anschließen werte vertauschen
			              $goal[$x][1] = $kleiner_wert; 
			              $goal[$x+1][1] = $grosser_wert; 
			              //-namen tauschen
			              $goal[$x][0] = $kleiner_name; 
			              $goal[$x+1][0] = $grosser_name; 
			              //-anzahl getippter spiele tauschen
						  $goal[$x][2] = $kleine_anz;
						  $goal[$x+1][2] = $grosse_anz;  
						  //-joker
						  $goal[$x][3] = $kleine_joker;
						  $goal[$x+1][3] = $grosse_joker;
			   			  //-team
						  $goal[$x][4] = $kleine_team;
						  $goal[$x+1][4] = $grosse_team;   	
			              }					
					  }//end else if 
					  */
          }//for2 
     }//for1

/*------------------------------------------------------------------------------
/* aufbereiten für endausgabe des sortierten arrays          
/*----------------------------------------------------------------------------*/

$htmlbody .= '
<body>
<h2>Ergebnis der Auswertung</h2>
'.$info.'
	<table align="center" class="auswert">
	   <tr>
	    <th class="auswert">Platz</th><th class="auswert">Name</th>';

//gesamten getippter spiele ausgeben?		
if ($show_sp_ges == 1) { 
    $htmlbody.= '<th class="auswert"><acronym title="Anzahl Spiele getippt"><u>'.$var_spiele.'</u></acronym></th>'; 
}

//werden jokerpunkte zugelassen? wenn ja, spalte einblenden	 ja oder nein?   
if (($show_joker == 1) and ($joker == 1)) { 
    $htmlbody .= '<th class="auswert"><acronym title="durch Joker dazugewonnene Punkte"><u>'.$var_joker.'</u></acronym></th>'; 
}

//quote richtiger spieler ausgeben ja oder nein?
if ($show_sp_proz == 1) { 
    if ($tippmodus == 0) { // tendenz
	    $htmlbody .= '<th class="auswert"><acronym title="Prozent Spieltipp richtig%"><u>'.$var_prozrichtig.'</u></acronym></th>';
	}
	elseif ($tippmodus == 1) { // ergebnis
		$htmlbody .= '<th class="auswert"><acronym title="Punkte pro Spiel"><u>'.$var_prozrichtig.'</u></acronym></th>';
	}
}

//anzahl tipps ausgeben ja oder nein?		
if ($show_punkte == 1) { 
    $htmlbody.= '<th class="auswert"><acronym title="Anzahl Tipps richtig"><u>'.$var_tippsrichtig.'</u></acronym></th>'; 
}

//team ausgeben ja oder nein?
if (($show_team == 1) and ($team == 1)) { 
    $htmlbody.= '<th class="auswert"><acronym title="Teamzugehörigkeit"><u>'.$var_team.'</u></acronym></th>'; 
}
		
$htmlbody .= '</tr>
';

$platz = 0;
$platz2 = 0;
$p1 = 0;
$p2 = 0;

/*		echo "<pre>";
		print_r(array_values($goal));
		echo "</pre>";


   
/* für html aufbereiten */
for ($i = 0; $i < count($goal); $i++) {
    
    // begrenzung anzahl tipper
    if ($i == $showtipper) { 
	    break; 
	} 
	
	//bedingung, die alle nicht-tipper rausfiltert falls gewünscht
	if (($shownichttipper != 0) or ($goal[$i][2] != 0)) {
	
		//wert im array mit username vergleichen -> fett darstellen
	    if (chop($goal[$i][0]) == $username) { 
		    $goal[$i][0] = "<b>". $goal[$i][0] ."</b>"; 
		}
	    
	    $platz++;
	    
	    //ausgabe der platzierung wenn: 
		//  1. punkte ungleich dem vorgänger
	    //  2. punkte gleich, aber anzahl getippter spiele unterschiedlich

	    if (($goal[$i][1] != $goal[$i-1][1]) or (($goal[$i][1] == $goal[$i-1][1]) and (($goal[$i][2] != $goal[$i-1][2])))) {
			
			$platz2++;

			//if (($platz <= 3) and ($platz2 <= $platz)) { $platz2 = $platz; }
			
			
			if ($platz2 == 1) {
			    $p1++;
			    $htmlbody .= '<tr bgcolor="'.$colorplatz1.'"> <td>';
			}
			else if (($platz2 == 2)and($p1<3)) {
			    	$p2++;
			        $htmlbody .= '<tr bgcolor="'.$colorplatz2.'"> <td>';
				 }
			 	  else if (($platz2 == 3)and(($p1+$p2)<3)) {
			    		 $htmlbody .= '<tr bgcolor="'.$colorplatz3.'"> <td>';
					  } 
					  else {
			     		   $htmlbody .= '<tr bgcolor="'.$tablebackcolor.'"> <td>';
				 	  }
			$htmlbody .= $platz.".";
		}
		else {
			 if ($platz2 == 1) {
			     $p1++;
			     $htmlbody .= '<tr bgcolor="'.$colorplatz1.'"> <td>';
			 }
			else if (($platz2 == 2)and($p1<3)) {
			    	$p2++;
			     	  $htmlbody .= '<tr bgcolor="'.$colorplatz2.'"> <td>';
			 	  }
			 	  else if (($platz2 == 3)and(($p1+$p2)<3)) {
			     		  $htmlbody .= '<tr bgcolor="'.$colorplatz3.'"> <td>';
					   }
					   else {
						    $htmlbody .= '<tr bgcolor="'.$tablebackcolor.'"> <td>';
							}		    
		     //$platz2 = $platz-1;
		     $htmlbody .= '&nbsp;'; 
		     
		}//else

	    
		//erfolgsquote in prozent oder in punkte pro spiel
		if ($goal[$i][2] > 0) { 
		    if ($tippmodus == 0) { // tendenz
				$quote = $goal[$i][1] / $goal[$i][2] * 100;
			}
			else { // ergebnis
			     $quote = $goal[$i][1] / $goal[$i][2];
			}
		}
		else { 
		     $quote = 0; 
			 }
		
		$htmlbody .= '</td> <td>'.$goal[$i][0].'</td>';
				
		//gesamten getippter spiele ausgeben?		
		if ($show_sp_ges == 1) { 
		    $htmlbody .= '<td>'.$goal[$i][2].'&nbsp;</td>'; 
		}
		
		//werden jokerpunkte zugelassen? wenn ja, spalte einblenden	 ja oder nein?   
		if (($show_joker == 1) and ($joker == 1)) { 
		    $htmlbody .= '<td>'.$goal[$i][3].'&nbsp;</td>'; 
		}
		
		//quote richtiger spieler ausgeben ja oder nein?
		if ($show_sp_proz == 1) { 
		    $htmlbody .= '<td>'.round($quote, 2).'&nbsp;</td>'; 
		}
		
		//anzahl tipps ausgeben ja oder nein?		
		if ($show_punkte == 1) { 
		    $htmlbody .= '<td>'.$goal[$i][1].'&nbsp;</td>'; 
		}

		//anzahl tipps ausgeben ja oder nein?		
		if (($show_team == 1) and ($team == 1)) { 
		    $htmlbody .= '<td>'.$goal[$i][4].'&nbsp;</td>'; 
		}
		
		$htmlbody .= '</tr>
		';	
						 
	}//end filter nicht-tipper
}//for
   
$htmlbody .= '</table>
<p align="center">';//'<font color='.$fontcolor.'>Anzahl der aktiven Tippspieler: '. count($goal) . '</font>';

if ($shownichttipper == 0) { 
    $htmlbody .= '<font class="foot">(Nicht-Tipper werden nicht dargestellt)</font>'; 
}

$zeit2 = microtime(); //stopp 

$htmlbody .= '<p align="center">Stand vom '.date("d.m.Y").' - '.date("H:i") .' Uhr<br><font class="foot">generiert in '.($zeit2-$zeit1).' Sekunden</font></p>
<p align="center"><a href="auswert3.php" title="zurück zur Auswahl">zurück zur Auswahl</a>&nbsp;|&nbsp;<a href="lmo.php?action=tipp" title="zurück zum Tippspiel">zurück zum Tippspiel</a></p>';

    
}//end-else - wenn ok-button geklickt wurde


// ausgabe html code an browser
echo $htmlhead . $htmlbody . $htmlfoot;

clearstatcache();

?>