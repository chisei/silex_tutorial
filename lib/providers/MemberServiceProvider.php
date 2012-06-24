<?php

namespace SilexTutorial\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class MemberServiceProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
        require __DIR__.'/../services/Member.php';

        $app['member'] = $app->share(function() use($app) {
            return new \SilexTutorial\Member($app['db']);
        });
    }

    public function boot(Application $app)
    {
    }
}
