<?php
namespace watoki\stores\transforming\transformers;

use watoki\reflect\Type;
use watoki\reflect\type\ClassType;
use watoki\stores\transforming\Transformer;
use watoki\stores\transforming\TransformerRegistry;

class TypedObjectTransformer implements Transformer{

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
            && $this->objectOfRightClass($value->getType(), $value->getValue());
    }

    private function objectOfRightClass(Type $type, $object) {
        return
            $type instanceof ClassType
            && is_object($object)
            && $type->getClass() == get_class($object);
    }

    /**
     * @param mixed $transformed
     * @return bool
     */
    public function hasTransformed($transformed) {
        return
            $transformed instanceof TypedValue
            && !(
                is_array($transformed->getValue())
                && array_key_exists(ObjectTransformer::TYPE_KEY, $transformed->getValue()))
            && $transformed->getType() instanceof ClassType;
    }

    /**
     * @param TypedValue $value
     * @return array
     */
    public function transform($value) {
        $realValue = $value->getValue();
        $transformedArray = $this->transformers->toTransform($realValue)->transform($realValue);

        return $transformedArray[ObjectTransformer::DATA_KEY];
    }

    /**
     * @param TypedValue $transformed
     * @return object
     */
    public function revert($transformed) {
        /** @var ClassType $type */
        $type = $transformed->getType();

        $array = [
            ObjectTransformer::TYPE_KEY => $type->getClass(),
            ObjectTransformer::DATA_KEY => $transformed->getValue()
        ];

        return $this->transformers->toRevert($array)->revert($array);
    }
}