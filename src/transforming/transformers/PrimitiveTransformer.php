<?php
namespace watoki\stores\transforming\transformers;

use watoki\stores\transforming\Transformer;

class PrimitiveTransformer implements Transformer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function canTransform($value) {
        return !is_array($value) && !is_object($value);
    }

    /**
     * @param mixed $transformed
     * @return bool
     */
    public function hasTransformed($transformed) {
        return !is_array($transformed) && !is_object($transformed);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function transform($value) {
        return $value;
    }

    /**
     * @param mixed $transformed
     * @return mixed
     */
    public function revert($transformed) {
        return $transformed;
    }
}