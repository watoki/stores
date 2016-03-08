<?php
namespace spec\watoki\stores;

use rtens\scrut\Assert;
use rtens\scrut\fixtures\ExceptionFixture;
use watoki\stores\exceptions\NotFoundException;
use watoki\stores\keys\CallbackKeyGenerator;
use watoki\stores\memory\MemoryStore;

/**
 * Store data in memory
 *
 * @property MemoryStore store
 * @property Assert assert <-
 * @property ExceptionFixture try <-
 */
class MemoryStoreSpec {

    function before() {
        $this->store = new MemoryStore();
    }

    function writesAndReads() {
        $this->store->write(['foo' => 'bar'], 'foo');
        $this->assert->equals($this->store->read('foo'), ['foo' => 'bar']);
    }

    function onlyAcceptsStringKeys() {
        $this->try->tryTo(function () {
            $this->store->write('Foo', 12);
        });
        $this->try->thenTheException_ShouldBeThrown('Memory keys must be strings.');
    }

    function throwsException() {
        $this->try->tryTo(function () {
            $this->store->read('foo');
        });
        $this->try->thenA_ShouldBeThrown(NotFoundException::class);
        $this->try->thenTheException_ShouldBeThrown('Could not find [foo]');

        $this->try->tryTo(function () {
            $this->store->remove('bar');
        });
        $this->try->thenA_ShouldBeThrown(NotFoundException::class);
        $this->try->thenTheException_ShouldBeThrown('Could not find [bar]');
    }

    function removes() {
        $this->store->write('uno', 'one');
        $this->store->write('dos', 'two');
        $this->store->write('tres', 'three');

        $this->store->remove('two');
        $this->assert->size($this->store->keys(), 2);
        $this->assert->not($this->store->has('two'));
    }

    function listsKeys() {
        $this->store->write('uno', 'one');
        $this->store->write('dos', 'two');
        $this->store->write('tres', 'three');

        $keys = $this->store->keys();
        $this->assert->size($keys, 3);
        $this->assert->contains($keys, 'one');
        $this->assert->contains($keys, 'two');
        $this->assert->contains($keys, 'three');
    }

    function generatesKey() {
        $store = new MemoryStore(new CallbackKeyGenerator(function () {
            return 'bla';
        }));

        $store->write('FOO');
        $this->assert->isTrue($store->has('bla'));
        $this->assert->equals($store->read('bla'), 'FOO');
    }
}