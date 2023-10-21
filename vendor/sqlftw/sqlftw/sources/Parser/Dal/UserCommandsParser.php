<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace SqlFtw\Parser\Dal;

use SqlFtw\Parser\ParserException;
use SqlFtw\Parser\TokenList;
use SqlFtw\Platform\Platform;
use SqlFtw\Sql\Dal\User\AddAuthFactor;
use SqlFtw\Sql\Dal\User\AlterAuthOption;
use SqlFtw\Sql\Dal\User\AlterCurrentUserCommand;
use SqlFtw\Sql\Dal\User\AlteredUser;
use SqlFtw\Sql\Dal\User\AlterUserAction;
use SqlFtw\Sql\Dal\User\AlterUserCommand;
use SqlFtw\Sql\Dal\User\AlterUserDefaultRoleCommand;
use SqlFtw\Sql\Dal\User\AlterUserFinishRegistrationCommand;
use SqlFtw\Sql\Dal\User\AlterUserInitiateRegistrationCommand;
use SqlFtw\Sql\Dal\User\AlterUserRegistrationCommand;
use SqlFtw\Sql\Dal\User\AlterUserUnregisterCommand;
use SqlFtw\Sql\Dal\User\AuthOption;
use SqlFtw\Sql\Dal\User\CreateRoleCommand;
use SqlFtw\Sql\Dal\User\CreateUserCommand;
use SqlFtw\Sql\Dal\User\DiscardOldPasswordAction;
use SqlFtw\Sql\Dal\User\DropAuthFactor;
use SqlFtw\Sql\Dal\User\DropRoleCommand;
use SqlFtw\Sql\Dal\User\DropUserCommand;
use SqlFtw\Sql\Dal\User\DynamicUserPrivilege;
use SqlFtw\Sql\Dal\User\GrantCommand;
use SqlFtw\Sql\Dal\User\GrantProxyCommand;
use SqlFtw\Sql\Dal\User\GrantRoleCommand;
use SqlFtw\Sql\Dal\User\IdentifiedUser;
use SqlFtw\Sql\Dal\User\ModifyAuthFactor;
use SqlFtw\Sql\Dal\User\RenameUserCommand;
use SqlFtw\Sql\Dal\User\RevokeAllCommand;
use SqlFtw\Sql\Dal\User\RevokeCommand;
use SqlFtw\Sql\Dal\User\RevokeProxyCommand;
use SqlFtw\Sql\Dal\User\RevokeRoleCommand;
use SqlFtw\Sql\Dal\User\RolesSpecification;
use SqlFtw\Sql\Dal\User\RolesSpecificationType;
use SqlFtw\Sql\Dal\User\SetDefaultRoleCommand;
use SqlFtw\Sql\Dal\User\SetPasswordCommand;
use SqlFtw\Sql\Dal\User\SetRoleCommand;
use SqlFtw\Sql\Dal\User\StaticUserPrivilege;
use SqlFtw\Sql\Dal\User\UnknownDynamicUserPrivilege;
use SqlFtw\Sql\Dal\User\UserCommand;
use SqlFtw\Sql\Dal\User\UserDefaultRolesSpecification;
use SqlFtw\Sql\Dal\User\UserPasswordLockOption;
use SqlFtw\Sql\Dal\User\UserPasswordLockOptionType;
use SqlFtw\Sql\Dal\User\UserPrivilege;
use SqlFtw\Sql\Dal\User\UserPrivilegeResource;
use SqlFtw\Sql\Dal\User\UserPrivilegeResourceType;
use SqlFtw\Sql\Dal\User\UserPrivilegeType;
use SqlFtw\Sql\Dal\User\UserResourceOption;
use SqlFtw\Sql\Dal\User\UserResourceOptionType;
use SqlFtw\Sql\Dal\User\UserTlsOption;
use SqlFtw\Sql\Dal\User\UserTlsOptionType;
use SqlFtw\Sql\EntityType;
use SqlFtw\Sql\Expression\BuiltInFunction;
use SqlFtw\Sql\Expression\FunctionCall;
use SqlFtw\Sql\Expression\Operator;
use SqlFtw\Sql\Expression\StringValue;
use SqlFtw\Sql\Keyword;
use SqlFtw\Sql\Statement;
use SqlFtw\Sql\UserName;
use function array_values;
use function count;
use function in_array;

class UserCommandsParser
{

    private const RESOURCE_PRIVILEGES = [
        UserPrivilegeResourceType::TABLE => [
            StaticUserPrivilege::ALL,
            StaticUserPrivilege::ALL_PRIVILEGES,
            StaticUserPrivilege::ALTER,
            StaticUserPrivilege::CREATE_VIEW,
            StaticUserPrivilege::CREATE,
            StaticUserPrivilege::DELETE,
            StaticUserPrivilege::DROP,
            StaticUserPrivilege::GRANT_OPTION,
            StaticUserPrivilege::INDEX,
            StaticUserPrivilege::INSERT,
            StaticUserPrivilege::REFERENCES,
            StaticUserPrivilege::SELECT,
            StaticUserPrivilege::SHOW_VIEW,
            StaticUserPrivilege::TRIGGER,
            StaticUserPrivilege::UPDATE,
        ],
        UserPrivilegeResourceType::FUNCTION => [
            StaticUserPrivilege::ALL,
            StaticUserPrivilege::ALL_PRIVILEGES,
            StaticUserPrivilege::USAGE,
            StaticUserPrivilege::ALTER_ROUTINE,
            StaticUserPrivilege::CREATE_ROUTINE,
            StaticUserPrivilege::EXECUTE,
            StaticUserPrivilege::GRANT_OPTION,
        ],
        UserPrivilegeResourceType::PROCEDURE => [
            StaticUserPrivilege::ALL,
            StaticUserPrivilege::ALL_PRIVILEGES,
            StaticUserPrivilege::USAGE,
            StaticUserPrivilege::ALTER_ROUTINE,
            StaticUserPrivilege::CREATE_ROUTINE,
            StaticUserPrivilege::EXECUTE,
            StaticUserPrivilege::GRANT_OPTION,
        ],
    ];

    /**
     * ALTER USER [IF EXISTS]
     *     user [auth_option] [, user [auth_option]] ...
     *     [REQUIRE {NONE | tls_option [[AND] tls_option] ...}]
     *     [WITH resource_option [resource_option] ...]
     *     [password_option | lock_option] ...
     *     [COMMENT 'comment_string' | ATTRIBUTE 'json_object']
     *
     * ALTER USER [IF EXISTS]
     *     USER() IDENTIFIED BY 'auth_string'
     *     [REPLACE 'current_auth_string']
     *     [RETAIN CURRENT PASSWORD]
     *     [DISCARD OLD PASSWORD]
     *
     * ALTER USER [IF EXISTS]
     *     user DEFAULT ROLE
     *     {NONE | ALL | role [, role ] ...}
     *
     * ALTER USER [IF EXISTS]
     *     user [registration_option]
     *
     * ALTER USER [IF EXISTS]
     *     USER() [registration_option]
     *
     * registration_option: {
     *     factor INITIATE REGISTRATION
     *   | factor FINISH REGISTRATION SET CHALLENGE_RESPONSE AS 'auth_string'
     *   | factor UNREGISTER
     * }
     *
     * @return UserCommand&Statement
     */
    public function parseAlterUser(TokenList $tokenList): UserCommand
    {
        $tokenList->expectKeywords(Keyword::ALTER, Keyword::USER);
        $ifExists = $tokenList->hasKeywords(Keyword::IF, Keyword::EXISTS);

        if ($tokenList->hasKeyword(Keyword::USER)) {
            $tokenList->expectSymbol('(');
            $tokenList->expectSymbol(')');
            $factor = $tokenList->getUnsignedInt();
            if ($factor !== null) {
                return $this->parseRegistration($tokenList, new FunctionCall(new BuiltInFunction(BuiltInFunction::USER)), (int) $factor, $ifExists);
            } else {
                $option = $replace = null;
                if ($tokenList->hasKeyword(Keyword::IDENTIFIED)) {
                    [$authPlugin, $password, $as, $oldHashedPassword] = $this->parseAuthOptionParts($tokenList, true);
                    $option = new AuthOption($authPlugin, $password, $as, null, $oldHashedPassword);
                }

                if ($tokenList->hasKeyword(Keyword::REPLACE)) {
                    $replace = $tokenList->expectString();
                }
                $retainCurrentPassword = $tokenList->hasKeywords(Keyword::RETAIN, Keyword::CURRENT, Keyword::PASSWORD);
                $discardOldPassword = $tokenList->hasKeywords(Keyword::DISCARD, Keyword::OLD, Keyword::PASSWORD);

                return new AlterCurrentUserCommand($option, $replace, $retainCurrentPassword, $discardOldPassword, $ifExists);
            }
        }

        $position = $tokenList->getPosition();
        $user = $this->parseUser($tokenList);
        $factor = $tokenList->getUnsignedInt();
        if ($factor !== null) {
            return $this->parseRegistration($tokenList, $user, (int) $factor, $ifExists);
        } elseif ($tokenList->hasKeywords(Keyword::DEFAULT, Keyword::ROLE)) {
            $role = $this->parseRoleSpecification($tokenList);

            return new AlterUserDefaultRoleCommand($user, $role, $ifExists);
        }

        $tokenList->rewind($position);

        $users = [];
        do {
            $user = $this->parseUser($tokenList);
            $action = null;
            if ($tokenList->hasAnyKeyword(Keyword::IDENTIFIED, Keyword::DISCARD, Keyword::ADD, Keyword::MODIFY, Keyword::DROP)) {
                $action = $this->parseAlterAuthOptions($tokenList->rewind(-1));
            }
            $users[] = new AlteredUser($user, $action);
        } while ($tokenList->hasSymbol(','));

        $tlsOptions = $this->parseTlsOptions($tokenList);
        $resourceOptions = $this->parseResourceOptions($tokenList);
        $passwordLockOptions = $this->parsePasswordLockOptions($tokenList);

        $comment = $attribute = null;
        if ($tokenList->hasKeyword(Keyword::COMMENT)) {
            $comment = $tokenList->expectString();
        } elseif ($tokenList->hasKeyword(Keyword::ATTRIBUTE)) {
            $attribute = $tokenList->expectString();
        }

        return new AlterUserCommand($users, $tlsOptions, $resourceOptions, $passwordLockOptions, $comment, $attribute, $ifExists);
    }

    /**
     * CREATE USER [IF NOT EXISTS]
     *     user [auth_option] [, user [auth_option]] ...
     *     [DEFAULT ROLE role [, role ] ...]
     *     [REQUIRE {NONE | tls_option [[AND] tls_option] ...}]
     *     [WITH resource_option [resource_option] ...]
     *     [password_option | lock_option] ...
     *     [COMMENT 'comment_string' | ATTRIBUTE 'json_object']
     */
    public function parseCreateUser(TokenList $tokenList): CreateUserCommand
    {
        $tokenList->expectKeywords(Keyword::CREATE, Keyword::USER);
        $ifNotExists = $tokenList->hasKeywords(Keyword::IF, Keyword::NOT, Keyword::EXISTS);

        $users = [];
        do {
            $user = $this->parseUser($tokenList);
            $option1 = $option2 = $option3 = null;
            if ($tokenList->hasKeyword(Keyword::IDENTIFIED)) {
                [$option1, $option2, $option3] = $this->parseCreateAuthOptions($tokenList);
            }
            $users[] = new IdentifiedUser($user, $option1, $option2, $option3);
        } while ($tokenList->hasSymbol(','));

        $defaultRoles = null;
        if ($tokenList->hasKeywords(Keyword::DEFAULT, Keyword::ROLE)) {
            $defaultRoles = $this->parseRolesList($tokenList);
        }

        $tlsOptions = $this->parseTlsOptions($tokenList);
        $resourceOptions = $this->parseResourceOptions($tokenList);
        $passwordLockOptions = $this->parsePasswordLockOptions($tokenList);

        $comment = $attribute = null;
        if ($tokenList->hasKeyword(Keyword::COMMENT)) {
            $comment = $tokenList->expectString();
        } elseif ($tokenList->hasKeyword(Keyword::ATTRIBUTE)) {
            $attribute = $tokenList->expectString();
        }

        return new CreateUserCommand($users, $defaultRoles, $tlsOptions, $resourceOptions, $passwordLockOptions, $comment, $attribute, $ifNotExists);
    }

    /**
     * ALTER USER [IF EXISTS]
     *     user [registration_option]
     *
     * ALTER USER [IF EXISTS]
     *     USER() [registration_option]
     *
     * registration_option: {
     *     factor INITIATE REGISTRATION
     *   | factor FINISH REGISTRATION SET CHALLENGE_RESPONSE AS 'auth_string'
     *   | factor UNREGISTER
     * }
     *
     * @param UserName|FunctionCall $user
     * @return AlterUserRegistrationCommand&Statement
     */
    private function parseRegistration(TokenList $tokenList, $user, int $factor, bool $ifExists): AlterUserRegistrationCommand
    {
        if ($tokenList->hasKeywords(Keyword::INITIATE, Keyword::REGISTRATION)) {
            return new AlterUserInitiateRegistrationCommand($user, $factor, $ifExists);
        } elseif ($tokenList->hasKeywords(Keyword::FINISH, Keyword::REGISTRATION, Keyword::SET, Keyword::CHALLENGE_RESPONSE, Keyword::AS)) {
            $authString = $tokenList->expectString();

            return new AlterUserFinishRegistrationCommand($user, $factor, $authString, $ifExists);
        } elseif ($tokenList->hasKeywords(Keyword::UNREGISTER)) {
            return new AlterUserUnregisterCommand($user, $factor, $ifExists);
        } else {
            $tokenList->missingAnyKeyword(Keyword::INITIATE, Keyword::FINISH, Keyword::UNREGISTER);
        }
    }

    /**
     * auth_option: {
     *     IDENTIFIED [WITH auth_plugin BY] {RANDOM PASSWORD | 'auth_string'} [REPLACE 'current_auth_string'] [RETAIN CURRENT PASSWORD]
     *   | IDENTIFIED WITH auth_plugin [AS 'auth_string']
     *   | DISCARD OLD PASSWORD
     *   | ADD factor factor_auth_option [ADD factor factor_auth_option]
     *   | MODIFY factor factor_auth_option [MODIFY factor factor_auth_option]
     *   | DROP factor [DROP factor]
     * }
     *
     * user_func_auth_option: {
     *     IDENTIFIED BY 'auth_string'
     *         [REPLACE 'current_auth_string']
     *         [RETAIN CURRENT PASSWORD]
     *   | DISCARD OLD PASSWORD
     * }
     *
     * factor_auth_option: {
     *     IDENTIFIED [WITH auth_plugin] BY {RANDOM PASSWORD | 'auth_string'}
     *   | IDENTIFIED WITH auth_plugin AS 'auth_string'
     * }
     *
     * factor: {2 | 3} FACTOR
     */
    private function parseAlterAuthOptions(TokenList $tokenList): AlterUserAction
    {
        if ($tokenList->hasKeyword(Keyword::IDENTIFIED)) {
            [$authPlugin, $password, $as, $oldHashedPassword] = $this->parseAuthOptionParts($tokenList);

            $replace = null;
            if ($tokenList->hasKeyword(Keyword::REPLACE)) {
                $replace = $tokenList->expectString();
            }
            $retainCurrentPassword = $tokenList->hasKeywords(Keyword::RETAIN, Keyword::CURRENT, Keyword::PASSWORD);

            return new AlterAuthOption($authPlugin, $password, $as, $replace, $retainCurrentPassword, $oldHashedPassword);
        } elseif ($tokenList->hasKeyword(Keyword::ADD)) {
            $factor1 = (int) $tokenList->expectUnsignedInt();
            [$authPlugin, $password, $as] = $this->parseAuthOptionParts($tokenList);
            $option1 = new AuthOption($authPlugin, $password, $as);
            $factor2 = $option2 = null;
            if ($tokenList->hasKeyword(Keyword::ADD)) {
                $factor2 = (int) $tokenList->expectUnsignedInt();
                [$authPlugin, $password, $as] = $this->parseAuthOptionParts($tokenList);
                $option2 = new AuthOption($authPlugin, $password, $as);
            }

            return new AddAuthFactor($factor1, $option1, $factor2, $option2);
        } elseif ($tokenList->hasKeyword(Keyword::MODIFY)) {
            $factor1 = (int) $tokenList->expectUnsignedInt();
            [$authPlugin, $password, $as] = $this->parseAuthOptionParts($tokenList);
            $option1 = new AuthOption($authPlugin, $password, $as);
            $factor2 = $option2 = null;
            if ($tokenList->hasKeyword(Keyword::MODIFY)) {
                $factor2 = (int) $tokenList->expectUnsignedInt();
                [$authPlugin, $password, $as] = $this->parseAuthOptionParts($tokenList);
                $option2 = new AuthOption($authPlugin, $password, $as);
            }

            return new ModifyAuthFactor($factor1, $option1, $factor2, $option2);
        } elseif ($tokenList->hasKeyword(Keyword::DROP)) {
            $factor1 = (int) $tokenList->expectUnsignedInt();
            $factor2 = null;
            if ($tokenList->hasKeyword(Keyword::DROP)) {
                $factor2 = (int) $tokenList->expectUnsignedInt();
            }

            return new DropAuthFactor($factor1, $factor2);
        } elseif ($tokenList->hasKeywords(Keyword::DISCARD, Keyword::OLD, Keyword::PASSWORD)) {
            return new DiscardOldPasswordAction();
        } else {
            $tokenList->missingAnyKeyword(Keyword::IDENTIFIED, Keyword::DISCARD, Keyword::ADD, Keyword::MODIFY, Keyword::DROP);
        }
    }

    /**
     * @return array{string|null, StringValue|false|null, StringValue|null, bool}
     */
    private function parseAuthOptionParts(TokenList $tokenList, bool $currentUser = false): array
    {
        $authPlugin = $password = $as = null;
        $oldHashedPassword = false;
        if (!$currentUser && $tokenList->hasKeyword(Keyword::WITH)) {
            $authPlugin = $tokenList->expectNonReservedNameOrString();
        }
        if (!$currentUser && $authPlugin !== null && $tokenList->hasKeyword(Keyword::AS)) {
            $as = $tokenList->expectStringValue();
        } elseif ($tokenList->hasKeyword(Keyword::BY)) {
            if ($tokenList->using(Platform::MYSQL, null, 50799) && $tokenList->hasKeyword(Keyword::PASSWORD)) {
                $oldHashedPassword = true;
                $password = $tokenList->expectStringValue();
            } elseif ($tokenList->hasKeywords(Keyword::RANDOM, Keyword::PASSWORD)) {
                $password = false;
            } else {
                $password = $tokenList->expectStringValue();
            }
        }

        return [$authPlugin, $password, $as, $oldHashedPassword];
    }

    /**
     * auth_option: {
     *     IDENTIFIED BY PASSWORD 'auth_string' -- deprecated
     *   | IDENTIFIED [WITH auth_plugin] BY {RANDOM PASSWORD | 'auth_string'} [AND 2fa_auth_option]
     *   | IDENTIFIED WITH auth_plugin [AS 'auth_string'] [AND 2fa_auth_option]
     *   | IDENTIFIED WITH auth_plugin [initial_auth_option]
     * }
     *
     * 2fa_auth_option: {
     *     IDENTIFIED [WITH auth_plugin] BY {RANDOM PASSWORD | 'auth_string'} [AND 3fa_auth_option]
     *   | IDENTIFIED WITH auth_plugin [AS 'auth_string'] [AND 3fa_auth_option]
     * }
     *
     * 3fa_auth_option: {
     *     IDENTIFIED [WITH auth_plugin BY] {RANDOM PASSWORD | 'auth_string'}
     *   | IDENTIFIED WITH auth_plugin [AS 'auth_string']
     * }
     *
     * initial_auth_option: {
     *     INITIAL AUTHENTICATION IDENTIFIED BY {RANDOM PASSWORD | 'auth_string'}
     *   | INITIAL AUTHENTICATION IDENTIFIED WITH auth_plugin AS 'auth_string'
     * }
     *
     * @return array{AuthOption, AuthOption|null, AuthOption|null}
     */
    private function parseCreateAuthOptions(TokenList $tokenList): array
    {
        $options = [];
        do {
            $authPlugin = $password = $as = $initial = null;
            $oldHashedPassword = false;
            if ($tokenList->hasKeyword(Keyword::WITH)) {
                $authPlugin = $tokenList->expectNonReservedNameOrString();
            }
            if ($authPlugin !== null && $tokenList->hasKeywords(Keyword::INITIAL, Keyword::AUTHENTICATION)) {
                if ($tokenList->hasKeywords(Keyword::BY, Keyword::RANDOM, Keyword::PASSWORD)) {
                    $initial = new AuthOption(null, false);
                } elseif ($tokenList->hasKeyword(Keyword::BY)) {
                    $initial = new AuthOption(null, $tokenList->expectStringValue());
                } else {
                    $tokenList->expectKeyword(Keyword::WITH);
                    $plugin = $tokenList->expectString();
                    $tokenList->expectKeyword(Keyword::AS);
                    $initial = new AuthOption($plugin, null, $tokenList->expectStringValue());
                }
            } elseif ($authPlugin !== null && $tokenList->hasKeyword(Keyword::AS)) {
                $as = $tokenList->expectStringValue();
            } elseif ($tokenList->hasKeyword(Keyword::BY)) {
                if ($tokenList->hasKeyword(Keyword::PASSWORD)) {
                    $oldHashedPassword = true;
                    $password = $tokenList->expectStringValue();
                } elseif ($tokenList->hasKeywords(Keyword::RANDOM, Keyword::PASSWORD)) {
                    $password = false;
                } else {
                    $password = $tokenList->expectStringValue();
                }
            }
            $options[] = new AuthOption($authPlugin, $password, $as, $initial, $oldHashedPassword);
            if (count($options) === 3) {
                break;
            }
        } while ($tokenList->hasKeywords(Keyword::AND, Keyword::IDENTIFIED));

        return [$options[0], $options[1] ?? null, $options[2] ?? null];
    }

    /**
     * [REQUIRE {NONE | tls_option [[AND] tls_option] ...}]
     *
     * tls_option: {
     *     SSL
     *   | X509
     *   | CIPHER 'cipher'
     *   | ISSUER 'issuer'
     *   | SUBJECT 'subject'
     * }
     *
     * @return list<UserTlsOption>|null
     */
    private function parseTlsOptions(TokenList $tokenList): ?array
    {
        $tlsOptions = null;
        if ($tokenList->hasKeyword(Keyword::REQUIRE)) {
            if ($tokenList->hasKeyword(Keyword::NONE)) {
                $tlsOptions = [];
            } else {
                $tlsOptions = [];
                $type = $tokenList->expectKeywordEnum(UserTlsOptionType::class);
                do {
                    $value = $tokenList->getString();
                    $tlsOptions[] = new UserTlsOption($type, $value);

                    $trailingAnd = $tokenList->hasKeyword(Keyword::AND);
                    // phpcs:ignore SlevomatCodingStandard.ControlStructures.AssignmentInCondition
                } while (($type = $tokenList->getKeywordEnum(UserTlsOptionType::class)) !== null);

                if ($trailingAnd) {
                    // throws
                    $tokenList->expectKeywordEnum(UserTlsOptionType::class);
                }
            }
        }

        return $tlsOptions;
    }

    /**
     * [WITH resource_option [resource_option] ...]
     *
     * resource_option: {
     *     MAX_QUERIES_PER_HOUR count
     *   | MAX_UPDATES_PER_HOUR count
     *   | MAX_CONNECTIONS_PER_HOUR count
     *   | MAX_USER_CONNECTIONS count
     * }
     *
     * @return non-empty-list<UserResourceOption>|null
     */
    private function parseResourceOptions(TokenList $tokenList): ?array
    {
        if (!$tokenList->hasKeyword(Keyword::WITH)) {
            return null;
        }

        $resourceOptions = [];
        $type = $tokenList->expectKeywordEnum(UserResourceOptionType::class);
        do {
            $value = (int) $tokenList->expectUnsignedInt();
            $resourceOptions[] = new UserResourceOption($type, $value);
            $type = $tokenList->getKeywordEnum(UserResourceOptionType::class);
        } while ($type !== null);

        return $resourceOptions;
    }

    /**
     * password_option: {
     *     PASSWORD EXPIRE [DEFAULT | NEVER | INTERVAL N DAY]
     *   | PASSWORD HISTORY {DEFAULT | N}
     *   | PASSWORD REUSE INTERVAL {DEFAULT | N DAY}
     *   | PASSWORD REQUIRE CURRENT [DEFAULT | OPTIONAL]
     *   | FAILED_LOGIN_ATTEMPTS N
     *   | PASSWORD_LOCK_TIME {N | UNBOUNDED}
     * }
     *
     * lock_option: {
     *     ACCOUNT LOCK
     *   | ACCOUNT UNLOCK
     * }
     *
     * @return non-empty-list<UserPasswordLockOption>|null
     */
    private function parsePasswordLockOptions(TokenList $tokenList): ?array
    {
        $passwordLockOptions = [];
        while ($keyword = $tokenList->getAnyKeyword(Keyword::PASSWORD, Keyword::ACCOUNT, Keyword::FAILED_LOGIN_ATTEMPTS, Keyword::PASSWORD_LOCK_TIME)) {
            if ($keyword === Keyword::ACCOUNT) {
                $keyword = $tokenList->expectAnyKeyword(Keyword::LOCK, Keyword::UNLOCK);
                $passwordLockOptions[] = new UserPasswordLockOption(new UserPasswordLockOptionType(UserPasswordLockOptionType::ACCOUNT), $keyword);
                continue;
            } elseif ($keyword === Keyword::FAILED_LOGIN_ATTEMPTS) {
                $value = (int) $tokenList->expectUnsignedInt();
                if ($value > 32767) {
                    throw new ParserException('Maximum value of failed login attempts is 32767.', $tokenList);
                }
                $passwordLockOptions[] = new UserPasswordLockOption(new UserPasswordLockOptionType(UserPasswordLockOptionType::FAILED_LOGIN_ATTEMPTS), $value);
                continue;
            } elseif ($keyword === Keyword::PASSWORD_LOCK_TIME) {
                if ($tokenList->hasKeyword(Keyword::UNBOUNDED)) {
                    $value = Keyword::UNBOUNDED;
                } else {
                    $value = (int) $tokenList->expectUnsignedInt();
                    if ($value > 32767) {
                        throw new ParserException('Maximum value of password lock time is 32767.', $tokenList);
                    }
                }
                $passwordLockOptions[] = new UserPasswordLockOption(new UserPasswordLockOptionType(UserPasswordLockOptionType::PASSWORD_LOCK_TIME), $value);
                continue;
            }

            $keyword = $tokenList->expectAnyKeyword(Keyword::EXPIRE, Keyword::HISTORY, Keyword::REUSE, Keyword::REQUIRE);
            if ($keyword === Keyword::EXPIRE) {
                $value = $tokenList->getAnyKeyword(Keyword::DEFAULT, Keyword::NEVER, Keyword::INTERVAL);
                if ($value === Keyword::INTERVAL) {
                    $value = (int) $tokenList->expectInt();
                    if ($value > 65535) {
                        throw new ParserException('Maximum expiration interval is 65535 days.', $tokenList);
                    }
                    $tokenList->expectKeyword(Keyword::DAY);
                }
                $passwordLockOptions[] = new UserPasswordLockOption(new UserPasswordLockOptionType(UserPasswordLockOptionType::PASSWORD_EXPIRE), $value);
            } elseif ($keyword === Keyword::HISTORY) {
                $value = Keyword::DEFAULT;
                if (!$tokenList->hasKeyword(Keyword::DEFAULT)) {
                    $value = (int) $tokenList->expectInt();
                }
                $passwordLockOptions[] = new UserPasswordLockOption(new UserPasswordLockOptionType(UserPasswordLockOptionType::PASSWORD_HISTORY), $value);
            } elseif ($keyword === Keyword::REUSE) {
                $tokenList->expectKeyword(Keyword::INTERVAL);
                $value = Keyword::DEFAULT;
                if (!$tokenList->hasKeyword(Keyword::DEFAULT)) {
                    $value = (int) $tokenList->expectInt();
                    $tokenList->expectKeyword(Keyword::DAY);
                }
                $passwordLockOptions[] = new UserPasswordLockOption(new UserPasswordLockOptionType(UserPasswordLockOptionType::PASSWORD_REUSE_INTERVAL), $value);
            } else {
                $tokenList->expectKeyword(Keyword::CURRENT);
                $value = $tokenList->getAnyKeyword(Keyword::DEFAULT, Keyword::OPTIONAL);
                $passwordLockOptions[] = new UserPasswordLockOption(new UserPasswordLockOptionType(UserPasswordLockOptionType::PASSWORD_REQUIRE_CURRENT), $value);
            }
        }

        return $passwordLockOptions !== [] ? $passwordLockOptions : null;
    }

    /**
     * CREATE ROLE [IF NOT EXISTS] role [, role ] ...
     */
    public function parseCreateRole(TokenList $tokenList): CreateRoleCommand
    {
        $tokenList->expectKeywords(Keyword::CREATE, Keyword::ROLE);
        $ifNotExists = $tokenList->hasKeywords(Keyword::IF, Keyword::NOT, Keyword::EXISTS);
        $roles = $this->parseRolesList($tokenList);

        return new CreateRoleCommand($roles, $ifNotExists);
    }

    /**
     * DROP ROLE [IF EXISTS] role [, role ] ...
     */
    public function parseDropRole(TokenList $tokenList): DropRoleCommand
    {
        $tokenList->expectKeywords(Keyword::DROP, Keyword::ROLE);
        $ifExists = $tokenList->hasKeywords(Keyword::IF, Keyword::EXISTS);
        $roles = $this->parseRolesList($tokenList);

        return new DropRoleCommand($roles, $ifExists);
    }

    /**
     * DROP USER [IF EXISTS] user [, user] ...
     */
    public function parseDropUser(TokenList $tokenList): DropUserCommand
    {
        $tokenList->expectKeywords(Keyword::DROP, Keyword::USER);
        $ifExists = $tokenList->hasKeywords(Keyword::IF, Keyword::EXISTS);
        $users = $this->parseUsersList($tokenList);

        return new DropUserCommand($users, $ifExists);
    }

    /**
     * Crom! Grant me revenge! And if you do not listen, then to hell with you!
     *
     * GRANT PROXY ON user
     *     TO user [, user] ...
     *     [WITH GRANT OPTION]
     *
     * GRANT role [, role] ...
     *     TO user [, user] ...
     *     [WITH ADMIN OPTION]
     *
     * GRANT
     *     priv_type [(column_list)] [, priv_type [(column_list)]] ...
     *     ON [object_type] priv_level
     *     TO user_or_role [, user_or_role] ...
     *     [WITH GRANT OPTION]
     *     [AS user
     *       [WITH ROLE {DEFAULT | NONE | ALL | ALL EXCEPT role [, role ] ... | role [, role ] ...}]
     *     ]
     *
     * MySQL 5.x:
     * GRANT
     *     priv_type [(column_list)] [, priv_type [(column_list)]] ...
     *     ON [object_type] priv_level
     *     TO user [auth_option] [, user [auth_option]] ...
     *     [REQUIRE {NONE | tls_option [[AND] tls_option] ...}]
     *     [WITH {GRANT OPTION | resource_option} ...]
     *
     * @return UserCommand&Statement
     */
    public function parseGrant(TokenList $tokenList): UserCommand
    {
        $tokenList->expectKeyword(Keyword::GRANT);

        if ($tokenList->hasKeywords(Keyword::PROXY, Keyword::ON)) {
            // GRANT PROXY ON
            $proxy = $this->parseUser($tokenList);
            $tokenList->expectKeyword(Keyword::TO);

            $users = [];
            do {
                $user = $this->parseUser($tokenList);
                $option = null;
                if ($tokenList->hasKeyword(Keyword::IDENTIFIED)) {
                    [$authPlugin, $password, $as] = $this->parseAuthOptionParts($tokenList);
                    $option = new AuthOption($authPlugin, $password, $as);
                }
                $users[] = new IdentifiedUser($user, $option);
            } while ($tokenList->hasSymbol(','));

            $withGrantOption = $tokenList->hasKeywords(Keyword::WITH, Keyword::GRANT, Keyword::OPTION);

            return new GrantProxyCommand($proxy, $users, $withGrantOption);
        } elseif (!$tokenList->seekKeywordBefore(Keyword::ON, Keyword::TO)) {
            // GRANT ... TO
            $roles = $this->parseRolesList($tokenList);
            $tokenList->expectKeyword(Keyword::TO);
            $users = $this->parseUsersList($tokenList);
            $withAdminOption = $tokenList->hasKeywords(Keyword::WITH, Keyword::ADMIN, Keyword::OPTION);

            return new GrantRoleCommand($roles, $users, $withAdminOption);
        } else {
            // GRANT ... ON ... TO
            $privileges = $this->parsePrivilegesList($tokenList);
            $resource = $this->parseResource($tokenList);
            $this->checkPrivilegeAndResource($tokenList, $resource, $privileges);

            $tokenList->expectKeyword(Keyword::TO);

            $users = [];
            do {
                $user = $this->parseUser($tokenList);
                $option = null;
                if ($tokenList->hasKeyword(Keyword::IDENTIFIED)) {
                    [$authPlugin, $password, $as] = $this->parseAuthOptionParts($tokenList);
                    $option = new AuthOption($authPlugin, $password, $as);
                }
                $users[] = new IdentifiedUser($user, $option);
            } while ($tokenList->hasSymbol(','));

            // 5.x only
            $tlsOptions = $this->parseTlsOptions($tokenList);
            $withGrantOption = $tokenList->hasKeywords(Keyword::WITH, Keyword::GRANT, Keyword::OPTION);
            // 5.x only
            $resourceOptions = $this->parseResourceOptions($tokenList);

            $as = $role = null;
            if ($tokenList->hasKeyword(Keyword::AS)) {
                $as = $this->parseUser($tokenList);
                if ($tokenList->hasKeywords(Keyword::WITH, Keyword::ROLE)) {
                    $role = $this->parseRoleSpecification($tokenList);
                }
            }

            return new GrantCommand($privileges, $resource, $users, $as, $role, $tlsOptions, $resourceOptions, $withGrantOption);
        }
    }

    /**
     * priv_type [(column_list)] [, priv_type [(column_list)]] ...
     *
     * @return non-empty-list<UserPrivilege>
     */
    private function parsePrivilegesList(TokenList $tokenList, bool $ifExists = false): array
    {
        $privileges = [];
        do {
            // static (keywords)
            $type = $tokenList->getMultiNameEnum(StaticUserPrivilege::class);
            if ($type !== null && $type->equalsValue(StaticUserPrivilege::PROXY)) {
                throw new ParserException('Cannot combine PROXY privilege with others.', $tokenList);
            }
            if ($type === null) {
                $name = $tokenList->getNonReservedNameOrString();
                if ($name !== null) {
                    // dynamic (names)
                    if (DynamicUserPrivilege::isValidValue($name)) {
                        $type = new DynamicUserPrivilege($name);
                    } elseif ($ifExists) {
                        $type = new UnknownDynamicUserPrivilege($name);
                    } else {
                        $tokenList->missingAnyKeyword(...array_values(StaticUserPrivilege::getAllowedValues()), ...array_values(DynamicUserPrivilege::getAllowedValues()));
                    }
                }
            }
            /** @var UserPrivilegeType $type */
            $type = $type;

            $columns = null;
            if ($tokenList->hasSymbol('(')) {
                $columns = [];
                do {
                    $columns[] = $tokenList->expectName(EntityType::COLUMN);
                } while ($tokenList->hasSymbol(','));
                $tokenList->expectSymbol(')');
            }
            $privileges[] = new UserPrivilege($type, $columns);
        } while ($tokenList->hasSymbol(','));

        return $privileges;
    }

    /**
     * ON [object_type] priv_level
     *
     * object_type: {
     *     TABLE
     *   | FUNCTION
     *   | PROCEDURE
     * }
     *
     * priv_level: {
     *     *
     *   | *.*
     *   | db_name.*
     *   | db_name.tbl_name
     *   | tbl_name
     *   | db_name.routine_name
     * }
     */
    private function parseResource(TokenList $tokenList): UserPrivilegeResource
    {
        $tokenList->expectKeyword(Keyword::ON);
        $resourceType = $tokenList->getKeywordEnum(UserPrivilegeResourceType::class);
        if ($tokenList->hasOperator(Operator::MULTIPLY)) {
            $object = false;
            if ($tokenList->hasSymbol('.')) {
                $tokenList->expectOperator(Operator::MULTIPLY);
                $object = true;
            }

            return new UserPrivilegeResource(UserPrivilegeResource::ALL, $object ? UserPrivilegeResource::ALL : null, $resourceType);
        } else {
            $name = $tokenList->expectName(EntityType::SCHEMA);
            if ($tokenList->hasSymbol('.')) {
                $schema = $name;
                if ($tokenList->hasOperator(Operator::MULTIPLY)) {
                    return new UserPrivilegeResource($schema, UserPrivilegeResource::ALL, $resourceType);
                } else {
                    $name = $tokenList->expectName(EntityType::TABLE);

                    return new UserPrivilegeResource($schema, $name, $resourceType);
                }
            } else {
                return new UserPrivilegeResource(null, $name, $resourceType);
            }
        }
    }

    /**
     * @param non-empty-list<UserPrivilege> $privileges
     */
    private function checkPrivilegeAndResource(TokenList $tokenList, UserPrivilegeResource $resource, array $privileges): void
    {
        $type = $resource->getObjectType();
        if ($type === null) {
            return;
        }
        $name = $type->getValue();
        if (isset(self::RESOURCE_PRIVILEGES[$name])) {
            foreach ($privileges as $privilege) {
                $privilegeType = $privilege->getType();
                if ($privilegeType instanceof StaticUserPrivilege && !in_array($privilegeType->getValue(), self::RESOURCE_PRIVILEGES[$name], true)) {
                    throw new ParserException('Invalid combination of resource and privilege.', $tokenList);
                }
            }
        }
    }

    /**
     * RENAME USER old_user TO new_user
     *     [, old_user TO new_user] ...
     */
    public function parseRenameUser(TokenList $tokenList): RenameUserCommand
    {
        $tokenList->expectKeywords(Keyword::RENAME, Keyword::USER);

        $users = [];
        $newUsers = [];
        do {
            $users[] = $this->parseUser($tokenList);
            $tokenList->expectKeyword(Keyword::TO);
            $newUsers[] = $tokenList->expectUserName();
        } while ($tokenList->hasSymbol(','));

        return new RenameUserCommand($users, $newUsers);
    }

    /**
     * REVOKE [IF EXISTS]
     *     priv_type [(column_list)] [, priv_type [(column_list)]] ...
     *     ON [object_type] priv_level
     *     FROM user_or_role [, user_or_role] ...
     *     [IGNORE UNKNOWN USER]
     *
     * REVOKE [IF EXISTS] ALL [PRIVILEGES], GRANT OPTION
     *     FROM user_or_role [, user_or_role] ...
     *     [IGNORE UNKNOWN USER]
     *
     * REVOKE [IF EXISTS] PROXY ON user_or_role
     *     FROM user_or_role [, user_or_role] ...
     *     [IGNORE UNKNOWN USER]
     *
     * REVOKE [IF EXISTS] role [, role ] ...
     *     FROM user_or_role [, user_or_role ] ...
     *     [IGNORE UNKNOWN USER]
     *
     * @return UserCommand&Statement
     */
    public function parseRevoke(TokenList $tokenList): UserCommand
    {
        $tokenList->expectKeyword(Keyword::REVOKE);
        $ifExists = $tokenList->hasKeywords(Keyword::IF, Keyword::EXISTS);

        if ($tokenList->hasKeyword(Keyword::ALL)) {
            // REVOKE ALL
            if (!$tokenList->seekKeyword(Keyword::ON, 15)) {
                $tokenList->passKeyword(Keyword::PRIVILEGES);
                $tokenList->expectSymbol(',');
                $tokenList->expectKeywords(Keyword::GRANT, Keyword::OPTION, Keyword::FROM);
                $users = $this->parseUsersList($tokenList);
                $ignoreUnknownUser = $tokenList->hasKeywords(Keyword::IGNORE, Keyword::UNKNOWN, Keyword::USER);

                return new RevokeAllCommand($users, $ifExists, $ignoreUnknownUser);
            } else {
                $tokenList->rewind(-1);
            }
        }
        if ($tokenList->hasKeywords(Keyword::PROXY)) {
            // REVOKE PROXY ON
            $tokenList->expectKeyword(Keyword::ON);
            $proxy = $this->parseUser($tokenList);
            $tokenList->expectKeyword(Keyword::FROM);
            $users = $this->parseUsersList($tokenList);
            $ignoreUnknownUser = $tokenList->hasKeywords(Keyword::IGNORE, Keyword::UNKNOWN, Keyword::USER);

            return new RevokeProxyCommand($proxy, $users, $ifExists, $ignoreUnknownUser);
        } elseif ($tokenList->seekKeywordBefore(Keyword::ON, Keyword::FROM)) {
            // REVOKE ... ON ... FROM
            $privileges = $this->parsePrivilegesList($tokenList, $ifExists);
            $resource = $this->parseResource($tokenList);
            $this->checkPrivilegeAndResource($tokenList, $resource, $privileges);

            $tokenList->expectKeyword(Keyword::FROM);
            $users = $this->parseUsersList($tokenList);
            $ignoreUnknownUser = $tokenList->hasKeywords(Keyword::IGNORE, Keyword::UNKNOWN, Keyword::USER);

            return new RevokeCommand($privileges, $resource, $users, $ifExists, $ignoreUnknownUser);
        } else {
            // REVOKE ... FROM
            $roles = $this->parseRolesList($tokenList);
            $tokenList->expectKeyword(Keyword::FROM);
            $users = $this->parseUsersList($tokenList);
            $ignoreUnknownUser = $tokenList->hasKeywords(Keyword::IGNORE, Keyword::UNKNOWN, Keyword::USER);

            return new RevokeRoleCommand($roles, $users, $ifExists, $ignoreUnknownUser);
        }
    }

    /**
     * SET DEFAULT ROLE
     *     {NONE | ALL | role [, role ] ...}
     *     TO user [, user ] ...
     */
    public function parseSetDefaultRole(TokenList $tokenList): SetDefaultRoleCommand
    {
        $tokenList->expectKeywords(Keyword::SET, Keyword::DEFAULT, Keyword::ROLE);
        $roles = $tokenList->getKeywordEnum(UserDefaultRolesSpecification::class);
        $rolesList = null;
        if ($roles === null) {
            $rolesList = $this->parseRolesList($tokenList);
        }

        $tokenList->expectKeyword(Keyword::TO);
        $users = $this->parseUsersList($tokenList);

        return new SetDefaultRoleCommand($users, $roles, $rolesList);
    }

    /**
     * 8.0 https://dev.mysql.com/doc/refman/8.0/en/set-password.html
     * SET PASSWORD [FOR user] auth_option
     *   [REPLACE 'current_auth_string']
     *   [RETAIN CURRENT PASSWORD]
     *
     * auth_option: {
     *     = 'auth_string'
     *   | TO RANDOM
     * }
     *
     * 5.7 https://dev.mysql.com/doc/refman/5.7/en/set-password.html
     * SET PASSWORD [FOR user] = password_option
     *
     * password_option: {
     *     PASSWORD('auth_string')
     *   | 'auth_string'
     * }
     */
    public function parseSetPassword(TokenList $tokenList): SetPasswordCommand
    {
        $tokenList->expectKeywords(Keyword::SET, Keyword::PASSWORD);
        $user = null;
        if ($tokenList->hasKeyword(Keyword::FOR)) {
            $user = $this->parseUser($tokenList);
        }

        $passwordFunction = $password = $replace = null;
        if ($tokenList->hasOperator(Operator::EQUAL)) {
            $passwordFunction = $tokenList->using(null, 50700)
                ? $tokenList->getAnyKeyword(Keyword::PASSWORD)
                : $tokenList->getAnyKeyword(Keyword::PASSWORD, Keyword::OLD_PASSWORD);
            if ($passwordFunction !== null) {
                $tokenList->expectSymbol('(');
            }
            $password = $tokenList->expectString();
            if ($passwordFunction !== null) {
                $tokenList->expectSymbol(')');
            }
        } else {
            $tokenList->expectKeywords(Keyword::TO, Keyword::RANDOM);
        }

        if ($tokenList->hasKeyword(Keyword::REPLACE)) {
            $replace = $tokenList->expectString();
        }
        $retain = $tokenList->hasKeywords(Keyword::RETAIN, Keyword::CURRENT, Keyword::PASSWORD);

        return new SetPasswordCommand($user, $passwordFunction, $password, $replace, $retain);
    }

    /**
     * SET ROLE {
     *     DEFAULT
     *   | NONE
     *   | ALL
     *   | ALL EXCEPT role [, role ] ...
     *   | role [, role ] ...
     * }
     */
    public function parseSetRole(TokenList $tokenList): SetRoleCommand
    {
        $tokenList->expectKeywords(Keyword::SET, Keyword::ROLE);
        $role = $this->parseRoleSpecification($tokenList);

        return new SetRoleCommand($role);
    }

    private function parseRoleSpecification(TokenList $tokenList): RolesSpecification
    {
        $keyword = $tokenList->getAnyKeyword(Keyword::DEFAULT, Keyword::NONE, Keyword::ALL);
        $except = false;
        if ($keyword === Keyword::ALL) {
            $except = $tokenList->hasKeyword(Keyword::EXCEPT);
        }
        $type = $keyword !== null
            ? ($except ? RolesSpecificationType::ALL_EXCEPT : $keyword)
            : RolesSpecificationType::LIST;

        $roles = null;
        if ($except !== false || $keyword === null) {
            $roles = $this->parseRolesList($tokenList);
        }

        return new RolesSpecification(new RolesSpecificationType($type), $roles);
    }

    /**
     * @return UserName|FunctionCall
     */
    private function parseUser(TokenList $tokenList)
    {
        if ($tokenList->hasKeyword(Keyword::CURRENT_USER)) {
            if ($tokenList->hasSymbol('(')) {
                $tokenList->passSymbol(')');
            }
            return new FunctionCall(new BuiltInFunction(BuiltInFunction::CURRENT_USER));
        } else {
            return $tokenList->expectUserName();
        }
    }

    /**
     * @return non-empty-list<UserName|FunctionCall>
     */
    private function parseUsersList(TokenList $tokenList): array
    {
        $users = [];
        do {
            $users[] = $this->parseUser($tokenList);
        } while ($tokenList->hasSymbol(','));

        return $users;
    }

    /**
     * @return non-empty-list<UserName>
     */
    private function parseRolesList(TokenList $tokenList): array
    {
        $roles = [];
        do {
            $roles[] = $tokenList->expectUserName(true);
        } while ($tokenList->hasSymbol(','));

        return $roles;
    }

}
