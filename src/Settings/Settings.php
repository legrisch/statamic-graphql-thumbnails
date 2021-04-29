<?php

namespace Legrisch\GraphQLThumbnails\Settings;

use Statamic\Yaml\ParseException;
use Illuminate\Support\Facades\Cache;

class Settings
{
  /**
   * Reads the YAML settings file and returns an array of settings
   *
   * Read is done from the cache, if the appropriate key exists and $fromCache
   * is set to true (default).
   *
   * @param   bool fromCache
   * @return  array
   * @throws  ParseException
   */
  public static function read($fromCache = true)
  {
    if ($fromCache && Cache::has(Settings::cacheKey())) {
      return Cache::get(Settings::cacheKey());
    }

    $values = \Statamic\Facades\YAML::parse(\Statamic\Facades\File::disk()->get(Settings::file()));
    Cache::forever(Settings::cacheKey(), $values);

    return $values;
  }

  /**
   * Writes the given array to the Yaml settings file and clears the cache for this key
   *
   * @param array $values
   * @return void
   */
  public static function write($values)
  {
    Cache::forget(Settings::cachekey());
    \Statamic\Facades\File::disk()->put(Settings::file(), \Statamic\Facades\YAML::dump($values));
  }

  /**
   * Returns the file name
   *
   * @return string
   */
  private static function file()
  {
    return base_path("content/gql-thumbnails-settings.yaml");
  }

  /**
   * Returns the cache key
   *
   * @return string
   */
  private static function cacheKey()
  {
    return "gql-thumbnails";
  }
}
