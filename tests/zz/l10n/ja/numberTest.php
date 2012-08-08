<?php
namespace zz;

use zz\l10n\ja;

class numberTest extends \PHPUnit_Framework_TestCase {

    /**
     *
     * @dataProvider dataArabic
     */
    public function testArabic($kanji, $number) {
        $actual = \zz\l10n\ja\number::arabic($kanji);
        $this->assertSame($number, $actual);
    }

    public function dataArabic() {
        return array(
            array('二〇〇八', '2008'),
            array('二〇一一', '2011'),
            array('十一', '11'),
            array('一千', '1000'),
            array('一万', '10000'),
            array('一万一', '10001'),
            array('二十一万一', '210001'),
            array('一億', '100000000'),
            array('六千九百三十三万二千三百十四', '69332314'),
            array('二千三百四十五万六千七百八十九', '23456789'),
            array('一億二千三百四十五万六千七百八十九', '123456789'),
            array('２億３３万２３１０', '200332310'),
            // 32bit PHP_INT_MAX => 2147483647
            array('二十一億四千七百四十八万三千六百四十七', '2147483647'),
            array('二一四七四八三六四七', '2147483647'),
            // 32bit PHP_INT_MAX + 1 => 2147483648
            array('二十一億四千七百四十八万三千六百四十八', '2147483648'),
            array('二一四七四八三六四八', '2147483648'),
            // 64bit PHP_INT_MAX => 9223372036854775807
            array('九二二三三七二兆三百六十八億五千四百七十七万五千八百七', '9223372036854775807'),
            // 64bit PHP_INT_MAX => 9223372036854775808
            array('九二二三三七二兆三百六十八億五千四百七十七万五千八百八', '9223372036854775808'),

            array('一〇〇〇億', '100000000000'),
            array('五〇〇億', '50000000000'),
            array('一千億', '100000000000'),
            array('五百億', '50000000000'),

            array('一無量大数', '100000000000000000000000000000000000000000000000000000000000000000000'),

            array('百弐拾万参千弐百', '1203200'),
        );
    }


    /**
     * @expectedException UnexpectedValueException
     */
    public function testArabic_UnexpectedValueException() {
        $kanji = '100億漢数字以外';
        $actual = \zz\l10n\ja\number::arabic($kanji);
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testArabic_no_string_UnexpectedValueException() {
        $kanji = '';
        $actual = \zz\l10n\ja\number::arabic($kanji);
    }
}
