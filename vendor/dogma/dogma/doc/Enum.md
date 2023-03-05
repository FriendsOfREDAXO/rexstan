

Dogma\Enum
==========

Properties of this implementation of enums and sets compared to others:

- Enum and Set values are NOT singletons - this means that:
  - cannot be compared with `===`. you should use `equals()`
  - can be constructed directly with `new`
  - can be serialized and deserialized
  - value inheritance is a possibility now
- There is support for "partial enums"


Enum/Set value inheritance:
---------------------------

To keep LSP, enums and sets has to be contravariant.
This means, that you can not add new values in a descendant class, but only remove them.
The class on top of hierarchy must be the most wide, with all possible values (e.g. Country),
while the child classes can have only subsets of original values (e.g. CountryEurope).

There are several ways of reducing values of a child enum:

1) redeclare the constants you want to keep

   The constants must have the same value as in parent class.
   Useful when inheriting only a few values.

2) remove unwanted constants with annotation `@removed`

   Using this annotation will remove the constant from valid values.
   Remaining constants inherited from parent class will be used as valid values for child class.

   For better readability in IDE it is better to mark removed constants also as `@deprecated`, but
   `@deprecated` itself will not remove the value from valid values. This is because `@deprecated`
   should keep it's original meaning.

3) both redeclaring and removing can be combined

   In this case only redeclared constants without `@removed` annotation will be used as child class values.

Equality through method `equals()` is maintained between classes with the same root ancestor.


Partial enums:
--------------

They are more like a value object, with pre-defined set of values and validation rule to accept other values.

One use case example: your app can send an HTTP request and needs to track the HTTP response code returned.
But even when there are some RFCs, you have no control over what the other side sends. It might send some 
non-existing code like 666, which would not fit into regular enum.
