# Häufig gestellte Fragen

## Was kann ich von rexstan erwarten?

rexstan visualisiert unterschiedlichste Probleme im source code.
Es ist nicht möglich diese Probleme vom AddOn selbst lösen zu lassen.

Wo nach Problem gesucht wird, kann in den Einstellungen definiert werden.
Es gibt sogenannte Levels die man in aufsteigender Folge nacheinander durcharbeiten sollte.


## Wie soll ich mit dem Addon arbeiten?

### Verwendung in neuen Projekten

In neue Projekten bietet es sich für rexstan Neulinge an, mit dem Level 5 zu starten.

Wenn bereits Erfahrung mit code analyse tools gesammelt wurde, steht einem Start in noch höherem Level, ggf. sogar mit aktiviertem Strict-Mode.


### Nachträgliche Verwendung in bestehenden Projekten

Als Neuling hat es sich bewährt zunächst alle Probleme im Level 0 zu bewerten und bestenfalls zu beheben.
Es ist nicht zwingend nötig alle Probleme eines Levels zu beheben um sich im Anschluß mit dem nächsten zu befassen.
Dennoch zeigt die Erfahrung, dass es empfehlenswert ist die Levels nacheinander zu durchlaufen.

Durch das aktivieren von PHP-Extensions (siehe Einstellungen), können weitere Probleme aufgedeckt werden.

Falls die Analyse zu lange dauert empfiehlt es sich den Umfang des scannings zu reduzieren.
Man sollte allerdings versuchen so viel Code möglich zu scannen.
Bei aktivierter PHPStorm integration - siehe Readme - können "beliebig" große Projekte untersucht werden.


### Wie kann ich Vorgehen?

Damit rexstan gute Analyse-Ergebnisse liefern kann ist eine präzise Typisierung notwendig.
Dies erreicht man indem man alle Parameter und Return-Typen von Funktionen und Methoden definiert.

Dies kann sowohl via PHPDoc, als auch nativen Typehints passieren:
- [PHPDocs Basics](https://phpstan.org/writing-php-code/phpdocs-basics)
- [PHPDoc Arten](https://phpstan.org/writing-php-code/phpdoc-types)


## Die Analyse liefert keine Ergebnisse.. was nun?

Du sollest sicherstellen dass für die Analyse in der PHP-CLI genügend Speicher zur Verfügung steht und keine timeouts eintreten.
Um die Fehlerursache einzugrenzen sollte das php-error log konsultiert werden.


## Wie kann rexstan mit Deprecations helfen?

Sobald unter Einstellungen "Deprecation Warnings" aktiviert sind, werden deprecations mit entsprechenden Hinweisen gemeldet. Dies betrifft sowohl Funktionen/Methoden von verwendeten AddOns und Bibliotheken, als auch native Funktionen von PHP selbst.

Während der Bearbeitung von Deprecations sollte darauf geachtet werden, dass ggf. Mindestversionen von Abhängigkeiten angehoben werden müssen, wenn stattdessen aktuellere Funktionen/Methoden verwendet werden.

## Was ist Bleeding Edge?

Bei aktivierter [Bleeding Edge](https://phpstan.org/blog/what-is-bleeding-edge) werden experimentelle Features aktiviert, die ggf. Einschränkungen mit sich bringen.


## Was ist der Strict-Mode?

Der Strict-Mode ist für erfahrene PHP Programmierer geeignet und verbietet die Nutzung vieler gebräuchlicher Funktionen, um u.a. das Fehlerpotentzial auf ein minimum zu reduzieren.

Details dazu sind unter [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules) zu finden.


## Was ist phpstan-dba?

[`phpstan-dba`](https://staabm.github.io/2022/05/01/phpstan-dba.html) ist eine Erweiterung für PHPStan, die die statische Code Analyse von Datenbankabfragen ermöglicht.
Somit werden u.a. Fehler in SQL Abfragen erkannt.


## Wie mit dem Fehler `Loose comparison via ... is not allowed.` umgehen?

Diese Fehler werden nur im Strict-Mode gemeldet. Es wird empfohlen mit `===` bzw. `!==` zu arbeiten.
Man muss dabei darauf achten dass es in Grenzfällen zu unterschiedlichen Ergebnissen führen kann.

Weiterlesen: [PHP – Loose Comparison (==) VS Strict Comparision (===)](https://techgeekgalaxy.com/php-equality-comparisons/)


##  Wie mit dem Fehler `Instantiated class X not found.` umgehen?

Falls die Klasse auf ein AddOn hinweist, das noch nicht aktiviert ist, sollte dieses aktiviert werden.

Gleiches gilt für vergleichbare Fehler wie Bspw.
- "Access to property YY on an unknown class X."
- "Call to method MM on an unknown class X."


## Wie mit dem Fehler `Call to an undefined method CC::MM().` umgehen?

In der Regel deuten derartige Fehler daraufhin, dass rexstan nicht den genauen Typ/Klasse einer Variable kennt,
oder ein inkorrekter Type zugeordnet ist.

Prüfe zum Beispiel wie die Variable definiert wird und ob dabei verwendete Methoden/Funktionen korrekte Parameter und Return-Typen definieren.  


## Wie mit dem Fehler `parameter $ep with generic class rex_extension_point but does not specify its types: T` umgehen?

Die Klasse `rex_extension_point` verwendet einen [Class-Level-Generic](https://phpstan.org/blog/generics-in-php-using-phpdocs#class-level-generics),
der den Rückgabewert der Methode `getSubject()` eingrenzt.

Das bedeutet dass man einen Parameter vom typ `rex_extension_point` mittels `<T>` PHPDoc weiter eingrenzen kann, zum Beispiel:

```php
/**
 * @param rex_extension_point<string> $ep 
 */
function myExtension(rex_extension_point $ep) {
    $ep->getSubject(); // aufgrund des generic phpdocs, weiß rexstan dass "string" returned wird.
}
```

Weiterlesen: [Generics in PHPStan](https://phpstan.org/blog/generics-in-php-using-phpdocs)
