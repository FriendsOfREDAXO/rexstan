<?php declare(strict_types = 1);

// phpcs:disable Squiz.PHP.Heredoc.NotAllowed
// phpcs:disable SlevomatCodingStandard.PHP.RequireNowdoc.RequiredNowdoc

namespace SqlFtw\Parser;

use SqlFtw\Tests\Assert;

require __DIR__ . '/../bootstrap.php';


// https://github.com/SQLFTW/sqlftw/issues/1
Assert::validCommand("
UPDATE `options_lng` AS `olu`
    INNER JOIN (
        SELECT `o`.`id` AS `optionId`, `l`.`indexName` AS `language`, `o`.`value`
        FROM `options` AS `o`
             INNER JOIN `languages` AS `l` ON TRUE
        WHERE `o`.`indexName` IN ('warning_in_the_cart', 'information_banner_text')
    ) AS `data` ON `data`.`optionId` = `olu`.`optionId`
SET `olu`.`value` = `data`.`value`");

// https://github.com/SQLFTW/sqlftw/issues/2
Assert::validCommand("
INSERT IGNORE INTO `options_lng` (`optionId`, `language`, `value`)
SELECT `o`.`id` AS `optionId`, `l`.`indexName` AS `language`, `o`.`value`
FROM `options` AS `o`
    INNER JOIN `languages` AS `l` ON TRUE
WHERE `o`.`indexName` IN ('warning_in_the_cart', 'information_banner_text')
ORDER BY `l`.`indexName`");

// https://github.com/SQLFTW/sqlftw/issues/3
Assert::validCommand("-- some comment");

// https://github.com/SQLFTW/sqlftw/issues/4
Assert::validCommand(<<<XXX
UPDATE page_elements
SET settings = regexp_replace(settings, '"limit":[0-9]+', '"limit":10')
WHERE isSection = 1
    AND (settings REGEXP '"limit":[0-9]{3,}'
        OR settings REGEXP '"limit":[2-9][0-9]'
        OR settings REGEXP '"limit":1[1-9]'
    )
XXX
);

// https://github.com/SQLFTW/sqlftw/issues/6
Assert::validCommand("
UPDATE `options` SET `value` = 180 
WHERE (`indexName` = 'truncate_short_description') 
    AND (`value` = 0 OR `value` > 180) 
    AND (@current_template = '07' OR @current_template = '09' OR @current_template = '10' OR @current_template = '11' OR @current_template = '12' OR @current_template = '13' OR @current_template = '14')");

// https://github.com/SQLFTW/sqlftw/issues/7
Assert::validCommand("
DELETE sm
FROM section_map sm
    LEFT JOIN section_map_parameter_rel smp ON smp.pageId = sm.id
WHERE sm.pageType = 4 AND smp.pageId IS NULL");

// https://github.com/SQLFTW/sqlftw/issues/8
Assert::validCommand("
CREATE TABLE `order_packages` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `orderId` mediumint(8) unsigned NOT NULL,
    `packingId` int(11) unsigned DEFAULT NULL,
    `type` varchar(255) COLLATE utf8_czech_ci NOT NULL,
    `name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
    `width` decimal(10,2) DEFAULT NULL,
    `height` decimal(10,2) DEFAULT NULL,
    `depth` decimal(10,2) DEFAULT NULL,
    `weight` decimal(10,2) DEFAULT NULL,
    `unit` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
    `created` datetime NOT NULL,
    `packingOrder` int(10) unsigned DEFAULT NULL,
    `status` varchar(255) COLLATE utf8_czech_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `orderId` (`orderId`),
    CONSTRAINT `order_packages_packs` FOREIGN KEY (`packingId`) REFERENCES `packs` (`id`),
    CONSTRAINT `order_packages_orders` FOREIGN KEY (`orderId`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;");

// https://github.com/SQLFTW/sqlftw/issues/9
Assert::validCommand("CREATE INDEX my_index ON my_table (my_column) ALGORITHM=INPLACE LOCK=NONE;");
