<?php

require_once __DIR__.'/../vendor/autoload.php'; 

require_once __DIR__.'/../providers/MemberServiceProvider.php';

$app = new Silex\Application(); 

$app['db'] = $app->share(function(){
    return new Pdo('mysql:host=localhost;dbname=silex_tutorial', 'silex', 'tutorial');
});

$app->get('/', function() use($app) {
    return $app['twig']->render('index.html');
});

$app->get('/register/', function() use($app) {
    return $app['twig']->render('register/index.html');
});

// TODO 何かしらの理由で失敗したらGET /register/のテンプレートを表示
$app->post('/register/', function() use($app) {
    $member = $app['member'];

    $data = $app['request']->get('member');

    $member->register($data);

    return 'register post page';
});

$app->post('/login/', function() use($app) {
    return 'login page';
});

$app->get('/logout/', function() use($app) {
    return 'logout page';
});

$app->get('/member/profile', function() use($app) {
    return 'member page';
});

$app->get('/member/edit', function() use($app) {
    return 'member edit';
});

$app->get('/member/contact', function() use($app) {
    return 'member contact page';
});

$app->get('/terms/', function() use($app) {
    return 'terms page';
});

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->register(new SilexTutorial\Provider\MemberServiceProvider(), array(
    'member.class_path' => __DIR__.'/../services',
));

$app->run(); 
