<?php
namespace watoki\stores\transforming;

class TransformerRegistry {

    /** @var Transformer[] */
    private $transformers = [];

    public function add(Transformer $transformer) {
        $this->transformers[] = $transformer;
    }

    public function insert(Transformer $transformer) {
        array_unshift($this->transformers, $transformer);
    }

    public function toTransform($value) {
        foreach ($this->transformers as $transformer) {
            if ($transformer->canTransform($value)) {
                return $transformer;
            }
        }
        throw new \Exception("No matching transformer found");
    }

    public function toRevert($transformed) {
        foreach ($this->transformers as $transformer) {
            if ($transformer->hasTransformed($transformed)) {
                return $transformer;
            }
        }

        throw new \Exception("No matching transformer found");
    }
}