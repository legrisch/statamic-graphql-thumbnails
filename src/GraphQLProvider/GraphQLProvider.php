<?php

namespace Legrisch\GraphQLThumbnails\GraphQLProvider;

use Statamic\Facades\GraphQL;
use Statamic\Facades\Image;
use Statamic\Facades\URL;
use League\Glide\Server;
use Illuminate\Support\Facades\Log;
use Legrisch\GraphQLThumbnails\Settings\Settings;
use phpDocumentor\Reflection\Types\Boolean;
use Statamic\Assets\Asset;
use Statamic\Imaging\ImageGenerator;
use Statamic\Support\Str;

class GraphQLProvider
{
  public static $settings;
  public static $imageGenerator;
  public static $glideServer;

  private static function getImageGenerator(): ImageGenerator {
    if (!self::$imageGenerator) {
      self::$imageGenerator = app(ImageGenerator::class);
    }
    return self::$imageGenerator;
  }

  private static function getGlideServer() {
    if (!self::$glideServer) {
      self::$glideServer = app(Server::class);
    }
    return self::$glideServer;
  }

  private static function getSettings()
  {
    if (!isset(self::$settings)) {
      self::$settings = Settings::read();
    }
    return self::$settings;
  }

  private static function jitEnabled(): bool
  {
    return self::getSettings()['build_jit'] ?? false;
  }

  private static function absoluteUrls(): bool
  {
    return self::getSettings()['absolute_urls'] ?? true;
  }

  private static function addFormatFields(): bool
  {
    return self::getSettings()['add_format_fields'] ?? false;
  }

  private static function addSrcset(): bool
  {
    return self::getSettings()['add_srcset'] ?? false;
  }

  private static function addPlaceholder(): bool
  {
    return self::getSettings()['add_placeholder'] ?? false;
  }

  private static function placeholderWidth(): int
  {
    return self::getSettings()['placeholder_width'] ?? 32;
  }

  private static function placeholderBlur(): int
  {
    return self::getSettings()['placeholder_blur'] ?? 5;
  }

  private static function formats()
  {
    $enabledFormats = array_values(array_filter(self::getSettings()['formats'] ?? [], function ($format) {
      $enabled = $format['enabled'];
      return (bool) $enabled;
    }));
    return $enabledFormats;
  }

  private static function hasFormats()
  {
    return count(self::formats()) > 0;
  }

  private static function makeAbsoluteUrl(string $url) : string {
    if (! Str::startsWith($url, '/')) {
      return $url;
    }
    return URL::tidy(Str::ensureLeft($url, config('app.url')));
  }

  private static function hasFormat(string $name)
  {
    if (!self::hasFormats()) return false;

    $names = array_map(function ($format) {
      return $format['name'] ?? '';
    }, self::formats());
    return in_array($name, $names);
  }

  private static function getFormat(string $name)
  {
    if (!self::hasFormat($name)) return null;

    $key = array_search($name, array_column(self::formats(), 'name'));
    return self::formats()[$key];
  }

  private static function formatExistsAndIsDisabled(string $name)
  {
    $allFormats = self::getSettings()['formats'] ?? [];
    if (count($allFormats) === 0) return false;
    $enabledFormats = self::formats();

    $allFormatNames = array_map(function ($format) {
      return $format['name'] ?? '';
    }, $allFormats);

    $enabledFormatNames = array_map(function ($format) {
      return $format['name'] ?? '';
    }, $enabledFormats);

    if (in_array($name, $allFormatNames) && !in_array($name, $enabledFormatNames)) {
      return true;
    }
    return false;
  }

  private static function manipulateImage(Asset $asset, ?int $width, ?int $height, ?string $fit, ?int $blur_amount, ?bool $base64Encode): string
  {
    $options = [];
    
    if ($width) {
      $options['w'] = $width;
    }
    if ($height) {
      $options['h'] = $height;
    }
    if ($blur_amount) {
      $options['blur'] = $blur_amount;
    }
    if ($fit) {
      $options['fit'] = $fit;
    } else {
      $options['fit'] = "crop_focal";
    }


    if ($base64Encode) {
      $path = self::getImageGenerator()->generateByAsset($asset, $options);
      $source = base64_encode(self::getGlideServer()->getCache()->read($path));
      return "data:" . self::getGlideServer()->getCache()->getMimetype($path) . ";base64,{$source}";
    } else {
      $url = Image::manipulate($asset, $options);
    if (self::absoluteUrls()) {
      return self::makeAbsoluteUrl($url);
    }
    return URL::makeRelative($url);
    }
  }

  private static function validateArguments(
    ?int $width,
    ?int $height,
    ?string $fit,
    ?string $name
  ) {
    if (!isset($name) && !isset($width) && !isset($height) && !isset($fit)) {
      throw new \Exception("No arguments provided. Please provide either JIT parameters ('width' or 'height' and optionally 'fit') or a format ('name').", 1);
    }

    // No mixing of JIT and format fields
    if (($width || $height || $fit) && $name) {
      throw new \Exception("JIT and format parameters mixed. Please provide either JIT parameters ('width' or 'height' and optionally 'fit') or a format ('name').", 1);
    }

    // Technically this should not be possible as the JIT fields are
    // not registered, nevertheless we account for that
    if (($width || $height || $fit) && !self::jitEnabled()) {
      throw new \Exception("JIT Thumbnails are requested but disabled.", 1);
    }

    $isFormatRequest = isset($name);

    if ($isFormatRequest) {
      if (!self::hasFormats()) {
        throw new \Exception("No formats defined.", 1);
      }
      if (!self::hasFormat($name)) {
        if (self::formatExistsAndIsDisabled($name)) {
          throw new \Exception("Format '" . $name . "' is disabled.", 1);
        } else {
          throw new \Exception("Unknown format '" . $name . "'.", 1);
        }
      }
    } else {
      if (isset($fit) && !isset($width) && !isset($height)) {
        throw new \Exception("Argument 'fit' provided but no width or height.", 1);
      }
      if (isset($width) && $width < 1) {
        throw new \Exception("Argument 'width' less than 1.", 1);
      }
      if (isset($height) && $height < 1) {
        throw new \Exception("Argument 'height' less than 1.", 1);
      }
      if (isset($fit)) {
        $allowedFitParams = ["contain", "max", "fill", "stretch", "crop", "crop_focal"];
        if (isset($fit) && !in_array($fit, $allowedFitParams)) {
          throw new \Exception("Provided fit '" . $fit . "' not found, refer to the docs for possible values: https://statamic.dev/tags/glide#parameters.", 1);
        }
      }
    }
  }

  static public function createFields()
  {
    if (!self::jitEnabled() && !self::hasFormats()) return;

    GraphQL::addField('AssetInterface', 'thumbnail', function () {

      $arguments = [];

      if (self::jitEnabled()) {
        $arguments["width"] = [
          'type' => GraphQL::int(),
        ];
        $arguments["height"] = [
          'type' => GraphQL::int(),
        ];
        $arguments["fit"] = [
          'type' => GraphQL::string(),
        ];
      }

      if (self::hasFormats()) {
        $arguments["name"] = [
          'type' => GraphQL::string(),
        ];
      }

      return [
        'type' => GraphQL::string(),
        'args' => $arguments,
        'resolve' => function (Asset $asset, $args) {
          try {
            if ($asset === null || !$asset->isImage()) return null;

            $name = $args["name"] ?? null;
            $width = $args["width"] ?? null;
            $height = $args["height"] ?? null;
            $fit = $args["fit"] ?? null;

            self::validateArguments($width, $height, $fit, $name);

            $isFormatRequest = isset($name);

            if ($isFormatRequest) {
              $format = self::getFormat($name);
              return self::manipulateImage(
                $asset,
                $format['width'] ?? null,
                $format['height'] ?? null,
                $format['fit'] ?? null,
                $format['blur_amount'] ?? null,
                $format['base64_encode'] ?? false,
              );
            } else {
              return self::manipulateImage($asset, $width, $height, $fit);
            }
          } catch (\Throwable $th) {
            Log::error("Unable to resolve field 'thumbnail': " . $th->getMessage());
            throw new \Exception("Unable to resolve field 'thumbnail': " . $th->getMessage(), 1);
          }
        },
      ];
    });

    if (self::hasFormats() && self::addFormatFields()) {
      foreach (self::formats() as $format) {
        $name = $format['name'];
        GraphQL::addField('AssetInterface', 'thumbnail_' . $name, function () use ($format) {
          return [
            'type' => GraphQL::string(),
            'resolve' => function (Asset $asset) use ($format) {
              if ($asset === null || !$asset->isImage()) return null;
  
              return self::manipulateImage(
                $asset,
                $format['width'] ?? null,
                $format['height'] ?? null,
                $format['fit'] ?? null,
                $format['blur_amount'] ?? null,
                $format['base64_encode'] ?? false,
              );
            }
          ];
        });
      }
    }

    if (self::hasFormats() && self::addSrcset()) {
      GraphQL::addField('AssetInterface', 'srcset', function () {
        return [
          'type' => GraphQL::string(),
          'resolve' => function (Asset $asset) {
            if ($asset === null || !$asset->isImage()) {
              return null;
            }

            $srcsetItems = [];
            foreach (self::formats() as $format) {
              try {
                $include = $format['include_in_srcset'] ?? false;
                if (!$include || !array_key_exists('width', $format)) continue;
                $formatLargerThanImage = $format['width'] > $asset->width();
                if ($formatLargerThanImage) continue;
                $url = self::manipulateImage(
                  $asset,
                  $format['width'] ?? null,
                  $format['height'] ?? null,
                  $format['fit'] ?? null,
                  $format['blur_amount'] ?? null,
                  $format['base64_encode'] ?? false,
                );
                array_push($srcsetItems, $url . " " .  $format['width'] . "w");
              } catch (\Throwable $th) {}
            }

            return join(', ', $srcsetItems);
          }
        ];
      });
    }
  }
}
