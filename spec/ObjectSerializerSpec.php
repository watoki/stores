<?php
namespace spec\watoki\stores;

use rtens\scrut\Assert;
use rtens\scrut\fixtures\ExceptionFixture;
use watoki\reflect\Type;
use watoki\reflect\type\ArrayType;
use watoki\reflect\type\ClassType;
use watoki\stores\serializing\ObjectSerializer;

/**
 * Serializes and inflates any object
 *
 * @property Assert assert <-
 * @property ExceptionFixture try <-
 */
class ObjectSerializerSpec {

    function handlesPrimitives() {
        $this->handle('string');
        $this->handle(42);
    }

    function handlesArrays() {
        $this->handle(['foo' => 'bar']);
    }

    function fallsBackToString() {
        $string = $this->serializer()->inflate('not valid json');
        $this->assert->equals($string, 'not valid json');
    }

    function handlesEmptyObjects() {
        $this->handle(new ObjectSerializerSpec_Foo());
    }

    function handlesObjectsWithProperties() {
        $this->handle(new ObjectSerializerSpec_Bar('FOO', 'BAR'), [
            ObjectSerializer::TYPE_KEY => ObjectSerializerSpec_Bar::class,
            'foo' => 'FOO',
            'bar' => 'BAR'
        ]);
    }

    function isBackwardsCompatible() {
        $object = $this->serializer()->inflate(json_encode([
            ObjectSerializer::TYPE_KEY => ObjectSerializerSpec_Bar::class,
            'foo' => 'foo'
        ]));
        $this->assert->equals($object, new ObjectSerializerSpec_Bar('foo', null));
    }

    function handlesArraysWithTypeKey() {
        $this->handle([
            ObjectSerializer::TYPE_KEY => 'foo',
            ObjectSerializer::ESCAPED_TYPE_KEY => 'bar'
        ]);
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

    function handlesTypedObject() {
        $this->handle(
            new ObjectSerializerSpec_Bar('FOO', 'BAR'),
            [
                'foo' => 'FOO',
                'bar' => 'BAR'
            ],
            new ClassType(ObjectSerializerSpec_Bar::class)
        );
    }

    function handlesArrayOfTypedObjects() {
        $this->handle(
            [
                new ObjectSerializerSpec_Bar('ONE', 'UNO'),
                new ObjectSerializerSpec_Bar('TWO', 'DOS'),
            ],
            [
                ['foo' => 'ONE', 'bar' => 'UNO'],
                ['foo' => 'TWO', 'bar' => 'DOS'],
            ],
            new ArrayType(new ClassType(ObjectSerializerSpec_Bar::class))
        );
    }

    function handlesArrayOfMixedObjects() {
        $this->handle(
            [
                new ObjectSerializerSpec_Bar('ONE', 'UNO'),
                new ObjectSerializerSpec_Foo(),
                'foo'
            ],
            [
                ['foo' => 'ONE', 'bar' => 'UNO'],
                [ObjectSerializer::TYPE_KEY => ObjectSerializerSpec_Foo::class],
                'foo'
            ],
            new ArrayType(new ClassType(ObjectSerializerSpec_Bar::class))
        );
    }

    function handlesObjectWithAnnotatedProperties() {
        $this->handle(
            new ObjectSerializerSpec_Baz(
                new ObjectSerializerSpec_Foo(),
                [
                    new ObjectSerializerSpec_Bar('one', 'uno'),
                    new ObjectSerializerSpec_Bar('two', 'dos'),
                ]
            ),
            [
                'foo' => [],
                'bar' => [
                    ['foo' => 'one', 'bar' => 'uno'],
                    ['foo' => 'two', 'bar' => 'dos'],
                ]
            ],
            new ClassType(ObjectSerializerSpec_Baz::class)
        );
    }

    private function handle($value, $serialized = null, $type = null) {
        $serializer = $this->serializer($type);

        $string = $serializer->serialize($value);

        $this->assert->isTrue(is_string($string));
        if ($serialized) {
            $this->assert->equals(json_decode($string, true), $serialized);
        }

        $this->assert->equals($serializer->inflate($string), $value);
    }

    private function serializer($type = null) {
        return new ObjectSerializer($type);
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

class ObjectSerializerSpec_Baz {

    /** @var ObjectSerializerSpec_Foo */
    private $foo;

    /** @var ObjectSerializerSpec_Bar[] */
    private $bar;

    public function __construct($foo = null, $bar = []) {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}