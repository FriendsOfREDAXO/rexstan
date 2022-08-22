<?php
/**
 * Formular-Klasse basierend auf rex_config_form mit folgenden Anpassungen:.
 *
 *   - class->getValue()
 *     Daten einlesen für ausgewählte Felder aus phpstan-Konfigurationsdateien
 *       a) user_config.neon
 *       b) default_config.neon (= Fallback)
 *     Für alle anderen via rex_config
 *   - class->save()
 *     Daten werden für ausgewählte Felder in user_config.neon gespeichert.
 *     Alle anderen via rex_config
 *   - class->init()
 *     Baut die Eingabefelder in das Formular ein
 *     Bereitet Daten für die Felder auf
 *
 * Immer mal wieder anzupassende Auswahllisten (z.B. phpVersionen, Extensons)
 * sind im vorderen Teil der Klasse zu finden. Init() bereitet daraus
 * Selects auf.
 *
 * Formularausgabe:
 *      $form = RexStanSettings::factory( 'rexstan' );
 *      $fragment = new \rex_fragment();
 *      $fragment->setVar('class', 'edit', false);
 *      $fragment->setVar('title', 'titel', false);
 *      $fragment->setVar('body', $form->get(), false);
 *      echo $fragment->parse('core/page/section.php');.
 */

namespace redaxo\phpstan;

use rex_addon;
use rex_config;
use rex_config_form;
use rex_extension;
use rex_extension_point;
use rex_path;
use RexStanUserConfig;

use function is_string;

final class RexStanSettings extends rex_config_form
{
    /**
     * Listen der derzeit verfügbaren phpstan-Extensions ... ['label' => 'pfad_im_addon'].
     *
     * @var array<string>
     */
    private static $phpstanExtensions = [
        'Bleeding-Edge' => 'vendor/phpstan/phpstan/conf/bleedingEdge.neon',
        'Strict-Mode' => 'vendor/phpstan/phpstan-strict-rules/rules.neon',
        'Deprecation Warnings' => 'vendor/phpstan/phpstan-deprecation-rules/rules.neon',
        'PHPUnit' => 'vendor/phpstan/phpstan-phpunit/rules.neon',
        'phpstan-dba' => 'lib/phpstan-dba.neon',
        'cognitive complexity' => 'lib/cognitive-complexity.neon',
    ];

    /**
     * ... und der Links zu Erläuterungen. ['label' => 'url'].
     *
     * @var array<string>
     */
    private static $phpstanExtensionDocLinks = [
        'Bleeding-Edge' => 'https://phpstan.org/blog/what-is-bleeding-edge',
        'Strict-Mode' => 'https://github.com/phpstan/phpstan-strict-rules#readme',
        'Deprecation Warnings' => 'https://github.com/phpstan/phpstan-deprecation-rules#readme">Deprecation-Warnings',
        'PHPUnit' => 'https://github.com/phpstan/phpstan-phpunit#readme">PHPUnit',
        'phpstan-dba' => 'https://staabm.github.io/archive.html#phpstan-dba',
        'cognitive complexity' => 'https://tomasvotruba.com/blog/2018/05/21/is-your-code-readable-by-humans-cognitive-complexity-tells-you/',
    ];

    /**
     * Liste der derzeit relevanten PHP-Versionen. [versionId => 'label'].
     *
     * @var array<string>
     */
    private static $phpVersionList = [
       70333 => '7.3 [Mindestanforderung für REDAXO]',
       70430 => '7.4',
       80022 => '8.0',
       80109 => '8.1',
       80200 => '8.2',
    ];

    /**
     * Initialisiert das Formular.
     *
     * @return void
     */
    public function init()
    {
        $extensions = [];
        foreach (self::$phpstanExtensions as $label => $path) {
            $extensions[rex_path::addon('rexstan', $path)] = $label;
        }

        $extensionLinks = [];
        foreach (self::$phpstanExtensionDocLinks as $label => $link) {
            $extensionLinks[] = '<a href="'.$link.'">'.$label.'</a>';
        }

        /**
         * Liste der analysierbaren Verzeichnisse (= Addons) ermitteln
         * Sonderfall: developer-Addon.
         */

        $scanTargets = [];
        foreach (rex_addon::getAvailableAddons() as $availableAddon) {
            $scanTargets[$availableAddon->getPath()] = $availableAddon->getName();

            if ('developer' === $availableAddon->getName() && class_exists(rex_developer_manager::class)) {
                $scanTargets[rex_developer_manager::getBasePath() .'/modules/'] = 'developer: modules';
                $scanTargets[rex_developer_manager::getBasePath() .'/templates/'] = 'developer: templates';
            }
        }

        /**
         * Die aktuelle PHP-Version des Servers (SAPI) markieren.
         * Die aktuelle PHP-Version in Terminal/Konsole (CLI) markieren
         */

        $sapiVersion = (int) (PHP_VERSION_ID / 100);
        $cliVersion = (int) shell_exec('php -r \'echo PHP_VERSION_ID;\'');
        $cliVersion = (int) ($cliVersion/100);

        $phpVersions = self::$phpVersionList;
        foreach( $phpVersions as $key=>&$label ) {
            $key = (int) $key/100;
            if( $key === $sapiVersion) {
                $label .= ' [aktuelle Webserver-Version (SAPI)]';
            }
            if( $key === $cliVersion) {
                $label .= ' [aktuelle Konsolen-Version (CLI)]';
            }
        }

        $field = $this->addInputField('number', 'level', null, ['class' => 'form-control', 'min' => 0, 'max' => 9]);
        $field->setLabel('Level');
        $field->setNotice('0 is the loosest and 9 is the strictest - <a href="https://phpstan.org/user-guide/rule-levels">see PHPStan Rule Levels</a>');

        $field = $this->addSelectField('paths', null, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'required' => 'required']); // die Klasse selectpicker aktiviert den Selectpicker von Bootstrap
        $field->setAttribute('multiple', 'multiple');
        $field->setLabel('AddOns');
        $field->setNotice('AddOns, die untersucht werden sollen');
        $select = $field->getSelect();
        $select->addOptions($scanTargets);

        $field = $this->addSelectField('includes', null, ['class' => 'form-control selectpicker']);
        $field->setAttribute('multiple', 'multiple');
        $field->setLabel('PHPStan Extensions');
        $field->setNotice('Weiterlesen bzgl. der verf&uuml;gbaren Extensions: '.implode(', ', $extensionLinks));
        $select = $field->getSelect();
        $select->addOptions($extensions);

        $field = $this->addSelectField('phpVersion', null, ['class' => 'form-control selectpicker']);
        $field->setLabel('PHP-Version');
        $field->setNotice('Referenz-Version für die Code-Analyse');
        $select = $field->getSelect();
        $select->addOptions($phpVersions);
    }

    /**
     * Feldwerte abrufen aus userConf, defaultConf oder rex_config.
     *
     * Die Parent-Methode ruft ausschließlich Werte aus der Tabelle rex_config ab.
     * Die überschriebene Methode versucht zunächst auf phpstan-Settings zuzugreifen.
     * Kommt der NAme nicht in der Liste (switch..) vor, wird unter dem Namen auf
     * rex_config zurückgegriffen.
     *
     * @param string $name  Der Feldname
     *
     * @return mixed  Der zum Feld gehörende Wert; default=''
     */
    protected function getValue($name)
    {
        switch ($name) {
            // Zunächst die phpstan-Parameter abrufen
            case 'level':
                $value = RexStanUserConfig::getLevel();
                break;
            case 'paths':
                $value = RexStanUserConfig::getPaths();
                break;
            case 'phpVersion':
                $value = RexStanUserConfig::getPhpVersion();
                break;
            case 'includes':
                $value = RexStanUserConfig::getIncludes();
                break;

                // Default: als rex_config-Wert behandeln
            default:
                $value = parent::getValue($name);
        }

        return $value;
    }

    /**
     * Speichert die Änderungen des Settings-Formuars.
     *
     * Die Parent-Methode schreibt die Werte in die Tabelle rex_config.
     * Hier werden zunächst die für phpstan vorgesehenen Werte abgefangen und in die $userConf
     * übertragen. Am Ende wird $userConf auf die Platte geschrieben.
     * Alle übrigen Felder werden als rex_config gespeichert.
     */
    protected function save(): bool
    {
    
        // für alle Fälle
        $level = RexStanUserConfig::getLevel();
        $paths = RexStanUserConfig::getPaths();
        $phpVersion = RexStanUserConfig::getPhpVersion();
        $includes = RexStanUserConfig::getIncludes();
    
        foreach ($this->getSaveElements() as $fieldsetElements) {
            foreach ($fieldsetElements as $element) {
                // read-only-fields nicht speichern
                if ($element->isReadOnly()) {
                    continue;
                }

                $fieldName = $element->getFieldName();
                $fieldValue = $element->getSaveValue();

                if (is_string($fieldValue)) {
                    $fieldValue = trim($fieldValue);
                }

                switch ($fieldName) {
                    // Zunächst die phpstan-Parameter auslesen und ggf aufbereiten
                    case 'level':
                        $level = (int) $fieldValue;
                        break;
                    case 'paths':
                        $paths = array_filter(explode('|', trim((string) $fieldValue, '|')), 'strlen'); // String |x|y| rückumwandeln in [x,y]
                        break;
                    case 'phpVersion':
                        $phpVersion = (int) $fieldValue;
                        break;
                    case 'includes':
                        $includes = array_filter(explode('|', trim((string) $fieldValue, '|')), 'strlen'); // String |x|y| rückumwandeln in [x,y]
                        break;

                        // Default: als rex_config-Wert behandeln
                    default:
                        rex_config::set('rexstan', $fieldName, $fieldValue);
                }
            }
        }

        // Für die phpstan-Konfigurationseinträge: separat sichern
        RexStanUserConfig::save($level, $paths, $includes, $phpVersion);
        
        // Just a notification via EP
        rex_extension::registerPoint(new rex_extension_point('REXSTAN_SETTINGS_SAVED', '', []));

        return true;
    }
}
