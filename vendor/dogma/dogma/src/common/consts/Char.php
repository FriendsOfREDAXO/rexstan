<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

/**
 * Special characters ASCII codes
 *
 * Dec Hex  Char                          Dec Hex Char  Dec Hex Char  Dec  Hex Char
 * -------------------------------------  ------------  ------------  -------------
 *  0  00h  NUL (null)                    32  20h  " "  64  40h  @     96  60h  `
 *  1  01h  SOH (start of heading)        33  21h  !    65  41h  A     97  61h  a
 *  2  02h  STX (start of text)           34  22h  "    66  42h  B     98  62h  b
 *  3  03h  ETX (end of text)             35  23h  #    67  43h  C     99  63h  c
 *  4  04h  EOT (end of transmission)     36  24h  $    68  44h  D    100  64h  d
 *  5  05h  ENQ (enquiry)                 37  25h  %    69  45h  E    101  65h  e
 *  6  06h  ACK (acknowledge)             38  26h  &    70  46h  F    102  66h  f
 *  7  07h  BEL (bell)                    39  27h  '    71  47h  G    103  67h  g
 *  8  08h  BS  (backspace)               40  28h  (    72  48h  H    104  68h  h
 *  9  09h  TAB (horizontal tab)          41  29h  )    73  49h  I    105  69h  i
 * 10  0Ah  LF  (NL line feed, new line)  42  2Ah  *    74  4Ah  J    106  6Ah  j
 * 11  0Bh  VT  (vertical tab)            43  2Bh  +    75  4Bh  K    107  6Bh  k
 * 12  0Ch  FF  (NP form feed, new page)  44  2Ch  ,    76  4Ch  L    108  6Ch  l
 * 13  0Dh  CR  (carriage return)         45  2Dh  -    77  4Dh  M    109  6Dh  m
 * 14  0Eh  SO  (shift out)               46  2Eh  .    78  4Eh  N    110  6Eh  n
 * 15  0Fh  SI  (shift in)                47  2Fh  /    79  4Fh  O    111  6Fh  o
 * 16  10h  DLE (data link escape)        48  30h  0    80  50h  P    112  70h  p
 * 17  11h  DC1 (device control 1)        49  31h  1    81  51h  Q    113  71h  q
 * 18  12h  DC2 (device control 2)        50  32h  2    82  52h  R    114  72h  r
 * 19  13h  DC3 (device control 3)        51  33h  3    83  53h  S    115  73h  s
 * 20  14h  DC4 (device control 4)        52  34h  4    84  54h  T    116  74h  t
 * 21  15h  NAK (negative acknowledge)    53  35h  5    85  55h  U    117  75h  u
 * 22  16h  SYN (synchronous idle)        54  36h  6    86  56h  V    118  76h  v
 * 23  17h  ETB (end of trans. block)     55  37h  7    87  57h  W    119  77h  w
 * 24  18h  CAN (cancel)                  56  38h  8    88  58h  X    120  78h  x
 * 25  19h  EM  (end of medium)           57  39h  9    89  59h  Y    121  79h  y
 * 26  1Ah  SUB (substitute)              58  3Ah  :    90  5Ah  Z    122  7Ah  z
 * 27  1Bh  ESC (escape)                  59  3Bh  ;    91  5Bh  [    123  7Bh  {
 * 28  1Ch  FS  (file separator)          60  3Ch  <    92  5Ch  \    124  7Ch  |
 * 29  1Dh  GS  (group separator)         61  3Dh  =    93  5Dh  ]    125  7Dh  }
 * 30  1Eh  RS  (record separator)        62  3Eh  >    94  5Eh  ^    126  7Eh  ~
 * 31  1Fh  US  (unit separator)          63  3Fh  ?    95  5Fh  _    127  7Fh  DEL
 */
class Char
{
    use StaticClassMixin;

    // phpcs:disable Squiz.WhiteSpace.OperatorSpacing.SpacingBefore
    public const NUL = "\x00"; // null
    public const SOH = "\x01"; // start of header
    public const STX = "\x02"; // start of text
    public const ETX = "\x03"; // end of text
    public const EOT = "\x04"; // end of transmission
    public const ENQ = "\x05"; // enquiry
    public const ACK = "\x06"; // acknowledge
    public const BEL = "\x07"; // bell
    public const BS  = "\x08"; // backspace
    public const HT  = "\x09"; // horizontal tab
    public const LF  = "\x0A"; // line feed
    public const VT  = "\x0B"; // vertical tab
    public const FF  = "\x0C"; // form feed
    public const CR  = "\x0D"; // enter / carriage return
    public const SO  = "\x0E"; // shift out
    public const SI  = "\x0F"; // shift in
    public const DLE = "\x10"; // data link escape
    public const DC1 = "\x11"; // device control 1
    public const DC2 = "\x12"; // device control 2
    public const DC3 = "\x13"; // device control 3
    public const DC4 = "\x14"; // device control 4
    public const NAK = "\x15"; // negative acknowledge
    public const SYN = "\x16"; // synchronize
    public const ETB = "\x17"; // end of trans. block
    public const CAN = "\x18"; // cancel
    public const EM  = "\x19"; // end of medium
    public const SUB = "\x1A"; // substitute
    public const ESC = "\x1B"; // escape
    public const FS  = "\x1C"; // file separator
    public const GS  = "\x1D"; // group separator
    public const RS  = "\x1E"; // record separator
    public const US  = "\x1F"; // unit separator
    public const DEL = "\x7F"; // delete

    public const NULL = "\x00";
    public const START_OF_HEADER = "\x01";
    public const START_OF_TEXT = "\x02";
    public const END_OF_TEXT = "\x03";
    public const END_OF_TRANSMISSION = "\x04";
    public const ENQUIRY = "\x05";
    public const ACKNOWLEDGE = "\x06";
    public const BELL = "\x07";
    public const BACKSPACE = "\x08";
    public const HORIZONTAL_TAB  = "\x09";
    public const LINE_FEED = "\x0A";
    public const VERTICAL_TAB = "\x0B";
    public const FORM_FEED = "\x0C";
    public const CARRIAGE_RETURN = "\x0D";
    public const SHIFT_OUT = "\x0E";
    public const SHIFT_IN = "\x0F";
    public const DATA_LINK_ESCAPE = "\x10";
    public const DEVICE_CONTROL_1 = "\x11";
    public const DEVICE_CONTROL_2 = "\x12";
    public const DEVICE_CONTROL_3 = "\x13";
    public const DEVICE_CONTROL_4 = "\x14";
    public const NEGATIVE_ACKNOWLEDGE = "\x15";
    public const SYNCHRONIZE = "\x16";
    public const END_OF_TRANSMISSION_BLOCK = "\x17";
    public const CANCEL = "\x18";
    public const END_OF_MEDIUM = "\x19";
    public const SUBSTITUTE = "\x1A";
    public const ESCAPE = "\x1B";
    public const FILE_SEPARATOR = "\x1C";
    public const GROUP_SEPARATOR = "\x1D";
    public const RECORD_SEPARATOR = "\x1E";
    public const UNIT_SEPARATOR = "\x1F";
    public const DELETE = "\x7F";

}
