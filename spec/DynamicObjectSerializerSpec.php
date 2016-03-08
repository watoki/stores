<?php
namespace spec\watoki\stores;

use rtens\scrut\Assert;
use watoki\stores\serializing\DynamicObjectSerializer;
use watoki\stores\serializing\JsonSerializer;

/**
 * Serializes and inflates any object.
 *
 * @property DynamicObjectSerializer serializer
 * @property Assert assert <-
 */
class DynamicObjectSerializerSpec {

    function before() {
        $this->serializer = new DynamicObjectSerializer(new JsonSerializer());
    }

    function handlesPrimitives() {
        $this->handle('string');
        $this->handle(42);
    }

    function handlesArrays() {
        $this->handle(['foo' => 'bar']);
    }

    function handlesEmptyObjects() {
        $this->handle(new ObjectSerializerSpec_Foo());
    }

    function handlesObjectsWithProperties() {
        $this->handle(new ObjectSerializerSpec_Bar('foo', 'bar'));
    }

    function isBackwardsCompatible() {
        $object = $this->serializer->inflate(json_encode([
            'type' => ObjectSerializerSpec_Bar::class,
            'data' => [
                'foo' => 'foo'
            ]
        ]));
        $this->assert->equals($object, new ObjectSerializerSpec_Bar('foo', null));
    }

    function fallsBackToString() {
        $string = $this->serializer->inflate('not valid json');
        $this->assert->equals($string, 'not valid json');
    }

    function handlesArraysWithTypeAndDataKeys() {
        $this->handle(['type' => 'foo', 'data' => 'bar']);
    }

    function handlesNestedObjects() {
        $this->handle(new ObjectSerializerSpec_Bar(
            new ObjectSerializerSpec_Foo(),
            new ObjectSerializerSpec_Bar('foo', [
                'foo' => new ObjectSerializerSpec_Foo(),
                'bar' => new ObjectSerializerSpec_Bar('foo', 'bar')
            ])
        ));
    }

    private function handle($value) {
        $serialized = $this->serializer->serialize($value);

        $this->assert->isTrue(is_string($serialized));
        $this->assert->equals($this->serializer->inflate($serialized), $value);
    }
}

class ObjectSerializerSpec_Foo {
}

class ObjectSerializerSpec_Bar {

    private $foo;
    private $bar;

    public function __construct($foo, $bar) {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}