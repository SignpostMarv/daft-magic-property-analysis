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
use Psalm\Internal\Analyzer\ClassLikeAnalyzer;
use Psalm\Plugin\Hook\AfterClassLikeVisitInterface;
use Psalm\Plugin\Hook\AfterClassLikeStoragePopulated;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Storage\PropertyStorage;
use Psalm\Type\Union;
use Psalm\Type\Atomic\Mixed;
use ReflectionMethod;
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
        $maybeExitEarly = static::MaybeRegisterTypesOrExitEarly($storage->name);

        if (is_null($maybeExitEarly)) {
            foreach (DefinitionAssistant::ObtainExpectedProperties($storage->name) as $property) {
                $getter = DefinitionAssistant::GetterMethodName($storage->name, $property);
                $setter = DefinitionAssistant::SetterMethodName($storage->name, $property);

                $storage->properties[$property] = new PropertyStorage();
                $storage->properties[$property]->is_static = false;
                $storage->properties[$property]->visibility =
                    DefinitionAssistant::PropertyIsPublic($storage->name, $property)
                        ? ClassLikeAnalyzer::VISIBILITY_PUBLIC
                        : ClassLikeAnalyzer::VISIBILITY_PROTECTED;
            }
        }
    }

    protected static function MaybeRegisterTypesOrExitEarly(string $className) : ? bool
    {
        return null;
    }
}
