<?php
namespace watoki\stores\stores;

use watoki\reflect\Type;
use watoki\stores\exceptions\NotFoundException;
use watoki\stores\serializing\Serializer;
use watoki\stores\serializing\SerializerRepository;
use watoki\stores\transforming\TransformerRegistry;
use watoki\stores\transforming\TransformerRegistryRepository;
use watoki\stores\transforming\transformers\TypedValue;
use watoki\stores\TypedStore;

abstract class SerializedStore implements TypedStore {

    /** @var Serializer */
    private $serializer;

    /** @var TransformerRegistry */
    private $transformers;

    /**
     * @param null|TransformerRegistry $transformers
     * @param null|Serializer $serializer
     */
    public function __construct(TransformerRegistry $transformers = null, Serializer $serializer = null) {
        $this->transformers = $transformers ?: TransformerRegistryRepository::getDefaultTransformerRegistry();
        $this->serializer = $serializer ?: SerializerRepository::getDefault();
    }

    /**
     * @param string $serialized
     * @param null|mixed $key
     * @return void
     */
    protected abstract function writeSerialized($serialized, $key = null);

    /**
     * @param mixed $key
     * @return string
     */
    protected abstract function readSerialized($key);

    /**
     * @param mixed $data
     * @param null|string $key
     * @return string The key
     * @throws \Exception
     */
    public function write($data, $key = null) {
        $transformed = $this->transformers->toTransform($data)->transform($data);
        $serialized = $this->serializer->serialize($transformed);
        $this->writeSerialized($serialized, $key);

        return $key;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws \Exception|NotFoundException
     */
    public function read($key) {
        $serialized = $this->readSerialized($key);
        $inflated = $this->serializer->inflate($serialized);
        $reverted = $this->transformers->toRevert($inflated)->revert($inflated);

        return $reverted;
    }

    /**
     * @param Type $type
     * @param mixed $data
     * @param null $key
     * @return string
     */
    public function writeTyped(Type $type, $data, $key = null) {
        return $this->write(new TypedValue($data, $type), $key);
    }

    /**
     * @param Type $type
     * @param mixed $key
     * @return mixed
     * @throws NotFoundException
     * @throws \Exception
     */
    public function readTyped(Type $type, $key) {
        $serialized = $this->readSerialized($key);
        $inflated = new TypedValue($this->serializer->inflate($serialized), $type);
        $reverted = $this->transformers->toRevert($inflated)->revert($inflated);

        return $reverted;
    }
}