<?php


namespace App\Serializers;


use App\Entity\Session;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

abstract class SessionSerializer
{
    public static $serializer;

    public static function serializeJson($obj, array $groups){
        self::initSerializer();
        return self::$serializer->normalize($obj, 'json', $groups);
    }

    public static function deSerializeJson($obj, array $groups){
        self::initSerializer();
        return self::$serializer->denormalize($obj, Session::class, null, $groups);
    }

    public static function initSerializer()
    {
        if(self::$serializer == null){
            $dateCallback = function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
                return $innerObject instanceof \DateTime ? $innerObject->format(\DateTime::RFC3339) : '';
            };
            $defaultContext = [
                AbstractNormalizer::CALLBACKS => [
                    'dateDebut' => $dateCallback,
                    'dateFin' => $dateCallback,
                    'getDuree' => $dateCallback
                ]
            ];
            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
            $normalizer = new ObjectNormalizer($classMetadataFactory, null,null,null,null,null,$defaultContext);
            self::$serializer = new Serializer([$normalizer],[new JsonEncoder()]);
        }
    }

}