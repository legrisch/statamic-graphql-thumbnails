<?php

namespace Legrisch\GraphQLThumbnails;

use Illuminate\Support\Facades\Log;
use Statamic\Providers\AddonServiceProvider;

use Statamic\Facades\GraphQL;
use Statamic\Facades\Image;
use Statamic\Facades\URL;

class ThumbnailsServiceProvider extends AddonServiceProvider
{
    public function boot()
    {
        GraphQL::addField('AssetInterface', 'thumbnail', function () {
            return [
                'type' => GraphQL::string(),
                'args' => [
                    'width' => [
                        'type' => GraphQL::int(),
                    ],
                    'height' => [
                        'type' => GraphQL::int(),
                    ]
                ],
                'resolve' => function ($entry, $args) {
                    try {
                        $width = $args["width"] ?? null;
                        $height = $args["height"] ?? null;

                        if ($width === null && $height === null) {
                            throw new \Exception("Error Processing Request: Please provide a width or a height", 1);
                        }

                        if ($entry == null || !$entry->isImage()) return null;

                        $image = Image::manipulate($entry);
                        if ($width) {
                            $image = $image->width($width);
                        }
                        if ($height) {
                            $image = $image->height($height);
                        }
                        $url = $image->build();
                        return URL::makeAbsolute($url);
                    } catch (\Throwable $th) {
                        Log::error("Unable to resolve field 'thumbnail': " . $th->getMessage());
                        throw new \Exception("Error Processing Request: " . $th->getMessage(), 1);
                    }
                },
            ];
        });
    }
}
