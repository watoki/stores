<?php
namespace watoki\stores\file;

use watoki\reflect\Type;
use watoki\reflect\type\ArrayType;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\NullableType;
use watoki\reflect\type\PrimitiveType;
use watoki\reflect\TypeFactory;
use watoki\stores\common\factories\ClassSerializerFactory;
use watoki\stores\common\factories\SimpleSerializerFactory;
use watoki\stores\common\GenericSerializer;
use watoki\stores\common\NoneSerializer;
use watoki\stores\common\Reflector;
use watoki\stores\exception\NotFoundException;
use watoki\stores\file\serializers\ArraySerializer;
use watoki\stores\file\serializers\DateTimeSerializer;
use watoki\stores\file\serializers\JsonSerializer;
use watoki\stores\file\serializers\NullableSerializer;
use watoki\stores\GeneralStore;
use watoki\stores\Serializer;
use watoki\stores\SerializerRegistry;

class FileStore extends GeneralStore {

    public static $CLASS = __CLASS__;

    private $root;

    /**
     * @param Serializer $serializer
     * @param $rootDirectory
     */
    public function __construct(Serializer $serializer, $rootDirectory) {
        parent::__construct($serializer);
        $this->root = rtrim($rootDirectory, '\\/');
    }

    /**
     * @param string $class
     * @param string $rootDirectory
     * @return FileStore
     */
    public static function forClass($class, $rootDirectory) {
        $registry = self::registerDefaultSerializers(new SerializerRegistry());

        $reflector = new Reflector($class, $registry, new TypeFactory());
        $serializer = $reflector->create(JsonSerializer::$CLASS);

        return new FileStore($serializer, $rootDirectory);
    }

    /**
     * @param SerializerRegistry $registry
     * @return SerializerRegistry
     */
    public static function registerDefaultSerializers(SerializerRegistry $registry) {
        $registry->add(new ClassSerializerFactory('DateTime', new DateTimeSerializer()));
        $registry->add(new SimpleSerializerFactory(NullableType::$CLASS,
            function (NullableType $type) use ($registry) {
                return new NullableSerializer($registry->get($type->getType()));
            }));
        $registry->add(new SimpleSerializerFactory(ArrayType::$CLASS,
            function (ArrayType $type) use ($registry) {
                return new ArraySerializer($registry->get($type->getItemType()));
            }));
        $registry->add(new SimpleSerializerFactory(ClassType::$CLASS,
            function (ClassType $type) use ($registry) {
                $reflector = new Reflector($type->getClass(), $registry, new TypeFactory());
                return $reflector->create(GenericSerializer::$CLASS);
            }));
        $registry->add(new SimpleSerializerFactory(PrimitiveType::$CLASS,
            function () {
                return new NoneSerializer();
            }));

        return $registry;
    }

    protected function _read($id) {
        if (!$this->hasKey($id)) {
            throw new NotFoundException("File [$id] does not exist.");
        }
        return $this->inflate(file_get_contents($this->fileName($id)));
    }

    protected function _create($entity, $id) {
        $file = $this->fileName($id);
        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        file_put_contents($file, $this->serialize($entity));
    }

    protected function _update($entity) {
        $this->_create($entity, $this->getKey($entity));
    }

    protected function _delete($key) {
        unlink($this->fileName($key));
    }

    public function keys() {
        $files = $this->files($this->root);
        sort($files);
        return $files;
    }

    private function files($in) {
        $files = array();
        foreach (glob($in . '/*') as $file) {
            if (is_dir($file)) {
                $files = array_merge($files, $this->files($file));
            } else {
                $files[] = substr($file, strlen($this->root) + 1);
            }
        }
        return $files;
    }

    public function hasKey($id) {
        $filename = $this->fileName($id);
        return file_exists($filename) && is_file($filename);
    }

    public function exists($id) {
        return $this->hasKey($id);
    }

    protected function fileName($id) {
        return $this->root . DIRECTORY_SEPARATOR . $id;
    }
}