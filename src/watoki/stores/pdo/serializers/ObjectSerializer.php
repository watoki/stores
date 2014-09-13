<?php
namespace watoki\stores\pdo\serializers;

use watoki\stores\pdo\Serializer;
use watoki\stores\pdo\SerializerRepository;

class ObjectSerializer extends \watoki\stores\ObjectSerializer implements Serializer {

    /**
     * @return \watoki\stores\pdo\SerializerRepository
     */
    protected function getSerializers() {
        return parent::getSerializers();
    }

    public function inflate($serialized) {
        $entity = parent::inflate($serialized);
        $entity->id = $serialized['id'];
        return $entity;
    }

    public function getDefinition($properties = null) {
        $fields = array('id' => '"id" INTEGER NOT NULL');
        foreach ($this->getPersistedProperties() as $property) {
            if (!$properties || in_array($property->getName(), $properties)) {
                $fields[$property->getName()] = $this->getPropertyDefinition($property->getName());
            }
        }

        $definitions = array_values($fields);
        $definitions[] = 'PRIMARY KEY ("id")';

        return implode(', ', $definitions);
    }

    public function getPropertyDefinition($propertyName) {
        return '"' . $propertyName . '" ' . $this->getFieldDefinition($this->class->getProperty($propertyName));
    }

    private function getFieldDefinition(\ReflectionProperty $property) {
        $nullAllowed = false;
        foreach ($this->getTypeHints($property) as $hint) {
            if ($this->getPrimitiveTypeFromHint($hint) == SerializerRepository::TYPE_NULL) {
                $nullAllowed = true;
            }
        }

        $definition = $this->getSerializers()->getSerializer($this->getTypeOfProperty($property))->getDefinition();
        return $definition . (!$nullAllowed ? ' NOT NULL' : ' DEFAULT NULL');
    }
}