<?php
namespace watoki\stores\transforming\transformers;

use watoki\reflect\Type;
use watoki\reflect\type\NullableType;
use watoki\stores\transforming\Transformer;
use watoki\stores\transforming\TransformerRegistry;

class TypedNullableObjectTransformer implements Transformer {

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
        return $this->isNullableType($value);
    }

    /**
     * @param mixed $transformed
     * @return bool
     */
    public function hasTransformed($transformed) {
        return $this->isNullableType($transformed);
    }

    /**
     * @param TypedValue $value
     * @return mixed
     */
    public function transform($value) {
        $innerValue = $this->makeInnerTypedValue($value);
        return $this->transformers->toTransform($innerValue)->transform($innerValue);
    }

    /**
     * @param TypedValue $transformed
     * @return mixed
     */
    public function revert($transformed) {
        $innerValue = $this->makeInnerTypedValue($transformed);
        return $this->transformers->toRevert($innerValue)->revert($innerValue);
    }

    private function isNullableType($value) {
        return
            $value instanceof TypedValue
            && $value->getType() instanceof NullableType;
    }

    private function makeInnerTypedValue(TypedValue $value) {
        return new TypedValue($value->getValue(), $this->getInnerType($value->getType()));
    }

    /**
     * @param Type|NullableType $type
     * @return Type
     */
    private function getInnerType(Type $type) {
        return $type->getType();
    }
}