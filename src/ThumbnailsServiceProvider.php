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
                    ],
                    'fit' => [
                        'type' => GraphQL::string(),
                    ]
                ],
                'resolve' => function ($asset, $args) {
                    try {
                        $width = $args["width"] ?? null;
                        $height = $args["height"] ?? null;
                        $fit = $args["fit"] ?? null;

                        if ($width === null && $height === null) {
                            throw new \Exception("Please provide a width and/or a height", 1);
                        }

                        if ($asset === null || !$asset->isImage()) return null;

                        $image = Image::manipulate($asset);

                        if ($width) {
                            $image = $image->width($width);
                        }
                        if ($height) {
                            $image = $image->height($height);
                        }

                        $allowedFitParams = ["contain", "max", "fill", "stretch", "crop", "crop_focal"];
                        if ($fit !== null && !in_array($fit, $allowedFitParams)) {
                            throw new \Exception("Provided fit not found, refer to the docs for possible values: https://statamic.dev/tags/glide#parameters", 1);
                        }
                        if ($fit) {
                            $image->fit($fit);
                        } else {
                            $image->fit("crop_focal");
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
