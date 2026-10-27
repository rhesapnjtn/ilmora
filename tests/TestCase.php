<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Feature tests must not depend on compiled frontend assets. CI runs the
        // test suite before `npm run build`, so a missing Vite manifest would make
        // every page that uses @vite() fail with a 500 instead of rendering.
        $this->withoutVite();
    }
}
