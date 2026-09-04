<?php

namespace FriendsOfRedaxo\RexStan;

use function array_sum;
use function round;

/**
 * Buckets PHPStan error identifiers into a rough priority for the analysis
 * summary. PHPStan's JSON output carries no severity beyond the rule
 * identifier itself (e.g. "property.nonObject" vs. "ternary.shortNotAllowed"),
 * so this maps the identifiers actually observed across this project's
 * configured rule sets (core PHPStan + strict-rules + deprecation-rules +
 * the cognitive-complexity/dead-code/type-perfect extensions) to
 * critical/style/maintainability.
 *
 * The mapping is necessarily a judgment call in places - e.g.
 * "booleanAnd.leftNotBoolean" comes from the strict-rules package but is
 * classified as critical here, not style, because passing a non-bool into a
 * boolean context is a real type-safety smell, not merely a style
 * preference. Any identifier not in the list below defaults to critical, so
 * an unclassified/new rule from a future rule-set is never silently hidden
 * as "just style".
 *
 * @phpstan-import-type PhpstanFileResult from RexResultsRenderer
 */
final class RexStanCategorizer
{
    private const CRITICAL = 'critical';
    private const STYLE = 'style';
    private const MAINTAINABILITY = 'maintainability';

    /**
     * @var array<string, string>
     */
    private const IDENTIFIER_CATEGORY = [
        // --- critical: real bug / type-safety / security risk ---
        'offsetAccess.nonOffsetAccessible' => self::CRITICAL,
        'argument.type' => self::CRITICAL,
        'method.nonObject' => self::CRITICAL,
        'binaryOp.invalid' => self::CRITICAL,
        'foreach.nonIterable' => self::CRITICAL,
        'echo.nonString' => self::CRITICAL,
        'offsetAccess.invalidOffset' => self::CRITICAL,
        'if.condNotBoolean' => self::CRITICAL,
        'method.notFound' => self::CRITICAL,
        'rexstan.rexSqlInjection' => self::CRITICAL,
        'booleanNot.exprNotBoolean' => self::CRITICAL,
        'property.nonObject' => self::CRITICAL,
        'assignOp.invalid' => self::CRITICAL,
        'return.type' => self::CRITICAL,
        'assign.propertyType' => self::CRITICAL,
        'booleanAnd.rightNotBoolean' => self::CRITICAL,
        'offsetAccess.notFound' => self::CRITICAL,
        'variable.implicitArray' => self::CRITICAL,
        'booleanAnd.leftNotBoolean' => self::CRITICAL,
        'rexstan.rexSqlSetValue' => self::CRITICAL,
        'staticMethod.notFound' => self::CRITICAL,
        'elseif.condNotBoolean' => self::CRITICAL,
        'classConstant.nonObject' => self::CRITICAL,
        'ternary.condNotBoolean' => self::CRITICAL,
        'variable.undefined' => self::CRITICAL,
        'rexstan.rexSqlGetValue' => self::CRITICAL,
        'foreach.valueOverwrite' => self::CRITICAL,
        'clone.nonObject' => self::CRITICAL,
        'preInc.type' => self::CRITICAL,
        'method.childReturnType' => self::CRITICAL,
        'foreach.keyOverwrite' => self::CRITICAL,
        'callable.nonCallable' => self::CRITICAL,
        'dba.syntaxError' => self::CRITICAL,
        'doWhile.condNotBoolean' => self::CRITICAL,
        'new.nonObject' => self::CRITICAL,
        'preDec.type' => self::CRITICAL,
        'postInc.type' => self::CRITICAL,
        'arguments.count' => self::CRITICAL,
        'staticMethod.nonObject' => self::CRITICAL,
        'class.notFound' => self::CRITICAL,

        // --- style: strict-rules preferences, no acute crash risk ---
        'equal.notAllowed' => self::STYLE,
        'notEqual.notAllowed' => self::STYLE,
        'cast.int' => self::STYLE,
        'cast.string' => self::STYLE,
        'function.strict' => self::STYLE,
        'ternary.shortNotAllowed' => self::STYLE,
        'method.nameCase' => self::STYLE,
        'empty.notAllowed' => self::STYLE,
        'cast.useless' => self::STYLE,
        'encapsedStringPart.nonString' => self::STYLE,
        'nullCoalesce.offset' => self::STYLE,
        'arrayFilter.strict' => self::STYLE,
        'property.tooWideBool' => self::STYLE,
        'varTag.nativeType' => self::STYLE,
        'property.protected' => self::STYLE,
        'method.dynamicName' => self::STYLE,
        'interface.nameCase' => self::STYLE,
        'staticMethod.dynamicCall' => self::STYLE,
        'varTag.type' => self::STYLE,

        // --- maintainability: type-hygiene, dead code, complexity ---
        'typePerfect.noMixedMethodCaller' => self::MAINTAINABILITY,
        'missingType.return' => self::MAINTAINABILITY,
        'missingType.parameter' => self::MAINTAINABILITY,
        'missingType.iterableValue' => self::MAINTAINABILITY,
        'missingType.property' => self::MAINTAINABILITY,
        'complexity.functionLike' => self::MAINTAINABILITY,
        'typePerfect.noArrayAccessOnObject' => self::MAINTAINABILITY,
        'public.method.unused' => self::MAINTAINABILITY,
        'public.property.unused' => self::MAINTAINABILITY,
        'missingType.generics' => self::MAINTAINABILITY,
        'complexity.classLike' => self::MAINTAINABILITY,
        'public.classConstant.unused' => self::MAINTAINABILITY,
        'typePerfect.noMixedPropertyFetcher' => self::MAINTAINABILITY,
        'function.deprecated' => self::MAINTAINABILITY,
        'property.onlyWritten' => self::MAINTAINABILITY,
        'deadCode.unreachable' => self::MAINTAINABILITY,
        'ternary.alwaysFalse' => self::MAINTAINABILITY,
        'ternary.alwaysTrue' => self::MAINTAINABILITY,
        'notEqual.alwaysTrue' => self::MAINTAINABILITY,
        'booleanAnd.alwaysFalse' => self::MAINTAINABILITY,
        'identical.alwaysFalse' => self::MAINTAINABILITY,
        'booleanAnd.leftAlwaysTrue' => self::MAINTAINABILITY,
        'equal.alwaysTrue' => self::MAINTAINABILITY,
        'notIdentical.alwaysTrue' => self::MAINTAINABILITY,
        'booleanNot.alwaysFalse' => self::MAINTAINABILITY,
        'function.alreadyNarrowedType' => self::MAINTAINABILITY,
        'classConstant.internalClass' => self::MAINTAINABILITY,
    ];

    /**
     * @return array<string, array{label: string, hint: string}>
     */
    private static function categoryMeta(): array
    {
        return [
            self::CRITICAL => [
                'label' => '🔴 Kritisch',
                'hint' => 'Typ-/Nullsicherheit, potenzielle Laufzeitfehler, SQL-Risiken',
            ],
            self::STYLE => [
                'label' => '🟡 Code-Style',
                'hint' => 'Strict-Rules-Präferenzen (Vergleichsoperatoren, Cast-Stil, Namenskonventionen)',
            ],
            self::MAINTAINABILITY => [
                'label' => '🔵 Wartbarkeit',
                'hint' => 'Fehlende Typangaben, unbenutzter Code, Komplexität, totes/redundantes Code',
            ],
        ];
    }

    /**
     * @return 'critical'|'style'|'maintainability'
     */
    private static function categorize(?string $identifier): string
    {
        if (null === $identifier) {
            return self::CRITICAL;
        }

        return self::IDENTIFIER_CATEGORY[$identifier] ?? self::CRITICAL;
    }

    /**
     * Renders a compact "by priority" summary table above the per-file detail
     * list, so a run with thousands of messages across many files is
     * scannable at a glance instead of only ever presented as a flat
     * file-by-file list.
     *
     * @param array<string, PhpstanFileResult> $files
     */
    public static function renderSummaryTable(array $files): string
    {
        $counts = [
            self::CRITICAL => 0,
            self::STYLE => 0,
            self::MAINTAINABILITY => 0,
        ];

        foreach ($files as $fileResult) {
            foreach ($fileResult['messages'] as $message) {
                $category = self::categorize($message['identifier'] ?? null);
                ++$counts[$category];
            }
        }

        $total = array_sum($counts);
        if (0 === $total) {
            return '';
        }

        $meta = self::categoryMeta();

        $rows = '';
        foreach ($counts as $category => $count) {
            if (0 === $count) {
                continue;
            }

            $percent = (int) round($count / $total * 100);
            $rows .= '<tr>'
                .'<td>'. $meta[$category]['label'] .'</td>'
                .'<td style="text-align:right;"><strong>'. $count .'</strong> ('. $percent .'%)</td>'
                .'<td class="text-muted">'. rex_escape($meta[$category]['hint']) .'</td>'
                .'</tr>';
        }

        return '<div class="rexstan-category-summary" style="margin-bottom:15px;">'
            .'<table class="table"><tbody>'. $rows .'</tbody></table>'
            .'</div>';
    }
}
