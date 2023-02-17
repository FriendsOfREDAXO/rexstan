<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: DATALINK FDW HV HW OLB UNHANDLED XQUERY

namespace SqlFtw\Sql\Dml\Error;

use SqlFtw\Sql\SqlEnum;

class SqlStateClass extends SqlEnum
{

    public const CUSTOM = '';

    public const C_00_SUCCESS = '00';
    public const C_01_WARNING = '01';
    public const C_02_NO_DATA = '02';
    public const C_07_SQL_ERROR = '07';
    public const C_08_CONNECTION_EXCEPTION = '08';
    public const C_09_TRIGGERED_ACTION_EXCEPTION = '09';
    public const C_0A_FEATURE_NOT_SUPPORTED = '0A';
    public const C_0D_INVALID_TARGET_TYPE_SPECIFICATION = '0D';
    public const C_0E_INVALID_SCHEMA_NAME_LIST_SPECIFICATION = '0E';
    public const C_0F_LOCATOR_EXCEPTION = '0F';
    public const C_0K_RESIGNAL_WHEN_HANDLER_NOT_ACTIVE = '0K';
    public const C_0L_INVALID_GRANTOR = '0L';
    public const C_0M_INVALID_SQL_INVOKED_PROCEDURE_REFERENCE = '0M';
    public const C_0N_SQL_XML_MAPPING_ERROR = '0N';
    public const C_0P_INVALID_ROLE_SPECIFICATION = '0P';
    public const C_0S_INVALID_TRANSFORM_GROUP_NAME_SPECIFICATION = '0S';
    public const C_0T_TARGET_TABLE_DISAGREES_WITH_CURSOR_SPECIFICATION = '0T';
    public const C_0U_ATTEMPT_TO_ASSIGN_TO_NON_UPDATABLE_COLUMN = '0U';
    public const C_0V_ATTEMPT_TO_ASSIGN_TO_ORDERING_COLUMN = '0V';
    public const C_0W_PROHIBITED_STATEMENT_ENCOUNTERED_DURING_TRIGGER_EXECUTION = '0W';
    public const C_0X_INVALID_FOREIGN_SERVER_SPECIFICATION = '0X';
    public const C_0Y_PASS_THROUGH_SPECIFIC_CONDITION = '0Y';
    public const C_0Z_DIAGNOSTICS_EXCEPTION = '0Z';
    public const C_10_XQUERY_ERROR = '10';
    public const C_20_CASE_NOT_FOUND_FOR_CASE_STATEMENT = '20';
    public const C_21_CARDINALITY_VIOLATION = '21';
    public const C_22_DATA_EXCEPTION = '22';
    public const C_23_INTEGRITY_CONSTRAINT_VIOLATION = '23';
    public const C_24_INVALID_CURSOR_STATE = '24';
    public const C_25_INVALID_TRANSACTION_STATE = '25';
    public const C_26_INVALID_SQL_STATEMENT_NAME = '26';
    public const C_27_TRIGGERED_DATA_CHANGE_VIOLATION = '27';
    public const C_28_INVALID_AUTHORIZATION_SPECIFICATION = '28';
    public const C_2B_DEPENDENT_PRIVILEGE_DESCRIPTORS_STILL_EXIST = '2B';
    public const C_2C_INVALID_CHARACTER_SET_NAME = '2C';
    public const C_2D_INVALID_TRANSACTION_TERMINATION = '2D';
    public const C_2E_INVALID_CONNECTION_NAME = '2E';
    public const C_2F_SQL_ROUTINE_EXCEPTION = '2F';
    public const C_2H_INVALID_COLLATION_NAME = '2H';
    public const C_30_INVALID_SQL_STATEMENT_IDENTIFIER = '30';
    public const C_33_INVALID_SQL_DESCRIPTOR_NAME = '33';
    public const C_34_INVALID_CURSOR_NAME = '34';
    public const C_35_INVALID_CONDITION_NUMBER = '35';
    public const C_36_CURSOR_SENSITIVITY_EXCEPTION = '36';
    public const C_38_EXTERNAL_ROUTINE_EXCEPTION = '38';
    public const C_39_EXTERNAL_ROUTINE_INVOCATION_EXCEPTION = '39';
    public const C_3B_SAVEPOINT_EXCEPTION = '3B';
    public const C_3C_AMBIGUOUS_CURSOR_NAME = '3C';
    public const C_3D_INVALID_CATALOG_NAME = '3D';
    public const C_3F_INVALID_SCHEMA_NAME = '3F';
    public const C_40_TRANSACTION_ROLLBACK = '40';
    public const C_41_SERIALIZATION_FAILURE = '41';
    public const C_42_INTEGRITY_CONSTRAINT_VIOLATION = '42';
    public const C_43_STATEMENT_COMPLETION_UNKNOWN = '43';
    public const C_44_TRIGGERED_ACTION_EXCEPTION = '44';
    public const C_42_SYNTAX_ERROR_OR_ACCESS_RULE_VIOLATION = '42';
    public const C_44_WITH_CHECK_OPTION_VIOLATION = '44';
    public const C_45_UNHANDLED_USER_DEFINED_EXCEPTION = '45';
    public const C_46_OLB_SPECIFIC_ERROR = '46';
    public const C_HW_DATALINK_EXCEPTION = 'HW';
    public const C_HV_FDW_SPECIFIC_CONDITION = 'HV';
    public const C_HY_CLI_SPECIFIC_CONDITION = 'HY';

    public function getCategory(): SqlStateCategory
    {
        $value = $this->getValue();
        $category = $value === self::C_00_SUCCESS
            ? SqlStateCategory::SUCCESS
            : ($value === self::C_01_WARNING
                ? SqlStateCategory::WARNING
                : ($value === self::C_02_NO_DATA
                    ? SqlStateCategory::NO_DATA
                    : SqlStateCategory::ERROR
                )
            );

        return new SqlStateCategory($category);
    }

}
