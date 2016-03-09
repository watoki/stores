<?php
namespace spec\watoki\stores\transforming;

use rtens\scrut\Assert;
use rtens\scrut\fixtures\ExceptionFixture;
use watoki\reflect\TypeFactory;
use watoki\stores\transforming\TransformerRegistry;
use watoki\stores\transforming\TransformerRegistryRepository;
use watoki\stores\transforming\transformers\ObjectTransformer;
use watoki\stores\transforming\TypeMapper;

/**
 * Maps between classes and their aliases
 *
 * @property TransformerRegistry transformers
 * @property TypeMapper mapper <-
 * @property Assert assert <-
 * @property ExceptionFixture try <-
 */
class TypeMapperSpec {

    function before() {
        $this->transformers = TransformerRegistryRepository::createDefault($this->mapper, new TypeFactory());
    }

    function cannotAddClassNameAsAlias() {
        $this->try->tryTo(function () {
            $this->mapper->addAlias(\DateTime::class, \DateTimeImmutable::class);
        });
        $this->try->thenTheException_ShouldBeThrown('An alias must not be an existing class.');
    }

    function cannotClassOfAliasMustExist() {
        $this->try->tryTo(function () {
            $this->mapper->addAlias('Foo', 'Bar');
        });
        $this->try->thenTheException_ShouldBeThrown('Class [Foo] does not exist.');
    }

    function cannotAddExistingAlias() {
        $this->mapper->addAlias(\DateTime::class, 'Baz');
        $this->try->tryTo(function () {
            $this->mapper->addAlias(\DateTimeImmutable::class, 'Baz');
        });
        $this->try->thenTheException_ShouldBeThrown('Alias [Baz] was already added to [DateTime].');
    }

    function mapsClassesToAliases() {
        $this->mapper->addAlias(TypeMapperSpec_Bar::class, 'Bar');
        $this->mapper->addAlias(TypeMapperSpec_Foo::class, 'Foo');
        $this->mapper->addAlias(\DateTime::class, 'Date');
        $this->mapper->addAlias(\DateTime::class, 'Time');
        $this->mapper->addAlias(\DateTimeImmutable::class, 'Immutable');

        $data = new TypeMapperSpec_Bar(
            new TypeMapperSpec_Foo(),
            [
                new \DateTime('2011-12-13 UTC'),
                new \DateTimeImmutable('2011-12-14 UTC'),
            ]
        );

        $expected = [
            ObjectTransformer::TYPE_KEY => 'Bar',
            ObjectTransformer::DATA_KEY => [
                'foo' => [
                    ObjectTransformer::TYPE_KEY => 'Foo',
                    ObjectTransformer::DATA_KEY => []
                ],
                'bar' => [
                    [
                        ObjectTransformer::TYPE_KEY => 'Date',
                        ObjectTransformer::DATA_KEY => '2011-12-13T00:00:00+00:00'
                    ],
                    [
                        ObjectTransformer::TYPE_KEY => 'Immutable',
                        ObjectTransformer::DATA_KEY => '2011-12-14T00:00:00+00:00'
                    ]
                ]
            ]
        ];

        $transformed = $this->transformers->toTransform($data)->transform($data);
        $this->assert->equals($transformed, $expected);
        $this->assert->equals($this->transformers->toRevert($transformed)->revert($transformed), $data);
    }

    function mapsBackFromDifferentAliases() {
        $this->mapper->addAlias(\DateTime::class, 'Foo');
        $this->mapper->addAlias(\DateTime::class, 'Bar');

        $data = [
            new \DateTime('2011-12-13 UTC'),
            new \DateTime('2011-12-14 UTC'),
        ];

        $transformed = [
            [
                ObjectTransformer::TYPE_KEY => 'Foo',
                ObjectTransformer::DATA_KEY => '2011-12-13T00:00:00+00:00'
            ],
            [
                ObjectTransformer::TYPE_KEY => 'Bar',
                ObjectTransformer::DATA_KEY => '2011-12-14T00:00:00+00:00'
            ]
        ];

        $this->assert->equals($this->transformers->toRevert($transformed)->revert($transformed), $data);

    }
}

class TypeMapperSpec_Foo {
}

class TypeMapperSpec_Bar {

    private $foo;
    private $bar;

    public function __construct($foo, $bar) {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}