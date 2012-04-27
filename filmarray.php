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

    const FAS_version = '0.2';

    public static $Scenario;
    public static $Movie;

    function __construct () {
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

    public static function NewFilm ($name, $author) {
        self::$Scenario = array (
            'name' => $name,
            'author' => $author,
            'frames' => array ()
        );
        self::$Movie = array ();
    }

    public static function SaveProject ($filename) {
        file_put_contents ($filename . '.fa', json_encode (self::$Scenario));
    }

    public static function LoadProject ($filename) {
        self::$Scenario = json_decode (file_get_contents ($filename . '.fa'), TRUE);
        self::$Movie = array ();
    }

    public static function MakeMovie () {
        self::$Movie = array ();
        foreach (self::$Scenario['frames'] as $key => $value) {
            self::$Movie = array_merge (self::$Movie, FilmArrayFX::DrawFrames ($value));
        }
    }

    public static function SaveMovie ($filename) {
        self::MakeMovie ();
        $BODY = '<pre style="font-size:2em;">';
        $BODY .= print_r (self::$Movie, TRUE);
        $BODY .= '<hr/>FilmArrayStudio ' . self::FAS_version . '<br/>FilmArrayFX ' . FilmArrayFX::FAFX_version . '<hr/>';
        file_put_contents ($filename . '.html', $BODY);
    }

}

class FilmArrayFX {

    const FAFX_version = '0.3';

    private static $Width;
    private static $Height;
    private static $FPS;
    private static $NullBit;
    private static $Templates;

    function __construct () {
        self::$Width = 32;
        self::$Height = 10;
        self::$FPS = 24;
        self::$NullBit = ' ';
        self::$Templates = array ();
        self::_gen_null_screen ();
    }

    public static function Titles ($titles, $speed) {
        /// scrolling titles
    }

    public static function Title ($titles, $fillbit = FALSE, $length = 2) {
        self::_set_title ($titles, $fillbit, $length);
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

    public static function DrawFrames ($rules) {
        $OUT = array ();
        switch ($rules['type']) {
            case 'null':
                for ($len = 0; $len < ($rules['data']['length'] * self::$FPS); $len++) {
                    $OUT[] = self::$Templates['null'];
                }
                break;
            case 'fill':
                for ($len = 0; $len < ($rules['data']['length'] * self::$FPS); $len++) {
                    $OUT[] = self::_gen_fill_screen ($rules['data']['bit']);
                }
                break;
            case 'centext':
                if ($rules['data']['bit'] !== FALSE) {
                    $frame = self::_gen_fill_screen ($rules['data']['bit']);
                } else {
                    $frame = self::$Templates['null'];
                }
                for ($index = 0; $index < mb_strlen ($rules['data']['text'], 'UTF-8'); $index++) {
                    $frame[$rules['data']['top']][$rules['data']['left'] + $index] = $rules['data']['text'][$index];
                }
                for ($len = 0; $len < ($rules['data']['length'] * self::$FPS); $len++) {
                    $OUT[] = $frame;
                }
                break;

            default:
                $OUT[] = self::$Templates['null'];
                break;
        }
        return $OUT;
    }

    private static function _gen_null_screen () {
        self::$Templates['null'] = self::_gen_fill_screen (self::$NullBit);
    }

    private static function _gen_fill_screen ($bit) {
        $FILL = array ();
        $line = '';
        for ($y = 0; $y <= self::$Width; $y++) {
            $line .= $bit;
        }
        for ($x = 0; $x < self::$Height; $x++) {
            $FILL[] = $line;
        }
        return $FILL;
    }

    private static function _set_title ($text, $bit, $length) {
        $TextWidth = mb_strlen ($text, 'UTF-8');
        $left = round ((self::$Width / 2) - ($TextWidth / 2));
        $top = round (self::$Height / 2);
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