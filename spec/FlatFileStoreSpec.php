<?php
namespace spec\watoki\stores;

use rtens\scrut\Assert;
use rtens\scrut\fixtures\ExceptionFixture;
use rtens\scrut\fixtures\FilesFixture;
use watoki\stores\keys\CallbackKeyGenerator;
use watoki\stores\exceptions\NotFoundException;
use watoki\stores\file\FlatFileStore;

/**
 * Store strings in a file without any serialization.
 *
 * @property FlatFileStore store
 * @property Assert assert <-
 * @property FilesFixture files <-
 * @property ExceptionFixture try <-
 */
class FlatFileStoreSpec {

    function before() {
        $this->store = new FlatFileStore($this->files->fullPath());
    }

    function writes() {
        $this->store->write('FOO!', 'foo');
        $this->files->thenThereShouldBeAFile_Containing('foo', 'FOO!');
    }

    function onlyAcceptsStrings() {
        $this->try->tryTo(function () {
            $this->store->write(['not' => 'a string']);
        });
        $this->try->thenTheException_ShouldBeThrown('Only strings can be stored in flat files.');
    }

    function onlyAcceptsStringKeys() {
        $this->try->tryTo(function () {
            $this->store->write('Foo', 12);
        });
        $this->try->thenTheException_ShouldBeThrown('Keys of flat files must be strings.');
    }

    function createsFolders() {
        $this->store->write('FOO', 'foo/bar/baz');
        $this->files->thenThereShouldBeAFolder('foo');
        $this->files->thenThereShouldBeAFolder('foo/bar');
        $this->files->thenThereShouldBeAFile('foo/bar/baz');
    }

    function reads() {
        $this->files->givenTheFile_Containing('foo/bar', 'FOO');
        $this->assert->equals($this->store->read('foo/bar'), 'FOO');
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

    function hasKey() {
        $this->files->givenTheFile_Containing('foo/bar', 'FOO');

        $this->assert->isTrue($this->store->has('foo/bar'));
        $this->assert->not()->isTrue($this->store->has('foo'));
        $this->assert->not()->isTrue($this->store->has('baz'));
    }

    function removes() {
        $this->files->givenTheFile_Containing('foo/bar', 'FOO');
        $this->store->remove('foo/bar');

        $this->files->thenThereShouldBeAFolder('foo');
        $this->files->thenThereShouldBeNoFile('foo/bar');
    }

    function listsKeys() {
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

    function generatesKey() {
        $store = new FlatFileStore($this->files->fullPath(), new CallbackKeyGenerator(function () {
            return 'bla';
        }));

        $store->write('FOO');
        $this->assert->isTrue($store->has('bla'));
        $this->assert->equals($store->read('bla'), 'FOO');
    }
}