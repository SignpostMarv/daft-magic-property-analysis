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

class Plugin implements AfterClassLikeVisitInterface
{

    public static function afterClassLikeVisit(
       ClassLike $stmt,
       ClassLikeStorage $storage,
       FileSource $statements_source,
       Codebase $codebase,
       array &$file_replacements = []
    ) : void {
        if ($stmt->name->name === "SignpostMarv\DaftMagicPropertyAnalysis\Tests\DaftMagicPropertyAnalysis\Fixtures\ucwordsPrefixedTypeInterface") {
            var_dump(static::MaybeRegisterTypesOrExitEarly($stmt->name));exit(1);
            if (static::MaybeRegisterTypesOrExitEarly($stmt->name)) {
                foreach (DefinitionAssistant::ObtainExpectedProperties($stmt->name) as $property) {
                    var_dump($stmt->name, $storage);exit(1);
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
