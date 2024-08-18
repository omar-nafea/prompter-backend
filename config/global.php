<?php

declare(strict_types=1);

return [
    'max_string_length' => 250,
    'min_password_length' => 6,
    'min_string_length' => 3,
    'max_text_length' => 2500,
    'min_text_length' => 3,
    'min_price' => 1,
    'max_price' => 999999999,
    'min_sort_level' => 1,
    'max_sort_level' => 5,
    'min_integer' => 1,
    'max_integer' => 999999999,
    'min_year' => 1980,
    'max_year' => now()->year,
    'max_image_size' => 1024 * 5,
    'max_pdf_size' => 1024 * 5,
    'default_sort_column' => 'created_at',
    'max_file_name_length' => 150,
];
