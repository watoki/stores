<?php
namespace spec\watoki\stores;

use watoki\stores\keys\KeyGenerator;
use watoki\stores\keys\KeyGeneratorFactory;
use watoki\stores\stores\MemoryStore;
use watoki\stores\Store;

/**
 * Store data in memory
 */
class MemoryStoreSpec extends StoreSpec {

    /**
     * @return Store
     */
    protected function createStore() {
        return new MemoryStore(KeyGeneratorFactory::getDefault());
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