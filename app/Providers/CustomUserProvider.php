<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use App\User;
use Session;

class CustomUserProvider implements UserProvider
{
    public function retrieveById($identifier){
        dd(1);
    }
    public function retrieveByToken($identifier, $token){
        dd(1);
    }
    public function updateRememberToken(Authenticatable $user, $token){
        dd(1);
    }
    public function retrieveByCredentials(array $credentials){
        dd(1);
    }
    public function validateCredentials(Authenticatable $user, array $credentials){
        dd(1);
    }
}