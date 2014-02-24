<?php
namespace watoki\stores;

use watoki\stores\serializers\ObjectSerializer;
use watoki\collections\Set;

abstract class Store {

    public static $CLASS = __CLASS__;

    /** @var Database */
    protected $db;

    /** @var SerializerRepository */
    private $repo;

    function __construct(Database $db, SerializerRepository $repo) {
        $this->db = $db;
        $this->repo = $repo;

        $repo->setSerializer($this->getEntityClass(), new ObjectSerializer($this->getEntityClass(), $repo));
    }

    abstract protected function getEntityClass();

    protected function getTableName() {
        $parts = explode('\\', $this->getEntityClass());
        return end($parts);
    }

    public function createTable() {
        $tableName = $this->getTableName();
        $definition = $this->repo->getSerializer($this->getEntityClass())->getDefinition();
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

    protected function readBy($column, $value) {
        $tableName = $this->getTableName();
        return $this->inflate($this->db->readOne("SELECT * FROM $tableName WHERE \"$column\" = ?", array($value)));
    }

    protected function readAll() {
        $tableName = $this->getTableName();
        return $this->inflateAll($this->db->readAll("SELECT * FROM $tableName"));
    }

    protected function readAllBy($column, $value) {
        $tableName = $this->getTableName();
        return $this->inflateAll($this->db->readAll("SELECT * FROM $tableName WHERE \"$column\" = ?", array($value)));
    }

    private function serialize($entity) {
        return $this->repo->getSerializer($this->getEntityClass())->serialize($entity);
    }

    protected function inflate($row) {
        return $this->repo->getSerializer($this->getEntityClass())->inflate($row);
    }

    protected function inflateAll($rows, $collection = null) {
        $entities = $collection ? : new Set();
        foreach ($rows as $row) {
            $entities[] = $this->inflate($row);
        }
        return $entities;
    }

}