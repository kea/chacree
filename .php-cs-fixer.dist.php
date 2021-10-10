<?php

$finder = PhpCsFixer\Finder::create()
    ->in('bin')
    ->in('src')
    ->exclude('vendor')
;

$config = new PhpCsFixer\Config();

return $config
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PHP80Migration' => true,
        '@PHP80Migration:risky' => true,
        'heredoc_indentation' => false,
        'general_phpdoc_annotation_remove' => ['annotations' => ['expectedDeprecation']],
    ])
    ;