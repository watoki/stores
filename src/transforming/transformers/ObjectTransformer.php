<?php
namespace watoki\stores\transforming\transformers;

use watoki\stores\transforming\Transformer;
use watoki\stores\transforming\TypeMapper;

abstract class ObjectTransformer implements Transformer {

    const TYPE_KEY = 'TYPE';
    const DATA_KEY = 'DATA';

    /** @var TypeMapper */
    protected $mapper;

    /**
     * @param TypeMapper $mapper
     */
    public function __construct(TypeMapper $mapper) {
        $this->mapper = $mapper;
    }

    /**
     * @param object $object
     * @return mixed
     */
    protected abstract function transformObject($object);

    /**
     * @param mixed $transformed
     * @param string $type
     * @return object
     */
    protected abstract function revertObject($transformed, $type);

    /**
     * @param mixed $value
     * @return bool
     */
    public function canTransform($value) {
        return is_object($value);
    }

    /**
     * @param mixed $transformed
     * @return bool
     */
    public function hasTransformed($transformed) {
        return is_array($transformed) && array_keys($transformed) == [self::TYPE_KEY, self::DATA_KEY];
    }

    /**
     * @param object $object
     * @return array
     */
    public function transform($object) {
        return [
            self::TYPE_KEY => $this->mapper->getAlias(get_class($object)),
            self::DATA_KEY => $this->transformObject($object)
        ];
    }

    /**
     * @param array $transformed
     * @return object
     */
    public function revert($transformed) {
        return $this->revertObject(
            $transformed[self::DATA_KEY],
            $this->mapper->getClass($transformed[self::TYPE_KEY]));
    }
}