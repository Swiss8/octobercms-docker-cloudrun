<?php

namespace October\Demo\Tests;

use Backend\Classes\AuthManager;
use Config;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use PluginTestCase;
use System\Classes\PluginManager;

abstract class BaseTestCase extends PluginTestCase
{
    public $header;

    /**
     * Perform test case set up.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->header = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        // Get the plugin manager
        $pluginManager = PluginManager::instance();

        // Register the plugins to make features like file configuration available
        $pluginManager->registerAll(true);

        // Boot all the plugins to test with dependencies of this plugin
        $pluginManager->bootAll(true);

        // Add factories
        $this->app->singleton(Factory::class, function($app) {
            $faker = $app->make(Generator::class);
            return Factory::construct($faker, __DIR__.('/../database/factories'));
        });
    }

    /**
     * Perform test case tear down.
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();

        // Get the plugin manager
        $pluginManager = PluginManager::instance();

        // Ensure that plugins are registered again for the next test
        $pluginManager->unregisterAll();
    }
}
