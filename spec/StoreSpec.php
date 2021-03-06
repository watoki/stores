<?php
namespace spec\watoki\stores;

use rtens\scrut\Assert;
use rtens\scrut\fixtures\ExceptionFixture;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\stores\exceptions\InvalidKeyException;
use watoki\stores\exceptions\NotFoundException;
use watoki\stores\keyGenerating\keyGenerators\CallbackKeyGenerator;
use watoki\stores\keyGenerating\KeyGenerator;
use watoki\stores\Store;

/**
 * Every Store implementation should follow a common contract
 *
 * @property Assert assert <-
 * @property ExceptionFixture try <-
 * @property Store store
 */
abstract class StoreSpec extends StaticTestSuite {

    /**
     * @return Store
     */
    abstract protected function createStore();

    /**
     * @param KeyGenerator $generator
     * @return Store|null
     */
    protected function createStoreWithKeyGenerator(KeyGenerator $generator) {
    }

    protected function data($suffix = '') {
        return 'bla bla bla' . $suffix;
    }

    private function acceptedKeys() {
        return ['foo', 42, 4.2, (double)4.2, new __StoreSpec_Foo('foo')];
    }

    private function notAcceptedKeys() {
        return [['array'], new \StdClass()];
    }

    protected function before() {
        $this->store = $this->createStore();
    }

    public function getTests() {
        $class = new \ReflectionClass($this);

        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (substr($method->getName(), 0, 2) == 'it') {
                yield $this->createTestCase($class, $method);
            }
        }
    }

    function itReturnsTheKey() {
        $this->assert->equals($this->store->write($this->data(), 'foo'), 'foo');
    }

    function itWritesAndReadsData() {
        $this->store->write($this->data(1), 'foo');
        $this->store->write($this->data(2), 'bar');

        $this->assert->isTrue($this->store->has('foo'));
        $this->assert->isTrue($this->store->has('bar'));

        $this->assert->equals($this->store->read('foo'), $this->data(1));
        $this->assert->equals($this->store->read('bar'), $this->data(2));
    }

    function itListsKeys() {
        $this->store->write('uno', 'one');
        $this->store->write('dos', 'two');
        $this->store->write('tres', 'three');

        $keys = $this->store->keys();

        $this->assert->size($keys, 3);
        $this->assert->contains($keys, 'one');
        $this->assert->contains($keys, 'two');
        $this->assert->contains($keys, 'three');
    }

    function itRemovesData() {
        $this->store->write('uno', 'one');
        $this->store->write('dos', 'two');
        $this->store->write('tres', 'three');

        $this->store->remove('two');

        $this->assert->size($this->store->keys(), 2);
        $this->assert->not($this->store->has('two'));
    }

    function itCannotReadUnknownKey() {
        $this->try->tryTo(function () {
            $this->store->read('foo');
        });
        $this->try->thenA_ShouldBeThrown(NotFoundException::class);
        $this->try->thenTheException_ShouldBeThrown('Could not find [foo]');
    }

    function itCannotRemoveUnknownKey() {
        $this->try->tryTo(function () {
            $this->store->remove('foo');
        });
        $this->try->thenA_ShouldBeThrown(NotFoundException::class);
        $this->try->thenTheException_ShouldBeThrown('Could not find [foo]');
    }

    function itAcceptsDifferentKeys() {
        foreach ($this->acceptedKeys() as $key) {
            $this->assert->equals($this->store->write($this->data(), $key), $key);
            $this->assert->isTrue($this->store->has($key));
            $this->assert->equals($this->store->read($key), $this->data());
            $this->store->remove($key);
            $this->assert->not($this->store->has($key));
        }
    }

    function itFailsIfAKeyIsNotAccepted() {
        $try = function ($method, $args) {
            $this->try->tryTo(function () use ($method, $args) {
                call_user_func_array([$this->store, $method], $args);
            });
            $this->try->thenA_ShouldBeThrown(InvalidKeyException::class);
        };

        foreach ($this->notAcceptedKeys() as $key) {
            $try('write', [$this->data(), $key]);
            $try('has', [$key]);
            $try('read', [$key]);
            $try('remove', [$key]);
        }
    }

    function itGeneratesKeys() {
        $i = 1;
        $store = $this->createStoreWithKeyGenerator(new CallbackKeyGenerator(function () use (&$i) {
            return 'key' . $i++;
        }));

        if (!$store) {
            $this->assert->pass();
            return;
        }

        $store->write('foo');
        $store->write('bar');

        $this->assert->size($store->keys(), 2);
        $this->assert->contains($store->keys(), 'key1');
        $this->assert->contains($store->keys(), 'key2');

        $this->assert->isTrue($store->has('key1'));
        $this->assert->equals($store->read('key1'), 'foo');

        $this->assert->isTrue($store->has('key2'));
        $this->assert->equals($store->read('key2'), 'bar');
    }
}

class __StoreSpec_Foo {
    private $value;
    public function __construct($value) {
        $this->value = $value;
    }
    function __toString() {
        return $this->value;
    }
}