<?php
namespace spec\watoki\stores;

use watoki\stores\keyGenerating\KeyGenerator;
use watoki\stores\Store;
use watoki\stores\stores\MemoryStore;

/**
 * Store data in memory
 */
class MemoryStoreSpec extends StoreSpec {

    /**
     * @return Store
     */
    protected function createStore() {
        return new MemoryStore();
    }

    protected function createStoreWithKeyGenerator(KeyGenerator $generator) {
        return new MemoryStore($generator);
    }

    function itWritesAndReadsAnything() {
        $this->store->write(['foo' => 'bar'], 'foo');
        $this->assert->equals($this->store->read('foo'), ['foo' => 'bar']);
    }

    function itConvertsKeysToStrings() {
        $this->store->write('foo', 42);
        $this->assert->isTrue($this->store->has(42));
        $this->assert->equals($this->store->read('42'), 'foo');

        $this->store->write('foo', 4.2);
        $this->assert->isTrue($this->store->has(4.2));
        $this->assert->equals($this->store->read('42'), 'foo');

        $this->store->write('foo', (double)42);
        $this->assert->isTrue($this->store->has((double)4.2));
        $this->assert->equals($this->store->read('42'), 'foo');

        $this->store->write('foo', new __MemoryStoreSpec_Foo('bar'));
        $this->assert->isTrue($this->store->has(new __MemoryStoreSpec_Foo('bar')));
        $this->assert->equals($this->store->read('bar'), 'foo');
    }

    function itFailsIfKeyCannotBeConvertedToString() {
        $this->try->tryTo(function () {
            $this->store->write('Foo', ['not' => 'a string']);
        });
        $this->try->thenTheException_ShouldBeThrown('Memory keys must be strings.');
    }
}

class __MemoryStoreSpec_Foo {
    private $value;
    public function __construct($value) {
        $this->value = $value;
    }
    function __toString() {
        return $this->value;
    }
}