<?php
namespace watoki\stores\transforming\transformers;

use watoki\stores\transforming\Transformer;
use watoki\stores\transforming\TransformerRegistry;

class ArrayTransformer implements Transformer {

    const ESCAPE_KEY = '_';

    private static $ESCAPE_KEYS;

    /** @var TransformerRegistry */
    private $transformers;

    /**
     * @param TransformerRegistry $transformers
     */
    public function __construct(TransformerRegistry $transformers) {
        $this->transformers = $transformers;

        if (!self::$ESCAPE_KEYS) {
            self::$ESCAPE_KEYS = [
                ObjectTransformer::TYPE_KEY => self::ESCAPE_KEY . ObjectTransformer::TYPE_KEY,
                ObjectTransformer::DATA_KEY => self::ESCAPE_KEY . ObjectTransformer::DATA_KEY,
            ];
        }
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function canTransform($value) {
        return is_array($value);
    }

    /**
     * @param mixed $transformed
     * @return bool
     */
    public function hasTransformed($transformed) {
        return is_array($transformed);
    }

    /**
     * @param array $array
     * @return array
     */
    public function transform($array) {
        $transformed = [];
        foreach ($array as $key => $value) {
            $key = str_replace(array_keys(self::$ESCAPE_KEYS), array_values(self::$ESCAPE_KEYS), $key);
            $transformed[$key] = $this->transformers->toTransform($value)->transform($value);
        }
        return $transformed;
    }

    /**
     * @param array $array
     * @return array
     */
    public function revert($array) {
        $reverted = [];
        foreach ($array as $key => $value) {
            $key = str_replace(array_values(self::$ESCAPE_KEYS), array_keys(self::$ESCAPE_KEYS), $key);
            $reverted[$key] = $this->transformers->toRevert($value)->revert($value);
        }
        return $reverted;
    }
}