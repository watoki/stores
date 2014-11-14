<?php
namespace spec\watoki\stores;

use watoki\scrut\Specification;
use watoki\stores\Store;

class StoreTest extends Specification {

    /** @var object */
    private $entity;

    /** @var StoreTest_TestStore */
    private $store;

    protected function setUp() {
        parent::setUp();
        $this->store = new StoreTest_TestStore();
        $this->entity = new \DateTime('2001-02-03');
    }

    function testSetKeyOnCreate() {
        $this->store->create($this->entity, 'some key');

        $this->assertEquals('some key', $this->store->getKey($this->entity));
        $this->assertSame($this->entity, $this->store->memory['some key']);
    }

    function testSetKeyOnRead() {
        $this->store->memory['my key'] = $this->entity;
        $read = $this->store->read('my key');

        $this->assertSame($this->entity, $read);
        $this->assertEquals('my key', $this->store->getKey($this->entity));
    }

    function testUnsetKeyOnDelete() {
        $this->store->create($this->entity, 'key');
        $this->store->delete('key');

        $this->assertArrayNotHasKey('key', $this->store->memory);
        $this->assertCannotGetKeyOfEntity();
    }

    function testGenerateKeyIfNotGiven() {
        $this->store->create($this->entity);
        $this->assertEquals('2001-02-03T00:00:00+00:00', $this->store->getKey($this->entity));
    }

    private function assertCannotGetKeyOfEntity() {
        try {
            $this->store->getKey($this->entity);
        } catch (\Exception $e) {
            $caught = $e;
        }
        if (!isset($caught)) {
            $this->fail("Should have thrown Exception");
        }
    }

}

class StoreTest_TestStore extends Store {

    public $memory = array();

    /**
     * @param object $entity
     * @return mixed
     * @throws \Exception
     */
    protected function serialize($entity) {
        return $entity;
    }

    /**
     * @param mixed $row
     * @return object
     * @throws \Exception
     */
    protected function inflate($row) {
        return $row;
    }

    /**
     * @param object $entity
     * @param string|int $key
     * @return void
     */
    protected function _create($entity, $key) {
        $this->memory[$key] = $entity;
    }

    /**
     * @param string|int $key
     * @return object Entity belonging given key
     * @throw EntityNotFoundException If no entity with given key exists
     */
    protected function _read($key) {
        return $this->memory[$key];
    }

    /**
     * @param object $entity
     * @return void
     */
    protected function _update($entity) {
    }

    /**
     * @param string|int $key
     * @return void
     */
    protected function _delete($key) {
        unset($this->memory[$key]);
    }

    /**
     * @return array|string[]|int[] All stored keys
     */
    public function keys() {
        return array_keys($this->memory);
    }

    /**
     * @param \DateTime $entity
     * @return string
     */
    protected function generateKey($entity) {
        return $entity->format('c');
    }
}