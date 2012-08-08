<?php
namespace zz\l10n\ja;

/*
 * 漢字表記の数字とアラビア数字間の変換を行います。
 * original http://blog.usoinfo.info/article/181276332.html
 */
class number {
    static protected $_charset = 'UTF-8';
// 数漢字-数値マップ
    static protected $_num_zenkaku = array(
        '０' => 0, '１' => 1, '２' => 2, '３' => 3, '４' => 4, '５' => 5, '６' => 6, '７' => 7, '８' => 8, '９' => 9,
    );

    static protected $_num_kanji = array(
        '〇' => 0, '一' => 1, '二' => 2, '三' => 3, '四' => 4, '五' => 5, '六' => 6, '七' => 7, '八' => 8, '九' => 9,
    );

    static protected $_lowerDigits = array(
        '十' => 1, '百' => 2, '千' => 3,
    );

    static protected $_upperDigits = array(
        '万' => 4, '億' => 8, '兆' => 12, '京' => 16,
    );

    /**
     * 文字コードをセットします。デフォルトはUTFｰ8
     * @static
     * @param $charset
     */
    static function setCharset($charset) {
        static::$_charset = $charset;
    }

    /**
     * 現在の文字コードを取得します。
     * @static
     * @param $charset
     * @return string
     */
    static function getCharset($charset) {
        return static::$_charset;
    }

    /**
     * 漢数字をアラビア数字に変換します
     * arabicメソッドのエイリアス
     * @static
     * @param $noarabic
     * @return int|mixed|string
     */
    static function arabic($noarabic) {
        return static::knum2arabic($noarabic);
    }

    /**
     * 10000以下の漢数字をアラビア数字に変換します。
     * @static
     * @param $kanji
     * @return int
     */
    static protected function knum2arabic_10000($kanji) {
        // 下桁字-倍率マップ
        $lowerDigits = static::powDex(static::$_lowerDigits);
        $ival = 0;
        $lnum = '';
        for ($i = 0, $len = mb_strlen($kanji, static::$_charset); $i < $len; $i++) {
            $ch = mb_substr($kanji, $i, 1, static::$_charset);
            if (isset($lowerDigits[$ch])) {
                $ival += $lnum ? (int)$lnum * $lowerDigits[$ch] : $lowerDigits[$ch];
                $lnum = '';
            } else {
                $lnum .= $ch;
            }
        }
        if ($lnum) {
            $ival += (int)$lnum;
        }

        return $ival;
    }

    /**
     * 漢数字をアラビア数字に変換します
     * @static
     * @param $kanji
     * @return int|mixed|string
     * @throws \RangeException
     * @throws \UnexpectedValueException
     */
    protected static function knum2arabic($kanji) {
        if ($kanji === null || $kanji === '') {
            throw new \UnexpectedValueException("empty given");
        }
        $kanji = static::simpleKnum2arabic($kanji);

        if (static::isNumeric($kanji)) {
            return $kanji;
        }
        // 上桁字-倍率マップ
        $upperDigits = static::powDex(static::$_upperDigits);

        $pattern_separator = join('', array_keys($upperDigits));
        $pattern_under_1000 = join('', array_keys(static::$_lowerDigits));
        $pattern = '/([' . $pattern_under_1000 . '0-9]+)([' . $pattern_separator . ']?)/u'; // 数漢字として扱う文字

        $parts = array();
        while (preg_match($pattern, $kanji, $matches)) {
            $parts[] = array($matches[1], $matches[2]);
            $kanji = str_replace($matches[0], '', $kanji);
        }

        if (!empty($kanji)) {
            throw new \UnexpectedValueException("Can not convert to arabic in `{$kanji}`");
        }

        $ival = 0;
        for ($i = 0, $len = count($parts); $i < $len; $i++) {
            list($_number, $_digit) = $parts[$i];
            if (static::isNumeric($_number)) {
                $ipart = $_number;
            } else {
                $ipart = static::knum2arabic_10000($_number);
            }

            if ($_digit) {
                $imult = $upperDigits[$_digit];
            } else {
                $imult = 0;
            }

            $ival += $ipart * ($imult != 0 ? $imult : 1);
        }

        if (is_float($ival)) {
            throw new \RangeException('exceed `PHP_INT_MAX` = ' . PHP_INT_MAX);
        }

        return $ival;
    }

    /**
     * 簡素な漢数字をアラビア数字に変換します。
     * @static
     * @param $kanji
     * @return string
     */
    public static function simpleKnum2arabic($kanji) {
        $_map = array_merge(static::$_num_zenkaku, static::$_num_kanji);

        $convertCharLists = array();

        for ($i = 0, $len = mb_strlen($kanji, static::$_charset); $i < $len; $i++) {
            $char = mb_substr($kanji, $i, 1, static::$_charset);

            if (isset($_map[$char])) {
                $convertCharLists[] = $_map[$char];
                continue;
            }
            $convertCharLists[] = $char;
        }
        return join('', $convertCharLists);
    }

    /**
     * 桁数字を展開した配列を生成します。
     * @static
     * @param array $digits
     * @return array
     */
    public static function powDex(Array $digits) {
        $dexDigits = array();
        foreach ($digits as $key => $digit) {
            $dexDigits[$key] = pow(10, $digit);
        }
        return $dexDigits;
    }

    /**
     * 整数であることをチェックします。
     * @static
     * @param $stringNumber
     * @return int
     */
    public static function isNumeric($stringNumber) {
        // regex = $allowZero ? '/\A(?:0|[1-9][0-9]*)\Z/' : '/\A[1-9][0-9]*\Z/';
        return preg_match('/\A(?:0|[1-9][0-9]*)\Z/', $stringNumber);
    }

}


