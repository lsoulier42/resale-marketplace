<?php

namespace App\Helper;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

class ClassConverterHelper
{
    /**
     * @return PropertyInfoExtractor
     */
    private static function getPropertyInfoExtractor(): PropertyInfoExtractor
    {
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        $listExtractors = [$reflectionExtractor];
        $typeExtractors = [$phpDocExtractor, $reflectionExtractor];
        $descriptionExtractors = [$phpDocExtractor];
        $accessExtractors = [$reflectionExtractor];
        $propertyInitializableExtractors = [$reflectionExtractor];

        return new PropertyInfoExtractor(
            $listExtractors,
            $typeExtractors,
            $descriptionExtractors,
            $accessExtractors,
            $propertyInitializableExtractors
        );
    }

    /**
     * @param object $from
     * @param object $to
     * @param array<string, mixed> $customMapping
     * @return object
     */
    public static function convertToClass(
        object $from,
        object $to,
        array $customMapping = []
    ): object {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $propertyInfo = self::getPropertyInfoExtractor();
        $fromClass = get_class($from);
        $toClass = get_class($to);
        $properties = $propertyInfo->getProperties($fromClass);
        foreach ($properties as $propertyName) {
            if (
                !$propertyInfo->isReadable($fromClass, $propertyName)
                || $propertyName === 'times'
            ) {
                continue;
            }
            $dtoValue = $propertyAccessor->getValue($from, $propertyName);
            $destPath = $propertyName;
            if (array_key_exists($propertyName, $customMapping)) {
                $dtoValue = $customMapping[$propertyName];
            }
            if (!$propertyInfo->isWritable($toClass, $destPath)) {
                continue;
            }
            $propertyAccessor->setValue($to, $destPath, $dtoValue);
        }
        return $to;
    }
}
