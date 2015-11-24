<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Repositories\UsersRepository;
use App\Http\Controllers\Controller;
use yajra\Datatables\Html\Builder;
use App\User;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Builder $htmlBuilder)
    {
        $DataTable = $htmlBuilder
            ->addColumn(['data' => 'name', 'name' => 'name', 'title' => trans('users.name')])
            ->addColumn(['data' => 'email', 'name' => 'email', 'title' => trans('users.email')])
            ->addColumn(['data' => 'is_active', 'name' => 'is_active', 'title' => trans('users.is_active')])
            ->ajax(action('UsersController@data'));
        return view()->make('users.index', compact('DataTable'));
    }

    /**
     * Data listing of the resource for DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function data()
    {
        return app('datatables')
            ->of(User::whereNotNull('name'))
            ->editColumn('name', function($user){
                if(app('policy')->check('App\Http\Controllers\UsersController', 'show', [$user->slug])) {
                    return link_to_action('UsersController@show', $user->name, $user->slug);
                }
                return $user->name;
            })
            ->editColumn('is_active', function($user){
                return $user->status();
            })
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view()->make('users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = UsersRepository::create(new User, $request->all());
        return redirect()
            ->action('UsersController@index')
            ->with('success', trans('users.created', ['name' => $user->name]));
    }

    /**
     * Display the specified resource.
     *
     * @param  App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return view()->make('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return view()->make('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $user = UsersRepository::update($user, $request->all());
        $user->roles()->sync($request->get('roles'));
        return redirect()
            ->action('UsersController@index')
            ->with('success', trans('users.updated', ['name' => $user->name]));
    }

    /**
     * Duplicates the specified resource.
     *
     * @param  App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function duplicate(User $user)
    {
        $rand = str_random(4);
        $user = UsersRepository::duplicate($user, [
            'name' => $user->name . '-' . $rand,
            'email' => $rand . '-' . $user->email,
        ]);
        return redirect()
            ->action('UsersController@edit', $user->slug)
            ->with('success', trans('users.created', ['name' => $user->name]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        UsersRepository::delete($user);
        return redirect()
            ->action('UsersController@index')
            ->with('success', trans('users.deleted', ['name' => $user->name]));
    }

    /**
     * Deletes the resource
     *
     * @param  App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function delete(User $user)
    {
        return $this->destroy($user);
    }

    /**
     * Display the specified resource revisions.
     *
     * @param  App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function revisions(User $user)
    {
        return view()->make('users.revisions', compact('user'));
    }

    /**
     * Login as user.
     *
     * @param  App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function assume(User $user)
    {
        UsersRepository::assume($user);
        return redirect()->to('/')
            ->with('success', trans('users.assumed_as', ['name' => $user->name]));
    }

    /**
     * Login as old user.
     *
     * @return \Illuminate\Http\Response
     */
    public function resume()
    {
        $user = app('auth')->user();
        UsersRepository::resume();
        return redirect()->action('UsersController@show', $user->slug)
            ->with('success', trans('users.resumed'));
    }

    /**
     * Activates a user.
     *
     * @param  App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function activate(User $user)
    {
        UsersRepository::setActivation($user, true);
        return redirect()->action('UsersController@show', $user->slug)
            ->with('success', trans('users.activated', ['name' => $user->name]));
    }

    /**
     * Deactivates a user.
     *
     * @param  App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function deactivate(User $user)
    {
        UsersRepository::setActivation($user, false);
        return redirect()->action('UsersController@show', $user->slug)
            ->with('success', trans('users.deactivated', ['name' => $user->name]));
    }

    public function __construct()
    {
        $this->middleware('title');
        $this->middleware('menu');
        $this->middleware('policy');
        $this->middleware('validate');
    }
}
