<?php
namespace watoki\stores\pdo;

use watoki\stores\pdo\serializers\ObjectSerializer;

abstract class Store extends \watoki\stores\Store {

    public static $CLASS = __CLASS__;

    /** @var Database */
    protected $db;

    function __construct(SerializerRepository $serializers, Database $db) {
        parent::__construct($serializers);
        $this->db = $db;
    }

    /**
     * @return SerializerRepository
     */
    protected function getSerializers() {
        return parent::getSerializers();
    }

    protected function createEntitySerializer() {
        return new ObjectSerializer($this->getEntityClass(), $this->getSerializers());
    }

    protected function getTableName() {
        $parts = explode('\\', $this->getEntityClass());
        return end($parts);
    }

    public function createTable() {
        $tableName = $this->getTableName();
        $definition = $this->getSerializers()->getSerializer($this->getEntityClass())->getDefinition();
        $this->db->execute("CREATE TABLE IF NOT EXISTS $tableName ($definition);");
    }

    public function dropTable() {
        $tableName = $this->getTableName();
        $this->db->execute("DROP TABLE $tableName;");
    }

    public function create($entity) {
        $columns = $this->serialize($entity);

        $quotedColumns = implode(', ', array_map(function ($key) {
            return '"' . $key . '"';
        }, array_keys($columns)));

        $preparedColumns = implode(', ', array_map(function ($key) {
            return ':' . $key;
        }, array_keys($columns)));

        $tableName = $this->getTableName();
        $this->db->execute("INSERT INTO $tableName ($quotedColumns) VALUES ($preparedColumns)", $columns);
        $entity->id = $this->db->getLastInsertedId();
    }

    public function read($id) {
        return $this->readBy('id', $id);
    }

    public function update($entity) {
        $columns = $this->serialize($entity);

        $preparedColumns = implode(', ', array_map(function ($key) {
            return '"' . $key . '" = :' . $key;
        }, array_keys($columns)));

        $columns['id'] = $entity->id;

        $tableName = $this->getTableName();
        $this->db->execute("UPDATE $tableName SET $preparedColumns WHERE id = :id", $columns);
    }

    public function delete($entity) {
        $tableName = $this->getTableName();
        $this->db->execute("DELETE FROM $tableName WHERE id = ?", array($entity->id));
    }

    public function readBy($column, $value) {
        $tableName = $this->getTableName();
        return $this->inflate($this->db->readOne("SELECT * FROM $tableName WHERE \"$column\" = ? LIMIT 1", array($value)));
    }

    public function readAll() {
        $tableName = $this->getTableName();
        return $this->inflateAll($this->db->readAll("SELECT * FROM $tableName"));
    }

    public function readAllBy($column, $value) {
        $tableName = $this->getTableName();
        return $this->inflateAll($this->db->readAll("SELECT * FROM $tableName WHERE \"$column\" = ?", array($value)));
    }

}