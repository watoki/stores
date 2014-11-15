<?php
namespace watoki\stores\sqlite\serializers;

use watoki\stores\GenericSerializer;
use watoki\stores\Serializer;
use watoki\stores\sqlite\SqliteSerializer;

class CompositeSerializer extends GenericSerializer implements SqliteSerializer {

    private static $SEPARATOR = '__';

    /** @var SqliteSerializer[] */
    private $serializers;

    /**
     * @param string $name
     * @param SqliteSerializer|Serializer $serializer
     * @param callable $getter
     * @param null $setter
     * @throws \InvalidArgumentException if $serializer is not a SqliteSerializer
     * @return $this
     */
    public function defineChild($name, Serializer $serializer, $getter, $setter = null) {
        if (!($serializer instanceof SqliteSerializer)) {
            throw new \InvalidArgumentException('Serializer must implement [watoki\stores\sqlite\SqliteSerializer]');
        }
        $this->serializers[$name] = $serializer;
        return parent::defineChild($name, $serializer, $getter, $setter);
    }

    /**
     * @return array|string[] Definitions of children, indexed by their names.
     */
    public function getDefinition() {
        $definitions = array();
        foreach ($this->serializers as $child => $serializer) {
            $definition = $serializer->getDefinition();
            if (!is_array($definition)) {
                $definitions[$child] = $definition;
            } else {
                foreach ($definition as $grandChild => $grandDefinition) {
                    $definitions[$child . self::$SEPARATOR . $grandChild] = $grandDefinition;
                }
            }
        }
        return $definitions;
    }

    /**
     * @param object $inflated
     * @return array
     */
    public function serialize($inflated) {
        $serialized = parent::serialize($inflated);
        foreach ($serialized as $child => $serializedChild) {
            if (is_array($serializedChild)) {
                unset($serialized[$child]);
                foreach ($serializedChild as $grandChild => $serializedGrandChild) {
                    $serialized[$child . self::$SEPARATOR . $grandChild] = $serializedGrandChild;
                }
            }
        }
        return $serialized;
    }

    /**
     * @param array $serialized
     * @return array
     */
    public function inflate($serialized) {
        foreach ($serialized as $child => $serializedChild) {
            if (strpos($child, self::$SEPARATOR)) {
                unset($serialized[$child]);
                list($child, $grandChild) = explode(self::$SEPARATOR, $child, 2);
                $serialized[$child][$grandChild] = $serializedChild;
            }
        }
        return parent::inflate($serialized);
    }
}