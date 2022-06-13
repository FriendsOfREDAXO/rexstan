# Häufig gestellte Fragen

## Was kann ich von rexstan erwarten?

rexstan visualisiert unterschiedlichste Probleme im source code.
Es ist nicht möglich diese Probleme vom AddOn selbst lösen zu lassen.

Wo nach Problem gesucht wird, kann in den Einstellungen definiert werden.
Es gibt sogenannte Levels die man in aufsteigender Folge nacheinander durcharbeiten sollte.

## Wie soll ich mit dem Addon arbeiten?

Als Neuling hat es sich bewährt zunächst alle Probleme im Level 0 zu bewerten und bestenfalls zu beheben.
Es ist nicht zwingend nötig alle Probleme eines Levels zu beheben um sich im Anschluß mit dem nächsten zu befassen.
Dennoch zeigt die Erfahrung dass es empfehlenswert die Levels nacheinander zu durchlaufen.

Durch das aktivieren von PHP-Extensions (siehe Einstellungen), können weitere Probleme aufgedeckt werden.

Falls die Analyse zu lange dauert empfiehlt es sich den Umfang des scannings zu reduzieren.
Man sollte allerdings versuchen so viel Code möglich zu scannen.
Bei aktivierter PHPStorm integration - siehe Readme - können "beliebig" große Projekte untersucht werden.

##  Wie mit dem Fehler "Instantiated class X not found." umgehen?

Falls die Klasse auf ein AddOn hinweist, das noch nicht aktiviert ist, sollte dieses aktiviert werden.

Gleiches gilt für vergleichbare Fehler wie Bspw.
- "Access to property YY on an unknown class X."
- "Call to method MM on an unknown class X."

## Wie mit dem Fehler "Call to an undefined method CC::MM()." umgehen?

In der Regel deuten derartige Fehler daraufhin, dass rexstan nicht den genauen Typ/Klasse einer Variable kennt,
oder ein inkorrekter Type zugeordnet ist.

Prüfe zum Beispiel wie die Variable definiert wird und ob dabei verwendete Methoden/Funktionen korrekte Parameter und Return-Typen definieren.  
