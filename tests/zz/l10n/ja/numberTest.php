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
        $this->assertEquals($number, $actual);
    }

    public function dataArabic() {
        return array(
            array('二〇〇八', 2008),
            array('六千九百三十三万二千三百十四', 69332314),
            array('二千三百四十五万六千七百八十九', 23456789),
            array('一億二千三百四十五万六千七百八十九', 123456789),
            // 32bit PHP_INT_MAX 2147483647
            array('二十一億四千七百四十八万三千六百四十七', 2147483647),
            array('２億３３万２３１０', 200332310),
            array('二〇一一', '2011'),
        );
    }

    /**
     *
     * @dataProvider dataArabic64bit
     */
    public function testArabic64bit($kanji, $number) {
       if (PHP_INT_SIZE <= 4) {
           $this->markTestSkipped('require 64bit');
       }
        $this->testArabic($kanji, $number);
    }

    public function dataArabic64bit() {
        return array(
            array('一〇〇〇億', 100000000000),
            array('五〇〇億', 50000000000),
            array('一千億', 100000000000),
            array('五百億', 50000000000),
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
