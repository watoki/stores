<?php
namespace watoki\stores\transforming\transformers;

use watoki\reflect\Type;
use watoki\reflect\type\ArrayType;
use watoki\stores\transforming\Transformer;
use watoki\stores\transforming\TransformerRegistry;

class TypedArrayTransformer implements Transformer {

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
        return
            $value instanceof TypedValue
            && is_array($value->getValue())
            && $value->getType() instanceof ArrayType;
    }

    /**
     * @param mixed $transformed
     * @return bool
     */
    public function hasTransformed($transformed) {
        return
            $transformed instanceof TypedValue
            && is_array($transformed->getValue())
            && $transformed->getType() instanceof ArrayType;
    }

    /**
     * @param TypedValue $typedArray
     * @return array
     */
    public function transform($typedArray) {
        $transformed = [];
        foreach ($typedArray->getValue() as $key => $value) {
            $typedValue = $this->typedItem($typedArray->getType(), $value);
            $transformed[$key] = $this->transformers->toTransform($typedValue)->transform($typedValue);
        }
        return $transformed;
    }

    /**
     * @param TypedValue $typedArray
     * @return array
     */
    public function revert($typedArray) {
        $reverted = [];
        foreach ($typedArray->getValue() as $key => $value) {
            $typedValue = $this->typedItem($typedArray->getType(), $value);
            $reverted[$key] = $this->transformers->toRevert($typedValue)->revert($typedValue);
        }
        return $reverted;
    }

    /**
     * @param ArrayType|Type $type
     * @param mixed $value
     * @return TypedValue
     */
    private function typedItem(Type $type, $value) {
        return new TypedValue($value, $type->getItemType());
    }
}