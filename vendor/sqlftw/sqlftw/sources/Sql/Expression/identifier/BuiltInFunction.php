<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Sql\Expression;

use SqlFtw\Sql\Charset;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\SqlEnum;
use function in_array;
use function strtoupper;

/**
 * Function name, e.g. CURRENT_TIME
 */
class BuiltInFunction extends SqlEnum implements FunctionIdentifier
{

    // comparison
    public const COALESCE = 'COALESCE';
    public const GREATEST = 'GREATEST';
    public const INTERVAL = 'INTERVAL';
    public const ISNULL = 'ISNULL';
    public const LEAST = 'LEAST';
    public const STRCMP = 'STRCMP';

    // flow control
    public const IF = 'IF';
    public const IFNULL = 'IFNULL';
    public const NULLIF = 'NULLIF';

    // type
    public const CAST = 'CAST';
    public const CONVERT = 'CONVERT';

    // strings
    public const ASCII = 'ASCII';
    public const BIN = 'BIN';
    public const BIT_LENGTH = 'BIN_LENGTH';
    public const CHAR = 'CHAR';
    public const CHAR_LENGTH = 'CHAR_LENGTH';
    public const CHARACTER_LENGTH = 'CHARACTER_LENGTH';
    public const CONCAT = 'CONCAT';
    public const CONCAT_WS = 'CONCAT_WS';
    public const ELT = 'ELT';
    public const EXPORT_SET = 'EXPORT_SET';
    public const FIELD = 'FIELD';
    public const FIND_IN_SET = 'FIND_IN_SET';
    public const FORMAT = 'FORMAT';
    public const FROM_BASE64 = 'FROM_BASE64';
    public const HEX = 'HEX';
    public const INSERT = 'INSERT';
    public const INSTR = 'INSTR';
    public const LCASE = 'LCASE';
    public const LEFT = 'LEFT';
    public const LENGTH = 'LENGTH';
    public const LOAD_FILE = 'LOAD_FILE';
    public const LOCATE = 'LOCATE';
    public const LOWER = 'LOWER';
    public const LPAD = 'LPAD';
    public const LTRIM = 'LTRIM';
    public const MAKE_SET = 'MAKE_SET';
    public const MID = 'MID';
    public const OCT = 'OCT';
    public const OCTET_LENGTH = 'OCTET_LENGTH';
    public const ORD = 'ORD';
    public const POSITION = 'POSITION';
    public const QUOTE = 'QUOTE';
    public const REGEXP_INSTR = 'REGEXP_INSTR';
    public const REGEXP_LIKE = 'REGEXP_LIKE';
    public const REGEXP_REPLACE = 'REGEXP_REPLACE';
    public const REGEXP_SUBSTR = 'REGEXP_SUBSTR';
    public const REPEAT = 'REPEAT';
    public const REPLACE = 'REPLACE';
    public const REVERSE = 'REVERSE';
    public const RIGHT = 'RIGHT';
    public const RPAD = 'RPAD';
    public const RTRIM = 'RTRIM';
    public const SOUNDEX = 'SOUNDEX';
    public const SPACE = 'SPACE';
    public const SUBSTR = 'SUBSTR';
    public const SUBSTRING = 'SUBSTRING';
    public const SUBSTRING_INDEX = 'SUBSTRING_INDEX';
    public const TO_BASE64 = 'TO_BASE64';
    public const TRIM = 'TRIM';
    public const UCASE = 'UCASE';
    public const UNHEX = 'UNHEX';
    public const UPPER = 'UPPER';
    public const WEIGHT_STRING = 'WEIGHT_STRING';

    // XML
    // @codingStandardsIgnoreStart
    public const ExtractValue = 'ExtractValue';
    public const UpdateXML = 'UpdateXML';
    // @codingStandardsIgnoreStart

    // numeric
    public const ABS = 'ABS';
    public const ACOS = 'ACOS';
    public const ASIN = 'ASIN';
    public const ATAN = 'ATAN';
    public const ATAN2 = 'ATAN2';
    public const BIT_COUNT = 'BIT_COUNT';
    public const CEIL = 'CEIL';
    public const CEILING = 'CEILING';
    public const CONV = 'CONV';
    public const COS = 'COS';
    public const COT = 'COT';
    public const CRC32 = 'CRC32';
    public const DEGREES = 'DEGREES';
    public const EXP = 'EXP';
    public const FLOOR = 'FLOOR';
    public const LN = 'LN';
    public const LOG = 'LOG';
    public const LOG10 = 'LOG10';
    public const LOG2 = 'LOG2';
    public const MOD = 'MOD';
    public const PI = 'PI';
    public const POW = 'POW';
    public const POWER = 'POWER';
    public const RADIANS = 'RADIANS';
    public const RAND = 'RAND';
    public const ROUND = 'ROUND';
    public const SIGN = 'SIGN';
    public const SIN = 'SIN';
    public const SQRT = 'SQRT';
    public const TAN = 'TAN';
    public const TRUNCATE = 'TRUNCATE';

    // date/time
    public const ADDDATE = 'ADDDATE';
    public const ADDTIME = 'ADDTIME';
    public const CONVERT_TZ = 'CONVERT_TZ';
    public const CURDATE = 'CURDATE';
    public const CURRENT_DATE = 'CURRENT_DATE';
    public const CURRENT_TIME = 'CURRENT_TIME';
    public const CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';
    public const CURTIME = 'CURTIME';
    public const DATE = 'DATE';
    public const DATE_ADD = 'DATE_ADD';
    public const DATE_FORMAT = 'DATE_FORMAT';
    public const DATE_SUB = 'DATE_SUB';
    public const DATEDIFF = 'DATEDIFF';
    public const DAY = 'DAY';
    public const DAYNAME = 'DAYNAME';
    public const DAYOFMONTH = 'DAYOFMONTH';
    public const DAYOFWEEK = 'DAYOFWEEK';
    public const DAYOFYEAR = 'DAYOFYEAR';
    public const EXTRACT = 'EXTRACT';
    public const FROM_DAYS = 'FROM_DAYS';
    public const FROM_UNIXTIME = 'FROM_UNIXTIME';
    public const GET_FORMAT = 'GET_FORMAT';
    public const HOUR = 'HOUR';
    public const LAST_DAY = 'LAST_DAY';
    public const LOCALTIME = 'LOCALTIME';
    public const LOCALTIMESTAMP = 'LOCALTIMESTAMP';
    public const MAKEDATE = 'MAKEDATE';
    public const MAKETIME = 'MAKETIME';
    public const MICROSECOND = 'MICROSECONDS';
    public const MINUTE = 'MINUTE';
    public const MONTH = 'MONTH';
    public const MONTHNAME = 'MONTHNAME';
    public const NOW = 'NOW';
    public const PERIOD_ADD = 'PERIOD_ADD';
    public const PERIOD_DIFF = 'PERIOD_DIFF';
    public const QUARTER = 'QUARTER';
    public const SEC_TO_TIME = 'SEC_TO_TIME';
    public const SECOND = 'SECOND';
    public const STR_TO_DATE = 'STR_TO_DATE';
    public const SUBDATE = 'SUBDATE';
    public const SUBTIME = 'SUBTIME';
    public const SYSDATE = 'SYSDATE';
    public const TIME = 'TIME';
    public const TIME_FORMAT = 'TIME_FORMAT';
    public const TIME_TO_SEC = 'TIME_TO_SEC';
    public const TIMEDIFF = 'TIMEDIFF';
    public const TIMESTAMP = 'TIMESTAMP';
    public const TIMESTAMPADD = 'TIMESTAMPADD';
    public const TIMESTAMPDIFF = 'TIMESTAMPDIFF';
    public const TO_DAYS = 'TO_DAYS';
    public const TO_SECONDS = 'TO_SECONDS';
    public const UNIX_TIMESTAMP = 'UNIX_TIMESTAMP';
    public const UTC_DATE = 'UTC_DATE';
    public const UTC_TIME = 'UTC_TIME';
    public const UTC_TIMESTAMP = 'UTC_TIMESTAMP';
    public const WEEK = 'WEEK';
    public const WEEKDAY = 'WEEKDAY';
    public const WEEKOFYEAR = 'WEEKOFYEAR';
    public const YEAR = 'YEAR';
    public const YEARWEEK = 'YEARWEEK';

    // encryption & compression
    public const AES_DECRYPT = 'AES_DECRYPT';
    public const AES_ENCRYPT = 'AES_ENCRYPT';
    public const ASYMMETRIC_DECRYPT = 'ASYMMETRIC_DECRYPT';
    public const ASYMMETRIC_DERIVE = 'ASYMMETRIC_DERIVE';
    public const ASYMMETRIC_ENCRYPT = 'ASYMMETRIC_ENCRYPT';
    public const ASYMMETRIC_SIGN = 'ASYMMETRIC_SIGN';
    public const ASYMMETRIC_VERIFY = 'ASYMMETRIC_VERIFY';
    public const COMPRESS = 'COMPRESS';
    public const CREATE_ASYMMETRIC_PRIV_KEY = 'CREATE_ASYMMETRIC_PRIV_KEY';
    public const CREATE_ASYMMETRIC_PUB_KEY = 'CREATE_ASYMMETRIC_PUB_KEY';
    public const CREATE_DH_PARAMETERS = 'CREATE_DH_PARAMETERS';
    public const CREATE_DIGEST = 'CREATE_DIGEST';
    public const DECODE = 'DECODE';
    public const DES_DECRYPT = 'DES_DECRYPT';
    public const DES_ENCRYPT = 'DES_ENCRYPT';
    public const ENCODE = 'ENCODE';
    public const ENCRYPT = 'ENCRYPT';
    public const MD5 = 'MD5';
    public const PASSWORD = 'PASSWORD';
    public const RANDOM_BYTES = 'RANDOM_BYTES';
    public const SHA = 'SHA';
    public const SHA1 = 'SHA1';
    public const SHA2 = 'SHA2';
    public const UNCOMPRESS = 'UNCOMPRESS';
    public const UNCOMPRESSED_LENGTH = 'UNCOMPRESSED_LENGTH';
    public const VALIDATE_PASSWORD_STRENGTH = 'VALIDATE_PASSWORD_STRENGTH';

    // information
    public const BENCHMARK = 'BENCHMARK';
    public const CHARSET = 'CHARSET';
    public const COERCIBILITY = 'COERCIBILITY';
    public const COLLATION = 'COLLATION';
    public const CONNECTION_ID = 'CONNECTION_ID';
    public const CURRENT_ROLE = 'CURRENT_ROLE';
    public const CURRENT_USER = 'CURRENT_USER';
    public const DATABASE = 'DATABASE';
    public const FOUND_ROWS = 'FOUND_ROWS';
    public const LAST_INSERT_ID = 'LAST_INSERT_ID';
    public const ROLES_GRAPHML = 'ROLES_GRAPHML';
    public const ROW_COUNT = 'ROW_COUNT';
    public const SCHEMA = 'SCHEMA';
    public const SESSION_USER = 'SESSION_USER';
    public const SYSTEM_USER = 'SYSTEM_USER';
    public const USER = 'USER';
    public const VERSION = 'VERSION';

    // spatial
    public const Contains = 'Contains'; // removed in 8.0
    public const Disjoint = 'Disjoint'; // removed in 8.0
    public const Equals = 'Equals'; // removed in 8.0
    public const Intersects = 'Intersects'; // removed in 8.0
    public const Overlaps = 'Overlaps'; // removed in 8.0
    public const Within = 'Within'; // removed in 8.0
    public const GLength = 'GLength'; // removed in 8.0
    public const Area = 'Area'; // removed in 8.0
    public const AsBinary = 'AsBinary'; // removed in 8.0
    public const AsText = 'AsText'; // removed in 8.0
    public const AsWKB = 'AsWKB'; // removed in 8.0
    public const AsWKT = 'AsWKT'; // removed in 8.0
    public const Buffer = 'Buffer'; // removed in 8.0
    public const Centroid = 'Centroid'; // removed in 8.0
    public const ConvexHull = 'ConvexHull'; // removed in 8.0
    public const Crosses = 'Crosses'; // removed in 8.0
    public const Dimension = 'Dimension'; // removed in 8.0
    public const Distance = 'Distance'; // removed in 8.0
    public const EndPoint = 'EndPoint'; // removed in 8.0
    public const Envelope = 'Envelope'; // removed in 8.0
    public const ExteriorRing = 'ExteriorRing'; // removed in 8.0
    public const GeomCollFromText = 'GeomCollFromText'; // removed in 8.0
    public const GeomCollFromWKB = 'GeomCollFromWKB'; // removed in 8.0
    public const GeomFromText = 'GeomFromText'; // removed in 8.0
    public const GeomFromWKB = 'GeomFromWKB'; // removed in 8.0
    public const GeometryCollectionFromText = 'GeometryCollectionFromText'; // removed in 8.0
    public const GeometryCollectionFromWKB = 'GeometryCollectionFromWKB'; // removed in 8.0
    public const GeometryFromText = 'GeometryFromText'; // removed in 8.0
    public const GeometryFromWKB = 'GeometryFromWKB'; // removed in 8.0
    public const GeometryN = 'GeometryN'; // removed in 8.0
    public const GeometryType = 'GeometryType'; // removed in 8.0
    public const InteriorRingN = 'InteriorRing'; // removed in 8.0
    public const IsClosed = 'IsClosed'; // removed in 8.0
    public const IsEmpty = 'IsEmpty'; // removed in 8.0
    public const IsSimple = 'IsSimple'; // removed in 8.0
    public const LineFromText = 'LineFromText'; // removed in 8.0
    public const LineFromWKB = 'LineFromWKB'; // removed in 8.0
    public const LineStringFromText = 'LineStringFromText'; // removed in 8.0
    public const LineStringFromWKB = 'LineStringFromWKB'; // removed in 8.0
    public const MLineFromText = 'MLineFromText'; // removed in 8.0
    public const MLineFromWKB = 'MLineFromWKB'; // removed in 8.0
    public const MPointFromText = 'MPointFromText'; // removed in 8.0
    public const MPointFromWKB = 'MPointFromWKB'; // removed in 8.0
    public const MPolyFromText = 'MPolyFromText'; // removed in 8.0
    public const MPolyFromWKB = 'MPolyFromWKB'; // removed in 8.0
    public const MultiLineStringFromText = 'MultilineStringFromText'; // removed in 8.0
    public const MultiLineStringFromWKB = 'MultilineStringFromWKB'; // removed in 8.0
    public const MultiPointFromText = 'MultiPointFromText'; // removed in 8.0
    public const MultiPointFromWKB = 'MultiPointFromWKB'; // removed in 8.0
    public const MultiPolygonFromText = 'MultiPolygonFromText'; // removed in 8.0
    public const MultiPolygonFromWKB = 'MultiPolygonFromWKB'; // removed in 8.0
    public const NumGeometries = 'NumGeometries'; // removed in 8.0
    public const NumInteriorRings = 'NumInteriorRings'; // removed in 8.0
    public const NumPoints = 'NumPoints'; // removed in 8.0
    public const PointFromText = 'PointFromText'; // removed in 8.0
    public const PointFromWKB = 'PointFromWKB'; // removed in 8.0
    public const PointN = 'PointN'; // removed in 8.0
    public const PolyFromText = 'PolyFromText'; // removed in 8.0
    public const PolyFromWKB = 'PolyFromWKB'; // removed in 8.0
    public const PolygonFromText = 'PolygonFromText'; // removed in 8.0
    public const PolygonFromWKB = 'PolygonFromWKB'; // removed in 8.0
    public const SRID = 'SRID'; // removed in 8.0
    public const StartPoint = 'StartPoint'; // removed in 8.0
    public const Touches = 'Touches'; // removed in 8.0
    public const X = 'X'; // removed in 8.0
    public const Y = 'Y'; // removed in 8.0

    public const GeometryCollection = 'GeometryCollection';
    public const LineString = 'LineString';
    public const MBRContains = 'MBRContains';
    public const MBRCoveredBy = 'MBRCoveredBy';
    public const MBRCovers = 'MBRCovers';
    public const MBRDisjoint = 'MBRDisjoint';
    public const MBREquals = 'MBREquals';
    public const MBRIntersects = 'MBRIntersects';
    public const MBROverlaps = 'MBROverlaps';
    public const MBRTouches = 'MBRTouches';
    public const MBRWithin = 'MBRWithin';
    public const MultiLineString = 'MultiLineString';
    public const MultiPoint = 'MultiPoint';
    public const MultiPolygon = 'MultiPolygon';
    public const Point = 'Point';
    public const Polygon = 'Polygon';
    public const ST_Area = 'ST_Area';
    public const ST_AsBinary = 'ST_AsBinary';
    public const ST_AsGeoJSON = 'ST_AsGeoJSON';
    public const ST_AsText = 'ST_AsText';
    public const ST_AsWKT = 'ST_AsWKT';
    public const ST_Buffer = 'ST_Buffer';
    public const ST_Buffer_Strategy = 'ST_Buffer_Strategy';
    public const ST_Centroid = 'ST_Centroid';
    public const ST_Collect = 'ST_Collect';
    public const ST_Contains = 'ST_Contains';
    public const ST_ConvexHull = 'ST_ConvexHull';
    public const ST_Crosses = 'ST_Crosses';
    public const ST_Difference = 'ST_Difference';
    public const ST_Dimension = 'ST_Dimension';
    public const ST_Disjoint = 'ST_Disjoint';
    public const ST_Distance = 'ST_Distance';
    public const ST_Distance_Sphere = 'ST_Distance_Sphere';
    public const ST_EndPoint = 'ST_EndPoint';
    public const ST_Envelope = 'ST_Envelope';
    public const ST_Equals = 'ST_Equals';
    public const ST_ExteriorRing = 'ST_ExteriorRing';
    public const ST_FrechetDistance = 'ST_FrechetDistance';
    public const ST_GeoHash = 'ST_GeoHash';
    public const ST_GeomCollFromText = 'ST_GeomCollFromText';
    public const ST_GeometryCollectionFromText = 'ST_GeometryCollectionFromText';
    public const ST_GeomCollFromTxt = 'ST_GeomCollFromTxt';
    public const ST_GeomCollFromWKB = 'ST_GeomCollFromWKB';
    public const ST_GeometryCollectionFromWKB = 'ST_GeometryCollectionFromWKB';
    public const ST_GeometryN = 'ST_GeometryN';
    public const ST_GeometryType = 'ST_GeometryType';
    public const ST_GeomFromGeoJSON = 'ST_GeomFromGeoJSON';
    public const ST_GeomFromText = 'ST_GeomFromText';
    public const ST_GeometryFromText = 'ST_GeometryFromText';
    public const ST_GeomFromWKB = 'ST_GeomFromWKB';
    public const ST_GeometryFromWKB = 'ST_GeometryFromWKB';
    public const ST_HausdorffDistance = 'ST_HausdorffDistance';
    public const ST_InteriorRingN = 'ST_InteriorRingN';
    public const ST_Intersection = 'ST_Intersection';
    public const ST_Intersects = 'ST_Intersects';
    public const ST_IsClosed = 'ST_IsClosed';
    public const ST_IsEmpty = 'ST_IsEmpty';
    public const ST_IsSimple = 'ST_IsSimple';
    public const ST_IsValid = 'ST_IsValid';
    public const ST_LatFromGeoHash = 'ST_LatFromGeoHash';
    public const ST_Latitude = 'ST_Latitude';
    public const ST_Length = 'ST_Length';
    public const ST_LineFromText = 'ST_LineFromText';
    public const ST_LineInterpolatePoint = 'ST_LineInterpolatePoint';
    public const ST_LineInterpolatePoints = 'ST_LineInterpolatePoints';
    public const ST_LineStringFromText = 'ST_LineStringFromText';
    public const ST_LineFromWKB = 'ST_LineFromWKB';
    public const ST_LineStringFromWKB = 'ST_LineStringFromWKB';
    public const ST_LongFromGeoHash = 'ST_LongFromGeoHash';
    public const ST_Longitude = 'ST_Longitude';
    public const ST_MakeEnvelope = 'ST_MakeEnvelope';
    public const ST_MLineFromText = 'ST_MLineFromText';
    public const ST_MultiLineStringFromText = 'ST_MultiLineStringFromText';
    public const ST_MLineFromWKB = 'ST_MLineFromWKB';
    public const ST_MultiLineStringFromWKB = 'ST_MultiLineStringFromWKB';
    public const ST_MPointFromText = 'ST_MPointFromText';
    public const ST_MultiPointFromText = 'ST_MultiPointFromText';
    public const ST_MPointFromWKB = 'ST_MPointFromWKB';
    public const ST_MultiPointFromWKB = 'ST_MultiPointFromWKB';
    public const ST_MPolyFromText = 'ST_MPolyFromText';
    public const ST_MultiPolygonFromText = 'ST_MultiPolygonFromText';
    public const ST_MPolyFromWKB = 'ST_MPolyFromWKB';
    public const ST_MultiPolygonFromWKB = 'ST_MultiPolygonFromWKB';
    public const ST_NumGeometries = 'ST_NumGeometries';
    public const ST_NumInteriorRing = 'ST_NumInteriorRing';
    public const ST_NumInteriorRings = 'ST_NumInteriorRings';
    public const ST_NumPoints = 'ST_NumPoints';
    public const ST_Overlaps = 'ST_Overlaps';
    public const ST_PointAtDistance = 'ST_PointAtDistance';
    public const ST_PointFromGeoHash = 'ST_PointFromGeoHash';
    public const ST_PointFromText = 'ST_PointFromText';
    public const ST_PointFromWKB = 'ST_PointFromWKB';
    public const ST_PointN = 'ST_PointN';
    public const ST_PolyFromText = 'ST_PolyFromText';
    public const ST_PolygonFromText = 'ST_PolygonFromText';
    public const ST_PolyFromWKB = 'ST_PolyFromWKB';
    public const ST_PolygonFromWKB = 'ST_PolygonFromWKB';
    public const ST_Simplify = 'ST_Simplify';
    public const ST_SRID = 'ST_SRID';
    public const ST_StartPoint = 'ST_StartPoint';
    public const ST_SwapXY = 'ST_SwapXY';
    public const ST_SymDifference = 'ST_SymDifference';
    public const ST_Touches = 'ST_Touches';
    public const ST_Transform = 'ST_Transform';
    public const ST_Union = 'ST_Union';
    public const ST_Validate = 'ST_Validate';
    public const ST_Within = 'ST_Within';
    public const ST_X = 'ST_X';
    public const ST_Y = 'ST_Y';

    // JSON
    public const JSON_APPEND = 'JSON_APPEND';
    public const JSON_ARRAY = 'JSON_ARRAY';
    public const JSON_ARRAY_APPEND = 'JSON_ARRAY_APPEND';
    public const JSON_ARRAY_INSERT = 'JSON_ARRAY_INSERT';
    public const JSON_CONTAINS = 'JSON_CONTAINS';
    public const JSON_CONTAINS_PATH = 'JSON_CONTAINS_PATH';
    public const JSON_DEPTH = 'JSON_DEPTH';
    public const JSON_EXTRACT = 'JSON_EXTRACT';
    public const JSON_INSERT = 'JSON_INSERT';
    public const JSON_KEYS = 'JSON_KEYS';
    public const JSON_LENGTH = 'JSON_LENGTH';
    public const JSON_MERGE = 'JSON_MERGE';
    public const JSON_MERGE_PATCH = 'JSON_MERGE_PATCH';
    public const JSON_MERGE_PRESERVE = 'JSON_MERGE_PRESERVE';
    public const JSON_OBJECT = 'JSON_OBJECT';
    public const JSON_OVERLAPS = 'JSON_OVERLAPS';
    public const JSON_PRETTY = 'JSON_PRETTY';
    public const JSON_QUOTE = 'JSON_QUOTE';
    public const JSON_REMOVE = 'JSON_REMOVE';
    public const JSON_REPLACE = 'JSON_REPLACE';
    public const JSON_SCHEMA_VALID = 'JSON_SCHEMA_VALID';
    public const JSON_SCHEMA_VALIDATION_REPORT = 'JSON_SCHEMA_VALIDATION_REPORT';
    public const JSON_SEARCH = 'JSON_SEARCH';
    public const JSON_SET = 'JSON_SET';
    public const JSON_STORAGE_FREE = 'JSON_STORAGE_FREE';
    public const JSON_STORAGE_SIZE = 'JSON_STORAGE_SIZE';
    public const JSON_TABLE = 'JSON_TABLE';
    public const JSON_TYPE = 'JSON_TYPE';
    public const JSON_UNQUOTE = 'JSON_UNQUOTE';
    public const JSON_VALID = 'JSON_VALID';
    public const JSON_VALUE = 'JSON_VALUE';

    // GTID
    public const GTID_SUBSET = 'GTID_SUBSET';
    public const GTID_SUBTRACT = 'GTID_SUBTRACT';
    public const WAIT_FOR_EXECUTED_GTID_SET = 'WAIT_FOR_EXECUTED_GTID_SET';
    public const WAIT_UNTIL_SQL_THREAD_AFTER_GTIDS = 'WAIT_UNTIL_SQL_THREAD_AFTER_GTIDS';

    // performance_schema
    public const FORMAT_BYTES = 'FORMAT_BYTES';
    public const FORMAT_PICO_TIME = 'FORMAT_PICO_TIME';
    public const PS_CURRENT_THREAD_ID = 'PS_CURRENT_THREAD_ID';
    public const PS_THREAD_ID = 'PS_THREAD_ID';

    // misc
    //public const PROCEDURE_ANALYSE = 'PROCEDURE ANALYSE'; // removed in 8.0; looks more like a construct than a function
    public const BIN_TO_UUID = 'BIN_TO_UUID';
    public const DEFAULT = 'DEFAULT';
    public const GET_LOCK = 'GET_LOCK';
    public const GROUPING = 'GROUPING';
    public const ICU_VERSION = 'ICU_VERSION';
    public const INET_ATON = 'INET_ATON';
    public const INET_NTOA = 'INET_NTOA';
    public const INET6_ATON = 'INET6_ATON';
    public const INET6_NTOA = 'INET6_NTOA';
    public const IS_FREE_LOCK = 'IS_FREE_LOCK';
    public const IS_IPV4 = 'IS_IPV4';
    public const IS_IPV4_COMPAT = 'IS_IPV4_COMPAT';
    public const IS_IPV4_MAPPED = 'IS_IPV4_MAPPED';
    public const IS_IPV6 = 'IS_IPV6';
    public const IS_USED_LOCK = 'IS_USED_LOCK';
    public const IS_UUID = 'IS_UUID';
    public const MASTER_POS_WAIT = 'MASTER_POS_WAIT';
    public const NAME_CONST = 'NAME_CONST';
    public const RELEASE_ALL_LOCKS = 'RELEASE_ALL_LOCKS';
    public const RELEASE_LOCK = 'RELEASE_LOCK';
    public const SLEEP = 'SLEEP';
    public const SOURCE_POS_WAIT = 'SOURCE_POS_WAIT';
    public const STATEMENT_DIGEST = 'STATEMENT_DIGEST';
    public const STATEMENT_DIGEST_NEXT = 'STATEMENT_DIGEST_NEXT';
    public const UUID = 'UUID';
    public const UUID_SHORT = 'UUID_SHORT';
    public const UUID_TO_BIN = 'UUID_TO_BIN';
    public const VALUES = 'VALUES';

    // aggregate functions
    public const ANY_VALUE = 'ANY_VALUE';
    public const AVG = 'AVG';
    public const BIT_AND = 'BIT_AND';
    public const BIT_OR = 'BIT_OR';
    public const BIT_XOR = 'BIT_XOR';
    public const COUNT = 'COUNT';
    public const COUNT_DISTINCT = 'COUNT_DISTINCT';
    public const GROUP_CONCAT = 'GROUP_CONCAT';
    public const JSON_ARRAYAGG = 'JSON_ARRAYAGG';
    public const JSON_OBJECTAGG = 'JSON_OBJECTAGG';
    public const MAX = 'MAX';
    public const MIN = 'MIN';
    public const STD = 'STD';
    public const STDDEV = 'STD';
    public const STDDEV_POP = 'STDDEV_POP';
    public const STDDEV_SAMP = 'STDDEV_SAMP';
    public const SUM = 'SUM';
    public const VAR_POP = 'VAR_POP';
    public const VAR_SAMP = 'VAR_SAMP';
    public const VARIANCE = 'VARIANCE';

    // window functions
    public const CUME_DIST = 'CUME_DIST';
    public const DENSE_RANK = 'DENSE_RANK';
    public const FIRST_VALUE = 'FIRST_VALUE';
    public const LAG = 'LAG';
    public const LAST_VALUE = 'LAST_VALUE';
    public const LEAD = 'LEAD';
    public const NTH_VALUE = 'NTH_VALUE';
    public const NTILE = 'NTILE';
    public const PERCENT_RANK = 'PERCENT_RANK';
    public const RANK = 'RANK';
    public const ROW_NUMBER = 'ROW_NUMBER';

    /** @var list<string> */
    private static array $aggregate = [
        self::ANY_VALUE,
        self::AVG,
        self::BIT_AND,
        self::BIT_OR,
        self::BIT_XOR,
        self::COUNT,
        self::COUNT_DISTINCT,
        self::GROUP_CONCAT,
        self::JSON_ARRAYAGG,
        self::JSON_OBJECTAGG,
        self::MAX,
        self::MIN,
        self::ST_Collect,
        self::STD,
        self::STDDEV,
        self::STDDEV_POP,
        self::STDDEV_SAMP,
        self::SUM,
        self::VAR_POP,
        self::VAR_SAMP,
        self::VARIANCE,
    ];

    /** @var list<string> */
    private static array $window = [
        self::CUME_DIST,
        self::DENSE_RANK,
        self::FIRST_VALUE,
        self::LAG,
        self::LAST_VALUE,
        self::LEAD,
        self::NTH_VALUE,
        self::NTILE,
        self::PERCENT_RANK,
        self::RANK,
        self::ROW_NUMBER,
    ];

    /** @var list<string> */
    private static array $nullTreatment = [
        self::FIRST_VALUE,
        self::LAG,
        self::LAST_VALUE,
        self::LEAD,
        self::NTH_VALUE,
    ];

    /** @var list<string> */
    private static array $bare = [
        self::CURRENT_TIME,
        self::CURRENT_DATE,
        self::CURRENT_TIMESTAMP,
        self::CURRENT_USER,
        self::LOCALTIME,
        self::LOCALTIMESTAMP,
        self::UTC_DATE,
        self::UTC_TIME,
        self::UTC_TIMESTAMP,
    ];

    /** @var list<string> */
    private static array $timeProviders = [
        self::CURDATE,
        self::CURRENT_TIME,
        self::CURRENT_DATE,
        self::CURRENT_TIMESTAMP,
        self::CURTIME,
        self::LOCALTIME,
        self::LOCALTIMESTAMP,
        self::NOW,
        self::SYSDATE,
        self::UNIX_TIMESTAMP,
        self::UTC_DATE,
        self::UTC_TIME,
        self::UTC_TIMESTAMP,
    ];

    /**
     * In most cases name is parsed as array key of the following argument - e.g. AVG(DISTINCT col1) -> ['DISTINCT' => 'col1']
     * Special cases:
     * - when no value follows the keyword (indicated by null), the keyword is parsed as a Literal - e.g. GET_FORMAT(DATE ISO) -> [new Literal('DATE'), 'ISO']
     * - parameters of some functions need special parsing (indicated by false) - CONVERT(), JSON_TABLE(), TRIM()
     *
     * @var array<self::*, array<Keyword::*|'AT TIME ZONE'|'ORDER BY'|'ON EMPTY'|'ON ERROR', class-string|string>>
     */
    private static array $namedParams = [
        // AVG([DISTINCT | ALL] expr)
        self::AVG => [Keyword::DISTINCT => RootNode::class, Keyword::ALL => RootNode::class],
        // BIT_OR([ALL] expr)
        self::BIT_OR => [Keyword::ALL => RootNode::class],
        // BIT_AND([ALL] expr)
        self::BIT_AND => [Keyword::ALL => RootNode::class],
        // CHAR(N, ... [USING charset_name])
        self::CHAR => [Keyword::USING => Charset::class],
        // CAST(expr AS type)
        // CAST(timestamp_value AT TIME ZONE timezone_specifier AS DATETIME[(precision)])
        self::CAST => [Keyword::AT . ' ' . Keyword::TIME . ' ' . Keyword::ZONE => TimeZone::class, Keyword::AS => CastType::class],
        // CONVERT(string, type), CONVERT(expr USING charset_name)
        // has special handling because of irregular syntax
        self::CONVERT => [Keyword::USING => Charset::class/*, 1 => CastType::class*/],
        // COUNT([DISTINCT | ALL] expr,[expr...])
        self::COUNT => [Keyword::DISTINCT => RootNode::class, Keyword::ALL => RootNode::class],
        // EXTRACT(unit FROM date)
        self::EXTRACT => [
            Keyword::MICROSECOND => EnumValueLiteral::class,
            Keyword::SECOND => EnumValueLiteral::class,
            Keyword::MINUTE => EnumValueLiteral::class,
            Keyword::HOUR => EnumValueLiteral::class,
            Keyword::DAY => EnumValueLiteral::class,
            Keyword::WEEK => EnumValueLiteral::class,
            Keyword::MONTH => EnumValueLiteral::class,
            Keyword::QUARTER => EnumValueLiteral::class,
            Keyword::YEAR => EnumValueLiteral::class,
            Keyword::SECOND_MICROSECOND => EnumValueLiteral::class,
            Keyword::MINUTE_SECOND => EnumValueLiteral::class,
            Keyword::HOUR_MINUTE => EnumValueLiteral::class,
            Keyword::DAY_HOUR => EnumValueLiteral::class,
            Keyword::YEAR_MONTH => EnumValueLiteral::class,
            Keyword::MINUTE_MICROSECOND => EnumValueLiteral::class,
            Keyword::HOUR_SECOND => EnumValueLiteral::class,
            Keyword::DAY_MINUTE => EnumValueLiteral::class,
            Keyword::HOUR_MICROSECOND => EnumValueLiteral::class,
            Keyword::DAY_SECOND => EnumValueLiteral::class,
            Keyword::DAY_MICROSECOND => EnumValueLiteral::class,
            Keyword::FROM => RootNode::class,
        ],
        // GET_FORMAT({DATE|TIME|DATETIME}, {'EUR'|'USA'|'JIS'|'ISO'|'INTERNAL'})
        self::GET_FORMAT => [Keyword::DATE => TimeTypeLiteral::class, Keyword::TIME => TimeTypeLiteral::class, Keyword::DATETIME => TimeTypeLiteral::class],
        // GROUP_CONCAT([DISTINCT] expr [,expr ...] [ORDER BY {unsigned_integer | col_name | expr} [ASC | DESC] [,col_name ...]] [SEPARATOR str_val])
        self::GROUP_CONCAT => [Keyword::DISTINCT => RootNode::class, Keyword::ORDER . ' ' . Keyword::BY => OrderByListExpression::class, Keyword::SEPARATOR => Literal::class,],
        // JSON_TABLE(expr, path COLUMNS (column_list) [AS] alias)
        // has special handling because of irregular syntax
        self::JSON_TABLE => [Keyword::COLUMNS => '*', Keyword::AS => '*'],
        // JSON_VALUE(json_doc, path [RETURNING type] [on_empty] [on_error])
        self::JSON_VALUE => [Keyword::RETURNING => CastType::class, Keyword::ON . ' ' . Keyword::EMPTY => '*', Keyword::ON . ' ' . Keyword::ERROR => '*'],
        // MAX([DISTINCT | ALL] expr)
        self::MAX => [Keyword::DISTINCT => RootNode::class, Keyword::ALL => RootNode::class],
        // MID(str,pos), MID(str FROM pos), MID(str,pos,len), MID(str FROM pos FOR len)
        self::MID => [Keyword::FROM => RootNode::class, Keyword::FOR => RootNode::class],
        // MIN([DISTINCT | ALL] expr)
        self::MIN => [Keyword::DISTINCT => RootNode::class, Keyword::ALL => RootNode::class],
        // POSITION(substr IN str)
        self::POSITION => [Keyword::IN => RootNode::class],
        // ST_COLLECT(DISTINCT location)
        self::ST_Collect => [Keyword::DISTINCT => RootNode::class],
        // STD([ALL] expr)
        self::STD => [Keyword::ALL => RootNode::class],
        // SUBSTR(str,pos), SUBSTR(str FROM pos), SUBSTR(str,pos,len), SUBSTR(str FROM pos FOR len)
        self::SUBSTR => [Keyword::FROM => RootNode::class, Keyword::FOR => RootNode::class],
        // SUBSTRING(str,pos), SUBSTRING(str FROM pos), SUBSTRING(str,pos,len), SUBSTRING(str FROM pos FOR len)
        self::SUBSTRING => [Keyword::FROM => RootNode::class, Keyword::FOR => RootNode::class],
        // SUM([DISTINCT | ALL] expr)
        self::SUM => [Keyword::DISTINCT => RootNode::class, Keyword::ALL => RootNode::class],
        // TRIM([{BOTH | LEADING | TRAILING} [remstr] FROM] str), TRIM([remstr FROM] str)
        // has special handling because of the suffix FROM
        self::TRIM => [Keyword::BOTH => '*', Keyword::LEADING => '*', Keyword::TRAILING => '*', Keyword::FROM => '*'],
        // VARIANCE([ALL] expr)
        self::VARIANCE => [Keyword::ALL => RootNode::class],
        // WEIGHT_STRING(str [AS {CHAR|BINARY}(N)] [LEVEL ...] [flags]) -- "The flags clause currently is unused."
        self::WEIGHT_STRING => [Keyword::AS => CastType::class, Keyword::LEVEL => 'SKIP'],
    ];

    public function isSimple(): bool
    {
        return !in_array($this->getValue(), self::$window, true) && !in_array($this->getValue(), self::$aggregate, true);
    }

    public function isAggregate(): bool
    {
        return in_array($this->getValue(), self::$aggregate, true);
    }

    public function isWindow(): bool
    {
        $name = $this->getValue();

        return in_array($name, self::$window, true)
            || (in_array($name, self::$aggregate, true) && $name !== self::GROUP_CONCAT);
    }

    public function hasNullTreatment(): bool
    {
        return in_array($this->getValue(), self::$nullTreatment, true);
    }

    public function hasFromFirstLast(): bool
    {
        return $this->getValue() === self::NTH_VALUE;
    }

    public function isBare(): bool
    {
        return in_array($this->getValue(), self::$bare, true);
    }

    public static function isBareName(string $name): bool
    {
        $name = strtoupper($name);

        return in_array($name, self::$bare, true);
    }

    /**
     * @return list<string>
     */
    public static function getTimeProviders(): array
    {
        return self::$timeProviders;
    }

    /**
     * @return array<Keyword::*|'AT TIME ZONE'|'ORDER BY'|'ON EMPTY'|'ON ERROR', class-string|string>
     */
    public function getNamedParams(): array
    {
        return self::$namedParams[$this->getValue()] ?? [];
    }

    public function hasNamedParams(): bool
    {
        return isset(self::$namedParams[$this->getValue()]);
    }

    public function getName(): string
    {
        return $this->getValue();
    }

    public function getFullName(): string
    {
        return $this->getValue();
    }

}
