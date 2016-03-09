<?php
namespace watoki\stores\stores;

use watoki\reflect\Type;
use watoki\stores\exceptions\NotFoundException;
use watoki\stores\keyGenerating\KeyGenerator;
use watoki\stores\serializing\Serializer;
use watoki\stores\serializing\SerializerRepository;
use watoki\stores\transforming\TransformerRegistry;
use watoki\stores\transforming\TransformerRegistryRepository;
use watoki\stores\transforming\transformers\TypedValue;
use watoki\stores\TypedStore;

class FileStore extends FlatFileStore implements TypedStore {

    /** @var Serializer */
    private $serializer;

    /** @var TransformerRegistry */
    private $transformers;

    /**
     * @param string $basePath
     * @param null|KeyGenerator $keyGenerator
     * @param null|TransformerRegistry $transformers
     * @param null|Serializer $serializer
     */
    public function __construct($basePath, KeyGenerator $keyGenerator = null, TransformerRegistry $transformers = null, Serializer $serializer = null) {
        parent::__construct($basePath, $keyGenerator);
        $this->transformers = $transformers ?: TransformerRegistryRepository::getDefault();
        $this->serializer = $serializer ?: SerializerRepository::getDefault();
    }

    /**
     * @param mixed $data
     * @param null|string $key
     * @return string The key
     * @throws \Exception
     */
    public function write($data, $key = null) {
        $transformed = $this->transformers->toTransform($data)->transform($data);
        $serialized = $this->serializer->serialize($transformed);

        return parent::write($serialized, $key);
    }

    /**
     * @param string $key
     * @return mixed
     * @throws \Exception|NotFoundException
     */
    public function read($key) {
        $serialized = parent::read($key);
        $inflated = $this->serializer->inflate($serialized);
        $reverted = $this->transformers->toRevert($inflated)->revert($inflated);

        return $reverted;
    }

    public function writeTyped(Type $type, $data, $key = null) {
        return $this->write(new TypedValue($data, $type), $key);
    }

    public function readTyped(Type $type, $key) {
        $serialized = parent::read($key);
        $inflated = new TypedValue($this->serializer->inflate($serialized), $type);
        $reverted = $this->transformers->toRevert($inflated)->revert($inflated);

        return $reverted;
    }
}