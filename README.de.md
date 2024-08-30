rexstan
=======
Fügt REDAXO eine Codeanalyse hinzu, um die Entwicklerproduktivität und Codequalität zu verbessern.
![Screenshots](https://github.com/FriendsOfREDAXO/rexstan/blob/assets/stanscreen.png?raw=true)

## IDE-Integration
Die effektivste Nutzung von rexstan erfolgt durch die Integration in die IDE.
So werden Probleme direkt während der Arbeit am Quellcode gemeldet.

### PHPStorm
Öffne die "Einstellungen" und suche nach "phpstan".
Navigiere zu "PHP" -> "Quality Tools" -> "PHPStan" und öffne die "Lokale Konfiguration" durch Klicken auf den "..."-Button.
Konfiguriere den "PHPStan-Pfad" zu `/pfad/zum/projekt/redaxo/src/addons/rexstan/vendor/bin/phpstan`.
Klicke auf "Validieren" und stelle sicher, dass kein Fehler gemeldet wird.
Klicke auf "PHPStan Inspektion". Aktiviere die "PHPStan-Validierung" durch Ankreuzen der Checkbox.
Konfiguriere die "Konfigurationsdatei" zu `/pfad/zum/projekt/redaxo/src/addons/rexstan/phpstan.neon`.
Es kann sinnvoll sein, den "Schweregrad" für die "PHPStan-Validierung" auf "Warnung" oder "Fehler" zu erhöhen.
Schließe alle Dialoge mit "OK".

## Web-Oberfläche
Sofern der Webserver es erlaubt, kann die Analyse über die REDAXO-Backend-Weboberfläche eingesehen und ausgeführt werden.
Dies funktioniert möglicherweise nicht auf jedem Server aufgrund von Sicherheitseinstellungen.
Für eine optimale Entwicklererfahrung sollte die REDAXO-Editor-Integration aktiviert werden.

## Ablauf von TODO-Kommentaren
Unter Verwendung von [phpstan-todo-by](https://github.com/staabm/phpstan-todo-by) unterstützt rexstan TODO-Kommentare im Code mit Ablaufdatum.
Beispiele:
```php
// TODO redaxo/redaxo#5860 wird zu einem phpstan-Fehler, wenn das GitHub-Issue (oder Pull Request) geschlossen wird
// TODO 2021-09-30 wird zu einem Fehler, wenn das aktuelle Datum nach dem 2021-09-30 liegt
```

## REDAXO-Konsole
Die Analyse kann über die REDAXO-Konsole mit dem Befehl `php redaxo/bin/console rexstan:analyze` ausgeführt werden, was in den meisten Umgebungen funktionieren sollte.
Dies kann nützlich sein, um beispielsweise Berichte zu erstellen und den Fortschritt bei der Behebung gemeldeter Probleme im Laufe der Zeit zu verfolgen.

## REDAXO Docker 
Bei Verwendung von rexstan mit [docker-redaxo](https://github.com/FriendsOfREDAXO/docker-redaxo) muss möglicherweise der /tmp-Ordner beschreibbar gemacht werden. Öffne die Docker-Konsole und führe aus: `chmod 777 -R /tmp && chmod o+t -R /tmp`

## PHP-Speicherlimits 
Bei Problemen mit dem Speicherverbrauch sollte das PHP-Speicherlimit erhöht werden. 
Setze das PHP-Speicherlimit in der php.ini auf: `memory_limit = 1024M` oder höher

**Für das REDAXO Docker Image**
Öffne die Docker-Konsole und setze das neue Speicherlimit mit: 
`printf 'memory_limit = 1024M\n' >> /usr/local/etc/php/conf.d/uploads.ini \`
Starte den Container neu

## 💌 rexstan unterstützen
[Eine Unterstützung des Projekts](https://github.com/sponsors/staabm) ermöglicht es, dieses Tool noch schneller für alle zu verbessern.

## Danksagungen
- rexstan von [Markus Staab](https://github.com/staabm)
- rexstan-Logo von Ralph Zumkeller, yakamara.de
- PHPStan von [Ondřej Mirtes](https://github.com/ondrejmirtes) und [Mitwirkenden](https://github.com/phpstan/phpstan-src/graphs/contributors)
