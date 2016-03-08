<?php
namespace watoki\stores\exceptions;

class NotFoundException extends \Exception {

    /** @var string */
    private $key;

    /**
     * @param string $key
     */
    public function __construct($key) {
        parent::__construct("Could not find [$key]");
        $this->key = $key;
    }
}