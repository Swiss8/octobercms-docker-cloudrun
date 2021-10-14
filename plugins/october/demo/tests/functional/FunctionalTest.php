<?php

namespace October\Demo\Tests\Feature;

use Auth;
use RainLab\User\Models\User;
use October\Demo\Tests\BaseTestCase;

class FunctionalTest extends BaseTestCase
{
    protected $user;

    /**
     * Perform test case set up.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // Requires Rainlab\User
        // $this->user = factory(User::class)->create();
    }

    private function login()
    {
        // Requires Rainlab\User
        // Auth::login($this->user);
    }

    public function testExample()
    {
        $this->markTestSkipped();
    }
}
