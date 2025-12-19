<?php

$finder = (new PhpCsFixer\Finder())
    ->in(
        [
            __DIR__.'/src',
            __DIR__.'/tests',
        ],
    )
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(isRiskyAllowed: true)
    ->setRules([
        '@Symfony' => true,
        'strict_comparison' => true,
        'declare_strict_types' => true,
        'blank_line_between_import_groups' => false,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'increment_style' => false,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'native_function_invocation' => false,
        'no_null_property_initialization' => false,
        'no_useless_concat_operator' => false,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => ['const', 'class', 'function'],
        ],
        'php_unit_method_casing' => ['case' => 'snake_case'],
        'phpdoc_align' => false,
        'phpdoc_array_type' => true,
        'phpdoc_separation' => false,
        'phpdoc_summary' => false,
        'phpdoc_to_comment' => false,
        'psr_autoloading' => true,
        'single_line_throw' => false,
        'single_quote' => false,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
        'fully_qualified_strict_types' => [
            'import_symbols' => true,
        ],
        'static_lambda' => true,
    ])
    ->setFinder($finder)
    ;
