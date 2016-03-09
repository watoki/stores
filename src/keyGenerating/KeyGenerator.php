<?php
namespace watoki\stores\keyGenerating;

interface KeyGenerator {

    /**
     * @return string
     */
    public function generate();
}