<?php
namespace watoki\stores\pdo\serializers;

use watoki\stores\pdo\Serializer;
use watoki\stores\pdo\SerializerRepository;
use watoki\factory\ClassResolver;

class ObjectSerializer extends \watoki\stores\ObjectSerializer implements Serializer {


    /**
     * @return \watoki\stores\pdo\SerializerRepository
     */
    protected function getSerializers() {
        return parent::getSerializers();
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

        $definition = $this->getSerializers()->getSerializer($this->getTypeOfProperty($property))->getDefinition();
        return $definition . (!$nullAllowed ? ' NOT NULL' : '');
    }
}