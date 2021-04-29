<?php

return [
  'general_section' => 'General',
  'general_section_title' => 'Just-In-Time Thumbnails',
  'general_section_instructions' => "While it's possible to generate Thumbnails just-in-time, for security reasons it is not recommended. It is possible for attackers to request a lot of thumbnails to slow down and possibly crash your server. Use with care. Example: `thumbnail(width: 100, crop: \"fill\")`",
  'build_jit' => 'Use Just-In-Time Thumbnails',

  'formats_section' => 'Formats',
  'formats_section_title' => 'Thumbnail formats',
  'formats_section_instructions' => "By defining named formats you are able to query thumbnails by name: `thumbnail(name: \"small\")`",
];
