<?php
namespace watoki\stores\stores;

use watoki\reflect\Type;
use watoki\reflect\type\UnknownType;
use watoki\stores\exceptions\NotFoundException;
use watoki\stores\serializing\Serializer;
use watoki\stores\serializing\SerializerRepository;
use watoki\stores\Store;
use watoki\stores\transforming\TransformerRegistry;
use watoki\stores\transforming\TransformerRegistryRepository;
use watoki\stores\transforming\transformers\TypedValue;

abstract class SerializedStore implements Store {

    /** @var Serializer */
    private $serializer;

    /** @var TransformerRegistry */
    private $transformers;

    /** @var Type */
    private $type;

    /**
     * @param null|Type $type
     * @param null|TransformerRegistry $transformers
     * @param null|Serializer $serializer
     */
    public function __construct(Type $type = null, TransformerRegistry $transformers = null, Serializer $serializer = null) {
        $this->type = $type ?: new UnknownType();
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
        $value = new TypedValue($data, $this->type);
        $transformed = $this->transformers->toTransform($value)->transform($value);
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
        $value = new TypedValue($inflated, $this->type);
        $reverted = $this->transformers->toRevert($value)->revert($value);

        return $reverted;
    }
}