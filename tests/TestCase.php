<?php

namespace Studio\Totem\Tests;

use Exception;
use Studio\Totem\User;
use Studio\Totem\Totem;
use Illuminate\Support\Facades\Auth;
use Orchestra\Testbench\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Studio\Totem\Providers\TotemServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'testing']);

        $this->artisan('totem:assets');

        $this->loadLaravelMigrations(['--database' => 'testing']);

        $this->withFactories(__DIR__.'/../database/factories/');

        $auth = function () {
            switch (app()->environment()) {
                case 'local':
                    return true;
                    break;
                case 'testing':
                    return Auth::check();
                    break;
                default:
                    return false;
            }
        };

        Totem::auth($auth);
    }

    protected function getPackageProviders($app)
    {
        return [
            TotemServiceProvider::class,
        ];
    }

    /**
     * Disable Exception Handling.
     */
    protected function disableExceptionHandling()
    {
        app()->instance(ExceptionHandler::class, new PassThroughHandler);

        return $this;
    }

    /**
     * Creates and signs in a user.
     *
     * @return $this
     */
    public function signIn()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user);

        return $this;
    }
}

class PassThroughHandler extends Handler
{
    public function __construct()
    {
    }
    public function report(Exception $e)
    {
        // no-op
    }
    public function render($request, Exception $e)
    {
        throw $e;
    }
}
