<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

//use Rector\Php80\Rector\FunctionLike\UnionTypesRector;
//use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;
//use Rector\Php80\Rector\NotIdentical\StrContainsRector;
//use Rector\Php80\Rector\Identical\StrEndsWithRector;
//use Rector\Php80\Rector\Identical\StrStartsWithRector;
//use Rector\Php80\Rector\Class_\StringableForToStringRector;
//use Rector\Php80\Rector\FunctionLike\UnionTypesRector;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SETS, [
        SetList::PERFORMANCE,
        SetList::TYPE_DECLARATION,
        SetList::PHP_74,
    ]);
};
