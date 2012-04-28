<?php

/**
 * @author AlexMcArrow 2008-2012
 * @copyright AlexMcArrow 2008-2012
 * @link http://mcarrow.ru/
 *
 */
header ("Content-Type: text/html; charset=utf-8");
include 'filmarray.php';

FilmArrayStudio::NewFilm ('Born of BIT', 'AlexMcArrow');

FilmArrayFX::NullScreen (5);
FilmArrayFX::Title ('Born of BIT');
FilmArrayFX::FillScreen ('0', 3);
FilmArrayFX::Title ('1', '0', 1.5);
FilmArrayFX::Title ('111', '0', 1);
FilmArrayFX::Title ('11111', '0', 1);
FilmArrayFX::Title ('1111111', '0', 1);
FilmArrayFX::Title ('111111111', '0', 1);
FilmArrayFX::Title ('11111111111', '0', 1);
FilmArrayFX::Title ('1111111111111', '0', 1);
FilmArrayFX::Title ('111111111111111', '0', 1);
FilmArrayFX::FillScreen ('1', 2);
FilmArrayFX::Title ('The End');
FilmArrayFX::NullScreen (0.2);
//FilmArrayFX::Titles (array ('Alex McArrow', '2012'), 3);
FilmArrayFX::Title ('Alex McArrow', FALSE, 1);
FilmArrayFX::Title ('2012');

FilmArrayStudio::MakeMovie (32);

FilmArrayStudio::SaveProject ('bitborn');

FilmArrayStudio::SaveMovie ('bitborn');

FilmArrayStudio::ShowLog ();