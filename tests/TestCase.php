<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function unlock(): static
    {
        $this->withSession(['crm_unlocked' => true]);

        return $this;
    }
}
