<?php
namespace watoki\stores\transforming\transformers;

abstract class ClassTransformer extends ObjectTransformer {

    /**
     * @return string
     */
    protected abstract function getClass();

    public function canTransform($value) {
        return parent::canTransform($value) && get_class($value) == $this->getClass();
    }

    public function hasTransformed($transformed) {
        return
            parent::hasTransformed($transformed)
            && in_array($transformed[self::TYPE_KEY], $this->mapper->getAliases($this->getClass()));
    }
}