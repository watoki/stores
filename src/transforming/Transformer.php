<?php
namespace watoki\stores\transforming;

interface Transformer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function canTransform($value);

    /**
     * @param mixed $transformed
     * @return bool
     */
    public function hasTransformed($transformed);

    /**
     * @param mixed $value
     * @return mixed
     */
    public function transform($value);

    /**
     * @param mixed $transformed
     * @return mixed
     */
    public function revert($transformed);
}