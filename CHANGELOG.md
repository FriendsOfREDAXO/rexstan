# Changelog

Alle wichtigen Änderungen an diesem Projekt werden in dieser Datei dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/),
und dieses Projekt folgt [Semantic Versioning](https://semver.org/lang/de/).

## [Unreleased]

### 🚀 New Features (Neue Funktionen)

#### Added (Hinzugefügt)

- **Nicht-blockierende Analyse-Seite**: `rexstan/analysis` blockiert den Browser-Request nicht mehr für die gesamte Dauer eines PHPStan-Laufs. Die Seite lädt sofort:
  - liegt bereits ein Ergebnis vor, wird es sofort angezeigt, mit Zeitstempel ("Ergebnis vom TT.MM.JJJJ HH:MM Uhr") und einem "🔄 Neu analysieren"-Button;
  - liegt noch keins vor, zeigt ein "▶️ Analyse starten"-Button;
  - läuft bereits ein Hintergrundlauf, erscheint sofort die Lauf-Anzeige samt automatischem Polling.
  - Der Button ist ein normaler Link (kein reines JS-Konstrukt) und funktioniert auch ohne JavaScript (lädt die Seite neu und zeigt danach den laufenden Hintergrundlauf) – JS fängt denselben Klick ab und ersetzt das Ergebnis dann ohne Reload.
  - Neuer Ajax-Endpunkt `index.php?rex-api-call=rexstan_analysis&action=start|status` (`lib/Api/AnalysisApi.php`).
  - Lauf-Status/-Ergebnis wird dateibasiert persistiert (`lib/RexStanRunStore.php`) statt an den auslösenden Request gebunden zu sein – ein verwaister/abgestürzter Hintergrundlauf blockiert nach 15 Minuten automatisch keine neuen Läufe mehr.
  - `RexStan::startBackgroundWebAnalysis()` spawnt den PHPStan-Lauf detached (Unix: `shell_exec('(...) &')`, Windows: `start /B`); Ergebnis wird atomar (erst in eine Temp-Datei, dann per `mv`/`move`) an seinen finalen Pfad geschrieben, damit ein Poller nie eine unvollständige Ergebnisdatei zu sehen bekommt.
  - `RexResultsRenderer::renderAnalysisBody()` extrahiert die bisher direkt in `pages/analysis.php` liegende Rendering-Logik in eine wiederverwendbare Methode, die sowohl beim normalen Seitenaufruf (gecachtes Ergebnis) als auch von der Ajax-Statusabfrage (frisches Ergebnis) genutzt wird.

- **Prioritäts-Zusammenfassung**: Über der Datei-für-Datei-Liste zeigt eine kompakte Tabelle die Anzahl der Probleme gebündelt nach Kritikalität – 🔴 Kritisch (Typ-/Nullsicherheit, potenzielle Laufzeitfehler, SQL-Risiken), 🟡 Code-Style (Strict-Rules-Präferenzen) und 🔵 Wartbarkeit (fehlende Typangaben, unbenutzter Code, Komplexität, totes Code) – jeweils mit Anzahl und Prozentanteil. Bei mehreren tausend Einzelmeldungen (z. B. Level 10 mit allen Zusatzregelsets) ist die reine Datei-Liste sonst kaum überblickbar.
  - Neue Klasse `RexStanCategorizer` (`lib/RexStanCategorizer.php`) ordnet jeden PHPStan-Fehler-Identifier einer der drei Kategorien zu. Die Zuordnung ist zwangsläufig eine Wertung (PHPStans JSON-Ausgabe kennt selbst keine Kritikalität, nur den Regel-Identifier) – ein nicht gelisteter/zukünftiger Identifier landet standardmäßig in "Kritisch", damit nichts Unbekanntes fälschlich als "nur Stil" versteckt wird.
  - Basiert auf den 86 tatsächlich in diesem Projekt beobachteten Identifiern (Level 10 + strict-rules + deprecation-rules + cognitive-complexity + dead-code + type-perfect), nicht auf Vermutungen.

### 🐛 Bug Fixes (Fehlerbehebungen)

- `RexStan::generateAnalysisBaseline()` und `RexStan::analyzeSummaryBaseline()` (Backend-Seite "Zusammenfassung") riefen PHPStan ohne `--no-progress` auf. Je nach Umgebung landeten dadurch rohe Fortschrittsbalken-Steuerzeichen im stderr-Output, der bei einem Fehler in der Exception-Message ausgegeben wird ("Unable to generate baseline: ⣾⣽⣻…") – die eigentliche Fehlerursache war darin nicht mehr lesbar. Beide Aufrufe haben jetzt `--no-progress`, wie alle anderen PHPStan-Aufrufe in dieser Datei bereits.

### 🧹 Code Quality

- `RexStan::runFromWeb()`'s Interpretation der rohen PHPStan-Ausgabe (JSON vs. Klartext-Fehler) in `RexStan::interpretAnalysisOutput()` extrahiert, damit sowohl der synchrone als auch der neue Hintergrund-Pfad dieselbe Logik nutzen.
- PSR-3-Log-Interpolation statt String-Konkatenation in `RexStan::interpretAnalysisOutput()`.
- `RexResultsRenderer::renderAnalysisBody()` (vorher eine einzelne, tief verschachtelte Methode mit hoher kognitiver Komplexität) in mehrere fokussierte private Methoden aufgeteilt (je ein Zweig: String-Fehler, Laufzeit-Fehler, Erfolg, Datei-Liste).

### ⚠️ Bekannte Einschränkungen

- Hintergrundausführung erfordert `shell_exec()`/`proc_open()` (auf Unix) bzw. `popen()` (auf Windows) – auf Hosts, auf denen das deaktiviert ist, ist dieselbe Einschränkung wie bisher bei der Web-UI insgesamt gegeben (siehe README: "Die Web UI funktioniert nicht auf allen Systemen"). Ein sauberer Fallback auf den bisherigen synchronen Weg fehlt in dieser Version noch.
- Kein manueller Abbruch eines laufenden Hintergrund-Laufs (nur automatische Stale-Erkennung nach 15 Minuten).
