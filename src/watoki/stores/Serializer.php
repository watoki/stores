<?php
namespace watoki\stores;

interface Serializer {

    public function serialize($inflated);

    public function inflate($serialized);

    public function getDefinition();

} 