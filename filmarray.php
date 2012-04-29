<?php

/**
 * @author AlexMcArrow 2008-2012
 * @copyright AlexMcArrow 2008-2012
 * @link http://mcarrow.ru/
 *
 * =FilmArrayStudio=
 * =FilmArrayFX=
 */
new FilmArrayStudio();
new FilmArrayFX();

class FilmArrayStudio {

    const FAS_version = '0.2.3';

    public static $Scenario;
    public static $Movie;
    private static $Log;

    function __construct () {
        self::$Log[] = 'FilmArrayStudio :: init';
        return TRUE;
    }

    public static function _get ($key) {
        if (isset (self::$Scenario[$key])) {
            return self::$Scenario[$key];
        }
        return FALSE;
    }

    public static function _set ($key, $value) {
        if (isset (self::$Scenario[$key])) {
            self::$Scenario[$key] = $value;
            return TRUE;
        }
        return FALSE;
    }

    public static function ShowLog () {
        echo implode ("<br/>", self::$Log);
    }

    public static function NewFilm ($name, $author) {
        self::$Scenario = array (
            'name' => $name,
            'author' => $author,
            'frames' => array ()
        );
        self::$Movie = array ();
        self::$Log[] = 'FilmArrayStudio :: new film';
    }

    public static function SaveProject ($filename) {
        file_put_contents ($filename . '.fa', json_encode (self::$Scenario));
        self::$Log[] = 'FilmArrayStudio :: project save as (' . $filename . '.fa)';
    }

    public static function LoadProject ($filename) {
        self::$Scenario = json_decode (file_get_contents ($filename . '.fa'), TRUE);
        self::$Movie = array ();
        self::$Log[] = 'FilmArrayStudio :: project load from (' . $filename . '.fa)';
    }

    public static function MakeMovie ($fps = 24) {
        $elepsed = microtime (TRUE);
        self::$Movie = array ();
        foreach (self::$Scenario['frames'] as $value) {
            self::$Movie = array_merge (self::$Movie, FilmArrayFX::DrawFrames ($value, $fps));
        }
        self::$Log[] = 'FilmArrayStudio :: movie compiled (' . count (self::$Movie) . '@' . (number_format ((microtime (TRUE) - $elepsed), 6, '.', '')) . ')';
        return print_r (self::$Movie, TRUE);
    }

    public static function SaveMovie ($filename, $scale = 1.5) {
        if (count (self::$Movie) > 0) {
            $BODY = '<pre style="font-size:' . (float) $scale . 'em;"><h1>' . self::$Scenario['name'] . '</h1>by ' . self::$Scenario['author'] . '<hr/>';
            $BODY .= print_r (self::$Movie, TRUE);
            $BODY .= '<hr/>FilmArrayStudio ' . self::FAS_version . '<br/>FilmArrayFX ' . FilmArrayFX::FAFX_version . '<hr/></pre>';
            file_put_contents ($filename . '.html', $BODY);
            self::$Log[] = 'FilmArrayStudio :: movie save as (' . $filename . '.html) (' . mb_strlen ($BODY) . ' bytes)';
        } else {
            self::$Log[] = 'FilmArrayStudio :: movie not compiled!!!';
        }
    }

    public static function SaveMovieBorder ($filename, $scale = 1.5) {
        if (count (self::$Movie) > 0) {
            $BODY = '<pre style="font-size:' . (float) $scale . 'em;"><h1>' . self::$Scenario['name'] . '</h1>by ' . self::$Scenario['author'] . '<hr/>';
            foreach (self::$Movie as $frame => $picture) {
                $BODY .= FilmArrayFX::StockOut ($frame, $picture);
            }
            $BODY .= '<hr/>FilmArrayStudio ' . self::FAS_version . '<br/>FilmArrayFX ' . FilmArrayFX::FAFX_version . '<hr/></pre>';
            file_put_contents ($filename . '.b.html', $BODY);
            self::$Log[] = 'FilmArrayStudio :: bordered movie save as (' . $filename . '.b.html) (' . mb_strlen ($BODY) . ' bytes)';
        } else {
            self::$Log[] = 'FilmArrayStudio :: bordered movie not compiled!!!';
        }
    }

}

class FilmArrayFX {

    const FAFX_version = '0.3.4';
    const FAFX_align_center = 0;
    const FAFX_align_left = 1;
    const FAFX_align_right = 2;

    private static $HeightOffset;
    private static $Width;
    private static $Height;
    private static $NullBit;
    private static $Templates;
    private static $BorderCounter;

    function __construct () {
        self::$Width = 47;
        self::$Height = 17;
        self::$HeightOffset = 10;
        self::$NullBit = ' ';
        self::$Templates = array ();
        self::$BorderCounter = 0;
    }

    public static function CountDown ($start = 9, $frames = 0.5, $ps = FALSE) {
        $Frames = FilmArrayStudio::_get ('frames');
        $Frames[] = array (
            'type' => 'countdown',
            'data' => array (
                'length' => $frames,
                'start' => $start,
                'ps' => $ps
            )
        );
        FilmArrayStudio::_set ('frames', $Frames);
    }

    public static function Title ($titles, $fillbit = FALSE, $length = 2) {
        self::_set_title ($titles, $fillbit, $length);
    }

    public static function Titles ($titles, $frames, $align = self::FAFX_align_center) {
        /// frames - the number of seconds for which the text should go all the way from the bottom to the top
        $Frames = FilmArrayStudio::_get ('frames');
        $Frames[] = array (
            'type' => 'titles',
            'data' => array (
                'length' => $frames,
                'titles' => $titles,
                'align' => $align
            )
        );
        FilmArrayStudio::_set ('frames', $Frames);
    }

    public static function NullScreen ($frames) {
        $Frames = FilmArrayStudio::_get ('frames');
        $Frames[] = array (
            'type' => 'null',
            'data' => array (
                'length' => $frames
            )
        );
        FilmArrayStudio::_set ('frames', $Frames);
    }

    public static function FillScreen ($bit, $frames) {
        $Frames = FilmArrayStudio::_get ('frames');
        $Frames[] = array (
            'type' => 'fill',
            'data' => array (
                'length' => $frames,
                'bit' => $bit
            )
        );
        FilmArrayStudio::_set ('frames', $Frames);
    }

    public static function DrawFrames ($rules, $fps) {
        $OUT = array ();
        switch ($rules['type']) {

            case 'null':
                for ($len = 0; $len < ($rules['data']['length'] * $fps); $len++) {
                    $OUT[] = self::_gen_fill_screen (self::$NullBit);
                }
                break;

            case 'fill':
                for ($len = 0; $len < ($rules['data']['length'] * $fps); $len++) {
                    $OUT[] = self::_gen_fill_screen ($rules['data']['bit']);
                }
                break;

            case 'centext':
                if ($rules['data']['bit'] !== FALSE) {
                    $frame = self::_gen_fill_screen ($rules['data']['bit']);
                } else {
                    $frame = self::_gen_fill_screen (self::$NullBit);
                }
                $frame = self::PlaceInFrame ($frame, $rules['data']['text'], ($rules['data']['top'] + self::$HeightOffset), ($rules['data']['left'] + $index));
                for ($len = self::$HeightOffset; $len < (($rules['data']['length'] * $fps) + self::$HeightOffset); $len++) {
                    $OUT[] = $frame;
                }
                break;

            case 'titles':
                $data = $rules['data']['titles'];
                krsort ($data);
                $posforframe = 1;
                $width = count ($data) * 2;
                $pos = count ($data);
                $titlesin = TRUE;
                $step = 0;
                while ($titlesin) {
                    $frame = self::_gen_fill_screen (self::$NullBit);
                    $linepos = self::$Height;
                    foreach ($data as $value) {
                        $values = self::TextSplit ($value, self::$Width - 2);
                        krsort ($values);
                        if (count ($values) > 1) {
                            foreach ($values as $val) {
                                switch ($rules['data']['align']) {
                                    case self::FAFX_align_center:
                                        $left = floor ((self::$Width / 2) - (mb_strlen ($val) / 2));
                                        break;
                                    case self::FAFX_align_left:
                                        $left = 1;
                                        break;
                                    case self::FAFX_align_right:
                                        $left = self::$Width - mb_strlen ($val);
                                        break;
                                }
                                $realpos = (self::$HeightOffset + self::$Height) - (($posforframe * $pos) - $linepos);
                                if (($realpos < (self::$HeightOffset + self::$Height)) && ($realpos > (self::$HeightOffset - 1))) {
                                    $frame = self::PlaceInFrame ($frame, $val, $realpos, $left);
                                }
                                $linepos = $linepos - 1;
                            }
                            $linepos = $linepos - 1;
                        } else {
                            switch ($rules['data']['align']) {
                                case self::FAFX_align_center:
                                    $left = floor ((self::$Width / 2) - (mb_strlen ($value) / 2));
                                    break;
                                case self::FAFX_align_left:
                                    $left = 1;
                                    break;
                                case self::FAFX_align_right:
                                    $left = self::$Width - mb_strlen ($value);
                                    break;
                            }
                            $realpos = (self::$HeightOffset + self::$Height) - (($posforframe * $pos) - $linepos);
                            if (($realpos < (self::$HeightOffset + self::$Height)) && ($realpos > (self::$HeightOffset - 1))) {
                                $frame = self::PlaceInFrame ($frame, $value, $realpos, $left);
                            }
                            $linepos = $linepos - 2;
                        }
                    }
                    $step++;
                    if ($step >= $rules['data']['length']) {
                        $pos++;
                        $step = 0;
                    }
                    if (($realpos - self::$HeightOffset + ($width * 2)) < $width) {
                        $titlesin = FALSE;
                    } else {
                        $OUT[] = $frame;
                    }
                }

                break;

            case 'countdown':
                $INFOMATRIX = array (
                    9 => array ('31', '17', '17', '31', '1', '1', '31'),
                    8 => array ('31', '17', '17', '31', '17', '17', '31'),
                    7 => array ('31', '1', '1', '1', '1', '1', '1'),
                    6 => array ('31', '16', '16', '31', '17', '17', '31'),
                    5 => array ('31', '16', '16', '31', '1', '1', '31'),
                    4 => array ('17', '17', '17', '31', '1', '1', '1'),
                    3 => array ('31', '1', '1', '31', '1', '1', '31'),
                    2 => array ('31', '1', '1', '31', '16', '16', '31'),
                    1 => array ('1', '1', '1', '1', '1', '1', '1'),
                    0 => array ('31', '17', '17', '17', '17', '17', '31')
                );
                $left = floor ((self::$Width / 2) - 2);
                foreach ($INFOMATRIX as $key => $value) {
                    if ($key <= $rules['data']['start']) {
                        $frame = self::_gen_fill_screen (self::$NullBit);
                        $top = floor ((self::$Height / 2) - 3 + self::$HeightOffset);
                        foreach ($value as $code) {
                            $line = self::prezero (decbin ((int) $code), 5);
                            $line = str_ireplace ('0', self::$NullBit, $line);
                            $line = str_ireplace ('1', $key, $line);
                            $frame = self::PlaceInFrame ($frame, $line, $top, $left);
                            $top++;
                        }
                        for ($x = 0; $x < ($rules['data']['length'] * ($fps / 2)); $x++) {
                            $OUT[] = $frame;
                        }
                    }
                }
                if ($rules['data']['ps'] === TRUE) {
                    $left = floor ((self::$Width / 2) - 15);
                    $top = floor ((self::$Height / 2) - 5 + self::$HeightOffset);
                    $noise = array ('2147483647', '1543503901', '1132462305', '1081083649', '1074673665', '1073856513', '1074673665', '1081083649', '1132462305', '1543503901', '2147483647');
                    $frame = self::_gen_fill_screen (self::$NullBit);
                    $OUT[] = $frame;
                    foreach ($noise as $code) {
                        $line = self::prezero (decbin ((int) $code), 31);
                        $line = str_ireplace ('0', self::$NullBit, $line);
                        $line = str_ireplace ('1', '*', $line);
                        $frame = self::PlaceInFrame ($frame, $line, $top, $left);
                        $top++;
                    }
                    $OUT[] = $frame;
                }

                break;

            default:
                $OUT[] = self::$Templates['null'];
                break;
        }
        return $OUT;
    }

    public static function StockOut ($frame, $picture) {
        $side = '|   |';
        $side2 = '| O |';
        if (self::$BorderCounter == 0) {
            $OUTFRAME = $side2 . str_pad ('- ' . $frame . ' ', (self::$Width + 1), '-') . $side2 . "\r\n";
        } else {
            $OUTFRAME = $side . str_pad ('- ' . $frame . ' ', (self::$Width + 1), '-') . $side . "\r\n";
        }
        self::$BorderCounter++;
        if (self::$BorderCounter > 3) {
            self::$BorderCounter = 0;
        }
        foreach ($picture as $line) {
            if (self::$BorderCounter == 0) {
                $OUTFRAME .= $side2 . $line . $side2 . "\r\n";
            } else {
                $OUTFRAME .= $side . $line . $side . "\r\n";
            }
            self::$BorderCounter++;
            if (self::$BorderCounter > 3) {
                self::$BorderCounter = 0;
            }
        }
        if (self::$BorderCounter == 0) {
            $OUTFRAME .= $side2 . str_pad (' FAS+FAFX -', (self::$Width + 1), '-', STR_PAD_LEFT) . $side2 . "\r\n";
        } else {
            $OUTFRAME .= $side . str_pad (' FAS+FAFX -', (self::$Width + 1), '-', STR_PAD_LEFT) . $side . "\r\n";
        }
        self::$BorderCounter++;
        if (self::$BorderCounter > 3) {
            self::$BorderCounter = 0;
        }
        return $OUTFRAME;
    }

    private static function PlaceInFrame ($frame, $text, $top, $left) {
        for ($index = 0; $index < mb_strlen ($text, 'UTF-8'); $index++) {
            $frame[$top][$left + $index] = $text[$index];
        }
        return $frame;
    }

    private static function prezero ($n, $max) {
        $ret = str_pad ($n, $max, "0", STR_PAD_LEFT);
        return $ret;
    }

    private static function TextSplit ($string, $your_desired_width) {
        $OUT = array ();
        $WORDS = explode (' ', $string);
        $line = '';
        foreach ($WORDS as $value) {
            if (mb_strlen ($line . $value) > $your_desired_width) {
                $OUT[] = $line;
                $line = $value;
            } else {
                $line .= ' ' . $value;
            }
        }
        $OUT[] = $line;
        return $OUT;
    }

    private static function _gen_fill_screen ($bit) {
        if (!isset (self::$Templates[$bit])) {
            $FILL = array ();
            $line = '';
            $z = self::$HeightOffset;
            for ($y = 0; $y <= self::$Width; $y++) {
                $line .= $bit;
            }
            for ($x = 0; $x < self::$Height; $x++) {
                $FILL[$z] = $line;
                $z++;
            }
            self::$Templates[$bit] = $FILL;
        }
        return self::$Templates[$bit];
    }

    private static function _set_title ($text, $bit, $length) {
        $TextWidth = mb_strlen ($text, 'UTF-8');
        $left = floor ((self::$Width / 2) - ($TextWidth / 2));
        $top = floor (self::$Height / 2);
        $Frames = FilmArrayStudio::_get ('frames');
        $Frames[] = array (
            'type' => 'centext',
            'data' => array (
                'length' => $length,
                'top' => $top,
                'left' => $left,
                'text' => $text,
                'bit' => $bit
            )
        );
        FilmArrayStudio::_set ('frames', $Frames);
    }

}