<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        $connection = $app['config']->get('database.default');
        $database = $app['config']->get('database.connections.sqlite.database');

        if (
            ! $app->environment('testing')
            || $connection !== 'sqlite'
            || $database !== ':memory:'
        ) {
            throw new \RuntimeException(
                'Tests blocked: testing environment and SQLite :memory: are required.'
            );
        }

        return $app;
    }
}
