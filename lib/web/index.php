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

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_mysql',
        'dbname'   => 'silex_tutorial',
        'host'     => 'localhost',
        'user'     => 'silex',
        'password' => 'tutorial'
    ),
));

$app->register(new Silex\Provider\SessionServiceProvider());

$app['session']->start();

$app->get('/', function() use($app) {
    return $app['twig']->render('index.html');
});

// member profile
$app->get('/member/', function() use($app) {
    if(!($member = $app['session']->get('member')))
    {
        return $app->redirect('/member/register');
    }

    return $app['twig']->render('member/index.html', array(
        'member' => $member
    ));
});

$app->get('/member/register', function() use($app) {
    $app['session']->start();
    if($app['session']->get('member'))
    {
        return $app->redirect('/member/');
    }

    return $app['twig']->render('member/register.html');
});

$app->post('/member/register', function() use($app) {
    $app['session']->start();

    // 既にアカウントを所有していたら/member/へ
    if($app['session']->get('member'))
    {
        return $app->redirect('/member/');
    }

    $data = $app['request']->get('member');

    // 登録成功
    if($app['member']->register($data))
    {
        $member = $app['member']->get();
        $app['session']->set('member', $member);

        return $app->redirect('/member/');
    }
    // 登録失敗
    else
    {
        return $app->redirect('/member/register', array(
            'error' => '登録できませんでした'
        ));
    }

});

$app->get('/member/edit', function() use($app) {
    if(!($member = $app['session']->get('member')))
    {
        return $app->redirect('/member/register');
    }

    return $app['twig']->render('member/edit.html', array(
        'member' => $member
    ));
});

$app->post('/member/edit', function() use($app) {
    if(!($member = $app['session']->get('member')))
    {
        return $app->redirect('/member/register');
    }

    $data = $app['request']->get('member');

    if(!$app['member']->edit($data))
    {
        return $app->redirect('/member/edit');
    }

    return $app->redirect('/member/');
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
    return 'logout page';
});

$app->get('/member/contact', function() use($app) {
    return 'member contact page';
});

$app->get('/terms/', function() use($app) {
    return 'terms page';
});


$app->run(); 
