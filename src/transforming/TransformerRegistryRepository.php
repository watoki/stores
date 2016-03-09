<?php
namespace watoki\stores\transforming;

use watoki\reflect\TypeFactory;
use watoki\stores\transforming\transformers\ArrayTransformer;
use watoki\stores\transforming\transformers\DateTimeImmutableTransformer;
use watoki\stores\transforming\transformers\DateTimeTransformer;
use watoki\stores\transforming\transformers\GenericObjectTransformer;
use watoki\stores\transforming\transformers\PrimitiveTransformer;
use watoki\stores\transforming\transformers\TypedArrayTransformer;
use watoki\stores\transforming\transformers\TypedObjectTransformer;
use watoki\stores\transforming\transformers\TypedValueTransformer;

class TransformerRegistryRepository {

    /** @var null|TransformerRegistry */
    private static $default;

    /** @var null|TypeMapper */
    private static $mapper;

    /** @var null|TypeFactory */
    private static $factory;

    /**
     * @param TransformerRegistry $default
     */
    public static function setDefault(TransformerRegistry $default) {
        self::$default = $default;
    }

    /**
     * @return TransformerRegistry
     */
    public static function getDefault() {
        if (!self::$default) {
            self::$default = self::createDefault(self::getTypeMapper(), self::getTypeFactory());
        }

        return self::$default;
    }

    /**
     * @param TypeMapper $mapper
     * @param TypeFactory $factory
     * @return TransformerRegistry With default Transformers registered
     */
    public static function createDefault(TypeMapper $mapper, TypeFactory $factory) {
        $registry = new TransformerRegistry();
        $registry->add(new DateTimeTransformer($mapper));
        $registry->add(new DateTimeImmutableTransformer($mapper));
        $registry->add(new TypedObjectTransformer($registry));
        $registry->add(new TypedArrayTransformer($registry));
        $registry->add(new TypedValueTransformer($registry));
        $registry->add(new GenericObjectTransformer($registry, $mapper, $factory));
        $registry->add(new ArrayTransformer($registry));
        $registry->add(new PrimitiveTransformer());

        return $registry;
    }

    /**
     * @param TypeMapper $mapper
     */
    public static function setMapper(TypeMapper $mapper) {
        self::$mapper = $mapper;
    }

    /**
     * @return TypeMapper
     */
    public static function getTypeMapper() {
        if (!self::$mapper) {
            self::$mapper = new TypeMapper();
        }

        return self::$mapper;
    }

    /**
     * @param TypeFactory $factory
     */
    public static function setFactory(TypeFactory $factory) {
        self::$factory = $factory;
    }

    /**
     * @return TypeFactory
     */
    public static function getTypeFactory() {
        if (!self::$factory) {
            self::$factory = new TypeFactory();
        }
        return self::$factory;
    }
}