<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\ObjectSerializer;
use watoki\stores\sqlite\Serializer;
use watoki\stores\sqlite\SerializerRepository;

class EntitySerializer extends ObjectSerializer implements Serializer {

    /**
     * @return \watoki\stores\sqlite\SerializerRepository
     */
    protected function getSerializers() {
        return parent::getSerializers();
    }

    public function inflate($serialized) {
        return parent::inflate($serialized);
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