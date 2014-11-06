<?php
namespace watoki\stores\pdo;

use watoki\collections\Set;
use watoki\stores\pdo\serializers\ObjectSerializer;
use watoki\stores\Store;

class PdoStore extends Store {

    public static $CLASS = __CLASS__;

    /** @var Database */
    protected $db;

    /**
     * @param $entityClass
     * @param SerializerRepository $serializers <-
     * @param Database $db <-
     */
    function __construct($entityClass, SerializerRepository $serializers, Database $db) {
        parent::__construct($entityClass, $serializers);
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

    public function createTable($properties = null) {
        $tableName = $this->getTableName();
        $definition = $this->getSerializer()->getDefinition($properties);
        $this->db->execute("CREATE TABLE IF NOT EXISTS $tableName ($definition);");
    }

    public function createColumn($property, $default = null) {
        $definition = $this->getSerializer()->getPropertyDefinition($property);
        $quotedDefault = $this->db->quote($default);
        $this->db->execute("ALTER TABLE {$this->getTableName()} ADD COLUMN $definition DEFAULT $quotedDefault");
    }

    public function dropTable() {
        $tableName = $this->getTableName();
        $this->db->execute("DROP TABLE $tableName;");
    }

    public function create($entity, $id = null) {
        $columns = $this->serialize($entity, $id);

        if (!is_null($id)) {
            $columns['id'] = $id;
        }

        $quotedColumns = implode(', ', array_map(function ($key) {
            return '"' . $key . '"';
        }, array_keys($columns)));

        $preparedColumns = implode(', ', array_map(function ($key) {
            return ':' . $key;
        }, array_keys($columns)));

        $tableName = $this->getTableName();
        $this->db->execute("INSERT INTO $tableName ($quotedColumns) VALUES ($preparedColumns)", $columns);

        $this->setKey($entity, $id ? : $this->db->getLastInsertedId());
    }

    public function read($id) {
        return $this->readBy('id', $id);
    }

    public function update($entity) {
        $columns = $this->serialize($entity, $this->getKey($entity));

        $preparedColumns = implode(', ', array_map(function ($key) {
            return '"' . $key . '" = :' . $key;
        }, array_keys($columns)));

        $columns['id'] = $this->getKey($entity);

        $tableName = $this->getTableName();
        $this->db->execute("UPDATE $tableName SET $preparedColumns WHERE id = :id", $columns);
    }

    public function delete($entity) {
        $tableName = $this->getTableName();
        $this->db->execute("DELETE FROM $tableName WHERE id = ?", array($this->getKey($entity)));
    }

    public function keys() {
        $tableName = $this->getTableName();
        $keys = $this->db->readAll("SELECT \"id\" FROM $tableName;");
        return array_map(function ($k) {
            return $k['id'];
        }, $keys);
    }

    public function readBy($column, $value) {
        $tableName = $this->getTableName();
        return $this->inflateRow($this->db->readOne("SELECT * FROM $tableName WHERE \"$column\" = ? LIMIT 1", array($value)));
    }

    public function readAll() {
        $tableName = $this->getTableName();
        return $this->inflateAll($this->db->readAll("SELECT * FROM $tableName"));
    }

    public function readAllBy($column, $value) {
        $tableName = $this->getTableName();
        return $this->inflateAll($this->db->readAll("SELECT * FROM $tableName WHERE \"$column\" = ?", array($value)));
    }

    protected function inflateRow($row) {
        return $this->inflate($row, $row['id']);
    }

    protected function inflateAll($rows, $collection = null) {
        $entities = $collection ? : new Set();
        foreach ($rows as $row) {
            $entities[] = $this->inflate($row, $row['id']);
        }
        return $entities;
    }

    /**
     * @return ObjectSerializer
     */
    private function getSerializer() {
        return $this->getSerializers()->getSerializer($this->getEntityClass());
    }

}