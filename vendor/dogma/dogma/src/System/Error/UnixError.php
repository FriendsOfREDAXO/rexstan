<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: PROG prog MULTIHOP

namespace Dogma\System\Error;

use Dogma\Enum\IntEnum;
use function str_replace;
use function strtolower;
use function ucfirst;

/**
 * UNIX system errors (from FreeBSD)
 */
class UnixError extends IntEnum implements SystemError
{

    // common with Linux:
    public const SUCCESS = 0;
    public const OPERATION_NOT_PERMITTED = 1;
    public const NO_SUCH_FILE_OR_DIRECTORY = 2;
    public const NO_SUCH_PROCESS = 3;
    public const INTERRUPTED_SYSTEM_CALL = 4;
    public const IO_ERROR = 5;
    public const NO_SUCH_DEVICE_OR_ADDRESS = 6;
    public const ARGUMENT_LIST_TOO_LONG = 7;
    public const EXEC_FORMAT_ERROR = 8;
    public const BAD_FILE_NUMBER = 9;
    public const NO_CHILD_PROCESSES = 10;
    public const TRY_AGAIN = 11;
    public const OUT_OF_MEMORY = 12;
    public const PERMISSION_DENIED = 13;
    public const BAD_ADDRESS = 14;
    public const BLOCK_DEVICE_REQUIRED = 15;
    public const DEVICE_OR_RESOURCE_BUSY = 16;
    public const FILE_EXISTS = 17;
    public const CROSS_DEVICE_LINK = 18;
    public const NO_SUCH_DEVICE = 19;
    public const NOT_A_DIRECTORY = 20;
    public const IS_A_DIRECTORY = 21;
    public const INVALID_ARGUMENT = 22;
    public const FILE_TABLE_OVERFLOW = 23;
    public const TOO_MANY_OPEN_FILES = 24;
    public const NOT_A_TYPEWRITER = 25;
    public const TEXT_FILE_BUSY = 26;
    public const FILE_TOO_LARGE = 27;
    public const NO_SPACE_LEFT_ON_DEVICE = 28;
    public const ILLEGAL_SEEK = 29;
    public const READONLY_FILE_SYSTEM = 30;
    public const TOO_MANY_LINKS = 31;
    public const BROKEN_PIPE = 32;
    public const NUMERICAL_ARGUMENT_OUT_OF_DOMAIN = 33;
    public const RESULT_TOO_LARGE = 34;
    public const RESOURCE_TEMPORARILY_UNAVAILABLE = 35;

    // differs from Linux;
    public const OPERATION_NOW_IN_PROGRESS = 36;
    public const OPERATION_ALREADY_IN_PROGRESS = 37;
    public const SOCKET_OPERATION_ON_NON_SOCKET = 38;
    public const DESTINATION_ADDRESS_REQUIRED = 39;
    public const MESSAGE_TOO_LONG = 40;
    public const PROTOCOL_WRONG_TYPE_FOR_SOCKET = 41;
    public const PROTOCOL_NOT_AVAILABLE = 42;
    public const PROTOCOL_NOT_SUPPORTED = 43;
    public const SOCKET_TYPE_NOT_SUPPORTED = 44;
    public const OPERATION_NOT_SUPPORTED = 45;
    public const PROTOCOL_FAMILY_NOT_SUPPORTED = 46;
    public const ADDRESS_FAMILY_NOT_SUPPORTED_BY_PROTOCOL_FAMILY = 47;
    public const ADDRESS_ALREADY_IN_USE = 48;
    public const CANT_ASSIGN_REQUESTED_ADDRESS = 49;
    public const NETWORK_IS_DOWN = 50;
    public const NETWORK_IS_UNREACHABLE = 51;
    public const NETWORK_DROPPED_CONNECTION_ON_RESET = 52;
    public const SOFTWARE_CAUSED_CONNECTION_ABORT = 53;
    public const CONNECTION_RESET_BY_PEER = 54;
    public const NO_BUFFER_SPACE_AVAILABLE = 55;
    public const SOCKET_IS_ALREADY_CONNECTED = 56;
    public const SOCKET_IS_NOT_CONNECTED = 57;
    public const CANT_SEND_AFTER_SOCKET_SHUTDOWN = 58;
    public const TOO_MANY_REFERENCES_CANT_SPLICE = 59;
    public const OPERATION_TIMED_OUT = 60;
    public const CONNECTION_REFUSED = 61;
    public const TOO_MANY_LEVELS_OF_SYMBOLIC_LINKS = 62;
    public const FILE_NAME_TOO_LONG = 63;
    public const HOST_IS_DOWN = 64;
    public const NO_ROUTE_TO_HOST = 65;
    public const DIRECTORY_NOT_EMPTY = 66;
    public const TOO_MANY_PROCESSES = 67;
    public const TOO_MANY_USERS = 68;
    public const DISC_QUOTA_EXCEEDED = 69;
    public const STALE_NFS_FILE_HANDLE = 70;
    public const TOO_MANY_LEVELS_OF_REMOTE_IN_PATH = 71;
    public const RPC_STRUCT_IS_BAD = 72;
    public const RPC_VERSION_WRONG = 73;
    public const RPC_PROG_NOT_AVAIL = 74;
    public const PROGRAM_VERSION_WRONG = 75;
    public const BAD_PROCEDURE_FOR_PROGRAM = 76;
    public const NO_LOCKS_AVAILABLE = 77;
    public const FUNCTION_NOT_IMPLEMENTED = 78;
    public const INAPPROPRIATE_FILE_TYPE_OR_FORMAT = 79;
    public const AUTHENTICATION_ERROR = 80;
    public const NEED_AUTHENTICATOR = 81;
    public const IDENTIFIER_REMOVED = 82;
    public const NO_MESSAGE_OF_DESIRED_TYPE = 83;
    public const VALUE_TOO_LARGE_TO_BE_STORED_IN_DATA_TYPE = 84;
    public const OPERATION_CANCELED = 85;
    public const ILLEGAL_BYTE_SEQUENCE = 86;
    public const ATTRIBUTE_NOT_FOUND = 87;
    public const PROGRAMMING_ERROR = 88;
    public const BAD_MESSAGE = 89;
    public const MULTIHOP_ATTEMPTED = 90;
    public const LINK_HAS_BEEN_SEVERED = 91;
    public const PROTOCOL_ERROR = 92;
    public const CAPABILITIES_INSUFFICIENT = 93;
    public const NOT_PERMITTED_IN_CAPABILITY_MODE = 94;
    public const MUST_BE_EQUAL_LARGEST_ERRNO = 94;

    public function getDescription(): string
    {
        return ucfirst(str_replace(
            ['non_socket', 'cant', 'references', 'rpc', 'nfs', 'prog_', '_'],
            ['non-socket', 'can\'t', 'references:', 'RPC', 'NFS', 'prog. ', ' '],
            strtolower($this->getConstantName())
        ));
    }

}
