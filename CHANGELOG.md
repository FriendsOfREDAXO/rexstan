# Changelog

Alle wichtigen Änderungen an diesem Projekt werden in dieser Datei dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/),
und dieses Projekt folgt [Semantic Versioning](https://semver.org/lang/de/).

## [Unreleased]

### 🚀 New Features (Neue Funktionen)

#### Added (Hinzugefügt)

- **Nicht-blockierende Analyse-Seite**: `rexstan/analysis` blockiert den Browser-Request nicht mehr für die gesamte Dauer eines PHPStan-Laufs. Die Seite lädt sofort (zeigt das zuletzt gecachte Ergebnis inkl. eines "Neu analysieren"-Buttons, oder startet beim allerersten Aufruf automatisch einen Lauf), ein Analyse-Lauf wird als abgekoppelter Hintergrundprozess gestartet, und kleines JS pollt den Fortschritt, bis das Ergebnis fertig ist und tauscht es dann ohne Seiten-Reload aus.
  - Neuer Ajax-Endpunkt `index.php?rex-api-call=rexstan_analysis&action=start|status` (`lib/Api/AnalysisApi.php`).
  - Lauf-Status/-Ergebnis wird dateibasiert persistiert (`lib/RexStanRunStore.php`) statt an den auslösenden Request gebunden zu sein – ein verwaister/abgestürzter Hintergrundlauf blockiert nach 15 Minuten automatisch keine neuen Läufe mehr.
  - `RexStan::startBackgroundWebAnalysis()` spawnt den PHPStan-Lauf detached (Unix: `shell_exec('(...) &')`, Windows: `start /B`); Ergebnis wird atomar (erst in eine Temp-Datei, dann per `mv`/`move`) an seinen finalen Pfad geschrieben, damit ein Poller nie eine unvollständige Ergebnisdatei zu sehen bekommt.
  - `RexResultsRenderer::renderAnalysisBody()` extrahiert die bisher direkt in `pages/analysis.php` liegende Rendering-Logik in eine wiederverwendbare Methode, die sowohl beim normalen Seitenaufruf (gecachtes Ergebnis) als auch von der Ajax-Statusabfrage (frisches Ergebnis) genutzt wird.

- **Hinweis auf niedrigeres Level bei sehr vielen Ergebnissen**: Liefert ein Lauf mehr als 200 Probleme, erscheint ein Hinweis, in den Einstellungen ein niedrigeres Level zu wählen und sich von dort schrittweise nach oben zu arbeiten. PHPStan-Level sind selbst bereits eine Priorisierung nach Strenge (jede Stufe baut auf den Prüfungen aller niedrigeren Stufen auf).

### 🧹 Code Quality

- `RexStan::runFromWeb()`'s Interpretation der rohen PHPStan-Ausgabe (JSON vs. Klartext-Fehler) in `RexStan::interpretAnalysisOutput()` extrahiert, damit sowohl der synchrone als auch der neue Hintergrund-Pfad dieselbe Logik nutzen.
- PSR-3-Log-Interpolation statt String-Konkatenation in `RexStan::interpretAnalysisOutput()`.

### ⚠️ Bekannte Einschränkungen

- Hintergrundausführung erfordert `shell_exec()`/`proc_open()` (auf Unix) bzw. `popen()` (auf Windows) – auf Hosts, auf denen das deaktiviert ist, ist dieselbe Einschränkung wie bisher bei der Web-UI insgesamt gegeben (siehe README: "Die Web UI funktioniert nicht auf allen Systemen"). Ein sauberer Fallback auf den bisherigen synchronen Weg fehlt in dieser Version noch.
- Kein manueller Abbruch eines laufenden Hintergrund-Laufs (nur automatische Stale-Erkennung nach 15 Minuten).
