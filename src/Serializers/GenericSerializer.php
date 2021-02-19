<?php


namespace App\Serializers;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

abstract class GenericSerializer
{
    public static $serializer;

    public static function serializeJson($obj, array $groups){
        self::initSerializer();
        return self::$serializer->normalize($obj, 'json', $groups);
    }

    public static function deSerializeJson($obj, array $groups, string $class){
        self::initSerializer();
        return self::$serializer->denormalize($obj, $class, $groups);
    }


    public static function initSerializer()
    {
        if(self::$serializer == null){
            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
            $normalizer = new ObjectNormalizer($classMetadataFactory);
            self::$serializer = new Serializer([$normalizer],[new JsonEncoder()]);
        }
    }
}