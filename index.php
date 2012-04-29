<?php

/**
 * @author AlexMcArrow 2008-2012
 * @copyright AlexMcArrow 2008-2012
 * @link http://mcarrow.ru/
 *
 */
header ("Content-Type: text/html; charset=utf-8");
include 'filmarray.php';

FilmArrayStudio::NewFilm ('About FilmArray', 'AlexMcArrow');


FilmArrayFX::NullScreen (5);
FilmArrayFX::CountDown (9, 1, TRUE);
FilmArrayFX::NullScreen (0.5);
FilmArrayFX::Title ('About "FilmArray"');
FilmArrayFX::Titles (array ('This set of scripts to create a "ArrayFilm".', '"ArrayFilm" - the technology to create animation using time-lapse scanning, as in film', 'To view the animation, you need to scroll down the screen at a constant rate'), 12, FilmArrayFX::FAFX_align_left);
FilmArrayFX::NullScreen (0.5);
FilmArrayFX::Title ('The End');
FilmArrayFX::Titles (array ('Alex McArrow', '2012', ' ', ' ', 'FilmArrayStudio + FilmArrayFX'), 6, FilmArrayFX::FAFX_align_center);
FilmArrayFX::NullScreen (1);


FilmArrayStudio::MakeMovie (24);
FilmArrayStudio::SaveProject ('about');
FilmArrayStudio::SaveMovie ('about');
FilmArrayStudio::SaveMovieBorder ('about');

FilmArrayStudio::ShowLog ();