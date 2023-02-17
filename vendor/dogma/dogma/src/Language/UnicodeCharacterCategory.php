<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Language;

use Dogma\Enum\StringEnum;

class UnicodeCharacterCategory extends StringEnum
{

    public const OTHER = 'C';
    public const CONTROL = 'Cc';
    public const FORMAT = 'Cf';
    public const PRIVATE_USE = 'Co';
    public const SURROGATE = 'Cs';

    public const LETTER = 'L';
    public const LOWER_CASE_LETTER = 'Ll';
    public const MODIFIER_LETTER = 'Lm';
    public const OTHER_LETTER = 'Lo';
    public const TITLE_CASE_LETTER = 'Lt';
    public const UPPER_CASE_LETTER = 'Lu';

    public const MARK = 'M';
    public const SPACING_MARK = 'Mc';
    public const ENCLOSING_MARK = 'Me';
    public const NONSPACING_MARK = 'Mn';

    public const NUMBER = 'N';
    public const DECIMAL_NUMBER = 'Nd';
    public const LETTER_NUMBER = 'Nl';
    public const OTHER_NUMBER = 'No';

    public const PUNCTUATION = 'P';
    public const CONNECTOR_PUNCTUATION = 'Pc';
    public const DASH_PUNCTUATION = 'Pd';
    public const CLOSE_PUNCTUATION = 'Pe';
    public const FINAL_PUNCTUATION = 'Pf';
    public const INITIAL_PUNCTUATION = 'Pi';
    public const OTHER_PUNCTUATION = 'Po';
    public const OPEN_PUNCTUATION = 'Ps';

    public const SYMBOL = 'S';
    public const CURRENCY_SYMBOL = 'Sc';
    public const MODIFIER_SYMBOL = 'Sk';
    public const MATH_SYMBOL = 'Sm';
    public const OTHER_SYMBOL = 'So';

    public const SEPARATOR = 'Z';
    public const LINE_SEPARATOR = 'Zl';
    public const PARAGRAPH_SEPARATOR = 'Zp';
    public const SPACE_SEPARATOR = 'Zs';

    /** @var string[] */
    private static $names = [
        self::OTHER => 'Other',
        self::CONTROL => 'Control',
        self::FORMAT => 'Format',
        self::PRIVATE_USE => 'Private Use',
        self::SURROGATE => 'Surrogate',
        self::LETTER => 'Letter',
        self::LOWER_CASE_LETTER => 'Lower case Letter',
        self::MODIFIER_LETTER => 'Modifier Letter',
        self::OTHER_LETTER => 'Other Letter',
        self::TITLE_CASE_LETTER => 'Title case Letter',
        self::UPPER_CASE_LETTER => 'Upper case Letter',
        self::MARK => 'Mark',
        self::SPACING_MARK => 'Spacing Mark',
        self::ENCLOSING_MARK => 'Enclosing Mark',
        self::NONSPACING_MARK => 'Nonspacing Mark',
        self::NUMBER => 'Number',
        self::DECIMAL_NUMBER => 'Decimal Number',
        self::LETTER_NUMBER => 'Letter Number',
        self::OTHER_NUMBER => 'Other Number',
        self::PUNCTUATION => 'Punctuation',
        self::CONNECTOR_PUNCTUATION => 'Connector Punctuation',
        self::DASH_PUNCTUATION => 'Dash Punctuation',
        self::CLOSE_PUNCTUATION => 'Close Punctuation',
        self::FINAL_PUNCTUATION => 'Final Punctuation',
        self::INITIAL_PUNCTUATION => 'Initial Punctuation',
        self::OTHER_PUNCTUATION => 'Other Punctuation',
        self::OPEN_PUNCTUATION => 'Open Punctuation',
        self::SYMBOL => 'Symbol',
        self::CURRENCY_SYMBOL => 'Currency Symbol',
        self::MODIFIER_SYMBOL => 'Modifier Symbol',
        self::MATH_SYMBOL => 'Math Symbol',
        self::OTHER_SYMBOL => 'Other Symbol',
        self::SEPARATOR => 'Separator',
        self::LINE_SEPARATOR => 'Line Separator',
        self::PARAGRAPH_SEPARATOR => 'Paragraph Separator',
        self::SPACE_SEPARATOR => 'Space Separator',
    ];

    public function getName(): string
    {
        return self::$names[$this->getValue()];
    }

}
