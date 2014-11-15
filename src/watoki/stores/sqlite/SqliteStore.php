<?php
namespace watoki\stores\sqlite;

use watoki\collections\Set;
use watoki\stores\GeneralStore;

class SqliteStore extends GeneralStore {

    public static $CLASS = __CLASS__;

    /** @var Database */
    protected $db;

    /** @var \watoki\stores\sqlite\SqliteSerializer */
    private $serializer;

    /** @var string */
    private $tableName;

    /**
     * @param SqliteSerializer $serializer
     * @param string $tableName
     * @param Database $db <-
     */
    function __construct(SqliteSerializer $serializer, $tableName, Database $db) {
        parent::__construct($serializer);
        $this->serializer = $serializer;
        $this->tableName = $tableName;
        $this->db = $db;
    }

    /**
     * @return string
     */
    protected function getTableName() {
        return $this->tableName;
    }

    public function createTable(array $properties) {
        $definitions = $this->serializer->getDefinition($properties);

        if (!is_array($definitions)) {
            throw new \LogicException("Definition of entity serializer must be array");
        }

        $fields = array('id' => '"id" INTEGER PRIMARY KEY AUTOINCREMENT');
        foreach ($definitions as $property => $definition) {
            $propertyName = $property;
            if (strpos($property, '_') !== false) {
                $propertyName = substr($property, 0, strpos($property, '_'));
            }
            if (in_array($propertyName, $properties)) {
                $fields[$property] = '"' . $property . '" ' . $definition;
            }
        }

        $definitions = implode(', ', array_values($fields));
        $this->db->execute("CREATE TABLE IF NOT EXISTS {$this->getTableName()} ($definitions);");
    }

    public function createColumn($property, $default = null) {
        $definitions = $this->serializer->getDefinition();
        $definition = $definitions[$property];

        $quotedDefault = $this->db->quote($default);
        $this->db->execute("ALTER TABLE {$this->getTableName()} ADD COLUMN \"$property\" $definition DEFAULT $quotedDefault");
    }

    public function dropTable() {
        $this->db->execute("DROP TABLE {$this->getTableName()};");
    }

    protected function _create($entity, $id) {
        $columns = $this->serialize($entity);

        if ($id) {
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

        if (!$id) {
            $this->setKey($entity, $this->db->getLastInsertedId());
        }
    }

    protected function generateKey($entity) {
        return null;
    }

    protected function _read($id) {
        return $this->readBy('id', $id);
    }

    protected function _update($entity) {
        $columns = $this->serialize($entity);

        $preparedColumns = implode(', ', array_map(function ($key) {
            return '"' . $key . '" = :' . $key;
        }, array_keys($columns)));

        $columns['id'] = $this->getKey($entity);

        $tableName = $this->getTableName();
        $this->db->execute("UPDATE $tableName SET $preparedColumns WHERE id = :id", $columns);
    }

    protected function _delete($key) {
        $this->db->execute("DELETE FROM {$this->getTableName()} WHERE id = ?", array($key));
    }

    public function keys() {
        return array_map(function ($k) {
            return $k['id'];
        }, $this->db->readAll("SELECT \"id\" FROM {$this->getTableName()};"));
    }

    public function readBy($column, $value) {
        return $this->inflate(
            $this->db->readOne("SELECT * FROM {$this->getTableName()} WHERE \"$column\" = ? LIMIT 1", array($value)));
    }

    public function readAll() {
        return $this->inflateAll(
            $this->db->readAll("SELECT * FROM {$this->getTableName()}"));
    }

    public function readAllBy($column, $value) {
        return $this->inflateAll(
            $this->db->readAll("SELECT * FROM {$this->getTableName()} WHERE \"$column\" = ?", array($value)));
    }

    protected function inflateAll($rows, $collection = null) {
        $entities = $collection ? : new Set();
        foreach ($rows as $row) {
            $entities[] = $this->inflate($row);
        }
        return $entities;
    }

}