<?php
namespace watoki\stores\keys;

class CallbackKeyGenerator implements KeyGenerator {

    /** @var callable */
    private $callback;

    /**
     * @param callable $callback
     */
    public function __construct(callable $callback) {
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    public function generate() {
        return call_user_func($this->callback);
    }
}