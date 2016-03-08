<?php
namespace spec\watoki\stores;

use rtens\scrut\fixtures\FilesFixture;
use watoki\stores\stores\FlatFileStore;
use watoki\stores\keys\KeyGenerator;
use watoki\stores\keys\KeyGeneratorFactory;
use watoki\stores\Store;

/**
 * Store strings in a file without any serialization.
 *
 * @property FilesFixture files <-
 */
class FlatFileStoreSpec extends StoreSpec {

    /**
     * @return Store
     */
    protected function createStore() {
        return new FlatFileStore($this->files->fullPath(), KeyGeneratorFactory::getDefault());
    }

    protected function createStoreWithKeyGenerator(KeyGenerator $generator) {
        return new FlatFileStore($this->files->fullPath(), $generator);
    }

    function itWritesIntoFiles() {
        $this->store->write('FOO!', 'foo');
        $this->files->thenThereShouldBeAFile_Containing('foo', 'FOO!');
    }

    function itOnlyAcceptsStrings() {
        $this->try->tryTo(function () {
            $this->store->write(['not' => 'a string']);
        });
        $this->try->thenTheException_ShouldBeThrown('Only strings can be stored in flat files.');
    }

    function itOnlyAcceptsStringKeys() {
        $this->try->tryTo(function () {
            $this->store->write('Foo', 12);
        });
        $this->try->thenTheException_ShouldBeThrown('Keys of flat files must be strings.');
    }

    function itCreatesFolders() {
        $this->store->write('FOO', 'foo/bar/baz');
        $this->files->thenThereShouldBeAFolder('foo');
        $this->files->thenThereShouldBeAFolder('foo/bar');
        $this->files->thenThereShouldBeAFile('foo/bar/baz');
    }

    function itReadsFromFiles() {
        $this->files->givenTheFile_Containing('foo/bar', 'FOO');
        $this->assert->equals($this->store->read('foo/bar'), 'FOO');
    }

    function itUsesFileNamesAsKeys() {
        $this->files->givenTheFile_Containing('foo/bar', 'FOO');

        $this->assert->isTrue($this->store->has('foo/bar'));
        $this->assert->not()->isTrue($this->store->has('foo'));
        $this->assert->not()->isTrue($this->store->has('baz'));
    }

    function itDeletesFiles() {
        $this->files->givenTheFile_Containing('foo/bar', 'FOO');
        $this->store->remove('foo/bar');

        $this->files->thenThereShouldBeAFolder('foo');
        $this->files->thenThereShouldBeNoFile('foo/bar');
    }

    function itListsFiles() {
        $this->files->givenTheFile_Containing('foo/bar', 'BAR');
        $this->files->givenTheFile_Containing('foo/baz', 'BAZ');
        $this->files->givenTheFile_Containing('foo/foo/bar', 'FOO BAR');
        $this->files->givenTheFile_Containing('bar/foo', 'FOO');
        $this->files->givenTheFile_Containing('baz', 'BAZ');
        $this->files->givenTheFolder('not');

        $keys = $this->store->keys();
        $this->assert->size($keys, 5);
        $this->assert->contains($keys, 'baz');
        $this->assert->contains($keys, 'bar/foo');
        $this->assert->contains($keys, 'foo/bar');
        $this->assert->contains($keys, 'foo/baz');
        $this->assert->contains($keys, 'foo/foo/bar');
    }
}