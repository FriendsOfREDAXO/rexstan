<?php

namespace redaxo\phpstan;

use rex_addon;
use rex_config;
use rex_config_form;
use rex_developer_manager;
use rex_path;

use function count;

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
        foreach (rex_addon::getAvailableAddons() as $availableAddon) {
            $availablePlugins = $availableAddon->getAvailablePlugins();
            $optGroup = 0 < count($availablePlugins) || ('developer' === $availableAddon->getName() && class_exists(rex_developer_manager::class));
            if ($optGroup) {
                $select->addOptgroup($availableAddon->getName());
            }
            $select->addOption($availableAddon->getName(), $availableAddon->getPath());
            if ($optGroup) {
                foreach ($availablePlugins as $availablePlugin) {
                    $select->addOption($availableAddon->getName() . ' ⇒ ' . $availablePlugin->getName(), $availablePlugin->getPath());
                }
                if ('developer' === $availableAddon->getName()) {
                    $select->addOption('developer: modules', rex_developer_manager::getBasePath() .'/modules/');
                    $select->addOption('developer: templates', rex_developer_manager::getBasePath() .'/templates/');
                }
                $select->endOptgroup();
            }
        }

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
}
