<?php
namespace watoki\stores\transforming\transformers;

use watoki\stores\transforming\Transformer;
use watoki\stores\transforming\TransformerRegistry;

class TypedValueTransformer implements Transformer {

    /** @var TransformerRegistry */
    private $transformers;

    /**
     * @param TransformerRegistry $transformers
     */
    public function __construct(TransformerRegistry $transformers) {
        $this->transformers = $transformers;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function canTransform($value) {
        return $value instanceof TypedValue;
    }

    /**
     * @param mixed $transformed
     * @return bool
     */
    public function hasTransformed($transformed) {
        return $transformed instanceof TypedValue;
    }

    /**
     * @param TypedValue $value
     * @return mixed
     */
    public function transform($value) {
        return $this->transformers->toTransform($value->getValue())->transform($value->getValue());
    }

    /**
     * @param TypedValue $transformed
     * @return mixed
     */
    public function revert($transformed) {
        return $this->transformers->toRevert($transformed->getValue())->revert($transformed->getValue());
    }
}