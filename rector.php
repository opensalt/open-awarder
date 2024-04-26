<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withParallel()
    ->withPaths([
        //__DIR__ . '/assets',
        //__DIR__ . '/config',
        //__DIR__ . '/public',
        __DIR__ . '/src',
        //__DIR__ . '/tests',
    ])
    ->withPhpVersion(\Rector\ValueObject\PhpVersion::PHP_83)
    ->withPhpSets(php83: true)
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
        \Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector::class
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        //naming: true,
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: true
    )
    ->withAttributesSets(
        symfony: true,
        doctrine: true,
    )
    ->withSets([
        \Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_83,
        \Rector\Symfony\Set\SymfonySetList::SYMFONY_64,
        \Rector\Symfony\Set\SymfonySetList::SYMFONY_CODE_QUALITY,
    ])
    ;
