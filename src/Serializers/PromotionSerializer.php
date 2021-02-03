<?php


namespace App\Serializers;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class PromotionSerializer
{
    public static $serializer;

    public static function serializeJson($obj, $groups){
        if(self::$serializer == null){
            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
            $normalizer = new ObjectNormalizer($classMetadataFactory);
            self::$serializer = new Serializer([$normalizer],[new JsonEncoder()]);
        }
        return self::$serializer->normalize($obj, 'json', $groups);
    }
}