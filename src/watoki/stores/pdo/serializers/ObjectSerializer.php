<?php
namespace watoki\stores\pdo\serializers;

use watoki\stores\pdo\Serializer;
use watoki\stores\pdo\SerializerRepository;
use watoki\factory\ClassResolver;

class ObjectSerializer implements Serializer {

    /** @var \ReflectionClass */
    private $class;

    /** @var \watoki\stores\pdo\SerializerRepository */
    private $repo;

    function __construct($class, SerializerRepository $repo) {
        $this->class = new \ReflectionClass($class);
        $this->repo = $repo;
    }

    public function serialize($inflated) {
        $row = array();
        foreach ($this->getPersistedProperties() as $property) {
            $value = $property->getValue($inflated);
            $type = $this->repo->getType($value);
            $row[$property->getName()] = $this->repo->getSerializer($type)->serialize($value);
        }
        return $row;
    }

    public function inflate($serialized) {
        $inflated = $this->class->newInstanceWithoutConstructor();
        foreach ($this->getPersistedProperties() as $property) {
            if (!array_key_exists($property->getName(), $serialized)) {
                continue;
            }

            $value = $serialized[$property->getName()];
            $type = $this->getTypeOfProperty($property);
            $property->setValue($inflated, $this->repo->getSerializer($type)->inflate($value));
        }
        return $inflated;
    }

    public function getDefinition() {
        $fields = array('id' => '"id" INTEGER NOT NULL');
        foreach ($this->getPersistedProperties() as $property) {
            $fields[$property->getName()] = '"' . $property->getName() . '" ' . $this->getFieldDefinition($property);
        }

        $definitions = array_values($fields);
        $definitions[] = 'PRIMARY KEY ("id")';

        return implode(', ', $definitions);
    }

    private function getFieldDefinition(\ReflectionProperty $property) {
        $nullAllowed = false;
        foreach ($this->getTypeHints($property) as $hint) {
            if ($this->getPrimitiveTypeFromHint($hint) == SerializerRepository::TYPE_NULL) {
                $nullAllowed = true;
            }
        }

        $definition = $this->repo->getSerializer($this->getTypeOfProperty($property))->getDefinition();
        return $definition . (!$nullAllowed ? ' NOT NULL' : '');
    }

    private function getTypeOfProperty(\ReflectionProperty $property) {
        $resolver = new ClassResolver($property->getDeclaringClass());

        foreach ($this->getTypeHints($property) as $typeHint) {
            $className = $resolver->resolve(ltrim($typeHint, '\\'));
            if ($className) {
                return $className;
            }

            $type = $this->getPrimitiveTypeFromHint($typeHint);
            if ($type && $type != SerializerRepository::TYPE_NULL) {
                return $type;
            }
        }

        throw new \Exception('Could not determine type of ' .
            "[{$property->getDeclaringClass()->getName()}::{$property->getName()}].");
    }

    private function getTypeHints(\ReflectionProperty $property) {
        $matches = array();
        $found = preg_match('/@var\s+(\S+)/', $property->getDocComment(), $matches);

        if (!$found) {
            throw new \Exception("Could not find type hint of property " .
                "[{$property->getDeclaringClass()->getName()}::{$property->getName()}].");
        }
        return $this->explodeMultipleHints($matches[1]);
    }

    private function explodeMultipleHints($hint) {
        if (strpos($hint, '|') !== false) {
            return explode('|', $hint);
        } else {
            return array($hint);
        }
    }

    private function getPrimitiveTypeFromHint($hint) {
        switch (strtolower($hint)) {
            case 'array':
                return SerializerRepository::TYPE_ARRAY;
            case 'int':
            case 'integer':
                return SerializerRepository::TYPE_INTEGER;
            case 'long':
                return SerializerRepository::TYPE_LONG;
            case 'float':
                return SerializerRepository::TYPE_FLOAT;
            case 'double':
                return SerializerRepository::TYPE_DOUBLE;
            case 'bool':
            case 'boolean':
                return SerializerRepository::TYPE_BOOLEAN;
            case 'string':
                return SerializerRepository::TYPE_STRING;
            case 'null':
                return SerializerRepository::TYPE_NULL;
            default:
                return null;
        }
    }

    /**
     * @return \ReflectionProperty[]
     */
    private function getPersistedProperties() {
        return array_filter($this->class->getProperties(), function (\ReflectionProperty $prop) {
            $prop->setAccessible(true);
            return !$prop->isStatic();
        });
    }
}