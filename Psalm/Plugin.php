<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftMagicPropertyAnalysis\Psalm;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Identifier;
use Psalm\Codebase;
use Psalm\FileManipulation;
use Psalm\FileSource;
use Psalm\Plugin\Hook\AfterClassLikeVisitInterface;
use Psalm\Plugin\Hook\AfterClassLikeStoragePopulated;
use Psalm\Storage\ClassLikeStorage;
use SignpostMarv\DaftMagicPropertyAnalysis\DefinitionAssistant;
use SignpostMarv\DaftMagicPropertyAnalysis\Tests\DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedTypeInterface;

class Plugin implements AfterClassLikeVisitInterface
{

    public static function afterClassLikeVisit(
       ClassLike $stmt,
       ClassLikeStorage $storage,
       FileSource $statements_source,
       Codebase $codebase,
       array &$file_replacements = []
    ) : void {
        if ($storage->name === ucwordsPrefixedTypeInterface::class) {
            var_dump(static::MaybeRegisterTypesOrExitEarly($storage->name));exit(1);
            if (static::MaybeRegisterTypesOrExitEarly($storage->name)) {
                foreach (DefinitionAssistant::ObtainExpectedProperties($storage->name) as $property) {
                    var_dump($storage->name, $storage);exit(1);
                }
            }

        }
    }

    protected static function MaybeRegisterTypesOrExitEarly(? Identifier $classLikeName) : ? bool
    {
        if ( ! is_null($classLikeName)) {
            var_dump($classLikeName->name);
        }
        if (
            ! is_null($classLikeName) &&
            ! DefinitionAssistant::IsTypeUnregistered($classLikeName->name)
        ) {
            return true;
        }

        return false;
    }
}
