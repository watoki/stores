<?php
namespace spec\watoki\stores;

use spec\watoki\stores\lib\TestEntity;
use watoki\scrut\Specification;
use watoki\stores\memory\SerializerRepository;
use watoki\stores\memory\Store;

class MemoryStoreTest extends Specification {

    function testCreate() {
        $entity1 = new lib\TestEntity(true, 42, 1.6, "Hi", new \DateTime());
        $this->store->create($entity1);
        $entity2 = new lib\TestEntity(true, 42, 1.6, "Hi", new \DateTime());
        $this->store->create($entity2);

        $this->assertSame($entity1, $this->store->read($entity1->id));
        $this->assertSame($entity2, $this->store->read($entity2->id));
        $this->assertNotEquals($entity1->id, $entity2->id);
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

    /** @var Store */
    private $store;

    protected function setUp() {
        parent::setUp();
        $this->store = new Store(TestEntity::$CLASS, new SerializerRepository());
    }

} 