<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected bool $authenticateAdmin = true;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->authenticateAdmin) {
            $this->withSession(['admin.authenticated' => true]);
        }
    }
}
