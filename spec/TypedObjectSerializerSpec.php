<?php
namespace spec\watoki\stores;

use rtens\scrut\Assert;
use rtens\scrut\fixtures\ExceptionFixture;
use watoki\reflect\Type;
use watoki\reflect\type\ArrayType;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\StringType;
use watoki\reflect\TypeFactory;
use watoki\stores\serializing\JsonSerializer;
use watoki\stores\serializing\TypedObjectSerializer;

/**
 * Serializes and inflates a special type of object
 *
 * @property Assert assert <-
 * @property ExceptionFixture try <-
 */
class TypedObjectSerializerSpec {

    function handlesStrings() {
        $this->handle(new StringType(), 'foo', 'foo');
    }

    function demandsThatTypesMatch() {
        $this->try->tryTo(function () {
            $this->handle(new StringType(), ['not a string'], '');
        });
        $this->try->thenTheException_ShouldBeThrown('Given value is not of type [string]');
    }

    function handlesArrays() {
        $this->handle(
            new ArrayType(new StringType()),
            [
                'foo' => 'FOO',
                'bar' => 'BAR'
            ],
            [
                'foo' => 'FOO',
                'bar' => 'BAR'
            ]);
    }

    function handlesEmptyObjects() {
        $this->handle(
            new ClassType(TypedObjectSerializerSpec_Foo::class),
            new TypedObjectSerializerSpec_Foo(),
            new \StdClass()
        );
    }

    function handlesObjectWithAnnotatedProperties() {
        $this->assert->incomplete('WIP');

        $this->handle(
            new ClassType(TypedObjectSerializerSpec_Bar::class),
            new TypedObjectSerializerSpec_Bar(
                new TypedObjectSerializerSpec_Foo()
            ),
            new \StdClass()
        );
    }

    private function handle(Type $type, $value, $serialized) {
        $serializer = new TypedObjectSerializer($type, new JsonSerializer(), new TypeFactory());

        $string = $serializer->serialize($value);
        $this->assert->equals($string, json_encode($serialized));
        $this->assert->equals($serializer->inflate($string), $value);
    }
}

class TypedObjectSerializerSpec_Foo {
}

class TypedObjectSerializerSpec_Bar {

    /** @var null|TypedObjectSerializerSpec_Foo */
    private $foo;

    /** @var TypedObjectSerializerSpec_Bar[] */
    private $bar;

    public function __construct($foo, $bar = []) {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}