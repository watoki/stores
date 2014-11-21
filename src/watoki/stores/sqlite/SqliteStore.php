<?php
namespace watoki\stores\sqlite;

use watoki\collections\Set;
use watoki\reflect\type\ArrayType;
use watoki\reflect\type\BooleanType;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\FloatType;
use watoki\reflect\type\IdentifierObjectType;
use watoki\reflect\type\IdentifierType;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\NullableType;
use watoki\reflect\type\StringType;
use watoki\reflect\Type;
use watoki\stores\common\factories\ClassSerializerFactory;
use watoki\stores\common\factories\SimpleSerializerFactory;
use watoki\stores\common\factories\StaticSerializerFactory;
use watoki\stores\common\Reflector;
use watoki\stores\exception\EntityNotFoundException;
use watoki\stores\GeneralStore;
use watoki\stores\SerializerRegistry;
use watoki\stores\sqlite\serializers\ArraySerializer;
use watoki\stores\sqlite\serializers\BooleanSerializer;
use watoki\stores\sqlite\serializers\CallbackSqliteSerializer;
use watoki\stores\sqlite\serializers\CompositeSerializer;
use watoki\stores\sqlite\serializers\DateTimeSerializer;
use watoki\stores\sqlite\serializers\FloatSerializer;
use watoki\stores\sqlite\serializers\IntegerSerializer;
use watoki\stores\sqlite\serializers\NullableSerializer;
use watoki\stores\sqlite\serializers\StringSerializer;

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
     * @param string $class
     * @param Database $database
     * @param null|\watoki\stores\SerializerRegistry $registry
     * @return SqliteStore
     */
    public static function forClass($class, Database $database, SerializerRegistry $registry = null) {
        $registry = self::registerDefaultSerializers($registry ?: new SerializerRegistry());

        $reflector = new Reflector($class, $registry);
        $serializer = $reflector->create(CompositeSerializer::$CLASS);

        $classParts = explode('\\', $class);

        return new SqliteStore($serializer, end($classParts), $database);
    }

    /**
     * @param SerializerRegistry $registry
     * @return SerializerRegistry
     */
    public static function registerDefaultSerializers(SerializerRegistry $registry) {
        $registry->add(new StaticSerializerFactory(array(
            BooleanType::$CLASS => new BooleanSerializer(),
            FloatType::$CLASS => new FloatSerializer(),
            IntegerType::$CLASS => new IntegerSerializer(),
            StringType::$CLASS => new StringSerializer()
        )));

        $registry->add(new ClassSerializerFactory('DateTime', new DateTimeSerializer()));

        $registry->add(new SimpleSerializerFactory(NullableType::$CLASS,
            function (NullableType $type) use ($registry) {
                return new NullableSerializer($registry->get($type->getType()));
            }
        ));
        $registry->add(new SimpleSerializerFactory(ArrayType::$CLASS,
            function (ArrayType $type) use ($registry) {
                return new ArraySerializer($registry->get($type->getItemType()));
            }
        ));
        $registry->add(new SimpleSerializerFactory(ClassType::$CLASS,
            function (ClassType $type) use ($registry) {
                $reflector = new Reflector($type->getClass(), $registry);
                return $reflector->create(CompositeSerializer::$CLASS);
            }));
        $registry->add(new SimpleSerializerFactory(IdentifierObjectType::$CLASS,
            function (IdentifierObjectType $type) use ($registry) {
                return new CallbackSqliteSerializer(
                    function ($object) {
                        return (string)$object;
                    },
                    function ($serialized) use ($type) {
                        return $type->inflate($serialized);
                    },
                    'TEXT DEFAULT NULL'
                );
            }));
        $registry->add(new SimpleSerializerFactory(IdentifierType::$CLASS,
            function (IdentifierType $type) use ($registry) {
                return $registry->get($type->getPrimitive());
            }));

        return $registry;
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

        if (!is_null($default)) {
            $definition .= ' DEFAULT ' . $this->db->quote($default);
        }
        $this->db->execute("ALTER TABLE {$this->getTableName()} ADD COLUMN \"$property\" $definition");
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
        return $this->readQuery("SELECT * FROM {$this->getTableName()} WHERE \"$column\" = ? LIMIT 1", array($value));
    }

    public function readQuery($sql, $variables = array()) {
        try {
            return $this->inflate($this->db->readOne($sql, $variables));
        } catch (\PDOException $e) {
            throw new EntityNotFoundException("Entity not found", 0, $e);
        }
    }

    public function readAll() {
        return $this->readAllQuery("SELECT * FROM {$this->getTableName()}");
    }

    public function readAllBy($column, $value) {
        return $this->readAllQuery("SELECT * FROM {$this->getTableName()} WHERE \"$column\" = ?", array($value));
    }

    public function readAllQuery($sql, $variables = array()) {
        return $this->inflateAll($this->db->readAll($sql, $variables));
    }

    protected function inflateAll($rows, $collection = null) {
        $entities = $collection ? : new Set();
        foreach ($rows as $row) {
            $entities[] = $this->inflate($row);
        }
        return $entities;
    }

}