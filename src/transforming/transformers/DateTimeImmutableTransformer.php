<?php
namespace watoki\stores\transforming\transformers;

class DateTimeImmutableTransformer extends ClassTransformer {

    /**
     * @return string
     */
    protected function getClass() {
        return \DateTimeImmutable::class;
    }

    /**
     * @param \DateTimeImmutable $object
     * @return mixed
     */
    protected function transformObject($object) {
        return $object->format('c');
    }

    /**
     * @param mixed $transformed
     * @return \DateTimeImmutable
     */
    protected function revertObject($transformed, $type) {
        return new \DateTimeImmutable($transformed);
    }
}