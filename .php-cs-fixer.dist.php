<?php

$finder = PhpCsFixer\Finder::create();
$config = new PhpCsFixer\Config();


return $config->setRules([
    '@PSR12' => true,
    '@PhpCsFixer' => true,
    'array_indentation' => true,
    'array_syntax' => ['syntax' => 'short'],
    'braces' => true,
    'cast_spaces' => ['space' => 'none'],
    'class_definition' => ['space_before_parenthesis' => true],
    'concat_space' => ['spacing' => 'one'],
    'echo_tag_syntax' => [
        'format' => 'short',
        'shorten_simple_statements_only' => true,
    ],
    'global_namespace_import' => [
        'import_classes' => true,
        'import_constants' => null,
        'import_functions' => null,
    ],
    'increment_style' => ['style' => 'post'],
    'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
    'multiline_comment_opening_closing' => false,
    'multiline_whitespace_before_semicolons' => false,
    'no_extra_blank_lines' => false,
    'no_unused_imports' => false,
    'no_whitespace_before_comma_in_array' => true,
    'phpdoc_to_comment' => false,
    'semicolon_after_instruction' => false,
    'trailing_comma_in_multiline' => true,
    'types_spaces' => ['space' => 'none', 'space_multiple_catch' => 'single'],
    'yoda_style' => false,
])->setFinder($finder);
