
Token type hierarchy:
---------------------

TokenType constants:

- **WHITESPACE**
- **COMMENT**
    - **BLOCK_COMMENT** - `/* ... * /`
        - **OPTIONAL_COMMENT** - `/*! ... * /`
        - **HINT_COMMENT** - `/*+ ... * /`
    - **DOUBLE_HYPHEN_COMMENT** - `-- ...`
    - **DOUBLE_SLASH_COMMENT** - `// ...`
    - **HASH_COMMENT** - `# ...`
- **NAME**
    - **UNQUOTED_NAME** - `table1` etc.
        - **KEYWORD** - `datetime` etc.
            - **RESERVED** - `SELECT` etc.
                - **OPERATOR** - `AND`, `OR` etc.
    - **DOUBLE_QUOTED_STRING** - `"table1"` (standard, MySQL in ANSI_STRINGS mode)
    - **BACKTICK_QUOTED_STRING** - `` `table1` `` (MySQL, PostgreSQL, Sqlite)
    - **SQUARE_BRACKETED_STRING** - `[table1]` (MSSQL, SqLite)
    - **AT_VARIABLE** - `@var`, `@@global`, `@'192.168.0.1'` (also includes host names)
        - **SINGLE_QUOTED_STRING** - `@'var'`
        - **DOUBLE_QUOTED_STRING** - `@"var"`
        - **BACKTICK_QUOTED_STRING** - `` @`var` ``
- **VALUE**
    - **STRING**
        - **SINGLE_QUOTED_STRING** - `'string'` (standard)
        - **DOUBLE_QUOTED_STRING** - `"string"` (MySQL in default mode)
        * **DOLLAR_QUOTED_STRING** - `$foo$table1$foo$` (PostgreSQL)
    - **NUMBER**
        - **INT**
            - **UINT**
    - **BINARY_LITERAL**
    - **HEXADECIMAL_LITERAL**
    - **UUID** - e.g. `3E11FA47-71CA-11E1-9E33-C80AA9429562`
- **SYMBOL** - `(`, `)`, `[`, `]`, `{`, `}`, `.`, `,`, `;`
    - **OPERATOR** - `+`, `||` etc.
- **PLACEHOLDER** - placeholder for a parameter
    - **QUESTION_MARK_PLACEHOLDER** - `?` (SQL, Doctrine, Laravel)
    - **NUMBERED_QUESTION_MARK_PLACEHOLDER** - `?123` (Doctrine)
    - **DOUBLE_COLON_PLACEHOLDER** - `:foo` (Doctrine, Laravel)
- **DELIMITER** - default `;`
- **DELIMITER_DEFINITION**
