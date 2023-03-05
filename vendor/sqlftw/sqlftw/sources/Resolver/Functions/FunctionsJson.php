<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Resolver\Functions;

use SqlFtw\Resolver\UnresolvableException;

trait FunctionsJson
{

    /**
     * -> - Return value from JSON column after evaluating path; equivalent to JSON_EXTRACT().
     *
     * @param mixed|null $left
     * @param mixed|null $right
     */
    public function _json_extract($left, $right): ?string
    {
        ///
        throw new UnresolvableException('_json_extract_unquote() is not implemented.');
    }

    /**
     * ->> - Return value from JSON column after evaluating path and unquoting the result; equivalent to JSON_UNQUOTE(JSON_EXTRACT()).
     *
     * @param mixed|null $left
     * @param mixed|null $right
     */
    public function _json_extract_unquote($left, $right): ?string
    {
        ///
        throw new UnresolvableException('_json_extract_unquote() is not implemented.');
    }

    // JSON_ARRAY() - Create JSON array
    // JSON_ARRAY_APPEND() - Append data to JSON document
    // JSON_ARRAY_INSERT() - Insert into JSON array
    // JSON_CONTAINS() - Whether JSON document contains specific object at path
    // JSON_CONTAINS_PATH() - Whether JSON document contains any data at path
    // JSON_DEPTH() - Maximum depth of JSON document
    // JSON_EXTRACT() - Return data from JSON document
    // JSON_INSERT() - Insert data into JSON document
    // JSON_KEYS() - Array of keys from JSON document
    // JSON_LENGTH() - Number of elements in JSON document
    // JSON_MERGE() - Merge JSON documents, preserving duplicate keys. Deprecated synonym for JSON_MERGE_PRESERVE()
    // JSON_MERGE_PATCH() - Merge JSON documents, replacing values of duplicate keys
    // JSON_MERGE_PRESERVE() - Merge JSON documents, preserving duplicate keys
    // JSON_OBJECT() - Create JSON object
    // JSON_OVERLAPS() - Compares two JSON documents, returns TRUE (1) if these have any key-value pairs or array elements in common, otherwise FALSE (0)
    // JSON_PRETTY() - Print a JSON document in human-readable format
    // JSON_QUOTE() - Quote JSON document
    // JSON_REMOVE() - Remove data from JSON document
    // JSON_REPLACE() - Replace values in JSON document
    // JSON_SCHEMA_VALID() - Validate JSON document against JSON schema; returns TRUE/1 if document validates against schema, or FALSE/0 if it does not
    // JSON_SCHEMA_VALIDATION_REPORT() - Validate JSON document against JSON schema; returns report in JSON format on outcome on validation including success or failure and reasons for failure - 8.0.17
    // JSON_SEARCH() - Path to value within JSON document
    // JSON_SET() - Insert data into JSON document
    // JSON_STORAGE_FREE() - Freed space within binary representation of JSON column value following partial update
    // JSON_STORAGE_SIZE() - Space used for storage of binary representation of a JSON document
    // JSON_TABLE() - Return data from a JSON expression as a relational table
    // JSON_TYPE() - Type of JSON value
    // JSON_UNQUOTE() - Unquote JSON value
    // JSON_VALID() - Whether JSON value is valid
    // JSON_VALUE() - Extract value from JSON document at location pointed to by path provided; return this value as VARCHAR(512) or specified type
    // MEMBER OF() - Returns true (1) if first operand matches any element of JSON array passed as second operand, otherwise returns false (0)

}
