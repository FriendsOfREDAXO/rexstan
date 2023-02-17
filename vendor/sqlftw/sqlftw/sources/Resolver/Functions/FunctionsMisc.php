<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: ipv6regex ffff

namespace SqlFtw\Resolver\Functions;

use Dogma\Re;
use SqlFtw\Resolver\UnresolvableException;
use SqlFtw\Sql\Expression\Value;
use SqlFtw\Util\Uuid;
use function random_int;
use function strlen;
use const PHP_INT_MAX;

trait FunctionsMisc
{

    // ANY_VALUE() - Suppress ONLY_FULL_GROUP_BY value rejection

    /**
     * BIN_TO_UUID() - Convert binary UUID to string
     *
     * @param scalar|Value|null $uuid
     * @param scalar|Value|null $swap
     */
    public function bin_to_uuid($uuid, $swap = false): ?string
    {
        $uuid = $this->cast->toString($uuid);
        $swap = $this->cast->toBool($swap) ?? false;

        if ($uuid === null) {
            return null;
        } elseif (strlen($uuid) !== 16) {
            throw new UnresolvableException('Invalid value of UUID.');
        }

        return Uuid::v1_binary_to_formatted($uuid, $swap);
    }

    // DEFAULT() - Return the default value for a table column
    // GROUPING() - Distinguish super-aggregate ROLLUP rows from regular rows
    // INET_ATON() - Return the numeric value of an IP address
    // INET_NTOA() - Return the IP address from a numeric value
    // INET6_ATON() - Return the numeric value of an IPv6 address
    // INET6_NTOA() - Return the IPv6 address from a numeric value

    /**
     * IS_IPV4() - Whether argument is an IPv4 address
     *
     * @param scalar|Value|null $address
     */
    public function is_ipv4($address): ?bool
    {
        static $ipv4 = '~^((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.){3}(25[0-5]|(2[0-4]|1\d|[1-9]|)\d)$~';

        $address = $this->cast->toString($address);
        if ($address === null) {
            return null;
        }

        return Re::hasMatch($ipv4, $address);
    }

    // IS_IPV4_COMPAT() - Whether argument is an IPv4-compatible address
    // IS_IPV4_MAPPED() - Whether argument is an IPv4-mapped address

    /**
     * IS_IPV6() - Whether argument is an IPv6 address
     *
     * @param scalar|Value|null $address
     */
    public function is_ipv6($address): ?bool
    {
        static $ipv6regex = '~^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$~';

        $address = $this->cast->toString($address);
        if ($address === null) {
            return null;
        }

        return Re::hasMatch($ipv6regex, $address);
    }

    /**
     * IS_UUID() - Whether argument is a valid UUID
     *
     * @param scalar|Value|null $uuid
     */
    public function is_uuid($uuid): ?bool
    {
        $uuid = $this->cast->toString($uuid);
        if ($uuid === null) {
            return null;
        }

        return Uuid::v1_validate($uuid);
    }

    /**
     * MASTER_POS_WAIT() - Block until the replica has read and applied all updates up to the specified position
     *
     * @param scalar|Value|null $logName
     * @param scalar|Value|null $logPos
     * @param scalar|Value|null $timeout
     * @param scalar|Value|null $channel
     */
    public function master_pos_wait($logName, $logPos, $timeout = null, $channel = null): ?int
    {
        return null; // arbitrary (null = replication not started)
    }

    /**
     * NAME_CONST() - Cause the column to have the given name (same as `SELECT value AS name`)
     *
     * @param scalar|Value|null $name
     * @param scalar|Value|null $value
     * @return scalar|null
     */
    public function name_const($name, $value)
    {
        return $this->cast->toScalar($value);
    }

    /**
     * SLEEP() - Sleep for a number of seconds
     *
     * @param scalar|Value|null $seconds
     */
    public function sleep($seconds): int
    {
        $seconds = $this->cast->toInt($seconds);
        if ($seconds === null) {
            throw new UnresolvableException('Invalid argument to sleep().');
        }

        return 0;
    }

    /**
     * SOURCE_POS_WAIT() - Block until the replica has read and applied all updates up to the specified position
     *
     * @param scalar|Value|null $logName
     * @param scalar|Value|null $logPos
     * @param scalar|Value|null $timeout
     * @param scalar|Value|null $channel
     */
    public function source_pos_wait($logName, $logPos, $timeout = null, $channel = null): ?int
    {
        return null; // arbitrary (null = replication not started)
    }

    /**
     * UUID() - Return a Universal Unique Identifier (UUID)
     */
    public function uuid(): string
    {
        return Uuid::v1_generate();
    }

    /**
     * UUID_SHORT() - Return an integer-valued universal identifier
     */
    public function uuid_short(): int
    {
        return random_int(0, PHP_INT_MAX);
    }

    /**
     * UUID_TO_BIN() - Convert string UUID to binary
     *
     * @param scalar|Value|null $uuid
     * @param scalar|Value|null $swap
     */
    public function uuid_to_bin($uuid, $swap = false): ?string
    {
        $uuid = $this->cast->toString($uuid);
        $swap = $this->cast->toBool($swap) ?? false;

        if ($uuid === null) {
            return null;
        } elseif (!Uuid::v1_validate($uuid)) {
            throw new UnresolvableException('Invalid value of UUID.');
        }

        return Uuid::v1_formatted_to_binary($uuid, $swap);
    }

    // VALUES() - Define the values to be used during an INSERT

}
