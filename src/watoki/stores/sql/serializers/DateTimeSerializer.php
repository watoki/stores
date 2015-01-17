<?php
namespace watoki\stores\sql\serializers;

class DateTimeSerializer extends ColumnSerializer {

    /**
     * @param \DateTime $inflated
     * @return string
     */
    public function serialize($inflated) {
        return $inflated->format('c');
    }

    /**
     * @param $serialized
     * @return \DateTime
     */
    public function inflate($serialized) {
        return new \DateTime($serialized);
    }

    protected function getColumnDefinition() {
        return 'VARCHAR(32)';
    }
}