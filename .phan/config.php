<?php

return [
    'backward_compatibility_checks' => false,
    'quick_mode' => true,
    'analyze_signature_compatibility' => true,
    'minimum_severity' => 5,
    'allow_missing_properties' => true,
    'null_casts_as_any_type' => true,
    'scalar_implicit_cast' => true,
    'ignore_undeclared_variables_in_global_scope' => true,
    'suppress_issue_types' => [
    ],
    'whitelist_issue_types' => [
    ],
    'directory_list' => [
        'src',
        'vendor',
    ],
    'exclude_analysis_directory_list' => [
        'vendor/',
    ],
];
