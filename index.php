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
$app->register(new Silex\Provider\FormServiceProvider(), array(
  'form.class_path' => __DIR__ . '/vendor/symfony/src'
));
$app->register(new Silex\Provider\SymfonyBridgesServiceProvider(), array(
  'symfony_bridges.class_path'  => __DIR__.'/vendor/symfony/src',
));
$app->register(new Silex\Provider\SessionServiceProvider(), array(
  'session.storage.save_path' => __DIR__.'/tmp'
));


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->match('/', function(Request $request) use($app) { 
  $form = $app['form.factory']->createBuilder('form')->add('bookmarks','file')->getForm();

  if ($request->getMethod() == 'POST')
  {
    $form->bindRequest($request);
    if ($form->isValid())
    {
      if (!$form['bookmarks']->getData() || !$form['bookmarks']->getData()->isValid())
      {
        $app['session']->setFlash('error', 'Error uploading file.');
        return $app->redirect('/');
      }

      $file = $form['bookmarks']->getData();
      $links = convertXml($_FILES['form']['tmp_name']['bookmarks']);

      return new Response($app['twig']->render('file.twig', array('links' => $links)), 200, array(
        'Content-Type' => 'text/html', 
        'Content-Disposition' => 'attachment;filename=pinboard_import.html',
      ));
    }
  }

  return $app['twig']->render('home.twig', array(
    'form' => $form->createView()
  ));
})->method('GET|POST'); 

$app->match('/{url}', function() use($app) {
  return $app->redirect('/');
})->assert('url','.*');


function convertXml($filename)
{
  $xml = simplexml_load_file($filename);
  $links = array();
  foreach ($xml->bookmarks->bookmark as $bookmark)
  {
    $labels = array();
    foreach($bookmark->labels->label as $label)
    {
      $labels[] = str_replace(' ', '-', $label);
    }

    $links[] = array(
      'href' => $bookmark->url->__toString(),
      'date' => substr($bookmark->timestamp,0,-6),
      'tags' => implode(',', $labels),
      'name' => $bookmark->title->__toString()
    );
  }
  return $links;
}

$app->run(); 
