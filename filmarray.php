<?php

/**
 * @author AlexMcArrow 2008-2012
 * @copyright AlexMcArrow 2008-2012
 * @link http://mcarrow.ru/
 *
 */
class FilmArrayStudio {

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

    public static function NewFilm ($name, $author, $width = 16) {
        self::$Scenario = array (
            'name' => $name,
            'author' => $author,
            'width' => $width,
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
        echo 'Making';
    }

}

class FilmArrayFX {

    public static function Titles ($titles) {
        if (is_array ($titles)) {
            foreach ($titles as $text) {
                self::_set_title ($text);
            }
        } else {
            self::_set_title ($titles);
        }
    }

    private static function _set_title ($text) {
        $FrameWidth = FilmArrayStudio::_get ('width');
        $FrameHeight = round ($FrameWidth / 4 * 3);
        $TextWidth = mb_strlen ($text);
        $left = round (($FrameWidth / 2) - ($TextWidth / 2));
        $top = round ($FrameHeight / 2);
        $Frames = FilmArrayStudio::_get ('frames');
        $Frames[] = array (
            'type' => 'centext',
            'data' => array (
                'length' => '4',
                'top' => $top,
                'left' => $left,
                'text' => $text
            )
        );
        FilmArrayStudio::_set ('frames', $Frames);
    }

}

class FilmArrayProjector {

}