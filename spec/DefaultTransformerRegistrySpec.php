<?php
namespace spec\watoki\stores;

use rtens\scrut\Assert;
use watoki\stores\transforming\transformers\ArrayTransformer;
use watoki\stores\transforming\transformers\ObjectTransformer;
use watoki\stores\transforming\TransformerRegistryRepository;

/**
 * Transform default objects and back.
 *
 * @property Assert assert <-
 */
class DefaultTransformerRegistrySpec {

    function handlesPrimitives() {
        $this->handle('string');
        $this->handle(42);
    }

    function handlesArrays() {
        $this->handle(['foo' => 'bar']);
    }

    function handlesArraysMimickingObjects() {
        $this->handle([
            ObjectTransformer::TYPE_KEY => 'foo',
            ObjectTransformer::DATA_KEY => 'bar',
            ArrayTransformer::ESCAPE_KEY . ObjectTransformer::TYPE_KEY => 'baz',
        ]);
    }

    function handlesDynamicObjects() {
        $object = new \stdClass();
        $object->foo = 'FOO';
        $object->bar = 'BAR';

        $this->handle($object, [
            ObjectTransformer::TYPE_KEY => \stdClass::class,
            ObjectTransformer::DATA_KEY => [
                'foo' => 'FOO',
                'bar' => 'BAR'
            ]
        ]);
    }

    function handlesStaticObjects() {
        $this->handle(new DefaultTransformerRegistrySpec_Foo('FOO', 'BAR'), [
            ObjectTransformer::TYPE_KEY => DefaultTransformerRegistrySpec_Foo::class,
            ObjectTransformer::DATA_KEY => [
                'foo' => 'FOO',
                'bar' => 'BAR'
            ]
        ]);
    }

    function handlesMixedObjects() {
        $object = new DefaultTransformerRegistrySpec_Foo('FOO', 'BAR');
        $object->baz = 'BAZ';

        $this->handle($object, [
            ObjectTransformer::TYPE_KEY => DefaultTransformerRegistrySpec_Foo::class,
            ObjectTransformer::DATA_KEY => [
                'foo' => 'FOO',
                'bar' => 'BAR',
                'baz' => 'BAZ'
            ]
        ]);
    }

    function handlesDateTimes() {
        $this->handle(new \DateTime('2011-12-13 14:15:16 UTC'), [
            ObjectTransformer::TYPE_KEY => \DateTime::class,
            ObjectTransformer::DATA_KEY => '2011-12-13T14:15:16+00:00'
        ]);
    }

    function handlesImmutableDateTimes() {
        $this->handle(new \DateTimeImmutable('2011-12-13 14:15:16 UTC'), [
            ObjectTransformer::TYPE_KEY => \DateTimeImmutable::class,
            ObjectTransformer::DATA_KEY => '2011-12-13T14:15:16+00:00'
        ]);
    }

    private function handle($value, $expectedTransformed = null) {
        $registry = TransformerRegistryRepository::getDefault();

        $transformed = $registry->toTransform($value)->transform($value);

        if ($expectedTransformed !== null) {
            $this->assert->equals($transformed, $expectedTransformed);
        }

        $reverted = $registry->toRevert($transformed)->revert($transformed);
        $this->assert->equals($value, $reverted);
    }
}

class DefaultTransformerRegistrySpec_Foo {
    private $foo;
    private $bar;
    public function __construct($foo, $bar) {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}