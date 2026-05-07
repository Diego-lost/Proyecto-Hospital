<?php

namespace Tests;

use Illuminate\Auth\GenericUser;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Usuario del panel (config/dev_login.php, mismo id que ConfigDevUserProvider). */
    protected function devPanelUser(): GenericUser
    {
        return new GenericUser([
            'id' => 1,
            'name' => config('dev_login.name'),
            'email' => config('dev_login.email'),
            'password' => '',
            'remember_token' => '',
        ]);
    }
}
