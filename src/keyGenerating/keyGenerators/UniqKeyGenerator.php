<?php
namespace watoki\stores\keyGenerating\keyGenerators;

use watoki\stores\keyGenerating\KeyGenerator;

class UniqKeyGenerator implements KeyGenerator {

    /**
     * @return string
     */
    public function generate() {
        return uniqid('', true);
    }
}