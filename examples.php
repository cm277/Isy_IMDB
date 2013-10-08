<?php
require_once('Isy_IMDB.php');

$imdb = new Isy_IMDB();
$mA = $imdb->getMovieInfo("tt1596343");
//$mA2 = $imdb->getMovieInfo("tt1596350");
$mCAST = $imdb->getFullCredits('tt1596343');
//$mCAST2 = $imdb->getFullCredits('tt1596350');

$mIP = $imdb->getPersonInfo("nm0000881");
//$mIP1 = $imdb->getPersonInfo("nm0001006");
//$mIP2 = $imdb->getPersonInfo("nm0005024");


?><pre><?php

print_r($mA);
//print_r($mA2);
print_r($mCAST);
//print_r($mCAST2);

print_r($mIP);
//print_r($mIP1);
//print_r($mIP2);

?></pre>