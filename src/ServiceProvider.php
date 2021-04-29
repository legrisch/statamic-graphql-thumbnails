<?php

namespace Legrisch\GraphQLThumbnails;

use Legrisch\GraphQLThumbnails\GraphQLProvider\GraphQLProvider;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;

class EntryPolicy
{
    public function edit($user, $entry)
    {
        return $user->hasPermission("manage-graphql-thumbnail-settings");
    }
}

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'cp' => __DIR__ . '/routes/cp.php',
    ];

    public function boot()
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__ . '/../resources/views/', 'gql-thumbnails');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'gql-thumbnails');

        Nav::extend(function ($nav) {
            $nav->content('GraphQL Thumbnails')
                ->section('Tools')
                ->can('manage graphql thumbnail settings')
                ->route('legrisch.gql-thumbnails.index')
                ->icon('folder-image');
        });

        GraphQLProvider::createFields();

        $this->app->booted(function () {
            Permission::register('manage graphql thumbnail settings')
                ->label('Manage GraphQL Thumbnail Settings');
        });
    }
}
