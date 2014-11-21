<?php
namespace watoki\stores\sqlite\serializers;

class DateTimeImmutableSerializer extends ColumnSerializer {

    /**
     * @param \DateTimeImmutable $inflated
     * @return string
     */
    public function serialize($inflated) {
        return $inflated->format('c');
    }

    /**
     * @param $serialized
     * @return \DateTimeImmutable
     */
    public function inflate($serialized) {
        return new \DateTimeImmutable($serialized);
    }

    protected function getColumnDefinition() {
        return 'TEXT(32)';
    }
}