<?php
require_once __DIR__.'/silex.phar'; 

$app = new Silex\Application(); 

$app->get('/', function() use($app) { 
  return 'Hello everyone!'; 
}); 

$app->run(); 
