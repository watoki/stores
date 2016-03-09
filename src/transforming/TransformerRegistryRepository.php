<?php
namespace watoki\stores\transforming;

use watoki\reflect\TypeFactory;
use watoki\stores\transforming\transformers\ArrayTransformer;
use watoki\stores\transforming\transformers\DateTimeImmutableTransformer;
use watoki\stores\transforming\transformers\DateTimeTransformer;
use watoki\stores\transforming\transformers\GenericObjectTransformer;
use watoki\stores\transforming\transformers\PrimitiveTransformer;

class TransformerRegistryRepository {

    /** @var null|TransformerRegistry */
    private static $default;

    /**
     * @param TransformerRegistry $default
     */
    public static function setDefault(TransformerRegistry $default) {
        self::$default = $default;
    }

    public static function getDefault() {
        if (!self::$default) {
            self::$default = new TransformerRegistry();
            self::$default->add(new DateTimeTransformer());
            self::$default->add(new DateTimeImmutableTransformer());
            self::$default->add(new GenericObjectTransformer(self::$default, new TypeFactory()));
            self::$default->add(new ArrayTransformer(self::$default));
            self::$default->add(new PrimitiveTransformer());
        }

        return self::$default;
    }
}