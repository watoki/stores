<?php
namespace spec\watoki\stores;

use spec\watoki\stores\lib\TestEntity;
use watoki\scrut\Specification;
use watoki\stores\memory\MemoryStore;

class MemoryStoreTest extends Specification {

    public function testGetKey() {
        $entity = new lib\TestEntity(true, 42, 1.6, "Hi", new \DateTime());
        $this->store->create($entity, 'myKey');
        $this->assertEquals('myKey', $this->store->getKey($entity));
    }

    function testCreate() {
        $entity1 = new lib\TestEntity(true, 42, 1.6, "Hi", new \DateTime());
        $this->store->create($entity1);
        $entity2 = new lib\TestEntity(true, 42, 1.6, "Hi", new \DateTime());
        $this->store->create($entity2);

        $this->assertSame($entity1, $this->store->read($this->store->getKey($entity1)));
        $this->assertSame($entity2, $this->store->read($this->store->getKey($entity2)));
        $this->assertNotEquals($this->store->getKey($entity1), $this->store->getKey($entity2));
    }

    function testReadWrongId() {
        $this->store->create(new lib\TestEntity(true, 42, 1.6, "Hi", new \DateTime()));

        try {
            $this->store->read(12);
            $this->fail('No Exception thrown');
        } catch (\Exception $e) {
            $this->assertEquals("Entity with ID [12] does not exist.", $e->getMessage());
        }
    }

    function testDelete() {
        $entity = new lib\TestEntity(true, 42, 1.6, "Hi", new \DateTime());
        $this->store->create($entity);
        $this->store->delete($entity);
        $this->assertEmpty($this->store->keys());

    }

    /** @var MemoryStore */
    private $store;

    protected function setUp() {
        parent::setUp();
        $this->store = $this->factory->getInstance(MemoryStore::$CLASS, array('entityClass' => TestEntity::$CLASS));
    }

} 