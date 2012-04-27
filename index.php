<?php

/**
 * @author AlexMcArrow 2008-2012
 * @copyright AlexMcArrow 2008-2012
 * @link http://mcarrow.ru/
 *
 */
include 'filmarray.php';

new FilmArrayStudio();

FilmArrayStudio::NewFilm ('Test', 'AlexMcArrow', 16);

FilmArrayFX::Titles ('The End');

//FilmArrayStudio::SaveProject ('test');

echo '<pre>';
print_r (FilmArrayStudio::$Scenario);
print_r (FilmArrayStudio::$Movie);