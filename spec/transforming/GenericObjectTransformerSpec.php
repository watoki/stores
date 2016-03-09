<?php
namespace spec\watoki\stores\transforming;

use rtens\scrut\Assert;
use rtens\scrut\fixtures\ExceptionFixture;
use watoki\reflect\Type;
use watoki\reflect\TypeFactory;
use watoki\stores\transforming\TransformerRegistryRepository;
use watoki\stores\transforming\transformers\GenericObjectTransformer;
use watoki\stores\transforming\transformers\ObjectTransformer;
use watoki\stores\transforming\TypeMapper;

/**
 * Transforms generic objects to primitives and back
 *
 * @property TypeMapper mapper <-
 * @property Assert assert <-
 * @property ExceptionFixture try <-
 */
class GenericObjectTransformerSpec {

    function handlesEmptyObjects() {
        $this->handle(new GenericObjectTransformerSpec_Foo(), [
            ObjectTransformer::TYPE_KEY => GenericObjectTransformerSpec_Foo::class,
            ObjectTransformer::DATA_KEY => []
        ]);
    }

    function handlesObjectsWithProperties() {
        $this->handle(new GenericObjectTransformerSpec_Bar('FOO', 'BAR'), [
            ObjectTransformer::TYPE_KEY => GenericObjectTransformerSpec_Bar::class,
            ObjectTransformer::DATA_KEY => [
                'foo' => 'FOO',
                'bar' => 'BAR'
            ]
        ]);
    }

    function isBackwardsCompatible() {
        $object = $this->transformer()->revert([
            ObjectTransformer::TYPE_KEY => GenericObjectTransformerSpec_Bar::class,
            ObjectTransformer::DATA_KEY => [
                'foo' => 'foo'
            ]
        ]);
        $this->assert->equals($object, new GenericObjectTransformerSpec_Bar('foo', null));
    }

    function handlesNestedObjects() {
        $this->handle(new GenericObjectTransformerSpec_Bar(
            new GenericObjectTransformerSpec_Foo(),
            new GenericObjectTransformerSpec_Bar('foo', [
                'foo' => new GenericObjectTransformerSpec_Foo(),
                'bar' => new GenericObjectTransformerSpec_Bar('foo', 'bar')
            ])
        ));
    }

    function usesTheRegistry() {
        $this->handle(new GenericObjectTransformerSpec_Bar(
            new \DateTime('2011-12-13 14:15:16 UTC'),
            new \DateTimeImmutable('2011-12-13 14:15:16 UTC')
        ), [
            ObjectTransformer::TYPE_KEY => GenericObjectTransformerSpec_Bar::class,
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

    private function handle($value, $expectedTransformed = null) {
        $transformer = $this->transformer();

        $transformed = $transformer->transform($value);

        if ($expectedTransformed !== null) {
            $this->assert->equals($transformed, $expectedTransformed);
        }

        $this->assert->equals($transformer->revert($transformed), $value);
    }

    private function transformer() {
        $factory = new TypeFactory();
        $transformers = TransformerRegistryRepository::createDefault($this->mapper, $factory);
        return new GenericObjectTransformer($transformers, $this->mapper, $factory);
    }
}

class GenericObjectTransformerSpec_Foo {
}

class GenericObjectTransformerSpec_Bar {

    private $foo;
    private $bar;

    public function __construct($foo, $bar) {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}