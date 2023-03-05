<?php declare(strict_types = 1);

// spell-check-ignore: abc abcabcabc abcd abcde bc bcde cd dcba ÁŠČŘ á áš áščř áščřáščř č čř ř řčšá š ščř

namespace SqlFtw\Parser;

use SqlFtw\Platform\Platform;
use SqlFtw\Resolver\Cast;
use SqlFtw\Resolver\Functions\Functions;
use SqlFtw\Session\Session;
use SqlFtw\Tests\Assert;

require __DIR__ . '/../../bootstrap.php';

$platform = Platform::get(Platform::MYSQL, '8.0');
$session = new Session($platform);
$f = new Functions($session, new Cast());


ascii:
Assert::same($f->ascii(null), null);
Assert::same($f->ascii("\0"), 0);
Assert::same($f->ascii('*'), 42);
Assert::same($f->ascii('★'), 226);
Assert::same($f->ascii('0'), 48);
Assert::same($f->ascii('1'), 49);
Assert::same($f->ascii(0), 48);
Assert::same($f->ascii(1), 49);
Assert::same($f->ascii(false), 48);
Assert::same($f->ascii(true), 49);


bin:
Assert::same($f->bin(null), null);
Assert::same($f->bin(42), '101010');


bit_length:
Assert::same($f->bit_length(null), null);
Assert::same($f->bit_length(''), 0);
Assert::same($f->bit_length(' '), 8);
Assert::same($f->bit_length('  '), 16);
Assert::same($f->bit_length('ščř'), 48);
Assert::same($f->bit_length('★★★'), 72);


char:
Assert::same($f->char(null), '');
Assert::same($f->char(42), '*');
Assert::same($f->char(9733, 9733, 9733), '★★★');


character_length:
Assert::same($f->char_length(null), null);
Assert::same($f->char_length(''), 0);
Assert::same($f->char_length('*'), 1);
Assert::same($f->char_length('★★★'), 3);


char_length:
Assert::same($f->character_length(null), null);
Assert::same($f->character_length(''), 0);
Assert::same($f->character_length('*'), 1);
Assert::same($f->character_length('★★★'), 3);


concat:
Assert::same($f->concat(null), null);
Assert::same($f->concat('a', null, 'c'), null);
Assert::same($f->concat('a', 'b', 'c'), 'abc');


concat_ws:
Assert::same($f->concat_ws(null, 'a', 'b', 'c'), null);
Assert::same($f->concat_ws('|', 'a', null, 'c'), null);
Assert::same($f->concat_ws('|', 'a', 'b', 'c'), 'a|b|c');


elt:
Assert::same($f->elt(null, 'a', 'b', 'c'), null);
Assert::same($f->elt(1, null, 'b', 'c'), null);
Assert::same($f->elt(1, 'a', 'b', 'c'), 'a');
Assert::same($f->elt(3, 'a', 'b', 'c'), 'c');


export_set:
Assert::same($f->export_set(null, '1', '0', ',', 8), null);
Assert::same($f->export_set(42, null, '0', ',', 8), null);
Assert::same($f->export_set(42, '1', null, ',', 8), null);
Assert::same($f->export_set(42, '1', '0', null, 8), null);
Assert::same($f->export_set(42, '1', '0', ',', null), null);
Assert::same($f->export_set(42, '1', '0', ',', 8), '0,1,0,1,0,1,0,0');
Assert::same($f->export_set(42, '1', '0'), '0,1,0,1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0');
Assert::same($f->export_set(42, '1', '0', '', 8), '01010100');
Assert::same($f->export_set(42, 'Y', 'N', '|', 8), 'N|Y|N|Y|N|Y|N|N');


field:
Assert::same($f->field(null, 'a', 'b', 'c'), 0);
Assert::same($f->field('*', 'a', 'b', 'c'), 0);
Assert::same($f->field('a', 'a', 'b', 'c'), 1);
Assert::same($f->field('b', 'a', 'b', 'c'), 2);
Assert::same($f->field('c', 'a', 'b', 'c'), 3);


find_in_set:
Assert::same($f->find_in_set('*', null), null);
Assert::same($f->find_in_set(null, 'a,b,c'), null);
Assert::same($f->find_in_set('*', 'a,b,c'), 0);
Assert::same($f->find_in_set('a', 'a,b,c'), 1);
Assert::same($f->find_in_set('b', 'a,b,c'), 2);
Assert::same($f->find_in_set('c', 'a,b,c'), 3);


format:
Assert::same($f->format(null, 1), null);
Assert::same($f->format(1.234, null), null);
Assert::same($f->format(1.234, -1), '1');
Assert::same($f->format(1.234, 0), '1');
Assert::same($f->format(1.234, 1), '1.2');
Assert::same($f->format(1.234, 2), '1.23');
Assert::same($f->format(1234, 1), '1,234.0');


from_base64:
Assert::same($f->from_base64('YWJj'), 'abc');
Assert::same($f->from_base64('YWJjZA=='), 'abcd');
Assert::same($f->from_base64("YWJj\nZA=="), 'abcd');
Assert::same($f->from_base64('YWJjZA'), null);


hex:
Assert::same($f->hex(null), null);
Assert::same($f->hex(42), '2a');
Assert::same($f->hex('*'), '2a');


insert:
Assert::same($f->insert(null, 1, 1, '*'), null);
Assert::same($f->insert('abcde', null, 1, '*'), null);
Assert::same($f->insert('abcde', 1, null, '*'), null);
Assert::same($f->insert('abcde', 1, 1, null), null);
Assert::same($f->insert('abcde', 7, 1, '*'), 'abcde');
Assert::same($f->insert('abcde', 0, 1, '*'), 'abcde');
Assert::same($f->insert('abcde', 1, 1, '*'), '*bcde');
Assert::same($f->insert('abcde', 3, 1, '*'), 'ab*de');
Assert::same($f->insert('abcde', 5, 1, '*'), 'abcd*');
Assert::same($f->insert('abcde', 2, 3, '***'), 'a***e');


instr:
Assert::same($f->instr('abcde', null), null);
Assert::same($f->instr(null, 'ab'), null);
Assert::same($f->instr('abcde', '*'), 0);
Assert::same($f->instr('abcde', 'ab'), 1);
Assert::same($f->instr('abcde', 'de'), 4);
Assert::same($f->instr('áščřáščř', 'á'), 1);
Assert::same($f->instr('áščřáščř', 'š'), 2);
Assert::same($f->instr('áščřáščř', 'č'), 3);
Assert::same($f->instr('áščřáščř', 'ř'), 4);


lcase:
Assert::same($f->lcase(null), null);
Assert::same($f->lcase('ABC'), 'abc');
Assert::same($f->lcase('ÁŠČŘ'), 'áščř');


left:
Assert::same($f->left(null, 1), null);
Assert::same($f->left('abc', null), null);
Assert::same($f->left('abc', 1), 'a');
Assert::same($f->left('abc', 2), 'ab');
Assert::same($f->left('áščř', 1), 'á');
Assert::same($f->left('áščř', 2), 'áš');


length:
Assert::same($f->length(null), null);
Assert::same($f->length(''), 0);
Assert::same($f->length('abc'), 3);
Assert::same($f->length('áščř'), 8);


load_file:
///


locate:
Assert::same($f->locate(null, 'abcde'), null);
Assert::same($f->locate('ab', null), null);
Assert::same($f->locate('*', 'abcde'), 0);
Assert::same($f->locate('ab', 'abcde'), 1);
Assert::same($f->locate('de', 'abcde'), 4);
Assert::same($f->locate('á', 'áščřáščř'), 1);
Assert::same($f->locate('š', 'áščřáščř'), 2);
Assert::same($f->locate('č', 'áščřáščř'), 3);
Assert::same($f->locate('ř', 'áščřáščř'), 4);
Assert::same($f->locate('á', 'áščřáščř', 4), 5);
Assert::same($f->locate('š', 'áščřáščř', 4), 6);
Assert::same($f->locate('č', 'áščřáščř', 4), 7);
Assert::same($f->locate('ř', 'áščřáščř', 4), 8);


lower:
Assert::same($f->lower(null), null);
Assert::same($f->lower('ABC'), 'abc');
Assert::same($f->lower('ÁŠČŘ'), 'áščř');


lpad:
Assert::same($f->lpad(null, 5, '*'), null);
Assert::same($f->lpad('abc', null, '*'), null);
Assert::same($f->lpad('abc', 5, null), null);
Assert::same($f->lpad('abc', 1, '*'), 'a');
Assert::same($f->lpad('abc', 5, '*'), '**abc');
Assert::same($f->lpad('abc', 5, '*^'), '*^abc');
Assert::same($f->lpad('abc', 6, '*^'), '*^*abc');
Assert::same($f->lpad('ščř', 5, '*'), '**ščř');
Assert::same($f->lpad('ščř', 5, '*^'), '*^ščř');
Assert::same($f->lpad('ščř', 6, '*^'), '*^*ščř');


ltrim:
Assert::same($f->ltrim(null), null);
Assert::same($f->ltrim('abc'), 'abc');
Assert::same($f->ltrim(' abc '), 'abc ');
Assert::same($f->ltrim("\nabc\n"), "\nabc\n");
Assert::same($f->ltrim("\rabc\r"), "\rabc\r");
Assert::same($f->ltrim("\tabc\t"), "\tabc\t");


make_set:
Assert::same($f->make_set(null, 'a', 'b', 'c'), null);
Assert::same($f->make_set(1, null, 'b', 'c'), '');
Assert::same($f->make_set(1, 'a', 'b', 'c'), 'a');
Assert::same($f->make_set(5, 'a', 'b', 'c'), 'a,c');


mid:
Assert::same($f->mid(null, 1, 1), null);
Assert::same($f->mid('abcde', null, 1), null);
Assert::same($f->mid('abcde', 1, null), null);
Assert::same($f->mid('abcde', 6, 1), '');
Assert::same($f->mid('abcde', 1, 1), 'a');
Assert::same($f->mid('abcde', 1, 2), 'ab');
Assert::same($f->mid('abcde', 5, 2), 'e');
Assert::same($f->mid('áščř', 1, 1), 'á');
Assert::same($f->mid('áščř', 1, 2), 'áš');
Assert::same($f->mid('áščř', 4, 2), 'ř');


oct:
Assert::same($f->oct(null), null);
Assert::same($f->oct(42), '52');


octet_length:
Assert::same($f->octet_length(null), null);
Assert::same($f->octet_length('abc'), 3);
Assert::same($f->octet_length('ščř'), 6);


ord:
Assert::same($f->ord(null), null);
Assert::same($f->ord(chr(253) . chr(0)), null);
Assert::same($f->ord('*'), 42);
Assert::same($f->ord('★'), 9733);


position:
Assert::same($f->position(null, 'abcde'), null);
Assert::same($f->position('ab', null), null);
Assert::same($f->position('*', 'abcde'), 0);
Assert::same($f->position('ab', 'abcde'), 1);
Assert::same($f->position('de', 'abcde'), 4);
Assert::same($f->position('á', 'áščřáščř'), 1);
Assert::same($f->position('š', 'áščřáščř'), 2);
Assert::same($f->position('č', 'áščřáščř'), 3);
Assert::same($f->position('ř', 'áščřáščř'), 4);
Assert::same($f->position('á', 'áščřáščř', 4), 5);
Assert::same($f->position('š', 'áščřáščř', 4), 6);
Assert::same($f->position('č', 'áščřáščř', 4), 7);
Assert::same($f->position('ř', 'áščřáščř', 4), 8);


quote:
Assert::same($f->quote(null), null);
Assert::same($f->quote("abc"), "'abc'");
Assert::same($f->quote("'"), "'\\''");
Assert::same($f->quote("\\"), "'\\\\'");
Assert::same($f->quote("\x00"), "'\\0'");
Assert::same($f->quote("\x1a"), "'\\Z'");


repeat:
Assert::same($f->repeat(null, 3), null);
Assert::same($f->repeat('abc', null), null);
Assert::same($f->repeat('abc', 0), '');
Assert::same($f->repeat('abc', 3), 'abcabcabc');


replace:
Assert::same($f->replace(null, 'bc', '*'), null);
Assert::same($f->replace('abcd', null, '*'), null);
Assert::same($f->replace('abcd', 'bc', null), null);
Assert::same($f->replace('abcd', 'bc', '*'), 'a*d');
Assert::same($f->replace('abcd', 'ad', '*'), 'abcd');


reverse:
Assert::same($f->reverse(null), null);
Assert::same($f->reverse('abcd'), 'dcba');
Assert::same($f->reverse('áščř'), 'řčšá');


right:
Assert::same($f->right(null, 1), null);
Assert::same($f->right('abcd', null), null);
Assert::same($f->right('abcd', 1), 'd');
Assert::same($f->right('abcd', 2), 'cd');
Assert::same($f->right('áščř', 1), 'ř');
Assert::same($f->right('áščř', 2), 'čř');


rpad:
Assert::same($f->rpad(null, 5, '*'), null);
Assert::same($f->rpad('abc', null, '*'), null);
Assert::same($f->rpad('abc', 5, null), null);
Assert::same($f->rpad('abc', 1, '*'), 'a');
Assert::same($f->rpad('abc', 5, '*'), 'abc**');
Assert::same($f->rpad('abc', 5, '*^'), 'abc*^');
Assert::same($f->rpad('abc', 6, '*^'), 'abc*^*');
Assert::same($f->rpad('ščř', 5, '*'), 'ščř**');
Assert::same($f->rpad('ščř', 5, '*^'), 'ščř*^');
Assert::same($f->rpad('ščř', 6, '*^'), 'ščř*^*');


rtrim:
Assert::same($f->rtrim(null), null);
Assert::same($f->rtrim('abc'), 'abc');
Assert::same($f->rtrim(' abc '), ' abc');
Assert::same($f->rtrim("\nabc\n"), "\nabc\n");
Assert::same($f->rtrim("\rabc\r"), "\rabc\r");
Assert::same($f->rtrim("\tabc\t"), "\tabc\t");


space:
Assert::same($f->space(null), null);
Assert::same($f->space(-1), '');
Assert::same($f->space(0), '');
Assert::same($f->space(1), ' ');
Assert::same($f->space(5), '     ');


strcmp:
Assert::same($f->strcmp(null, 'b'), null);
Assert::same($f->strcmp('a', null), null);
Assert::same($f->strcmp('a', 'a'), 0);
Assert::same($f->strcmp('a', 'b'), -1);
Assert::same($f->strcmp('b', 'a'), 1);


substr:
Assert::same($f->substr(null, 1, 1), null);
Assert::same($f->substr('abcde', null, 1), null);
Assert::same($f->substr('abcde', 1, null), null);
Assert::same($f->substr('abcde', 6, 1), '');
Assert::same($f->substr('abcde', 1, 1), 'a');
Assert::same($f->substr('abcde', 1, 2), 'ab');
Assert::same($f->substr('abcde', 5, 2), 'e');
Assert::same($f->substr('áščř', 1, 1), 'á');
Assert::same($f->substr('áščř', 1, 2), 'áš');
Assert::same($f->substr('áščř', 4, 2), 'ř');


substring:
Assert::same($f->substring(null, 1, 1), null);
Assert::same($f->substring('abcde', null, 1), null);
Assert::same($f->substring('abcde', 1, null), null);
Assert::same($f->substring('abcde', 6, 1), '');
Assert::same($f->substring('abcde', 1, 1), 'a');
Assert::same($f->substring('abcde', 1, 2), 'ab');
Assert::same($f->substring('abcde', 5, 2), 'e');
Assert::same($f->substring('áščř', 1, 1), 'á');
Assert::same($f->substring('áščř', 1, 2), 'áš');
Assert::same($f->substring('áščř', 4, 2), 'ř');


substring_index:
Assert::same($f->substring_index(null, '.', 2), null);
Assert::same($f->substring_index('a.b.c', null, 2), null);
Assert::same($f->substring_index('a.b.c', '.', null), null);
Assert::same($f->substring_index('a.b.c', '.', 2), 'a.b');
Assert::same($f->substring_index('a.b.c', '.', -2), 'b.c');


to_base64:
Assert::same($f->to_base64('abc'), 'YWJj');
Assert::same($f->to_base64('abcd'), 'YWJjZA==');


trim:
Assert::same($f->trim(null), null);
Assert::same($f->trim('abc'), 'abc');
Assert::same($f->trim('  abc  '), 'abc');
Assert::same($f->trim("\nabc\n"), "\nabc\n");
Assert::same($f->trim("\rabc\r"), "\rabc\r");
Assert::same($f->trim("\tabc\t"), "\tabc\t");


ucase:
Assert::same($f->ucase(null), null);
Assert::same($f->ucase('abc'), 'ABC');
Assert::same($f->ucase('áščř'), 'ÁŠČŘ');


unhex:
Assert::same($f->unhex(null), null);
Assert::same($f->unhex('2a'), '*');


upper:
Assert::same($f->upper(null), null);
Assert::same($f->upper('abc'), 'ABC');
Assert::same($f->upper('áščř'), 'ÁŠČŘ');


weight_string:
///
