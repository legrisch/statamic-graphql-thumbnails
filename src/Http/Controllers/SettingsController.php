<?php

namespace Legrisch\GraphQLThumbnails\Http\Controllers;

use Legrisch\GraphQLThumbnails\Settings\Settings;
use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class SettingsController extends CpController
{
  public function __construct(Request $request)
  {
    parent::__construct($request);
  }

  public function index(Request $request)
  {
    if (!User::current()->can('manage graphql thumbnail settings')) {
      // TODO naive Permissions handling
      return;
    }

    $blueprint = $this->formBlueprint();
    $fields = $blueprint->fields();

    $values = Settings::read(false);

    $fields = $fields->addValues($values);

    $fields = $fields->preProcess();

    return view('gql-thumbnails::settings', [
      'blueprint' => $blueprint->toPublishArray(),
      'values'    => $fields->values(),
      'meta'      => $fields->meta(),
    ]);
  }

  public function update(Request $request)
  {
    if (!User::current()->can('manage graphql thumbnail settings')) {
      // TODO naive Permissions handling
      return;
    }

    $blueprint = $this->formBlueprint();
    $fields = $blueprint->fields()->addValues($request->all());

    // Perform validation. Like Laravel's standard validation, if it fails,
    // a 422 response will be sent back with all the validation errors.
    $fields->validate();

    // Perform post-processing. This will convert values the Vue components
    // were using into values suitable for putting into storage.
    $values = $fields->process()->values();

    Settings::write($values->toArray());
  }

  protected function formBlueprint()
  {
    return Blueprint::makeFromSections([
      'name' => [
        'display' => __('gql-thumbnails::general.general_section'),
        'fields' => [
          'general_section' => [
            'type' => 'section',
            'display' => __('gql-thumbnails::general.general_section_title'),
            'instructions' => __('gql-thumbnails::general.general_section_instructions')
          ],
          'build_jit' => [
            'display' => __('gql-thumbnails::general.build_jit'),
            'type' => 'toggle',
            'icon' => 'toggle',
            'listable' => 'hidden',
            'validate' => ['required']
          ]
        ],
      ],
      'formats' => [
        'display' => __('gql-thumbnails::general.formats_section'),
        'fields' => [
          'formats_section' => [
            'type' => 'section',
            'display' => __('gql-thumbnails::general.formats_section_title'),
            'instructions' => __('gql-thumbnails::general.formats_section_instructions')
          ],
          'add_format_fields' => [
            'display' => __('gql-thumbnails::general.add_format_fields_title'),
            'instructions' => __('gql-thumbnails::general.add_format_fields_instructions'),
            'type' => 'toggle',
            'icon' => 'toggle',
            'listable' => 'hidden',
            'validate' => ['required']
          ],
          'formats' => [
            'type' => 'replicator',
            'display' => 'Formats',
            'sets' => [
              'text' => [
                'display' => 'Format',
                'fields' => [
                  'name' => [
                    'handle' => 'name',
                    'field' => [
                      'input_type' => 'text',
                      'antlers' => false,
                      'display' => "Name",
                      'type' => "text",
                      'icon' => "text",
                      'width' => 100,
                      'listable' => "hidden",
                      'validate' => ['required', 'alpha_dash']
                    ],
                  ],
                  'width' => [
                    'handle' => 'width',
                    'field' => [
                      'display' => "Width",
                      'type' => "integer",
                      'icon' => "integer",
                      'width' => 33,
                      'listable' => "hidden"
                    ],
                  ],
                  'height' => [
                    'handle' => 'height',
                    'field' => [
                      'display' => "Height",
                      'type' => "integer",
                      'icon' => "integer",
                      'width' => 33,
                      'listable' => "hidden"
                    ],
                  ],
                  'fit' => [
                    'handle' => 'fit',
                    'field' => [
                      'options' => [
                        "contain" => "contain",
                        "max" => "max",
                        "fill" => "fill",
                        "stretch" => "stretch",
                        "crop" => "crop",
                        "crop_focal" => "crop_focal",
                      ],
                      'default' => 'crop_focal',
                      'required' => true,
                      'multiple' => false,
                      'clearable' => false,
                      'searchable' => true,
                      'taggable' => false,
                      'push_tags' => false,
                      'cast_booleans' => false,
                      'display' => 'Fit',
                      'type' => 'select',
                      'icon' => 'select',
                      'listable' => 'hidden',
                      'width' => 33,
                      'validate' => ['required']
                    ],
                  ],
                ]
              ]
            ]
          ],
        ],
      ],
    ]);
  }
}
