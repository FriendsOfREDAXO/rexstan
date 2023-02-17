<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: CSI SRMOUNT MULTIHOP RFS csi rfs

namespace Dogma\System\Error;

use Dogma\Enum\IntEnum;
use function str_replace;
use function strtolower;
use function ucfirst;

/**
 * Linux system errors
 */
class LinuxError extends IntEnum implements SystemError
{

    // common with Unix:
    public const SUCCESS = UnixError::SUCCESS;
    public const OPERATION_NOT_PERMITTED = UnixError::OPERATION_NOT_PERMITTED;
    public const NO_SUCH_FILE_OR_DIRECTORY = UnixError::NO_SUCH_FILE_OR_DIRECTORY;
    public const NO_SUCH_PROCESS = UnixError::NO_SUCH_PROCESS;
    public const INTERRUPTED_SYSTEM_CALL = UnixError::INTERRUPTED_SYSTEM_CALL;
    public const IO_ERROR = UnixError::IO_ERROR;
    public const NO_SUCH_DEVICE_OR_ADDRESS = UnixError::NO_SUCH_DEVICE_OR_ADDRESS;
    public const ARGUMENT_LIST_TOO_LONG = UnixError::ARGUMENT_LIST_TOO_LONG;
    public const EXEC_FORMAT_ERROR = UnixError::EXEC_FORMAT_ERROR;
    public const BAD_FILE_NUMBER = UnixError::BAD_FILE_NUMBER;
    public const NO_CHILD_PROCESSES = UnixError::NO_CHILD_PROCESSES;
    public const TRY_AGAIN = UnixError::TRY_AGAIN;
    public const OUT_OF_MEMORY = UnixError::OUT_OF_MEMORY;
    public const PERMISSION_DENIED = UnixError::PERMISSION_DENIED;
    public const BAD_ADDRESS = UnixError::BAD_ADDRESS;
    public const BLOCK_DEVICE_REQUIRED = UnixError::BLOCK_DEVICE_REQUIRED;
    public const DEVICE_OR_RESOURCE_BUSY = UnixError::DEVICE_OR_RESOURCE_BUSY;
    public const FILE_EXISTS = UnixError::FILE_EXISTS;
    public const CROSS_DEVICE_LINK = UnixError::CROSS_DEVICE_LINK;
    public const NO_SUCH_DEVICE = UnixError::NO_SUCH_DEVICE;
    public const NOT_A_DIRECTORY = UnixError::NOT_A_DIRECTORY;
    public const IS_A_DIRECTORY = UnixError::IS_A_DIRECTORY;
    public const INVALID_ARGUMENT = UnixError::INVALID_ARGUMENT;
    public const FILE_TABLE_OVERFLOW = UnixError::FILE_TABLE_OVERFLOW;
    public const TOO_MANY_OPEN_FILES = UnixError::TOO_MANY_OPEN_FILES;
    public const NOT_A_TYPEWRITER = UnixError::NOT_A_TYPEWRITER;
    public const TEXT_FILE_BUSY = UnixError::TEXT_FILE_BUSY;
    public const FILE_TOO_LARGE = UnixError::FILE_TOO_LARGE;
    public const NO_SPACE_LEFT_ON_DEVICE = UnixError::NO_SPACE_LEFT_ON_DEVICE;
    public const ILLEGAL_SEEK = UnixError::ILLEGAL_SEEK;
    public const READONLY_FILE_SYSTEM = UnixError::READONLY_FILE_SYSTEM;
    public const TOO_MANY_LINKS = UnixError::TOO_MANY_LINKS;
    public const BROKEN_PIPE = UnixError::BROKEN_PIPE;
    public const NUMERICAL_ARGUMENT_OUT_OF_DOMAIN = UnixError::NUMERICAL_ARGUMENT_OUT_OF_DOMAIN;
    public const RESULT_TOO_LARGE = UnixError::RESULT_TOO_LARGE;
    public const RESOURCE_TEMPORARILY_UNAVAILABLE = UnixError::RESOURCE_TEMPORARILY_UNAVAILABLE;

    // differs from Unix;
    public const FILE_NAME_TOO_LONG = 36;
    public const NO_RECORD_LOCKS_AVAILABLE = 37;
    public const FUNCTION_NOT_IMPLEMENTED = 38;
    public const DIRECTORY_NOT_EMPTY = 39;
    public const TOO_MANY_SYMBOLIC_LINKS_ENCOUNTERED = 40;
    public const NO_MESSAGE_OF_DESIRED_TYPE = 42;
    public const IDENTIFIER_REMOVED = 43;
    public const CHANNEL_NUMBER_OUT_OF_RANGE = 44;
    public const LEVEL_2_NOT_SYNCHRONIZED = 45;
    public const LEVEL_3_HALTED = 46;
    public const LEVEL_3_RESET = 47;
    public const LINK_NUMBER_OUT_OF_RANGE = 48;
    public const PROTOCOL_DRIVER_NOT_ATTACHED = 49;
    public const NO_CSI_STRUCTURE_AVAILABLE = 50;
    public const LEVEL_2_HALTED = 51;
    public const INVALID_EXCHANGE = 52;
    public const INVALID_REQUEST_DESCRIPTOR = 53;
    public const EXCHANGE_FULL = 54;
    public const NO_ANODE = 55;
    public const INVALID_REQUEST_CODE = 56;
    public const INVALID_SLOT = 57;
    public const BAD_FONT_FILE_FORMAT = 59;
    public const DEVICE_NOT_A_STREAM = 60;
    public const NO_DATA_AVAILABLE = 61;
    public const TIMER_EXPIRED = 62;
    public const OUT_OF_STREAMS_RESOURCES = 63;
    public const MACHINE_IS_NOT_ON_THE_NETWORK = 64;
    public const PACKAGE_NOT_INSTALLED = 65;
    public const OBJECT_IS_REMOTE = 66;
    public const LINK_HAS_BEEN_SEVERED = 67;
    public const ADVERTISE_ERROR = 68;
    public const SRMOUNT_ERROR = 69;
    public const COMMUNICATION_ERROR_ON_SEND = 70;
    public const PROTOCOL_ERROR = 71;
    public const MULTIHOP_ATTEMPTED = 72;
    public const RFS_SPECIFIC_ERROR = 73;
    public const NOT_A_DATA_MESSAGE = 74;
    public const VALUE_TOO_LARGE_FOR_DEFINED_DATA_TYPE = 75;
    public const NAME_NOT_UNIQUE_ON_NETWORK = 76;
    public const FILE_DESCRIPTOR_IN_BAD_STATE = 77;
    public const REMOTE_ADDRESS_CHANGED = 78;
    public const CAN_NOT_ACCESS_A_NEEDED_SHARED_LIBRARY = 79;
    public const ACCESSING_A_CORRUPTED_SHARED_LIBRARY = 80;
    public const DOT_LIB_SECTION_IN_A_OUT_CORRUPTED = 81;
    public const ATTEMPTING_TO_LINK_IN_TOO_MANY_SHARED_LIBRARIES = 82;
    public const CANNOT_EXEC_A_SHARED_LIBRARY_DIRECTLY = 83;
    public const ILLEGAL_BYTE_SEQUENCE = 84;
    public const INTERRUPTED_SYSTEM_CALL_SHOULD_BE_RESTARTED = 85;
    public const STREAMS_PIPE_ERROR = 86;
    public const TOO_MANY_USERS = 87;
    public const SOCKET_OPERATION_ON_NON_SOCKET = 88;
    public const DESTINATION_ADDRESS_REQUIRED = 89;
    public const MESSAGE_TOO_LONG = 90;
    public const PROTOCOL_WRONG_TYPE_FOR_SOCKET = 91;
    public const PROTOCOL_NOT_AVAILABLE = 92;
    public const PROTOCOL_NOT_SUPPORTED = 93;
    public const SOCKET_TYPE_NOT_SUPPORTED = 94;
    public const OPERATION_NOT_SUPPORTED_ON_TRANSPORT_ENDPOINT = 95;
    public const PROTOCOL_FAMILY_NOT_SUPPORTED = 96;
    public const ADDRESS_FAMILY_NOT_SUPPORTED_BY_PROTOCOL = 97;
    public const ADDRESS_ALREADY_IN_USE = 98;
    public const CANNOT_ASSIGN_REQUESTED_ADDRESS = 99;
    public const NETWORK_IS_DOWN = 100;
    public const NETWORK_IS_UNREACHABLE = 101;
    public const NETWORK_DROPPED_CONNECTION_BECAUSE_OF_RESET = 102;
    public const SOFTWARE_CAUSED_CONNECTION_ABORT = 103;
    public const CONNECTION_RESET_BY_PEER = 104;
    public const NO_BUFFER_SPACE_AVAILABLE = 105;
    public const TRANSPORT_ENDPOINT_IS_ALREADY_CONNECTED = 106;
    public const TRANSPORT_ENDPOINT_IS_NOT_CONNECTED = 107;
    public const CANNOT_SEND_AFTER_TRANSPORT_ENDPOINT_SHUTDOWN = 108;
    public const TOO_MANY_REFERENCES_CANNOT_SPLICE = 109;
    public const CONNECTION_TIMED_OUT = 110;
    public const CONNECTION_REFUSED = 111;
    public const HOST_IS_DOWN = 112;
    public const NO_ROUTE_TO_HOST = 113;
    public const OPERATION_ALREADY_IN_PROGRESS = 114;
    public const OPERATION_NOW_IN_PROGRESS = 115;
    public const STALE_NFS_FILE_HANDLE = 116;
    public const STRUCTURE_NEEDS_CLEANING = 117;
    public const NOT_A_XENIX_NAMED_TYPE_FILE = 118;
    public const NO_XENIX_SEMAPHORES_AVAILABLE = 119;
    public const IS_A_NAMED_TYPE_FILE = 120;
    public const REMOTE_IO_ERROR = 121;
    public const QUOTA_EXCEEDED = 122;
    public const NO_MEDIUM_FOUND = 123;
    public const WRONG_MEDIUM_TYPE = 124;
    public const OPERATION_CANCELED = 125;
    public const REQUIRED_KEY_NOT_AVAILABLE = 126;
    public const KEY_HAS_EXPIRED = 127;
    public const KEY_HAS_BEEN_REVOKED = 128;
    public const KEY_WAS_REJECTED_BY_SERVICE = 129;
    public const OWNER_DIED = 130;
    public const STATE_NOT_RECOVERABLE = 131;

    public function getDescription(): string
    {
        return ucfirst(str_replace(
            ['dot_lib', 'a_out', 'io', 'cross_device', 'readonly', 'non_socket', 'references', 'csi', 'rfs', 'nfs', 'xenix', '_'],
            ['.lib', 'a.out', 'I/O', 'cross-device', 'read-only', 'non-socket', 'references:', 'CSI', 'RFS', 'NFS', 'XENIX', ' '],
            strtolower($this->getConstantName())
        ));
    }

}
