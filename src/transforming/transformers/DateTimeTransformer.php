<?php
namespace watoki\stores\transforming\transformers;

class DateTimeTransformer extends ClassTransformer {

    /**
     * @return string
     */
    protected function getClass() {
        return \DateTime::class;
    }

    /**
     * @param \DateTime $object
     * @return mixed
     */
    protected function transformObject($object) {
        return $object->format('c');
    }

    /**
     * @param mixed $transformed
     * @return \DateTime
     */
    protected function revertObject($transformed, $type) {
        return new \DateTime($transformed);
    }
}