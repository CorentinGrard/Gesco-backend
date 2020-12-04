<?php


namespace App\Serializers;


use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SessionSerializer
{
    public static $serializer;

    public static function serializeJson($obj, $groups){
        if(self::$serializer == null){
            $dateCallback = function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
                return $innerObject instanceof \DateTime ? $innerObject->format(\DateTime::RFC3339) : '';
            };
            $defaultContext = [
                AbstractNormalizer::CALLBACKS => [
                    'dateDebut' => $dateCallback,
                    'dateFin' => $dateCallback
                ]
            ];
            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
            $normalizer = new ObjectNormalizer($classMetadataFactory, null,null,null,null,null,$defaultContext);
            self::$serializer = new Serializer([$normalizer],[new JsonEncoder()]);
        }
        return self::$serializer->normalize($obj, 'json', $groups);
    }

}