<?php
namespace zz\l10n\ja;

/*
 * 漢字表記の数字とアラビア数字間の変換を行います。
 * original http://blog.usoinfo.info/article/181276332.html
 */
class number {
    static protected $_charset = 'UTF-8';

    // 数値マップ
    // 全角 -> アラビア
    static protected $_num_zenkaku = array(
        '０' => 0, '１' => 1, '２' => 2, '３' => 3, '４' => 4, '５' => 5, '６' => 6, '７' => 7, '８' => 8, '９' => 9,

    );


    // 数漢字 -> アラビア
    static protected $_num_kanji = array(
        '〇' => 0, '一' => 1, '二' => 2, '三' => 3, '四' => 4, '五' => 5, '六' => 6, '七' => 7, '八' => 8, '九' => 9, // 現在
    );

    // 数漢字 -> アラビア
    static protected $_num_kanji_alias = array(
        '零' => 0, // 別名
        '弌' => 1, '弍' => 2, '弎' => 3, //古字
        '肆' => 4, '伍' => 5, '陸' => 6, '漆' => 7, '柒' => 7, '質' => 7, '捌' => 8, '玖' => 9, // 旧字体
        '佰' => '百', '陌' => '百', '仟' => '千', '仟' => '千', '萬' => '万', // 旧字体
        '壱' => 1, '弐' => 2, '参' => 3, '拾' => '十', '廿' => '二十', '卅' => '三十', '丗' => '三十', // 大字
        '壹' => 1, '貳' => 2, '參' => 3, // 大字(旧字体)
    );

    static protected $_lowerDigits = array(
        '十' => 1, '百' => 2, '千' => 3,
    );

    static protected $_upperDigits = array(
        '万' => 4,
        '億' => 8,
        '兆' => 12,
        '京' => 16,
        '垓' => 20,
        '𥝱' => 24,
        '穣' => 28,
        '溝' => 32,
        '澗' => 36,
        '正' => 40,
        '載' => 44,
        '極' => 48,
        '恒河沙' => 52,
        '阿僧祗' => 56,
        '那由他' => 60,
        '不可思議' => 64,
        '無量大数' => 68,
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
     * @return string
     */
    static function arabic($noarabic) {
        return static::knum2arabic($noarabic);
    }

    /**
     * 10000未満の漢数字をアラビア数字に変換します。
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
                $ival += $lnum ? $lnum * $lowerDigits[$ch] : $lowerDigits[$ch];
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
     * @return string
     * @throws \UnexpectedValueException
     */
    protected static function knum2arabic($kanji) {
        if ($kanji === null || $kanji === '') {
            throw new \UnexpectedValueException("empty string or null given");
        }
        $kanji = static::simpleKnum2arabic($kanji);

        if (static::isNumeric($kanji)) {
            return $kanji;
        }
        // 上桁字-倍率マップ
        $upperDigits = static::$_upperDigits;

        $pattern_separator = join('|', array_keys($upperDigits));
        $pattern_under_1000 = join('', array_keys(static::$_lowerDigits));
        $pattern = '/([' . $pattern_under_1000 . '0-9]+)((' . $pattern_separator . ')?)/u'; // 数漢字として扱う文字

        $parts = array();
        while (preg_match($pattern, $kanji, $matches)) {
            $parts[] = array($matches[1], $matches[2]);
            $kanji = str_replace($matches[0], '', $kanji);
        }

        if (!empty($kanji)) {
            throw new \UnexpectedValueException("Can not convert to arabic in `{$kanji}`");
        }

        $ival = '0000';
        $parts = array_reverse($parts);

        for ($i = 0, $len = count($parts); $i < $len; $i++) {

            list($_number, $_digit) = $parts[$i];
            if (static::isNumeric($_number)) {
                $ipart = $_number;
            } else {
                $ipart = static::knum2arabic_10000($_number);
            }

            $ipart = sprintf('%04s', $ipart);

            if ($_digit) {
                $imult = $upperDigits[$_digit];
            } else {
                $imult = 0;
            }

            if ($imult) {
                if (strlen($ival) !== $imult) {
                    $ival = sprintf('%0' . $imult . 's', $ival);
                }
                $ival = $ipart . $ival;
            } else {
                if ($i === 0) {
                    $ival = $ipart;
                } else {
                    $ival .= $ipart;
                }

            }
        }

        return ltrim($ival, '0');
    }

    /**
     * 簡素な漢数字をアラビア数字に変換します。
     * @static
     * @param $kanji
     * @return string
     */
    public static function simpleKnum2arabic($kanji) {
        $_map = array_merge(static::$_num_zenkaku, static::$_num_kanji);
        $_map_alias = static::$_num_kanji_alias;

        $convertCharLists = array();

        foreach ($_map_alias as $alias => $replace) {
            $kanji = str_replace($alias,$replace, $kanji);
        }

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


