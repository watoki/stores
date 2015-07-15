<?php
namespace spec\watoki\stores;

use watoki\reflect\Type;
use watoki\reflect\type\StringType;
use watoki\reflect\TypeFactory;
use watoki\scrut\Specification;
use watoki\stores\common\CallbackSerializer;
use watoki\stores\common\factories\ClassSerializerFactory;
use watoki\stores\common\factories\StaticSerializerFactory;
use watoki\stores\common\GenericSerializer;
use watoki\stores\common\NoneSerializer;
use watoki\stores\common\Reflector;
use watoki\stores\file\serializers\JsonSerializer;
use watoki\stores\SerializerRegistry;

/**
 * @property \spec\watoki\reflect\fixtures\ClassFixture class <-
 * @property \watoki\scrut\ExceptionFixture try <-
 */
class InferSerializersFromTypeHintsTest extends Specification {

    function testFailIfTypeHintMissing() {
        $this->class->givenTheClass_WithTheBody('ReflectingSerializer\NoHint', '
            protected $noHint;
        ');

        $this->whenITryToSerialize('ReflectingSerializer\NoHint');
        $this->try->thenTheException_ShouldBeThrown(
            'Could not infer Serializer of [ReflectingSerializer\NoHint::noHint]: ' .
            'Unknown type [].');
    }

    function testFailIfTypeHintInvalid() {
        $this->class->givenTheClass_WithTheBody('ReflectingSerializer\InvalidHint', '
            /** @var notAType */
            protected $invalid;
        ');

        $this->whenITryToSerialize('ReflectingSerializer\InvalidHint');
        $this->try->thenTheException_ShouldBeThrown(
            'Could not infer Serializer of [ReflectingSerializer\InvalidHint::invalid]: ' .
            'Unknown type [notAType].');
    }

    function testFailIfMultipleTypeHints() {
        $this->class->givenTheClass_WithTheBody('ReflectingSerializer\TooMany', '
            /** @var int|string */
            protected $tooMany;
        ');

        $this->whenITryToSerialize('ReflectingSerializer\TooMany');
        $this->try->thenTheException_ShouldBeThrown(
            'Could not infer Serializer of [ReflectingSerializer\TooMany::tooMany]: ' .
            'Ambiguous type.');
    }

    function testSerializeSingleProperty() {
        $this->class->givenTheClass_WithTheBody('ReflectingSerializer\OneProperty', '
            /** @var string */
            public $property = "foo";
        ');

        $this->whenISerialize('ReflectingSerializer\OneProperty');
        $this->thenTheResultShouldBe(array(
            'property' => 'foo'
        ));
    }

    function testInflateSingleProperty() {
        $this->class->givenTheClass_WithTheBody('ReflectingSerializer\OneProperty', '
            /** @var string */
            public $property = "foo";
        ');

        $this->whenIInflate_With('ReflectingSerializer\OneProperty', array(
            'property' => 'bar'
        ));
        $this->thenItsProperty_ShouldBe('property', 'bar');
    }

    function testUseSerializerRegistry() {
        $this->class->givenTheClass_WithTheBody('UseRegistry\SomeClass', '
            /** @var OtherClass */
            protected $other;
        ');
        $this->class->givenTheClass('UseRegistry\OtherClass');
        $this->givenIHaveRegisteredASerializerFor_SerializingItTo('UseRegistry\OtherClass', '"other class"');

        $this->whenISerialize('UseRegistry\SomeClass');
        $this->thenTheResultShouldBe(array(
            'other' => 'other class'
        ));
    }

    function testUseOtherGenericSerializer() {
        $this->class->givenTheClass_WithTheBody('GenericSerializer\SomeClass', '
            /** @var string */
            public $property = "foo";
        ');

        $this->whenISerialize_Using('GenericSerializer\SomeClass', JsonSerializer::$CLASS);
        $this->thenTheResultShouldBe('{"property":"foo"}');
    }

    function testFailIfNotSubClassOfGenericSerializer() {
        $this->class->givenTheClass('NotAGenericSerializer');
        $this->whenITryToSerialize_Using('DateTime', 'NotAGenericSerializer');
        $this->try->thenTheException_ShouldBeThrown(
            '[NotAGenericSerializer] is not a subclass of [watoki\stores\common\GenericSerializer]');
    }

    function testFailIfTypeNotRegisteredAndFallBackFails() {
        $this->class->givenTheClass_WithTheBody('TypeNotRegistered\SomeClass', '
            /** @var int */
            public $property = 42;
        ');
        $this->whenITryToSerialize('TypeNotRegistered\SomeClass');
        $this->try->thenTheException_ShouldBeThrown(
            'Could not infer Serializer of [TypeNotRegistered\SomeClass::property]: ' .
            'No Serializer registered for [watoki\reflect\type\IntegerType Object()]');
    }

    ######################################################################################################

    private $serialized;

    private $inflated;

    /** @var SerializerRegistry */
    private $registry;

    protected function setUp() {
        parent::setUp();
        $this->registry = new SerializerRegistry();
        $this->registry->add(new StaticSerializerFactory(array(
            StringType::$CLASS => new NoneSerializer()
        )));
    }


    private function givenIHaveRegisteredASerializerFor_SerializingItTo($class, $return) {
        $this->registry->add(new ClassSerializerFactory($class, new CallbackSerializer(
                function () use ($return) {
                    return eval('return ' . $return . ';');
                },
                function () {
                    return 'whatever';
                }
            )
        ));
    }

    private function whenITryToSerialize($class) {
        $that = $this;
        $this->try->tryTo(function () use ($class, $that) {
            $that->whenISerialize($class);
        });
    }

    public function whenISerialize($class) {
        $this->whenISerialize_Using($class, GenericSerializer::$CLASS);
    }

    public function whenISerialize_Using($class, $genericSerializer) {
        $serializer = new Reflector($class, $this->registry, new TypeFactory());
        $serializer = $serializer->create($genericSerializer);
        $this->serialized = $serializer->serialize(new $class);
    }

    private function whenITryToSerialize_Using($class, $genericSerializer) {
        $that = $this;
        $this->try->tryTo(function () use ($class, $genericSerializer, $that) {
            $that->whenISerialize_Using($class, $genericSerializer);
        });
    }

    private function whenIInflate_With($class, $array) {
        $serializer = new Reflector($class, $this->registry, new TypeFactory());
        $serializer = $serializer->create(GenericSerializer::$CLASS);
        $this->inflated = $serializer->inflate($array);
    }

    private function thenTheResultShouldBe($array) {
        $this->assertEquals($array, $this->serialized);
    }

    private function thenItsProperty_ShouldBe($name, $value) {
        $this->assertEquals($value, $this->inflated->$name);
    }

}