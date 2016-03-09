<?php
namespace watoki\stores\transforming\transformers;

use watoki\reflect\PropertyReader;
use watoki\reflect\TypeFactory;
use watoki\stores\transforming\TransformerRegistry;

class GenericObjectTransformer extends ObjectTransformer {

    /** @var TypeFactory */
    private $types;

    /** @var TransformerRegistry */
    private $transformers;

    /**
     * @param TransformerRegistry $transformers
     * @param TypeFactory $types
     */
    public function __construct(TransformerRegistry $transformers, TypeFactory $types) {
        $this->transformers = $transformers;
        $this->types = $types;
    }

    /**
     * @param object $object
     * @return array
     * @throws \Exception
     */
    protected function transformObject($object) {
        $reader = new PropertyReader($this->types, $object);

        $array = [];
        foreach ($reader->readState() as $property) {
            $value = $property->get($object);
            $array[$property->name()] = $this->transformers->toTransform($value)->transform($value);
        }
        foreach ($object as $propertyName => $propertyValue) {
            if (!array_key_exists($propertyName, $array)) {
                $array[$propertyName] = $this->transformers->toTransform($propertyValue)->transform($propertyValue);
            }
        }
        return $array;
    }

    /**
     * @param array $transformed
     * @param string $type
     * @return object
     * @throws \Exception
     */
    protected function revertObject($transformed, $type) {
        $class = new \ReflectionClass($type);
        $instance = $class->newInstanceWithoutConstructor();

        $reader = new PropertyReader($this->types, $class->getName());

        $properties = [];
        foreach ($reader->readState() as $property) {
            $properties[] = $property->name();
            if (array_key_exists($property->name(), $transformed)) {
                $value = $transformed[$property->name()];
                $property->set($instance, $this->transformers->toRevert($value)->revert($value));
            }
        }

        foreach ($transformed as $key => $value) {
            if (!in_array($key, $properties)) {
                $instance->$key = $this->transformers->toRevert($value)->revert($value);
            }
        }

        return $instance;
    }
}