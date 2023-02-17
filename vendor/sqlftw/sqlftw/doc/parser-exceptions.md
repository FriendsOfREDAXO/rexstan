
Parser exceptions hierarchy:
----------------------------

- **ParsingException**
  - **LexerException** - thrown by `Lexer` before `TokenList` is created
  - **ParserException** - thrown by `Parser` after `TokenList` is created
    - **InvalidVersionException** - parsed feature is not supported by current `Platform`
    - **InvalidTokenException** - unexpected token type or concrete token value, e.g. unexpected keyword
    - **InvalidValueException** - unexpected value, e.g. invalid date value or integer out of range

also can be thrown:

- **InvalidDefinitionException** - should not be thrown when using `Parser`, only when assembling commands or their components by hand


- LexerError
  - message 
  - input
  - input position
- ParserError
  - @SqlNode 
  - message
  - token list position
- ParserWarning
  - @SqlNode
  - message
- VersionError
  - @SqlNode
  - message
  - version
- TypeError
  - @SqlNode
  - message
  - invalid value
  - expected type
