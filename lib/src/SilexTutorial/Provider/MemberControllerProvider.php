<?php

namespace SilexTutorial\Provider;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;

class MemberControllerProvider implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', function(Application $app) {
            if(!($member = $app['session']->get('member')))
            {
                return $app->redirect('/member/register');
            }

            return $app['twig']->render('member/index.html', array(
                'member' => $member
            ));
        });

        $controllers->get('/register', function(Application $app) {
            if($app['session']->get('member'))
            {
                return $app->redirect('/member/');
            }

            return $app['twig']->render('member/register.html');
        });

        $controllers->post('/register', function(Application $app) {
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

        $controllers->get('/edit', function(Application $app) {
            if(!($member = $app['session']->get('member')))
            {
                return $app->redirect('/member/register');
            }

            return $app['twig']->render('member/edit.html', array(
                'member' => $member
            ));
        });

        $controllers->post('/edit', function(Application $app) {
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

        return $controllers;
    }
}
