<?php

declare(strict_types=1);

namespace rexstan;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use rex_yform_manager_dataset;
use RuntimeException;

use function call_user_func;

final class YOrmDatasetPropertyClassReflectionExtension implements PropertiesClassReflectionExtension
{
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        return null !== $this->getPropertyType($classReflection, $propertyName);
    }

    public function getProperty(
        ClassReflection $classReflection,
        string $propertyName
    ): PropertyReflection {
        $propertyType = $this->getPropertyType($classReflection, $propertyName);
        if (null === $propertyType) {
            throw new RuntimeException('expecting property type');
        }

        return new class($classReflection, $propertyType) implements PropertyReflection {
            /**
             * @var ClassReflection
             */
            private $classReflection;
            /**
             * @var Type
             */
            private $propertyType;

            public function __construct(ClassReflection $classReflection, Type $propertyType)
            {
                $this->classReflection = $classReflection;
                $this->propertyType = $propertyType;
            }

            public function getDeclaringClass(): ClassReflection
            {
                return $this->classReflection;
            }

            public function isStatic(): bool
            {
                return false;
            }

            public function isPrivate(): bool
            {
                return false;
            }

            public function isPublic(): bool
            {
                return true;
            }

            public function getDocComment(): ?string
            {
                return null;
            }

            public function getReadableType(): Type
            {
                return $this->propertyType;
            }

            public function getWritableType(): Type
            {
                return $this->propertyType;
            }

            public function canChangeTypeAfterAssignment(): bool
            {
                return true;
            }

            public function isReadable(): bool
            {
                return true;
            }

            public function isWritable(): bool
            {
                return true;
            }

            public function isDeprecated(): TrinaryLogic
            {
                return TrinaryLogic::createNo();
            }

            public function getDeprecatedDescription(): ?string
            {
                return null;
            }

            public function isInternal(): TrinaryLogic
            {
                return TrinaryLogic::createNo();
            }
        };
    }

    private function getPropertyType(
        ClassReflection $classReflection,
        string $propertyName
    ): ?Type {
        if (!class_exists(rex_yform_manager_dataset::class)) {
            return null;
        }

        if (!$classReflection->isSubclassOf(rex_yform_manager_dataset::class)) {
            return null;
        }

        /** @phpstan-ignore-next-line */
        $datasetObject = call_user_func([$classReflection->getName(), 'create']);
        if (!$datasetObject instanceof rex_yform_manager_dataset) {
            throw new RuntimeException('expecting dataset object');
        }

        $resultType = RexSqlReflection::getResultOffsetValueType(
            'SELECT * FROM '. $datasetObject->getTableName(),
            $propertyName
        );

        // treat non-existing properties as mixed, as this might be calculated or virtual field
        if (null === $resultType) {
            return new MixedType();
        }

        return $resultType;
    }
}
