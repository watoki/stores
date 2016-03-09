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

    function itOnlyAcceptsStringKeys() {
        $this->try->tryTo(function () {
            $this->store->write('Foo', 12);
        });
        $this->try->thenTheException_ShouldBeThrown('Memory keys must be strings.');
    }
}