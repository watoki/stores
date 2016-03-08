<?php
namespace spec\watoki\stores;

use rtens\scrut\fixtures\FilesFixture;
use watoki\stores\keys\KeyGeneratorFactory;
use watoki\stores\serializing\JsonSerializer;
use watoki\stores\Store;
use watoki\stores\stores\FileStore;

/**
 * Stores any data into a file.
 *
 * @property FilesFixture files <-
 */
class FileStoreSpec extends StoreSpec {

    /**
     * @return Store
     */
    protected function createStore() {
        return new FileStore($this->files->fullPath(), KeyGeneratorFactory::getDefault(), new JsonSerializer());
    }

    function itSerializesTheData() {
        $this->store->write(['foo' => 'bar'], 'foo');
        $this->files->thenThereShouldBeAFile_Containing('foo', json_encode(['foo' => 'bar']));
    }

    function itInflatesTheData() {
        $this->files->givenTheFile_Containing('foo', json_encode(['foo' => 'bar']));
        $this->assert->equals($this->store->read('foo'), ['foo' => 'bar']);
    }
}