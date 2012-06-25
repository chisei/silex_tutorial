<?php

namespace SilexTutorial\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use SilexTutorial\Service;

class MemberServiceProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
        $app['member'] = $app->share(function() use($app) {
            return new \SilexTutorial\Service\Member($app['db']);
        });
    }

    public function boot(Application $app)
    {
    }
}
