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

    const FAS_version = '0.2.2';

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
        self::$Movie = array ();
        foreach (self::$Scenario['frames'] as $key => $value) {
            self::$Movie = array_merge (self::$Movie, FilmArrayFX::DrawFrames ($value, $fps));
        }
        self::$Log[] = 'FilmArrayStudio :: movie compiled';
        return print_r (self::$Movie, TRUE);
    }

    public static function SaveMovie ($filename) {
        self::MakeMovie ();
        $BODY = '<pre style="font-size:1.5em;"><h1>' . self::$Scenario['name'] . '</h1>by ' . self::$Scenario['author'] . '<hr/>';
        $BODY .= print_r (self::$Movie, TRUE);
        $BODY .= '<hr/>FilmArrayStudio ' . self::FAS_version . '<br/>FilmArrayFX ' . FilmArrayFX::FAFX_version . '<hr/></pre>';
        file_put_contents ($filename . '.html', $BODY);
        self::$Log[] = 'FilmArrayStudio :: movie save as (' . $filename . '.html)';
    }

}

class FilmArrayFX {

    const FAFX_version = '0.3.2';

    private static $HeightOffset;
    private static $Width;
    private static $Height;
    private static $NullBit;
    private static $Templates;

    function __construct () {
        self::$Width = 43;
        self::$Height = 15;
        self::$HeightOffset = 10;
        self::$NullBit = ' ';
        self::$Templates = array ();
    }

    public static function Title ($titles, $fillbit = FALSE, $length = 2) {
        self::_set_title ($titles, $fillbit, $length);
    }

    public static function Titles ($titles, $frames) {
        /// frames - the number of seconds for which the text should go all the way from the bottom to the top
        $Frames = FilmArrayStudio::_get ('frames');
        $Frames[] = array (
            'type' => 'titles',
            'data' => array (
                'length' => $frames,
                'titles' => $titles
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
                for ($index = 0; $index < mb_strlen ($rules['data']['text'], 'UTF-8'); $index++) {
                    $frame[$rules['data']['top'] + self::$HeightOffset][$rules['data']['left'] + $index] = $rules['data']['text'][$index];
                }
                for ($len = self::$HeightOffset; $len < (($rules['data']['length'] * $fps) + self::$HeightOffset); $len++) {
                    $OUT[] = $frame;
                }
                break;

            case 'titles':
                break;

            default:
                $OUT[] = self::$Templates['null'];
                break;
        }
        return $OUT;
    }

    private static function _gen_titles_screens () {
        
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