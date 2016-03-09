<?php
namespace spec\watoki\stores\transforming;

use rtens\scrut\Assert;
use watoki\reflect\Type;
use watoki\reflect\type\ClassType;
use watoki\reflect\TypeFactory;
use watoki\stores\transforming\TransformerRegistryRepository;
use watoki\stores\transforming\transformers\ObjectTransformer;
use watoki\stores\transforming\transformers\TypedObjectTransformer;
use watoki\stores\transforming\transformers\TypedValue;

/**
 * Transforms objects with type to primitives and back
 *
 * @property Assert assert <-
 */
class TypedObjectTransformerSpec {

    function handlesTypedObject() {
        $this->handle(
            new ClassType(__TypedObjectTransformerSpec_Bar::class),
            new __TypedObjectTransformerSpec_Bar('FOO', 'BAR'),
            [
                'foo' => 'FOO',
                'bar' => 'BAR'
            ]
        );
    }

    function handlesObjectWithAnnotatedProperties() {
        $this->handle(
            new ClassType(__TypedObjectTransformerSpec_Baz::class),
            new __TypedObjectTransformerSpec_Baz(
                new __TypedObjectTransformerSpec_Foo()
            ),
            [
                'foo' => [],
                'bar' => []
            ]
        );
    }

    function handleObjectNotMatchingAnnotation() {
        $this->handle(
            new ClassType(__TypedObjectTransformerSpec_Baz::class),
            new __TypedObjectTransformerSpec_Baz(
                new __TypedObjectTransformerSpec_Bar('one', 'two')
            ),
            [
                'foo' => [
                    ObjectTransformer::TYPE_KEY => __TypedObjectTransformerSpec_Bar::class,
                    ObjectTransformer::DATA_KEY => [
                        'foo' => 'one',
                        'bar' => 'two'
                    ],
                ],
                'bar' => []
            ]
        );
    }

    function handlesNestedTypedObjects() {
        $this->handle(
            new ClassType(__TypedObjectTransformerSpec_Baz::class),
            new __TypedObjectTransformerSpec_Baz(
                new __TypedObjectTransformerSpec_Foo(),
                [
                    new __TypedObjectTransformerSpec_Bar('one', 'uno'),
                    new __TypedObjectTransformerSpec_Bar('two', 'dos'),
                ]
            ),
            [
                'foo' => [],
                'bar' => [
                    ['foo' => 'one', 'bar' => 'uno'],
                    ['foo' => 'two', 'bar' => 'dos'],
                ]
            ]
        );
    }

    function handlesNestedMixedObjects() {
        $this->handle(
            new ClassType(__TypedObjectTransformerSpec_Baz::class),
            new __TypedObjectTransformerSpec_Baz(
                new __TypedObjectTransformerSpec_Foo(),
                [
                    new __TypedObjectTransformerSpec_Bar('one', 'uno'),
                    new __TypedObjectTransformerSpec_Foo()
                ]
            ),
            [
                'foo' => [],
                'bar' => [
                    ['foo' => 'one', 'bar' => 'uno'],
                    [
                        ObjectTransformer::TYPE_KEY => __TypedObjectTransformerSpec_Foo::class,
                        ObjectTransformer::DATA_KEY => [],
                    ]
                ]
            ]
        );
    }

    private function handle(ClassType $type, $value, $expectedTransformed = null) {
        $transformer = new TypedObjectTransformer(TransformerRegistryRepository::getDefault(), new TypeFactory());

        $transformed = $transformer->transform(new TypedValue($value, $type));

        if ($expectedTransformed !== null) {
            $this->assert->equals($transformed, $expectedTransformed);
        }

        $this->assert->equals($transformer->revert(new TypedValue($transformed, $type)), $value);
    }
}

class __TypedObjectTransformerSpec_Foo {
}

class __TypedObjectTransformerSpec_Bar {

    private $foo;
    private $bar;

    public function __construct($foo, $bar) {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}

class __TypedObjectTransformerSpec_Baz {

    /** @var __TypedObjectTransformerSpec_Foo */
    private $foo;

    /** @var __TypedObjectTransformerSpec_Bar[] */
    private $bar;

    public function __construct($foo = null, $bar = []) {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}