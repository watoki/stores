<?php
namespace spec\watoki\stores;

use watoki\scrut\Specification;
use watoki\stores\exception\NotFoundException;
use watoki\stores\file\raw\File;
use watoki\stores\file\raw\RawFileStore;

class RawFileStoreTest extends Specification {

    private $tmpDir;

    function testCreateRawFile() {
        $store = new RawFileStore($this->tmpDir);
        $store->create(new File('Some text'), 'here');

        $this->then_ShouldContain('here', 'Some text');
    }

    function testReadRawFile() {
        $this->givenAFile_Containing('foo', 'more foo');

        $store = new RawFileStore($this->tmpDir);
        $read = $store->read('foo');

        $this->assertEquals('more foo', $read->getContents());
    }

    function testNonExistingFile() {
        $store = new RawFileStore($this->tmpDir);
        try {
            $store->read('foo');
            $this->fail("Should have thrown an exception");
        } catch (NotFoundException $expected) {
        }
    }

    function testFoldersAreNotFiles() {
        $this->givenAFile_Containing('foo/bar', '');

        $store = new RawFileStore($this->tmpDir);
        try {
            $store->read('foo');
            $this->fail("Should have thrown an exception");
        } catch (NotFoundException $expected) {
        }
    }

    private function givenAFile_Containing($key, $content) {
        $file = $this->tmpDir . DIRECTORY_SEPARATOR . $key;
        @mkdir(dirname($file));
        file_put_contents($file, $content);
    }

    private function then_ShouldContain($key, $content) {
        $this->assertEquals(json_decode($content, true),
            json_decode(file_get_contents($this->tmpDir . DIRECTORY_SEPARATOR . $key), true));
    }

    protected function setUp() {
        parent::setUp();

        $this->tmpDir = __DIR__ . DIRECTORY_SEPARATOR . '_tmp_';
        $this->clear($this->tmpDir);
        @mkdir($this->tmpDir, 0777, true);
    }

    protected function tearDown() {
        $this->clear($this->tmpDir);
        parent::tearDown();
    }

    private function clear($dir) {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $this->clear($file);
            } else {
                @unlink($file);
            }
        }
        @rmdir($dir);
    }
}