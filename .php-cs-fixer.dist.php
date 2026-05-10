<?php

declare(strict_types=1);

use PhpCsFixer\Finder;
use PhpCsFixer\Config;

$finder = Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
    ->exclude([
        'bootstrap/cache',
        'storage',
        'vendor',
    ]);

return (new Config())
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setRules([
        '@PSR12' => true,
        '@PSR12:risky' => true,

        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => ['class', 'function', 'const'],
        ],
        'no_unused_imports' => true,
        'single_import_per_statement' => true,
        'blank_line_after_namespace' => true,
        'declare_strict_types' => true,
        'blank_line_after_opening_tag' => true,

        'declare_parentheses' => false,
        'declare_equal_normalize' => ['space' => 'none'],

        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],
        'unary_operator_spaces' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => false,
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'single_space_around_construct' => true,
        'space_after_semicolon' => true,

        'single_quote' => true,

        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters', 'match'],
        ],

        'cast_spaces' => true,
        'no_short_bool_cast' => true,

        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
            ],
        ],
        'method_chaining_indentation' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_blank_lines_after_phpdoc' => true,

        'modifier_keywords' => [
            'elements' => ['property', 'method', 'const'],
        ],

        // One space between type and name; no description column (see phpcs Spacing* excludes).
        'phpdoc_align' => [
            'align' => 'left',
            'tags' => ['param', 'return', 'throws', 'var', 'property', 'method'],
        ],
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => false],
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_line_span' => [
            'const' => 'multi',
            'method' => 'multi',
            'property' => 'multi',
        ],
        'phpdoc_to_comment' => false,
        'phpdoc_no_access' => true,
        'phpdoc_no_alias_tag' => true,
        'phpdoc_no_empty_return' => false,
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_order' => ['order' => ['param', 'return', 'throws']],
        'phpdoc_return_self_reference' => true,
        // Kept off: normalizes int/bool in PHPDoc and fights Squiz/PHPCBF, which use integer/boolean
        'phpdoc_scalar' => false,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_tag_casing' => true,
        'phpdoc_trim' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_types' => true,
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_last',
            'sort_algorithm' => 'none',
        ],
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_var_without_name' => true,

        'control_structure_braces' => true,
        'control_structure_continuation_position' => true,
        'elseif' => true,
        'no_alternative_syntax' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,

        'function_declaration' => [
            'closure_function_spacing' => 'one',
        ],
        'lambda_not_used_import' => true,
        'nullable_type_declaration_for_default_null_value' => true,

        'constant_case' => ['case' => 'lower'],
        'lowercase_keywords' => true,
        'lowercase_static_reference' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'native_function_casing' => true,

        'native_type_declaration_casing' => true,

        'no_leading_namespace_whitespace' => true,
        'no_mixed_echo_print' => ['use' => 'echo'],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_null_property_initialization' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_spaces_after_function_name' => true,
        'no_spaces_around_offset' => true,
        'no_trailing_comma_in_singleline' => true,
        'no_trailing_whitespace' => true,
        'no_trailing_whitespace_in_comment' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unneeded_final_method' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_whitespace_in_blank_line' => true,
        'normalize_index_brace' => true,
        'object_operator_without_whitespace' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],
        'ordered_traits' => true,
        'return_assignment' => true,
        'self_static_accessor' => true,
        'short_scalar_cast' => true,
        'simple_to_complex_string_variable' => true,
        'single_blank_line_at_eof' => true,
        'single_class_element_per_statement' => true,
        'single_line_after_imports' => true,
        'single_line_comment_style' => [
            'comment_types' => ['hash'],
        ],
        'single_line_throw' => false,
        'single_trait_insert_per_statement' => true,
        'standardize_increment' => true,
        'standardize_not_equals' => true,
        'switch_continue_to_break' => true,
        'ternary_operator_spaces' => true,
        'ternary_to_null_coalescing' => true,
        'trim_array_spaces' => true,
        'types_spaces' => [
            'space' => 'none',
            'space_multiple_catch' => 'none',
        ],
        'void_return' => true,
        'whitespace_after_comma_in_array' => true,
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
    ]);