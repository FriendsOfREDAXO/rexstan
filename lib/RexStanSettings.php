<?php

namespace rexstan;

use rex;
use rex_addon;
use rex_config;
use rex_config_form;
use rex_editor;
use rex_file;
use rex_path;
use rex_version;

use function dirname;

final class RexStanSettings
{
    public const ANALYSIS_BASELINE_FILE = 'analysis-baseline.neon';

    public const BASELINE_ENABLED = 1;
    public const BASELINE_REPORT_UNMATCHED = 2;
    /**
     * @var array<string, string>
     */
    private static $phpstanExtensions = [
        'REDAXO SuperGlobals' => 'config/rex-superglobals.neon',
        'Bleeding-Edge' => 'vendor/phpstan/phpstan/conf/bleedingEdge.neon',
        'Strict-Mode' => 'vendor/phpstan/phpstan-strict-rules/rules.neon',
        'Deprecation Warnings' => 'vendor/phpstan/phpstan-deprecation-rules/rules.neon',
        'PHPUnit' => 'config/phpstan-phpunit.neon',
        'phpstan-dba' => 'config/phpstan-dba.neon',
        'cognitive complexity' => 'config/cognitive-complexity.neon',
        'report mixed' => 'config/code-complexity.neon',
        'dead code' => 'config/dead-code.neon',
    ];

    /**
     * @var array<string, string>
     */
    private static $phpstanExtensionDocLinks = [
        'Bleeding-Edge' => 'https://phpstan.org/blog/what-is-bleeding-edge',
        'Strict-Mode' => 'https://github.com/phpstan/phpstan-strict-rules#readme',
        'Deprecation Warnings' => 'https://github.com/phpstan/phpstan-deprecation-rules#readme',
        'PHPUnit' => 'https://github.com/phpstan/phpstan-phpunit#readme',
        'phpstan-dba' => 'https://staabm.github.io/archive.html#phpstan-dba',
        'cognitive complexity' => 'https://tomasvotruba.com/blog/2018/05/21/is-your-code-readable-by-humans-cognitive-complexity-tells-you/',
    ];

    /**
     * @var array<int, string>
     */
    private static $phpVersionListUpTp5_14 = [
        70333 => '7.3.x [Mindestanforderung für REDAXO bis 5.14]',
        70433 => '7.4.x',
        80028 => '8.0.x',
        80115 => '8.1.x',
        80203 => '8.2.x',
    ];

    /**
     * @var array<int, string>
     */
    private static $phpVersionListFrom5_15 = [
        80115 => '8.1.x [Mindestanforderung für REDAXO ab 5.15]',
        80203 => '8.2.x',
    ];

    /**
     * @return rex_config_form
     */
    public static function createForm()
    {
        $extensions = [];
        foreach (self::$phpstanExtensions as $label => $path) {
            $extensions[self::relativePath(rex_path::addon('rexstan', $path))] = $label;
        }

        $extensionLinks = [];
        foreach (self::$phpstanExtensionDocLinks as $label => $link) {
            $extensionLinks[] = '<a href="'.$link.'">'.$label.'</a>';
        }

        $scanTargets = [];
        foreach (rex_addon::getAvailableAddons() as $availableAddon) {
            $scanTargets[self::relativePath($availableAddon->getPath())] = $availableAddon->getName();

            if ($availableAddon->getName() === 'developer') {
                $modulesDir = DeveloperAddonIntegration::getModulesDir();
                if ($modulesDir !== null) {
                    $scanTargets[self::relativePath($modulesDir)] = 'developer: modules';
                }

                $templatesDir = DeveloperAddonIntegration::getTemplatesDir();
                if ($templatesDir !== null) {
                    $scanTargets[self::relativePath($templatesDir)] = 'developer: templates';
                }
            }
        }

        $sapiVersion = PHP_VERSION_ID;
        $cliVersion = RexCmd::getCliPhpVersion();

        if (rex_version::compare(rex::getVersion(), '5.15.0-dev', '>=')) {
            $phpVersions = self::$phpVersionListFrom5_15;
        } else {
            $phpVersions = self::$phpVersionListUpTp5_14;
        }
        foreach ($phpVersions as $key => &$label) {
            if (RexPhpVersion::isEqualMajorMinor($key, $sapiVersion)) {
                $label .= ' [aktuelle Webserver-Version (WEB-SAPI)]';
            }
            if (RexPhpVersion::isEqualMajorMinor($key, $cliVersion)) {
                $label .= ' [aktuelle Konsolen-Version (CLI-SAPI)]';
            }
        }

        $baselineFile = self::getAnalysisBaselinePath();
        $url = rex_editor::factory()->getUrl($baselineFile, 0);

        $baselineButton = '';
        if ($url !== null) {
            $baselineButton .= '<a href="'. $url .'">Baseline im Editor &ouml;ffnen</a> - ';
        }

        self::fixAbsoluteToRelativePaths();

        $form = rex_config_form::factory('rexstan');
        $field = $form->addInputField('number', 'level', null, ['class' => 'form-control', 'min' => 0, 'max' => 9]);
        $field->setLabel('Level');
        $field->setNotice('von 0 einfach, bis 9 sehr strikt - <a href="https://phpstan.org/user-guide/rule-levels">PHPStan Rule Levels</a>');

        $field = $form->addCheckboxField('baseline');
        $field->addOption('Baseline verwenden', self::BASELINE_ENABLED);
        $field->addOption('Ungenutzte Baseline Probleme melden', self::BASELINE_REPORT_UNMATCHED);
        $field->setNotice($baselineButton .'Weiterlesen: <a href="https://phpstan.org/user-guide/baseline">Baseline</a>, <a href="https://phpstan.org/user-guide/ignoring-errors#reporting-unused-ignores">reportUnmatchedIgnoredErrors</a>');

        $field = $form->addSelectField('addons', null, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'required' => 'required']); // die Klasse selectpicker aktiviert den Selectpicker von Bootstrap
        $field->setAttribute('multiple', 'multiple');
        $field->setLabel('AddOns');
        $field->setNotice('AddOns, die untersucht werden sollen');
        $select = $field->getSelect();
        $select->addOptions($scanTargets);

        $field = $form->addSelectField('extensions', null, ['class' => 'form-control selectpicker']);
        $field->setAttribute('multiple', 'multiple');
        $field->setLabel('PHPStan Extensions');
        $field->setNotice('Weiterlesen bzgl. der verf&uuml;gbaren Extensions: '.implode(', ', $extensionLinks));
        $select = $field->getSelect();
        $select->addOptions($extensions);

        $field = $form->addSelectField('phpversion', null, ['class' => 'form-control selectpicker']);
        $field->setLabel('PHP-Version');
        $field->setNotice('<a href="https://phpstan.org/config-reference#phpversion">Referenz PHP-Version</a> für die Code-Analyse');
        $select = $field->getSelect();
        $select->addOptions($phpVersions);

        return $form;
    }

    public static function getAnalysisBaselinePath(): string
    {
        $addon = rex_addon::get('rexstan');
        $dataDir = $addon->getDataPath();
        $filePath = $dataDir .self::ANALYSIS_BASELINE_FILE;

        if (!is_file($filePath)) {
            rex_file::put($filePath, '');
        }

        return $filePath;
    }

    /**
     * @return string
     */
    public static function outputSettings()
    {
        $sapiVersion = PHP_VERSION_ID;
        $cliVersion = RexCmd::getCliPhpVersion();

        if (rex_version::compare(rex::getVersion(), '5.15.0-dev', '>=')) {
            $phpVersions = self::$phpVersionListFrom5_15;
        } else {
            $phpVersions = self::$phpVersionListUpTp5_14;
        }

        $phpVersion = $phpVersions[RexStanUserConfig::getPhpVersion()];

        if (RexPhpVersion::isEqualMajorMinor(RexStanUserConfig::getPhpVersion(), $sapiVersion)) {
            $phpVersion .= ' [aktuelle Webserver-Version (WEB-SAPI)]';
        }
        if (RexPhpVersion::isEqualMajorMinor(RexStanUserConfig::getPhpVersion(), $cliVersion)) {
            $phpVersion .= ' [aktuelle Konsolen-Version (CLI-SAPI)]';
        }

        $output = '<table class="table table-striped table-hover">';
        $output .= '<tbody>';

        $output .= '<tr><td>Level</td><td>' . RexStanUserConfig::getLevel() . '</td></tr>';
        $output .= '<tr><td>AddOns</td><td>' . self::getAddOns() . '</td></tr>';
        $output .= '<tr><td>Extensions</td><td>' . self::getExtensions() . '</td></tr>';
        $output .= '<tr><td>PHP-Version</td><td>' . $phpVersion . '</td></tr>';

        $output .= '</tbody>';
        $output .= '</table>';

        return $output;
    }

    public static function relativePath(string $path, ?string $base = null): string
    {
        $relativeAddonPath = rex_path::relative($base ?? rex_path::addonData('rexstan'));
        $prefix = str_repeat('..' . DIRECTORY_SEPARATOR, substr_count($relativeAddonPath, DIRECTORY_SEPARATOR));

        return $prefix . rex_path::relative($path);
    }

    public static function absolutePath(string $relativePath): ?string {
        $absPath = realpath(rex_path::addonData('rexstan'). $relativePath);

        if ($absPath === false) {
            return null;
        }

        return $absPath;
    }

    private static function getAddOns(): string
    {
        $scanTargets = [];
        foreach (RexStanUserConfig::getPaths() as $scanpath) {
            foreach (rex_addon::getAvailableAddons() as $availableAddon) {
                if (basename($scanpath) === $availableAddon->getName()) {
                    $scanTargets[] = $availableAddon->getName();
                    break;
                }
                if (basename(dirname($scanpath)) === 'developer') {
                    if (basename($scanpath) === 'modules' || basename($scanpath) === 'templates') {
                        $scanTargets[] = 'developer:' . basename($scanpath);
                        break;
                    }
                }
            }
        }
        $addons = implode(', ', $scanTargets);
        return $addons;
    }

    private static function getExtensions(): string
    {
        $extensions = [];
        $config_extensions = RexStanUserConfig::getIncludes();
        foreach ($config_extensions as $extpath) {
            foreach (self::$phpstanExtensions as $label => $path) {
                if (basename($extpath) === basename($path)) {
                    $extensions[] = $label;
                    break;
                }
            }
        }
        return implode(', ', $extensions);
    }

    /**
     * Migrate settings which were stored with absolute paths in the past to relative ones.
     */
    private static function fixAbsoluteToRelativePaths(): void
    {
        $absolutePath = rex_path::base();

        foreach (['addons', 'extensions'] as $key) {
            $config = rex_config::get('rexstan', $key);
            if ($config === null) {
                continue;
            }
            if (stripos($config, $absolutePath) === false) {
                continue;
            }

            $config = explode('|', trim($config, '|'));
            foreach ($config as $i => $path) {
                $config[$i] = self::relativePath($path);
            }

            rex_config::set('rexstan', $key, '|'.implode('|', $config).'|');
        }
    }
}
