# Tipp-Auswertung: beste Spieltage (by Marcus)

Individuelle Auswertung der LMO-Tippspielligen **„Beste Spieltage“**: Jeder Tipper wird nur nach seinen
X besten Tippspieltagen gewertet (X wird im Formular eingegeben). So fallen einzelne schwache Spieltage
oder verpasste Spieltage weniger ins Gewicht.

Autor: Marcus ([BC Erlbach](http://www.bcerlbach.de/)), Version 1.0 (Dezember 2006), hier für **PHP 8**
und die aktuelle LMO-Version überarbeitet.

## Voraussetzungen

- LMO (PHP-8-Version) mit eingerichtetem und ausgewertetem Tippspiel (Addon `tipp`)
- Die Auswertungsdatei `gesamt.aus` sowie die `*.aus`-Dateien der Ligen müssen vorhanden sein
  (im Tipp-Admin „Tippspiel neu auswerten“). Sie werden nur gelesen, ein Backup ist nicht nötig.

## Installation

1. `auswert3.php` in das **LMO-Hauptverzeichnis** kopieren (dorthin, wo `init.php` liegt).
2. Optional die Einstellungen am Dateianfang anpassen (siehe unten).
3. Aufrufen: `https://deine-domain/lmo/auswert3.php`

## Bedienung

1. Gewünschte Liga wählen (oder „gesamt“).
2. Haken bei **„nur die besten … Spieltage werten“** setzen und die Anzahl der Spieltage eintragen
   (nur ganze Zahlen, 1–99).
3. Auswerten. Über der Tabelle steht, wie viele Spieltage gewertet wurden.
   Ist die Eingabe größer als der aktuell verfügbare Tippspieltag, werden alle vorhandenen
   Spieltage gewertet und das wird angezeigt.

Ohne den Haken werden alle Spieltage gewertet.

## Einstellungen

Im Block `ab hier können Einstellungen vorgenommen werden` am Dateianfang:

| Einstellung                              | Bedeutung                                              |
|------------------------------------------|--------------------------------------------------------|
| `$fontfamily`, `$fontsize`, `$fontcolor` | Schrift, Größe, Farbe                                  |
| `$tablehaedercolor`, `$tablebackcolor`, `$tablefontcolor`, `$tablefontsize` | Tabellenfarben und -schrift |
| `$colorplatz1` … `$colorplatz3`          | Hintergrund für Platz 1–3 (leer lassen = keine Farbe)  |
| `$showtipper`                            | Anzahl angezeigter Tipper (`-1` = alle)                |
| `$shownichttipper`                       | Tipper ohne Tipp anzeigen (`1`) oder nicht (`0`)       |
| `$show_sp_ges`, `$show_sp_proz`, `$show_joker`, `$show_punkte`, `$show_team` | Spalten ein-/ausblenden (1/0) |
| `$var_spiele`, `$var_joker`, `$var_tippsrichtig`, `$var_team` | Spaltenüberschriften              |
| `$title`, `$status`                      | Seitentitel / Statusleistentext                        |

Unterhalb von `ab hier nichts mehr ändern` sollte nichts angepasst werden.

Tippmodus (Tendenz oder Ergebnis) und Tippverzeichnis werden automatisch aus der Konfiguration
des Tipp-Addons gelesen.

## Änderungen für PHP 8

- Nicht gesetzte Variablen, Array-Schlüssel und `$_POST`-Werte werden geprüft (`isset`, `??`)
- Das Eingabefeld „Spieltage“ akzeptiert nur ganze Zahlen (`ctype_digit`, `(int)`); der Wert wird in der
  Ausgabe nicht mehr ungeprüft in die Seite geschrieben (vorher war über dieses Feld Script-Einschleusung
  / XSS möglich)
- Ligadatei aus dem Formular wird mit `basename()` auf den Dateinamen begrenzt (kein Pfad möglich)
- Sortierung und Schleifen mit Grenzprüfung, Typ-Umwandlungen bei Zahlen aus Textdateien
- Verzeichnis der Auswertungsdateien und Tippmodus aus der Addon-Konfiguration statt fest verdrahtet
- PSR-12-formatiert (php-cs-fixer), UTF-8, LF-Zeilenenden

## Dateien

| Datei                                      | Zweck                                   |
|--------------------------------------------|-----------------------------------------|
| `auswert3.php`                             | das Auswertungsskript                   |
| `readme_tipp_auswert_beste_spieltage.txt`  | ursprüngliche Beschreibung (Version 1.0) |
| `readme_tipp_auswert_beste_spieltage.htm`  | dito als HTML                           |
| `LICENSE`                                  | GNU General Public License              |

## Lizenz

GNU General Public License, Version 2 oder höher – siehe [LICENSE](LICENSE).
Die Urheberhinweise im Quelltext dürfen nicht entfernt oder geändert werden.
