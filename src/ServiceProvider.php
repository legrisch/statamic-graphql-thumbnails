<?php

namespace Legrisch\GraphQLThumbnails;

use Statamic\Providers\AddonServiceProvider;

use Statamic\Facades\GraphQL;
use Statamic\Facades\Image;
use Statamic\Facades\URL;

class ServiceProvider extends AddonServiceProvider
{
    public function boot()
    {
        GraphQL::addField('AssetInterface', 'thumbnail', function () {
            return [
                'type' => GraphQL::string(),
                'args' => [
                    'width' => [
                        'type' => GraphQL::int(),
                    ]
                ],
                'resolve' => function ($entry, $args) {
                    if ($entry == null) return null;
                    $isImage = $entry->isImage();
                    if (!$isImage) {
                        return null;
                    }
                    $url = Image::manipulate($entry)->width($args["width"])->build();
                    return URL::makeAbsolute($url);
                },
            ];
        });
    }
}
