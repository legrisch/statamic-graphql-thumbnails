<?php

return [
  'git-commit-message' => 'GraphQL thumbnail settings changed.',

  'absolute_urls' => 'Use absolute thumbnail URLs',

  'general_section' => 'General',
  'general_section_jit_title' => 'Just-In-Time thumbnails',
  'general_section_jit_instructions' => "While it's possible to generate thumbnails just-in-time, for security reasons it is not recommended. It is possible for attackers to request a lot of thumbnails to slow down and possibly crash your server. Use with care. Example: `thumbnail(width: 100, crop: \"fill\")`",
  'build_jit' => 'Use Just-In-Time thumbnails',

  'formats_section' => 'Formats',
  'formats_section_title' => 'Thumbnail formats',
  'formats_section_instructions' => "By defining named formats you are able to query thumbnails by name: `thumbnail(name: \"small\")`.",

  'add_format_fields_title' => 'Add format fields',
  'add_format_fields_instructions' => "This allows to query for provided formats without passing parameters. Query for a thumbnail format with the name small: `thumbnail_small`.",

  'add_srcset_title' => 'Add srcset field',
  'add_srcset_instructions' => "This allows to directly query for a srcset with the provided formats.",

  'add_placeholder_title' => 'Add placeholder field',
  'add_placeholder_instructions' => "This adds a field `placeholder` to AssetInterface containing a base64 encoded image with a variable width.",

  'placeholder_width_title' => 'Placeholder width',
  'placeholder_width_instructions' => "Defaults to 32px.",

  'placeholder_blur_title' => 'Placeholder blur amount',
  'placeholder_blur_instructions' => "Defaults to 5.",
];
