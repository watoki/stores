<?php
namespace watoki\stores\keys;

interface KeyGenerator {

    /**
     * @return string
     */
    public function generate();
}