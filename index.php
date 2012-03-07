<?php

//$phar = new Phar('silex.phar');
//$phar->extractTo('./silex'); // extract all files

//require_once __DIR__.'/silex.phar'; 
require_once __DIR__ . '/vendor/silex/vendor/.composer/autoload.php';

$app = new Silex\Application(); 
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path'       => __DIR__.'/views',
  'twig.class_path' => __DIR__.'/vendor/twig/lib',
));
$app->register(new Silex\Provider\ValidatorServiceProvider(), array(
  'validator.class_path'    => __DIR__.'/vendor/symfony/src',
));
$app->register(new Silex\Provider\FormServiceProvider(), array(
  'form.class_path' => __DIR__ . '/vendor/symfony/src'
));

$app->get('/{url}', function() use($app) { 
  $form = $app['form.factory']
    ->createBuilder('form')
    ->add('username','text')
    ->add('password','password')
    ->add('bookmarks','file')
    ->getForm()
    ->createView();

  return $app['twig']->render('home.twig', array(
    'form' => $form
  ));
})->assert('url','.*'); 


$app->run(); 
