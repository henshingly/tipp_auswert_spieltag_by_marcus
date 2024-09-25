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

// Tabellenschriftgröße
$tablefontsize = "10";

// Zellenhintergrundfarbe für Platz 1 bis 3
$colorplatz1 = "#efed25"; // wenn nicht dann frei lassen -> $colorplatz1="";
$colorplatz2 = "#bab4a2"; // wenn nicht dann frei lassen -> $colorplatz2="";
$colorplatz3 = "#cc9b18"; // wenn nicht dann frei lassen -> $colorplatz3="";

// Anzahl der anzuzeigenden Tipper festlegen
$showtipper = -1; // -1=keine Begrenzung

// sollen Tipper die noch keinen Tipp abgegeben haben angezeigt werden?
$shownichttipper = 0; // 0=nein - 1=ja

// Was soll bei der Auswertung angezeigt werden?  1 = anzeigen; 0 nicht anzeigen
$show_sp_ges = 1;   // Anzahl Spiele getippt
$show_sp_proz = 1;  // Quote richtiger Tipps - oder Punkte pro Spiel
$show_joker = 1;    // Jokerpunkte
$show_punkte = 1;   // Aanzahl Punkte -> hier ist die 1 empfohlen
$show_team = 1;     // Teamnamen anzeigen

// Zeichen im Tabellenkopf bei der Ausgabe einstellen - Variablen anpassen
$var_spiele = "Sp";         // Anzahl Spiele getippt - Standard "Sp"
$var_joker = "JP";          // durch Joker dazugewonnene Punkte - standard "JP"
$var_tippsrichtig = "Pkt";  // Anzahl Tipps richtig - Standard "P"
$var_team = "MS";           // Team Mannschaft der man angehört

// Seitentitel
$title = "www.bcerlbach.de - Individuelle Auswertung der Tippspielligen";

// Statusleistentext - falls nicht gewünscht frei lassen -> $status = "";
$status = "www.bcerlbach.de - Individuelle Auswertung der Tippspielligen";


/***** ab hier nichts mehr ändern *********************************************/


require_once(dirname(__FILE__).'/init.php');

// Datei gesamt.aus in Array einlesen... evtl. Pfad anpassen
$auswertdatei = PATH_TO_ADDONDIR."/tipp/tipps/auswert/gesamt.aus";

// Prüfen ob Datei vorhanden ist
if (is_file($auswertdatei)) {
  $array = @file($auswertdatei);
}
else {
  //Skript abbrechen wenn Datei nicht vorhanden
  die("Datei $auswertdatei nicht vorhanden - Tippspiel neu auswerten!");
}

// Tippmodus aus config-Datei auslesen
$tippmodus = @file(PATH_TO_LMO."/config/tipp/cfg.txt");
$tippmodus = substr($tippmodus[34], 10, 1);  // 0=Tendenz  1=Ergebnis

if ($tippmodus == 0) {
  $var_prozrichtig = "Sp%";  // Prozent Spieltipp richtig - Standard "Sp%"
}
else {
  $var_prozrichtig = "Sp&Oslash;";  // Punkte pro Spiel bei Ergebnistipp
}

// Anzahl der Tipp-Ligen ermitteln
$zeile = trim($array[1]);  // unnötige Zeilenumbrüche ... entfernen
$anzligen = substr($zeile, 9, strlen($zeile));  // -> eigentlich immer ab der 10. Stelle

// Anzahl der Sportsfreunde (Tipper)
$anzahl_tipper = count( file( PATH_TO_ADDONDIR."/tipp/".$tipp_tippauthtxt ));

// Version
$ver = "1.0";

// Zurück-Button
$zurueck = "<b><a href=\"javascript:history.go(-1);\">zurück</a></b><br>";


//------------------------------------------------------------------------------
// eigene Funktion zum ermitteln des Dateinames
function dateiname($zeile) {
  $pos = strpos($zeile, "=");  // Suche nach dem =
  if ($pos++ !== false) {
    $dateiname = substr($zeile, $pos++, strlen($zeile));  // Ligenname
  }

  $dateiname = str_replace('.aus', '.l98', $dateiname);
  return $dateiname;  // z.b. Liga1.l98
}  // end-function Dateiname
//------------------------------------------------------------------------------


//------------------------------------------------------------------------------
// eigene Funktion zum ermitteln der Ligen-Info
function dateiinfo($datei) {
  $dateiname = $datei;  // wird benötigt, falls die Datei nicht vorhanden ist
  $datei = getcwd() . "/ligen/". $datei;  // Ligenpfad

  // Überprüfen, ob die Ligen-Datei existiert
  if (is_file($datei)) {
    $liga = file($datei);  // File wird in Array eingelesen
    $dateiinfo = str_replace('Name=', '', trim($liga[2]));  // liga-Info in der 3ten Zeile
    $liga = '';
  }
  else {
    // Wenn Datei nicht vorhanden -> Dateiname als Info verwenden
    $dateiinfo = $dateiname;
    $dateiname = "";
  }

  return $dateiinfo;
}  // end-function Ligen-Info
//------------------------------------------------------------------------------


//------------------------------------------------------------------------------
// eigene Funktion zum ermitteln max Tipptages
function maxtipptag($datei) {
  $auswertdatei = PATH_TO_ADDONDIR ."/tipp/tipps/auswert/" . $datei;
  $auswertdatei = str_replace('.l98', '.aus', $auswertdatei);

  $tmp = 0;
  $max = 0;

  // Überprüfen, ob Ligen-Datei existiert
  if (is_file($auswertdatei)) {
    $array = file($auswertdatei);//file wird in array eingelesen

    // for durchläuft jede Zeile der Auswertungsdatei
    for ($i = 1; $i < sizeof($array); $i++) {
      $zeile = trim($array[$i]);
      //SG ermitteln
      $pos = strpos($zeile, "SG");
      if (($pos !== false) and ($pos == 0)) {
        $tmp = substr($zeile, 2, strpos($zeile, "=")-2);
        if ($tmp > $max) { $max = $tmp; }
      }  // if $pos
    }  // for Auswertungsdatei
  }
  else {
    // Wenn Auswertdatei nicht vorhanden -> Meldung ausgeben
    $max = "<acronym title=\"Benötigte Auswertdatei nicht gefunden - Tippspielliga neu auswerten\">Fehler";
  }
  return $max;
}  // end-function maxtipptag
//------------------------------------------------------------------------------


//------------------------------------------------------------------------------
// Sortiert das Array und übernimmt nur die x-besten Tippspieltage
// Funktion liefert Punkte und Spiele für einen Tipper
function sortieren_abschneiden($array, $userwerte) {
  // Paramter:
  // $array - alle verfügbaren Werte von einem User
  // $userwerte - dient zur Rückgabe von summierten Punkten und Anzahl der Spiele

  $anzahl_elemente = count($array);  // Anzahl der Elemente ermitteln. -1 da Arrays mit 0 beginnen! ;o)

  // Schleife wird entsprechend der Anzahl der Elemente im Array $zahlen wiederholt
  for($y = 0; $y < $anzahl_elemente; $y++) {
    // Jedes Element wird einzelen angesprochen und verschoben wenn das linke Element grösser ist als der rechte
    for($x = 0; $x < $anzahl_elemente; $x++) {
      // In diesem Beispiel aufsteigend.
      // Möchte man absteigend sortieren, einfach das grösser Zeichen mit einem kleiner Zeichen tauschen

      // tauschen wenn:
      // 1. erzielte Punkte unterschiedlich  oder
      // 2. erzielte Punkte gleich + erzielte Punkte>0 + anz. Tipp unterschiedlich
      if (($array[$x][1] < $array[$x+1][1])
      or (($array[$x][1] == $array[$x+1][1])
      and ($array[$x][1] > 0)
      and ($array[$x][2] > $array[$x+1][2]))) {

        // Werte werden zwischengespeichert...
        // Anzahl punkte
        $grosser_wert = $array[$x][1];
        $kleiner_wert = $array[$x+1][1];
        // Anzahl getippter spiele
        $grosse_anz = $array[$x][2];
        $kleine_anz = $array[$x+1][2];
        /*/ Joker
        $grosse_joker = $array[$x][3];
        $kleine_joker = $array[$x+1][3];
        // Team
        $grosse_team = $array[$x][4];
        $kleine_team = $array[$x+1][4];
        */

        // ... und anschließend vertauscht
        // Anzahl Punkte
        $array[$x][1] = $kleiner_wert;
        $array[$x+1][1] = $grosser_wert;
        // Anzahl getippter Spiele tauschen
        $array[$x][2] = $kleine_anz;
        $array[$x+1][2] = $grosse_anz;
        /*/ Joker
        $array[$x][3] = $kleine_joker;
        $array[$x+1][3] = $grosse_joker;
        // Team
        $array[$x][4] = $kleine_team;
        $array[$x+1][4] = $grosse_team;
        */
      }  // if end
    }  // for 2 end
  }  // for 1 end


  // Wenn Eingabewert eine Zahl ist, dann Array auf gewünschte Länge schneiden
  if (is_numeric($_POST['spieltage'])) {
    $array = array_slice ($array, 0, $_POST['spieltage']);
  }

  $userwerte[0] = 0;
  $userwerte[1] = 0;

  // Summiere Anzahl getippter Spiele und erzielter Punkte
  for ($i = 0; $i < count($array); $i++) {
    $userwerte[0] += $array[$i][2];
    $userwerte[1] += $array[$i][1];
  }

  /* echo "<pre>";
  print_r(array_values($array));
  echo "</pre>";
*/

  return $userwerte;
}  // end-function sortieren_abschneiden
//------------------------------------------------------------------------------


// Eingabemaske zusammenbasteln und ausgeben
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
a:visited  { text-decoration: underline; color: #3E4753; }
a:hover    { text-decoration: underline; color: #104E8B; }
a:active  { text-decoration: underline; color: #D8E4EC; }

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
/* Formular anzeigen, noch nichts geklickt
/*----------------------------------------------------------------------------*/
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

  // for stellt das Formular zusammen in dem die Ligen ausgewählt werden können
  for ($i = 0; $i < $anzligen; $i++) {
    $z = $i+1; //for beginnt mit 0, die ausgabe aber mit 1
    $zl = $i+2; //wird benötigt, da die ligen in der datei ab der 3ten zeile stehen

    $dateiname = dateiname(trim($array[$zl])); // liefert ligen-name

    $htmlbody .= '  <tr><td align="left" height="30"> <input type="radio" value="'.$dateiname.'" name="liga"  ';

    if  ($i == 0) { $htmlbody .= 'checked /'; }

    // den im Moment max. Tippspieltag der jeweiligen Tippspielliga ermitteln
    $maxtt = maxtipptag($dateiname);

    $htmlbody .= '>&nbsp;'.dateiinfo($dateiname)/* Funktionsaufruf um Ligeninfo zu ermittlen und auszugeben*/.' </td><td>&nbsp;&nbsp;(<acronym title="max. Tippspieltag">'. $maxtt .'</acronym>)
  <input type="hidden" name="maxtipptag'.$i.'" value='.$maxtt.' /></td></tr>
  ';
  }  // end-for


  $htmlbody .= '<tr><td colspan="2"><br /><hr /><input type="checkbox" value="1" name="bestespieltage" class="checkbox">&nbsp;nur die besten <input type="input"  name="spieltage" id="spieltage" size="2" maxlength="2" value=""> Spieltage werten<hr /></td></tr>
<tr><td colspan="2" align="center" nowrap="nowrap"><br><input type="submit"  value="zur Auswertung" name="submit" /><input type="hidden" name="iswas" value="1" /></td></tr>
</table></form></table>
<p align="center"><a href="lmo.php?action=tipp">zurück zum Tippspiel</a></p>';


//******************************************************************************
}
else {  // wenn der Anzeige-Button geklickt wurde
  $zeit1 = microtime();  // Zeit nehmen Start

  // eingeloggten User ermitteln
  $username = "";
  if ( (isset($_SESSION['lmotippername']) && $_SESSION['lmotippername'] != "") && (isset($_SESSION['lmotipperok']) && $_SESSION['lmotipperok'] > 0) ) {
    $username = "[". $_SESSION['lmotippername'] ."]";
  }

  // Prüfen ob die Eingabe kleiner gleich dem max. Tippspieltag ist
  $spieltage = $_POST['spieltage'];
  $maxtt = maxtipptag($_POST['liga']);
  // Checkbox markiert und Wert eingetragen
  if (($_POST['bestespieltage'] == 1) and (!empty($spieltage)) and ($spieltage > 0)) {

    if (($spieltage != 1) and ($spieltage <= $maxtt)) {
      $info = '<p align="center">&bull;&nbsp;die ' .$spieltage.' besten Spieltage wurden gewertet</p>';
    }
    else if (($spieltage == 1) and ($spieltage <= $maxtt)) {
      $info = '<p align="center">&bull;&nbsp;der beste Spieltag wurde gewertet</p>';
    }
    else {  // wenn Eingabe maxtipptag größer ist als max Tippspieltage
      $info = '<p align="center">&bull;&nbsp;Eingabewert ist größer als der momentan verfügbare Tippspieltag, deshalb wurden ' .$maxtt.' Spieltage gewertet!</p>';
    }
  }  // if bestespieltage end



  $goal = array(array(),array());  // zweidimensionales Array anlegen
  $tmp = array(array(),array());

  $userwerte = array();  // Beinhaltet Spiele und Punkte

  $anztipper = -1;  // Zählvariable
  $z = 0;

  $auswertdatei = PATH_TO_ADDONDIR ."/tipp/tipps/auswert/" . $_POST['liga'];
  $auswertdatei = str_replace('.l98', '.aus', $auswertdatei);

  // Überprüfen, ob Ligen-Datei existiert
  if (is_file($auswertdatei)) {

    $array = file($auswertdatei);  // File in Array einlesen

    // Wenn Spieltagelimit eingegeben wurde
    if ($_POST['bestespieltage'] == 1) {  // Checkbox markiert

      // for durchläuft jede Zeile der Auswertungsdatei
      for ($i = 1; $i <= sizeof($array); $i++) {

        $zeile = trim($array[$i]);  // entfernt überflüssige Zeichen

        // leere Zeile bedeutet, dass neuer Tipper folgt
        if ($zeile == '') {

          $z = 0;

          // Funktion
          sortieren_abschneiden($tmp, $userwerte);

          $goal[$anztipper][2] = $userwerte[0]; // anzahl getippter spiele
          $goal[$anztipper][1] = $userwerte[1]; // erzielte punkte

          // Array muss gelöscht und dann wieder neu angelegt werden - um den inhalt sowie die Länge zu löschen
          // = initilisieren  ... da es keinen Array-Inhalt-lösche-Befehl gibt
          // unset($tmp); <- muss nicht unbedingt stehen, array neu anlegen genügt
          $tmp = array(array(),array());
        }  // if leere Zeile end

        // Name ermitteln
        $pos1 = strpos($zeile, "[");
        $pos2 = strpos($zeile, "]");
        if (($pos1 !== false) and ($pos2 !== false)) {
            $goal[++$anztipper][0] = $zeile;
        }

        // SG (getippte Spiele) ermitteln
        $pos = strpos($zeile, "SG");
        if (($pos !== false) and ($pos == 0)) {
            $tmp[$z][2] = substr($zeile, strpos($zeile, "=")+1, strlen($zeile));
        }  // if getippte Spiele end

        // TP (erzielte Punkte) ermitteln
        $pos = strpos($zeile, "TP");
        if (($pos !== false) and ($pos == 0)) {
            $tmp[$z++][1] = substr($zeile, strpos($zeile, "=")+1, strlen($zeile));
        }  // if erzielte Punkte end

      }  //for Durchlauf Auswertungsdatei end

    }  // if bestespieltage=1 end

    else {  // Ergebniss der Tippspielliga wird normal ausgegeben
      // for durchläuft jede Zeile der Auswertungsdatei
      for ($i = 1; $i <= sizeof($array); $i++) {

        $zeile = trim($array[$i]);

        // Name ermitteln
        $pos1 = strpos($zeile, "[");
        $pos2 = strpos($zeile, "]");
        if (($pos1 !== false) and ($pos2 !== false)) {
          $goal[++$anztipper][0] = $zeile;
        }

        // SG (getippte Spiele) ermitteln
        $pos = strpos($zeile, "SG");
        if (($pos !== false) and ($pos == 0)) {
          $goal[$anztipper][2] += substr($zeile, strpos($zeile, "=")+1, strlen($zeile));
        }  // if end

        // TP (erzielte Punkte) ermitteln
        $pos = strpos($zeile, "TP");
        if (($pos !== false) and ($pos == 0)) {
          $goal[$anztipper][1] += substr($zeile, strpos($zeile, "=")+1, strlen($zeile));
        }  // if end

        // falls die liga aboniert, aber nicht getippt wurde
        if ($goal[$anztipper][2] == '') { $goal[$anztipper][2] = 0; }
        if ($goal[$anztipper][1] == '') { $goal[$anztipper][1] = 0; }

      }  // for end

    }  // else bestespieltage=1 end

  }  // if isfile end

/*/ Test
echo "<pre>";
print_r(array_values($goal));
echo "</pre>";

//  $anzgoal = -1;

/*
  // for durchläuft jede Zeile der Auswertungsdatei
  for ($i = $anzligen+3; $i < sizeof($array); $i++) {
    // Usernamen ermitteln, wenn gefunden in Array speichern
    $posname = strpos($array[$i], "[");
    if ($posname !== false) {
      // gefundenen Namen ins Array speichern
      $goal[++$anzgoal][0] = $array[$i];
    }

    // foreach1 ermittelt die erzielten Punkte
    foreach ($ligenkurz as $value) {
      $value = $value."=";  // = muss stehen da bei TP1 auch TP10 TP11 erfasst
      $pos1 = strpos($array[$i], $value);
      if ($pos1 !== false) {
        // Punkte gleich Array dazu addieren
        $goal[$anzgoal][1] += ltrim(strrchr($array[$i],'='),'=');
      }
    }  // foreach1 end

    // foreach2 ermittelt die Anzahl an getippten Spielen
    foreach ($anzgetipptkurz as $value) {
      $value = $value."=";  // = muss stehen da bei TP1 auch TP10 TP11 erfasst
      $pos1 = strpos($array[$i], $value);
      if ($pos1 !== false) {
        // Anzahl getippter Spiele gleich Array dazu addieren
        $goal[$anzgoal][2] += ltrim(strrchr($array[$i],'='),'=');
      }
    }  // foreach2 end

    // wird nur benötigt, wenn die Joker-Punkte angezeigt werden sollen
    if ($show_joker == 1) {
      // foreach3 ermittelt Jokerpunkte
      foreach ($jokerkurz as $value) {
        $value = $value."=";  // = muss stehen da bei TP1 auch TP10 TP11 erfasst
        $pos1 = strpos($array[$i], $value);
        if ($pos1 !== false) {
          // Anzahl getippter Spiele gleich Array dazu addieren
          $goal[$anzgoal][3] += ltrim(strrchr($array[$i],'='),'=');
          // var. zeigt ob Jokerpunkte genutzt werden, wenn ja joker=1
          $joker = 1;
        }
      }  // foreach3 end
    }  // if show_joker end

    // wird nur benötigt, wenn Teams angezeigt werden sollen
    if ($show_team == 1) {
      // Teamname ermitteln, wenn gefunden in Array speichern
      $posname = strpos($array[$i], "Team=");
      if ($posname !== false) {
        // gefundenen Namen ins Array speichern
        $goal[$anzgoal][4] = ltrim(strrchr($array[$i],'='),'=');
        //var. zeigt ob Teams genutzt werden muss, wenn ja team=1
        if (strlen($goal[$anzgoal][4]) != 1) {
          $team = 1;
        }
      }
    }  // if show_team end

  }  // for Durchlauf Auswertungsdatei end

  /*for ($i=0; $i < count($goal); $i++) {
    echo "$i  ".$goal[$i][0]."  ".$goal[$i][1]." ".$goal[$i][2]."  ".$goal[$i][3]."  ".$goal[$i][4]."<br>";
  }*/



  /*------------------------------------------------------------------------------
  /* BUBBLE SORT des zweidimensionalen Arrays */
  /*-------------------------------------------*/

  $anzahl_elemente = count($goal);  // Anzahl der Elemente ermittlen. -1 da Arrays mit 0 beginnen! ;o)

  // Schleife wird entsprechend der Anzahl der Elemente im Array $zahlen wiederholt
  for($y = 0; $y < $anzahl_elemente; $y++) {
    //Jedes Element wird einzelen angesprochen und verschoben wenn das linke Element grösser ist als der rechte
    for($x = 0; $x < $anzahl_elemente; $x++) {
      // In diesem Beispiel aufsteigend.
      // Möchte man absteigend sortieren, einfach das grösser Zeichen mit einem kleiner Zeichen tauschen

      // tauschen wenn:
      // 1. erzielte Punkte unterschiedlich oder
      // 2. erzielte Punkte gleich + erzielte Punkte > 0 + anz. Tipp unterschiedlich
      if (($goal[$x][1] < $goal[$x+1][1])
      or (($goal[$x][1] == $goal[$x+1][1])
      and ($goal[$x][1] > 0)
      and ($goal[$x][2] > $goal[$x+1][2]))) {

        // Werte werden zwischengespeichert...
        $grosser_wert = $goal[$x][1];
        $kleiner_wert = $goal[$x+1][1];
        // Namen ebenfalls
        $grosser_name = $goal[$x][0];
        $kleiner_name = $goal[$x+1][0];
        // Anzahl getippter Spiele
        $grosse_anz = $goal[$x][2];
        $kleine_anz = $goal[$x+1][2];
        // Joker
        $grosse_joker = $goal[$x][3];
        $kleine_joker = $goal[$x+1][3];
        // Team
        $grosse_team = $goal[$x][4];
        $kleine_team = $goal[$x+1][4];

        // ... und anschließend Werte vertauschen
        $goal[$x][1] = $kleiner_wert;
        $goal[$x+1][1] = $grosser_wert;
        // Namen tauschen
        $goal[$x][0] = $kleiner_name;
        $goal[$x+1][0] = $grosser_name;
        // Anzahl getippter Spiele tauschen
        $goal[$x][2] = $kleine_anz;
        $goal[$x+1][2] = $grosse_anz;
        // Joker
        $goal[$x][3] = $kleine_joker;
        $goal[$x+1][3] = $grosse_joker;
        // Team
        $goal[$x][4] = $kleine_team;
        $goal[$x+1][4] = $grosse_team;
      }  // if end

/*
      // Erzielte Punkte sind gleich -> Anzahl Tipps entscheidet
      else if (($goal[$x][1] == $goal[$x+1][1]) and ($goal[$x][1] > 0)) {
        if ($goal[$x][2] > $goal[$x+1][2]) {  // Anzahl Tipps auswerten
          // Werte werden zwischengespeichert...
          $grosser_wert = $goal[$x][1];
          $kleiner_wert = $goal[$x+1][1];
          // Namen ebenfalls
          $grosser_name = $goal[$x][0];
          $kleiner_name = $goal[$x+1][0];
          // Anzahl getippter spiele
          $grosse_anz = $goal[$x][2];
          $kleine_anz = $goal[$x+1][2];
          // Joker
          $grosse_joker = $goal[$x][3];
          $kleine_joker = $goal[$x+1][3];
          // Team
          $grosse_team = $goal[$x][4];
          $kleine_team = $goal[$x+1][4];

          // ... und anschließend Werte vertauschen
          $goal[$x][1] = $kleiner_wert;
          $goal[$x+1][1] = $grosser_wert;
          // Namen tauschen
          $goal[$x][0] = $kleiner_name;
          $goal[$x+1][0] = $grosser_name;
          // Anzahl getippter Spiele tauschen
          $goal[$x][2] = $kleine_anz;
          $goal[$x+1][2] = $grosse_anz;
          // Joker
          $goal[$x][3] = $kleine_joker;
          $goal[$x+1][3] = $grosse_joker;
          // Team
          $goal[$x][4] = $kleine_team;
          $goal[$x+1][4] = $grosse_team;
        }
      }  // else if end
*/
    }  // for2 end
  }  // for1 end

  /*------------------------------------------------------------------------------
  /* Aufbereiten für Endausgabe des sortierten Arrays
  /*----------------------------------------------------------------------------*/

  $htmlbody .= '
<body>
<h2>Ergebnis der Auswertung</h2>
'.$info.'
  <table align="center" class="auswert">
     <tr>
      <th class="auswert">Platz</th><th class="auswert">Name</th>';

  // Gesamtinhalt getippter Spiele ausgeben?
  if ($show_sp_ges == 1) {
    $htmlbody.= '<th class="auswert"><acronym title="Anzahl Spiele getippt"><u>'.$var_spiele.'</u></acronym></th>';
  }

  // Werden Jokerpunkte zugelassen? wenn ja, Spalte einblenden - ja oder nein?
  if (($show_joker == 1) and ($joker == 1)) {
    $htmlbody .= '<th class="auswert"><acronym title="durch Joker dazugewonnene Punkte"><u>'.$var_joker.'</u></acronym></th>';
  }

  // Quote richtiger Spiele ausgeben - ja oder nein?
  if ($show_sp_proz == 1) {
    if ($tippmodus == 0) {  // Tendenz
      $htmlbody .= '<th class="auswert"><acronym title="Prozent Spieltipp richtig%"><u>'.$var_prozrichtig.'</u></acronym></th>';
    }
    elseif ($tippmodus == 1) {  // Ergebnis
      $htmlbody .= '<th class="auswert"><acronym title="Punkte pro Spiel"><u>'.$var_prozrichtig.'</u></acronym></th>';
    }
  }

  // Anzahl Tipps ausgeben - ja oder nein?
  if ($show_punkte == 1) {
    $htmlbody.= '<th class="auswert"><acronym title="Anzahl Tipps richtig"><u>'.$var_tippsrichtig.'</u></acronym></th>';
  }

  // Team ausgeben - ja oder nein?
  if (($show_team == 1) and ($team == 1)) {
    $htmlbody.= '<th class="auswert"><acronym title="Teamzugehörigkeit"><u>'.$var_team.'</u></acronym></th>';
  }

  $htmlbody .= '</tr>
';

  $platz = 0;
  $platz2 = 0;
  $p1 = 0;
  $p2 = 0;

/*
  echo "<pre>";
  print_r(array_values($goal));
  echo "</pre>";
*/


  // für HTML aufbereiten
  for ($i = 0; $i < count($goal); $i++) {
    // Begrenzung Anzahl Tipper
    if ($i == $showtipper) {
      break;
    }

    // Bedingung, die alle Nicht-Tipper rausfiltert falls gewünscht
    if (($shownichttipper != 0) or ($goal[$i][2] != 0)) {

      // Wert im array mit Username vergleichen -> fett darstellen
      if (chop($goal[$i][0]) == $username) {
        $goal[$i][0] = "<b>". $goal[$i][0] ."</b>";
      }

      $platz++;

      // Ausgabe der Platzierung wenn:
      //  1. Punkte ungleich dem Vorgänger
      //  2. Punkte gleich, aber Anzahl getippter Spiele unterschiedlich

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
      }  // if end
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

      }  // else end

      // Erfolgsquote in Prozent oder in Punkte pro Spiel
      if ($goal[$i][2] > 0) {
        if ($tippmodus == 0) {  // Tendenz
        $quote = $goal[$i][1] / $goal[$i][2] * 100;
        }
        else {  // Ergebnis
          $quote = $goal[$i][1] / $goal[$i][2];
        }
      }
      else {
        $quote = 0;
      }

      $htmlbody .= '</td> <td>'.$goal[$i][0].'</td>';

      // Gesamtinhalt getippter Spiele ausgeben?
      if ($show_sp_ges == 1) {
        $htmlbody .= '<td>'.$goal[$i][2].'&nbsp;</td>';
      }

      // werden Jokerpunkte zugelassen? wenn ja, Spalte einblenden - ja oder nein?
      if (($show_joker == 1) and ($joker == 1)) {
        $htmlbody .= '<td>'.$goal[$i][3].'&nbsp;</td>';
      }

      // Quote richtiger Spiele ausgeben - ja oder nein?
      if ($show_sp_proz == 1) {
        $htmlbody .= '<td>'.round($quote, 2).'&nbsp;</td>';
      }

      // Anzahl Tipps ausgeben - ja oder nein?
      if ($show_punkte == 1) {
        $htmlbody .= '<td>'.$goal[$i][1].'&nbsp;</td>';
      }

      // Anzahl Tipps ausgeben - ja oder nein?
      if (($show_team == 1) and ($team == 1)) {
        $htmlbody .= '<td>'.$goal[$i][4].'&nbsp;</td>';
      }

      $htmlbody .= '</tr>
';

    }  // Filter Nicht-Tipper end 
  }  // for end

  $htmlbody .= '</table>
<p align="center">';//'<font color='.$fontcolor.'>Anzahl der aktiven Tippspieler: '. count($goal) . '</font>';

  if ($shownichttipper == 0) {
    $htmlbody .= '<font class="foot">(Nicht-Tipper werden nicht dargestellt)</font>';
  }

  $zeit2 = microtime(); // Zeit Stopp

  $htmlbody .= '<p align="center">Stand vom '.date("d.m.Y").' - '.date("H:i") .' Uhr<br><font class="foot">generiert in '.($zeit2-$zeit1).' Sekunden</font></p>
<p align="center"><a href="auswert3.php" title="zurück zur Auswahl">zurück zur Auswahl</a>&nbsp;|&nbsp;<a href="lmo.php?action=tipp" title="zurück zum Tippspiel">zurück zum Tippspiel</a></p>';


}  // else - wenn OK-Button geklickt wurde end


// Ausgabe HTML Code an Browser
echo $htmlhead . $htmlbody . $htmlfoot;

clearstatcache();

?>