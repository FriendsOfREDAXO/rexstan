
Validation:
===========

Validation error severity
-------------------------

### Parser errors

collected when parsing (command is not parsed)

validated by parser(s) without need for context

- invalid token (*LexerException*)
- invalid statement syntax
- invalid statement structure (simple logic rules describing allowed relations between statement components outside the scope of basic syntax)
- otherwise valid syntax not supported by currently selected platform/version


### Static analysis errors

collected after parsing

constructs/actions not allowed by server or not reasonable to do. there should be no exception from rules with this severity


### Static analysis warnings

collected after parsing

things you should know about and take into consideration. there might be valid exceptions to rules with this severity 


### Parser warnings

collected when parsing (command is parsed)

warnings about features that are not processes by parser (parsed/skipped but not represented in resulting model/AST)

- comments inside statements
- repeated occurrence of some construct, that is allowed by syntax, but not represented in AST (e.g. column type definition is allowed to appear more than once. last one is used)
- not yet implemented features
- not supported deprecated features
- not supported features of paid plugins


### Static analysis notices

collected after parsing

useful tips how to make your SQL code better/more readable. does not require you to take any action


Static analysis rule types:
---------------------------

- errors from rules on single statement without context
   - rules similar to "invalid statement structure" rules, but impractical to built in to parser(s) itself
   - basic static analysis (variable types, argument types etc.)
- errors from rules on single statement with context
   - rules needing to know current schema or configuration of database, to validate given command against that  
- errors from rules on multiple statements
   - rules validating a sequence of commands as a whole
