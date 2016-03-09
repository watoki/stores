<?php
namespace spec\watoki\stores;

use rtens\scrut\fixtures\FilesFixture;
use watoki\reflect\type\ClassType;
use watoki\stores\keyGenerating\KeyGenerator;
use watoki\stores\Store;
use watoki\stores\stores\FileStore;
use watoki\stores\transforming\transformers\ObjectTransformer;

/**
 * Stores any data into a file.
 *
 * @property FileStore store
 * @property FilesFixture files <-
 */
class FileStoreSpec extends StoreSpec {

    /**
     * @return Store
     */
    protected function createStore() {
        return new FileStore($this->files->fullPath());
    }

    protected function createStoreWithKeyGenerator(KeyGenerator $generator) {
        return new FileStore($this->files->fullPath(), null, $generator);
    }

    function itSerializesTheData() {
        $this->store->write(['foo' => 'bar'], 'foo');
        $this->files->thenThereShouldBeAFile_Containing('foo', json_encode(['foo' => 'bar']));
    }

    function itInflatesTheData() {
        $this->files->givenTheFile_Containing('foo', json_encode(['foo' => 'bar']));
        $this->assert->equals($this->store->read('foo'), ['foo' => 'bar']);
    }

    function itUsesTheRegistry() {
        $data = new \DateTime('2011-12-13 14:15:16 UTC');
        $this->store->write($data, 'foo');

        $this->files->thenThereShouldBeAFile_Containing('foo', json_encode([
            ObjectTransformer::TYPE_KEY => \DateTime::class,
            ObjectTransformer::DATA_KEY => '2011-12-13T14:15:16+00:00'
        ]));
        $this->assert->equals($this->store->read('foo'), $data);
    }

    function itSerializesWithType() {
        $this->store = new FileStore($this->files->fullPath(), new ClassType(\DateTime::class));
        $data = new \DateTime('2011-12-13 14:15:16 UTC');

        $this->store->write($data, 'foo');
        $this->files->thenThereShouldBeAFile_Containing('foo', json_encode('2011-12-13T14:15:16+00:00'));
        $this->assert->equals($this->store->read('foo'), $data);
    }
}