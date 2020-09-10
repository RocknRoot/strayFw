<?php

declare(strict_types = 1);

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
    ]);
    $services = $containerConfigurator->services();
    $services->set(Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector::class);
    $services->set(Rector\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector::class);
    $services->set(Rector\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector::class);
    $services->set(Rector\Php74\Rector\Class_\ClassConstantToSelfClassRector::class);
    $services->set(Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector::class);
    $services->set(Rector\Php74\Rector\FuncCall\GetCalledClassToStaticClassRector::class);
    $services->set(Rector\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector::class);
    $services->set(Rector\Php74\Rector\Assign\NullCoalescingOperatorRector::class);
    $services->set(Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector::class);
    $services->set(Rector\Php74\Rector\Property\TypedPropertyRector::class);
};
