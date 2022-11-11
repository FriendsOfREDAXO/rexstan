<?php

namespace rexstan;

use rex_addon;
use rex_config;
use rex_config_form;
use rex_developer_manager;
use rex_path;
use RexStanConfigVO;

final class RexStanSettings
{
    /**
     * @var array<string, string>
     */
    private static $phpstanExtensions = [
        'REDAXO SuperGlobals' => 'config/rex-superglobals.neon',
        'Bleeding-Edge' => 'vendor/phpstan/phpstan/conf/bleedingEdge.neon',
        'Strict-Mode' => 'vendor/phpstan/phpstan-strict-rules/rules.neon',
        'Deprecation Warnings' => 'vendor/phpstan/phpstan-deprecation-rules/rules.neon',
        'PHPUnit' => 'vendor/phpstan/phpstan-phpunit/rules.neon',
        'phpstan-dba' => 'config/phpstan-dba.neon',
        'cognitive complexity' => 'config/cognitive-complexity.neon',
        'code complexity' => 'config/code-complexity.neon',
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
    private static $phpVersionList = [
        70333 => '7.3.x [Mindestanforderung für REDAXO]',
        70430 => '7.4.x',
        80022 => '8.0.x',
        80109 => '8.1.x',
        80200 => '8.2.x',
    ];

    /**
     * @return rex_config_form
     */
    public static function createForm()
    {
        $extensions = [];
        foreach (self::$phpstanExtensions as $label => $path) {
            $extensions[rex_path::addon('rexstan', $path)] = $label;
        }

        $extensionLinks = [];
        foreach (self::$phpstanExtensionDocLinks as $label => $link) {
            $extensionLinks[] = '<a href="'.$link.'">'.$label.'</a>';
        }

        $scanTargets = [];
        foreach (rex_addon::getAvailableAddons() as $availableAddon) {
            $scanTargets[$availableAddon->getPath()] = $availableAddon->getName();

            if ('developer' === $availableAddon->getName() && class_exists(rex_developer_manager::class)) {
                $scanTargets[rex_developer_manager::getBasePath() .'/modules/'] = 'developer: modules';
                $scanTargets[rex_developer_manager::getBasePath() .'/templates/'] = 'developer: templates';
            }
        }

        $sapiVersion = (int) (PHP_VERSION_ID / 100);
        $cliVersion = (int) shell_exec('php -r \'echo PHP_VERSION_ID;\'');
        $cliVersion = (int) ($cliVersion / 100);

        $phpVersions = self::$phpVersionList;
        foreach ($phpVersions as $key => &$label) {
            $key = (int) ($key / 100);

            if ($key === $sapiVersion) {
                $label .= ' [aktuelle Webserver-Version (WEB-SAPI)]';
            }
            if ($key === $cliVersion) {
                $label .= ' [aktuelle Konsolen-Version (CLI-SAPI)]';
            }
        }

        $form = rex_config_form::factory('rexstan');
        $field = $form->addInputField('number', 'level', null, ['class' => 'form-control', 'min' => 0, 'max' => 9]);
        $field->setLabel('Level');
        $field->setNotice('0 is the loosest and 9 is the strictest - <a href="https://phpstan.org/user-guide/rule-levels">see PHPStan Rule Levels</a>');

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

    /**
     * @return RexStanConfigVO
     */
    public static function getSettings()
    {
        $addon = rex_addon::get('rexstan');

        $scanTargets = [];
        $config_scanTargets = explode('|', trim(strval($addon->getConfig('addons')), '|'));
        foreach ($config_scanTargets as $scanpath) {
            foreach (rex_addon::getAvailableAddons() as $availableAddon) {
                if (pathinfo($scanpath)['basename'] === $availableAddon->getName()) {
                    $scanTargets[] = $availableAddon->getName();
                    break;
                }
                if (isset(pathinfo($scanpath)['dirname']) && pathinfo(pathinfo($scanpath)['dirname'])['basename'] === 'developer') {
                    if (pathinfo($scanpath)['basename'] === 'modules' || pathinfo($scanpath)['basename'] === 'templates') {
                        $scanTargets[] = 'developer:' . pathinfo($scanpath)['basename'];
                        break;
                    }
                }
            }
        }

        $extensions = [];
        $config_extensions = explode('|', trim(strval($addon->getConfig('extensions')), '|'));
        foreach ($config_extensions as $extpath) {
            foreach (self::$phpstanExtensions as $label => $path) {
                if (pathinfo($extpath)['basename'] === pathinfo($path)['basename']) {
                    $extensions[] = $label;
                    break;
                }
            }
        }

        $level = intval($addon->getConfig('level'));
        $addons = implode(', ', $scanTargets);
        $extensions = implode(', ', $extensions);

        $sapiVersion = (int) (PHP_VERSION_ID / 100);
        $cliVersion = (int) shell_exec('php -r \'echo PHP_VERSION_ID;\'');
        $cliVersion = (int) ($cliVersion / 100);

        $phpVersion = self::$phpVersionList[$addon->getConfig('phpversion')];
        if ((int) ($addon->getConfig('phpversion') / 100)  === $sapiVersion) {
            $phpVersion .= ' [aktuelle Webserver-Version (WEB-SAPI)]';
        }
        if ((int) ($addon->getConfig('phpversion') / 100) === $cliVersion) {
            $phpVersion .= ' [aktuelle Konsolen-Version (CLI-SAPI)]';
        }

        $settings = new RexStanConfigVO($level, $addons, $extensions, $phpVersion);
        return $settings;
    }

    /**
     * @return string
     */
    public static function outputSettings()
    {
        $settings = self::getSettings();

        $output = '';

        $output = '<table class="table table-striped table-hover">';
        $output .= '<tbody>';

        $output .=  '<tr><td>Level</td><td>' . $settings->getLevel() . '</td></tr>';
        $output .=  '<tr><td>AddOns</td><td>' . $settings->getAddons() . '</td></tr>';
        $output .=  '<tr><td>Extensions</td><td>' . $settings->getExtensions() . '</td></tr>';
        $output .=  '<tr><td>PHP-Version</td><td>' . $settings->getPhpVersion() . '</td></tr>';

        $output .= '</tbody>';
        $output .= '</table>';

        return $output;
    }

}
