<?php

require_once __DIR__.'/../vendor/autoload.php'; 

use SilexTutor\Provider;

$app = new Silex\Application(); 

// twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

// member
$app->register(new SilexTutorial\Provider\MemberServiceProvider());

// doctrine
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_mysql',
        'dbname'   => 'silex_tutorial',
        'host'     => 'localhost',
        'user'     => 'silex',
        'password' => 'tutorial'
    ),
));

// session
$app->register(new Silex\Provider\SessionServiceProvider());
$app['session']->start();

// form
$app->register(new Silex\Provider\FormServiceProvider());

$app->get('/', function() use($app) {
    return $app['twig']->render('index.html');
});

$app->mount('/member', new SilexTutorial\Provider\MemberControllerProvider());
$app->mount('/admin', new SilexTutorial\Provider\AdminControllerProvider());

$app->get('/hello/world', function() use($app) {
    return 'Hello world!';
});

$app->get('/hello/{name}', function($name) use($app) {
    return 'Hello ' . $app->escape($name);
});

$app->get('/login/', function() use($app) {
    return $app['twig']->render('/login.html');
});

$app->post('/login/', function() use($app) {
    $request = $app['request'];

    $email = $app['request']->get('email');
    $password = $app['request']->get('password');

    $authenticated = $app['member']->authenticate($email, $password);

    if($authenticated === true)
    {
        $app['session']->set('member', $app['member']->getByEmail($email));

        return $app->redirect('/member/');
    }

    return $app->redirect('/');
});

$app->get('/logout/', function() use($app) {
    if(!($member = $app['session']->get('member')))
    {
        return $app->redirect('/');
    }

    $app['session']->remove('member');

    return $app->redirect('/');
});

$app->run(); 
