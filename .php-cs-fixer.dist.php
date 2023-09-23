<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__,
    ])
    ->append([
        __FILE__,
    ])
    ->exclude([
        'vendor',
    ])
;

return
    (new PhpCsFixer\Config())
        ->setFinder($finder)
        ->setCacheFile(__DIR__ . '/.php-cs-fixer.dist.cache')
        ->setRiskyAllowed(true)
        ->setRules([
            '@PHP80Migration:risky' => true,
            '@PHP81Migration' => true,
            '@PhpCsFixer' => true,
            '@PhpCsFixer:risky' => true,
            '@PHPUnit100Migration:risky' => true,
            '@PER-CS2.0' => true,
            '@PER-CS2.0:risky' => true,
            'blank_line_before_statement' => [
                'statements' => [
                    'continue',
                    'declare',
                    'default',
                    'return',
                    'throw',
                    'try',
                ],
            ],
            'blank_line_between_import_groups' => false,
            'curly_braces_position' => [
                'classes_opening_brace' => 'same_line',
                'functions_opening_brace' => 'same_line',
            ],
            'comment_to_phpdoc' => ['ignored_tags' => ['fixme']],
            'date_time_immutable' => true,
            'final_class' => true,
            'final_public_method_for_abstract_class' => true,
            'fopen_flags' => ['b_mode' => true],
            'global_namespace_import' => [
                'import_constants' => false,
                'import_functions' => false,
                'import_classes' => false,
            ],
            'logical_operators' => true,
            'method_chaining_indentation' => false,
            'no_break_comment' => false,
            'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true],
            'no_trailing_whitespace_in_string' => false,
            'nullable_type_declaration_for_default_null_value' => true,
            'increment_style' => false,
            'ordered_class_elements' => [
                'order' => [
                    'use_trait',
                    'case',
                    'constant_public',
                    'constant_protected',
                    'constant_private',
                    'property_public_static',
                    'property_protected_static',
                    'property_private_static',
                    'property_public',
                    'property_protected',
                    'property_private',
                    'construct',
                    'destruct',
                    'phpunit',
                    'method_public_static',
                    'method_public_abstract_static',
                    'method_protected_static',
                    'method_protected_abstract_static',
                    'method_private_static',
                    'method_public',
                    'method_public_abstract',
                    'method_protected',
                    'method_protected_abstract',
                    'method_private',
                ],
            ],
            'ordered_imports' => ['imports_order' => ['class', 'function', 'const']],
            'php_unit_strict' => false,
            'php_unit_test_case_static_method_calls' => false,
            'php_unit_internal_class' => false,
            'php_unit_test_class_requires_covers' => false,
            'phpdoc_add_missing_param_annotation' => false,
            'phpdoc_align' => false,
            'phpdoc_separation' => false,
            'phpdoc_to_comment' => false,
            'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
            'return_assignment' => false,
            'single_line_comment_style' => ['comment_types' => ['hash']],
            'strict_comparison' => true,
            'trailing_comma_in_multiline' => ['after_heredoc' => true, 'elements' => ['arrays', 'arguments', 'parameters']],
            'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        ])
;
