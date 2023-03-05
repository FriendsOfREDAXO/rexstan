
## Reflection loading

All objects in the reflection hierarchy are immutable from the outside, however all of them can change internally by 
lazy loading already existing database objects from external reference source (database, structure exports).



## Reflection structure

- Session - holds session state
    - **string** $schema
    - **VariablesReflection** $variables
    - **VariablesReflection** $userVariables

- ServerReflection (not implemented yet. applicable on PostgreSQL)
    - **DatabaseReflection**[]
        - **SchemaReflection**[]
            - **TableReflection**[]
                - **ColumnReflection**[]
                    - ?IndexReflection ???
                    - ?ForeignKeyReflection ???
                    - ?CheckReflection ???
                - **IndexReflection**[]
                    - ColumnReflection (origin)
                    - ColumnReflection[] ???
                - **ForeignKeyReflection**[]
                    - ColumnReflection (origin)
                    - ColumnReflection[] ???
                - **CheckReflection**[]
                    - ColumnReflection (origin)
                    - ColumnReflection[] ???
                - **ConstraintReflection** ???
                    - IndexReflection|ForeignKeyReflection|CheckReflection
                - **PartitionReflection** []
                - TablespaceReflection
            - **EventReflection**[]
                - ?FunctionReflection|ProcedureReflection ???
            - **FunctionReflection**[]
            - **ProcedureReflection**[]
            - **TriggerReflection**[]
                - TableReflection ???
            - **ViewReflection**[]
    - **VariablesReflection** - persistent/server state
    - **TablespaceReflection**[] (currently on Database level. should be on server level on PostgreSQL)
    - **UserReflection**[] ??? (currently on Database level. should be on server level on PostgreSQL)


## Reflection exceptions

exceptions can be sorted by two parameters:
 - the object `<type>` they are related to (schema, table, index)
 - and the `<event>`, that caused the exception (not found, dropped, ...)

additionally, their interfaces differ by their `<container>` (which determines how they are addressed: simple name, qualified name or name and table name)

all exceptions:
 - extend abstract `ReflectionException` via abstract `<type>Exception`
 - implement `ObjectException` via one of second level `<container>ObjectException` and one of `Object<event>Exception` interfaces


### Exception object `<container>` and `<type>` hierarchy:

first and second level are interfaces. third level are exceptions

- `ObjectException`
  - `DatabaseObjectException`
    - `Schema..`
    - `Tablespace..`
  - `SchemaObjectException`
    - `Table..`
    - `View..`
    - `Procedure..`
    - `Function..`
    - `Event..`
    - `Trigger..` *
  - `TableObjectException`
    - `Column..`
    - `Constraint..`
    - `Index..`
    - `ForeignKey..`
    - `Check..`

*) trigger works on a table, but SQL syntax treats it as schema object.
 (it can be dropped and inspected without using table name)


### Exception `<event>` hierarchy:
`Object..` exceptions are interfaces, all others are exceptions

- `..Exception` *(abstract)*
  - `..AlreadyExistsException`
  - `..DoesNotExistException` *(abstract)*
    - `..NotFoundException`
    - `..DroppedException`
    - `..RenamedException`
  - `..LoadingFailedException`


### Exceptions matrix:

|            | Exception | Already.. | DoesNot.. | NotFound | Dropped | Renamed | Loading.. |
|------------|-----------|-----------|-----------|----------|---------|---------|-----------|
| Object     | i         | i         | i         | i        | i       | i       | i         |
| Schema     |           |           |           |          |         | /       |           |
| Tablespace |           |           |           |          |         | /       |           |
| Table      |           |           |           |          |         |         |           |
| View       |           |           |           |          |         | *       |           |
| Procedure  |           |           |           |          |         | **      |           |
| Function   |           |           |           |          |         | **      |           |
| Event      |           |           |           |          |         |         |           |
| Trigger    |           |           |           |          |         | /       |           |
| Column     |           |           |           |          |         |         | -         |
| Constraint |           |           |           |          |         | /       | -         |
| Index      |           |           |           |          |         | /       | -         |
| ForeignKey |           |           |           |          |         | /       | -         |
| Check      |           |           |           |          |         | /       | -         |

- `i` interface 
- `/` not implemented by DB (exception does exist, but is not currently used)
- `-` use case does not make sense (exception does not exist)
- `*` renamed by RENAME TABLE
- `**` can be renamed via system tables (not implemented in reflection yet)
