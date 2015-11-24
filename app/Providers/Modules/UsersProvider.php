<?php

namespace App\Providers\Modules;

use Illuminate\Support\ServiceProvider;
use App\Libraries\Booted\BootedTrait;
use App\User;

class UsersProvider extends ServiceProvider
{

    use BootedTrait;

    protected $controller = 'App\Http\Controllers\UsersController';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootBootedTrait();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        // register policies
        app('policy')->register($this->controller, 'App\Policies\UsersPolicy');

        // register validations
        app('validation')->register($this->controller, 'App\Validators\UsersValidators');

        // module routing
        app('router')->group(['namespace' => 'App\Http\Controllers', 'prefix' => 'admin'], function($router){
            $router->bind('users', function($slug) {
                if(!$user = (User::whereSlug($slug)->first() ?: User::find($slug)))
                    app()->abort(404);
                return $user;
            });
            $router->get('users/data', 'UsersController@data');
            $router->get('users/{users}/duplicate', 'UsersController@duplicate');
            $router->get('users/{users}/delete', 'UsersController@delete');
            $router->get('users/{users}/revisions', 'UsersController@revisions');
            $router->get('users/{users}/assume', 'UsersController@assume');
            $router->get('users/{users}/activate', 'UsersController@activate');
            $router->get('users/{users}/deactivate', 'UsersController@deactivate');
            $router->get('auth/resume', 'UsersController@resume');
            $router->resource('users', 'UsersController');
        });
    }

    public function booted()
    {
        // register menus
        app('menu')->register($this->controller, 'App\Menus\UsersMenu');
    }


}
