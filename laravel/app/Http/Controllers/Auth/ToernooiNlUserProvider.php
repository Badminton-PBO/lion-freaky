<?php namespace App\Http\Controllers\Auth;

use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Auth\UserProvider;
use App\User;

class ToernooiNlUserProvider implements UserProvider {

    protected $model;

    public function __construct(UserContract $model)
    {
        $this->model = $model;
    }

    public function retrieveById($identifier)
    {

        return new User($identifier."+ID","");
    }

    public function retrieveByToken($identifier, $token)
    {
        return new User("TOKEN","TOKEN");
    }

    public function updateRememberToken(UserContract $user, $token)
    {
        //not needed
    }

    public function retrieveByCredentials(array $credentials)
    {
        //dd("aaa:".$credentials['username']);
        return new User("CRED","CRED");

    }

    public function validateCredentials(UserContract $user, array $credentials)
    {
        $plain = $credentials['password'];
        return true;
    }

}