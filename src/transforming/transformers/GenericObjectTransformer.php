<?php
namespace watoki\stores\transforming\transformers;

use watoki\reflect\Property;
use watoki\reflect\PropertyReader;
use watoki\reflect\TypeFactory;
use watoki\reflect\ValuePrinter;
use watoki\stores\transforming\TransformerRegistry;
use watoki\stores\transforming\TypeMapper;

class GenericObjectTransformer extends ObjectTransformer {

    /** @var TypeFactory */
    private $types;

    /** @var TransformerRegistry */
    private $transformers;

    /**
     * @param TransformerRegistry $transformers
     * @param TypeMapper $mapper
     * @param TypeFactory $types
     */
    public function __construct(TransformerRegistry $transformers, TypeMapper $mapper, TypeFactory $types) {
        parent::__construct($mapper);
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
            $value = new TypedValue($property->get($object), $property->type());
            $transformer = $this->transformers->toTransform($value);
            $array[$property->name()] = $transformer->transform($value);
        }

        foreach ($object as $propertyName => $propertyValue) {
            if (!array_key_exists($propertyName, $array)) {
                $transformer = $this->transformers->toTransform($propertyValue);
                $array[$propertyName] = $transformer->transform($propertyValue);
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

        if (!is_array($transformed)) {
            throw new \Exception("Error while reverting [$type]. Input is not an array: " . ValuePrinter::serialize($transformed));
        }

        $reader = new PropertyReader($this->types, $class->getName());

        $properties = [];
        foreach ($reader->readState() as $property) {
            $properties[] = $property->name();
            if (array_key_exists($property->name(), $transformed)) {
                $value = new TypedValue($transformed[$property->name()], $property->type());
                $transformer = $this->transformers->toRevert($value);
                $property->set($instance, $transformer->revert($value));
            }
        }

        foreach ($transformed as $key => $value) {
            if (!in_array($key, $properties)) {
                $transformer = $this->transformers->toRevert($value);
                $instance->$key = $transformer->revert($value);
            }
        }

        return $instance;
    }
}