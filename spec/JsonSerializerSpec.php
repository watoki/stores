<?php
namespace spec\watoki\stores;

use rtens\scrut\Assert;
use watoki\stores\serializing\JsonSerializer;

/**
 * Serializes a value to a JSON string
 *
 * @property JsonSerializer serializer
 * @property Assert assert <-
 */
class JsonSerializerSpec {

    function before() {
        $this->serializer = new JsonSerializer();
    }

    function serializesPrimitives() {
        $string = $this->serializer->serialize(['foo' => [1, 'bar'], null]);
        $this->assert->equals($string, '{"foo":[1,"bar"],"0":null}');
    }

    function inflatesPrimitives() {
        $inflated = $this->serializer->inflate('{"foo":[1,"bar"],"0":null}');
        $this->assert->equals($inflated, ['foo' => [1, 'bar'], null]);
    }

    function handlesBinaryValues() {
        $value = [
            'foo' => hex2bin('716af24e'),
            'bar' => [
                'foo' => hex2bin('6feab13e'),
                'bar' => 'BAR'
            ]
        ];

        $string = $this->serializer->serialize($value);
        $inflated = $this->serializer->inflate($string);

        $this->assert->equals($inflated, $value);
    }

    function printsPretty() {
        $this->serializer->setPrettyPrint(true);

        $string = $this->serializer->serialize(['foo' => 'bar']);
        $this->assert->equals($string,
            '{' . "\n" .
            '    "foo": "bar"' . "\n" .
            '}');
    }
}