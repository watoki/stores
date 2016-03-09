<?php
namespace watoki\stores\transforming;

class TransformerRegistry {

    /** @var Transformer[] */
    private $transformers = [];

    /**
     * @param Transformer $transformer
     * @return $this
     */
    public function add(Transformer $transformer) {
        $this->transformers[] = $transformer;
        return $this;
    }

    /**
     * @param Transformer $transformer
     * @return $this
     */
    public function insert(Transformer $transformer) {
        array_unshift($this->transformers, $transformer);
        return $this;
    }

    /**
     * @param mixed $value
     * @return Transformer
     * @throws \Exception
     */
    public function toTransform($value) {
        foreach ($this->transformers as $transformer) {
            if ($transformer->canTransform($value)) {
                return $transformer;
            }
        }
        throw new \Exception("No matching transformer found");
    }

    /**
     * @param mixed $transformed
     * @return Transformer
     * @throws \Exception
     */
    public function toRevert($transformed) {
        foreach ($this->transformers as $transformer) {
            if ($transformer->hasTransformed($transformed)) {
                return $transformer;
            }
        }

        throw new \Exception("No matching transformer found");
    }
}