<?php

//$phar = new Phar('silex.phar');
//$phar->extractTo('./silex'); // extract all files

//require_once __DIR__.'/silex.phar'; 
require_once __DIR__ . '/silex/vendor/.composer/autoload.php';

$app = new Silex\Application(); 

$app->get('/', function() use($app) { 
  return 'Hello everyone!'; 
}); 

$app->run(); 
