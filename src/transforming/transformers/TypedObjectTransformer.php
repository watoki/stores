<?php
namespace watoki\stores\transforming\transformers;

use watoki\reflect\Type;
use watoki\reflect\type\ClassType;

class TypedObjectTransformer extends GenericObjectTransformer {

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
            && !parent::hasTransformed($transformed->getValue())
            && is_array($transformed->getValue())
            && $transformed->getType() instanceof ClassType;
    }

    /**
     * @param TypedValue $value
     * @return array
     */
    public function transform($value) {
        return $this->transformObject($value->getValue());
    }

    /**
     * @param TypedValue $transformed
     * @return object
     */
    public function revert($transformed) {
        /** @var ClassType $type */
        $type = $transformed->getType();
        return $this->revertObject($transformed->getValue(), $type->getClass());
    }
}