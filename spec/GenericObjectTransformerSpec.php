<?php
namespace spec\watoki\stores;

use rtens\scrut\Assert;
use rtens\scrut\fixtures\ExceptionFixture;
use watoki\reflect\Type;
use watoki\reflect\type\ArrayType;
use watoki\reflect\type\ClassType;
use watoki\reflect\TypeFactory;
use watoki\stores\transforming\transformers\GenericObjectTransformer;
use watoki\stores\transforming\TransformerRegistryRepository;
use watoki\stores\transforming\transformers\ObjectTransformer;

/**
 * Transforms objects to primitives and back
 *
 * @property Assert assert <-
 * @property ExceptionFixture try <-
 */
class GenericObjectTransformerSpec {

    function handlesEmptyObjects() {
        $this->handle(new ObjectTransformerSpec_Foo(), [
            ObjectTransformer::TYPE_KEY => ObjectTransformerSpec_Foo::class,
            ObjectTransformer::DATA_KEY => []
        ]);
    }

    function handlesObjectsWithProperties() {
        $this->handle(new ObjectTransformerSpec_Bar('FOO', 'BAR'), [
            ObjectTransformer::TYPE_KEY => ObjectTransformerSpec_Bar::class,
            ObjectTransformer::DATA_KEY => [
                'foo' => 'FOO',
                'bar' => 'BAR'
            ]
        ]);
    }

    function isBackwardsCompatible() {
        $object = $this->transformer()->revert([
            ObjectTransformer::TYPE_KEY => ObjectTransformerSpec_Bar::class,
            ObjectTransformer::DATA_KEY => [
                'foo' => 'foo'
            ]
        ]);
        $this->assert->equals($object, new ObjectTransformerSpec_Bar('foo', null));
    }

    function handlesNestedObjects() {
        $this->handle(new ObjectTransformerSpec_Bar(
            new ObjectTransformerSpec_Foo(),
            new ObjectTransformerSpec_Bar('foo', [
                'foo' => new ObjectTransformerSpec_Foo(),
                'bar' => new ObjectTransformerSpec_Bar('foo', 'bar')
            ])
        ));
    }

    function usesTheFactory() {
        $this->handle(new ObjectTransformerSpec_Bar(
            new \DateTime('2011-12-13 14:15:16 UTC'),
            new \DateTimeImmutable('2011-12-13 14:15:16 UTC')
        ), [
            ObjectTransformer::TYPE_KEY => ObjectTransformerSpec_Bar::class,
            ObjectTransformer::DATA_KEY => [
                'foo' => [
                    ObjectTransformer::TYPE_KEY => \DateTime::class,
                    ObjectTransformer::DATA_KEY => '2011-12-13T14:15:16+00:00'
                ],
                'bar' => [
                    ObjectTransformer::TYPE_KEY => \DateTimeImmutable::class,
                    ObjectTransformer::DATA_KEY => '2011-12-13T14:15:16+00:00'
                ]
            ]
        ]);
    }

    function handlesTypedObject() {
        $this->assert->incomplete('WIP');

        $this->handle(
            new ObjectTransformerSpec_Bar('FOO', 'BAR'),
            [
                'foo' => 'FOO',
                'bar' => 'BAR'
            ],
            new ClassType(ObjectTransformerSpec_Bar::class)
        );
    }

    function handlesNestedTypedObjects() {
        $this->assert->incomplete('WIP');

        $this->handle(
            [
                new ObjectTransformerSpec_Bar('ONE', 'UNO'),
                new ObjectTransformerSpec_Bar('TWO', 'DOS'),
            ],
            [
                ['foo' => 'ONE', 'bar' => 'UNO'],
                ['foo' => 'TWO', 'bar' => 'DOS'],
            ],
            new ArrayType(new ClassType(ObjectTransformerSpec_Bar::class))
        );
    }

    function handlesNestedMixedObjects() {
        $this->assert->incomplete('WIP');

        $this->handle(
            [
                new ObjectTransformerSpec_Bar('ONE', 'UNO'),
                new ObjectTransformerSpec_Foo(),
                'foo'
            ],
            [
                ['foo' => 'ONE', 'bar' => 'UNO'],
                [ObjectTransformer::TYPE_KEY => ObjectTransformerSpec_Foo::class],
                'foo'
            ],
            new ArrayType(new ClassType(ObjectTransformerSpec_Bar::class))
        );
    }

    function handlesObjectWithAnnotatedProperties() {
        $this->assert->incomplete('WIP');

        $this->handle(
            new ObjectTransformerSpec_Baz(
                new ObjectTransformerSpec_Foo(),
                [
                    new ObjectTransformerSpec_Bar('one', 'uno'),
                    new ObjectTransformerSpec_Bar('two', 'dos'),
                ]
            ),
            [
                'foo' => [],
                'bar' => [
                    ['foo' => 'one', 'bar' => 'uno'],
                    ['foo' => 'two', 'bar' => 'dos'],
                ]
            ],
            new ClassType(ObjectTransformerSpec_Baz::class)
        );
    }

    private function handle($value, $expectedTransformed = null) {
        $transformer = $this->transformer();

        $transformed = $transformer->transform($value);

        if ($expectedTransformed !== null) {
            $this->assert->equals($transformed, $expectedTransformed);
        }

        $this->assert->equals($transformer->revert($transformed), $value);
    }

    private function transformer() {
        return new GenericObjectTransformer(TransformerRegistryRepository::getDefault(), new TypeFactory());
    }
}

class ObjectTransformerSpec_Foo {
}

class ObjectTransformerSpec_Bar {

    private $foo;
    private $bar;

    public function __construct($foo, $bar) {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}

class ObjectTransformerSpec_Baz {

    /** @var ObjectTransformerSpec_Foo */
    private $foo;

    /** @var ObjectTransformerSpec_Bar[] */
    private $bar;

    public function __construct($foo = null, $bar = []) {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}