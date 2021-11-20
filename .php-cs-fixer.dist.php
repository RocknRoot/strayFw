<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__ . '/src');

$config = new PhpCsFixer\Config();
$config->setRules([
    '@PSR2' => true,
    '@PSR12' => true,
    'no_trailing_comma_in_singleline_array' => true,
    'no_singleline_whitespace_before_semicolons' => true,
    'braces' => ['allow_single_line_closure' => true],
    'compact_nullable_typehint' => true,
    'concat_space' => [ 'spacing' => 'one' ],
    'no_unused_imports' => true,
    'ordered_imports' => true,
    'blank_line_after_opening_tag' => true,
    'declare_equal_normalize' => [ 'space' => 'none' ],
    'function_typehint_space' => true,
    'lowercase_cast' => true,
    'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
    'native_function_casing' => true,
    'new_with_braces' => true,
    'no_blank_lines_after_class_opening' => true,
    'no_blank_lines_after_phpdoc' => true,
    'no_empty_statement' => true,
    'no_leading_import_slash' => true,
    'no_leading_namespace_whitespace' => true,
    'no_mixed_echo_print' => [ 'use' => 'echo' ],
    'no_whitespace_in_blank_line' => true,
    'class_attributes_separation' => ['elements' => ['method' => 'one', 'trait_import' => 'one']],
    'return_type_declaration' => ['space_before' => 'none'],
    'single_trait_insert_per_statement' => true,
    'phpdoc_add_missing_param_annotation' => true,
    'phpdoc_align' => true,
    'phpdoc_indent' => true,
    'phpdoc_no_empty_return' => true,
    'phpdoc_order' => true,
    'phpdoc_scalar' => true,
    'phpdoc_single_line_var_spacing' => true,
    'phpdoc_types_order' => true,
    'phpdoc_var_annotation_correct_order' => true,
    ])->setFinder($finder);
return $config;
