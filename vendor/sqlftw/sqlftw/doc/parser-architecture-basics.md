
How Parser works:
=================

- `Lexer::tokenize()` is a generator which produces `Tokens` from input string
- `Lexer::slice()` is a generator which buffers `Tokens` produced by `tokenize()` and checks if a currently valid 
  delimiter (or delimiter definition) has been produced. in that case it creates a `TokenList` with all tokens from
  buffer. also notes the delimiter change in `Session`
- `Parser::parse()` (the entrypoint of the process) takes `TokenLists` produced by `Lexer` and passes them to 
  `Parser::parseTokenList()`. checks every returned `Command` and all commands affecting parsing process passes 
  to `SessionUpdater`
- `SessionUpdater` interprets commands like `SET sql_mode = ...`, `SET NAMES ...` and `SET CHARSET ...` and updates 
  state of `Session`, so both `Lexer` and `Parser` can react to syntax changes.
- `Parser::parseTokenList()` receives a `TokenList` and parses it repeatedly* to `Parse::parseCommand()`. 
  (*this is needed because commands might be separated either by a current delimiter or with a semicolon, even when
  it is not set as a delimiter. this means that `TokenList` might contain `Tokens` for more than one command.)
- `Parser::parseCommand()` receives a `TokenList` (maybe the same list repeatedly) and continues parsing from current 
  position in list. 

