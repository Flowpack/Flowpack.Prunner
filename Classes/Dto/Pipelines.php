<?php


namespace Flowpack\Prunner\Dto;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
class Pipelines implements \IteratorAggregate
{
    protected array $pipelines;

    public static function fromJsonArray(array $in): self
    {
        $converted = [];
        foreach ($in as $el) {
            $converted[] = Pipeline::fromJsonArray($el);
        }

        $pipelines = new self();
        $pipelines->pipelines = $converted;
        return $pipelines;
    }

    /**
     * @return Pipeline[]
     */
    public function getArray(): array
    {
        return $this->pipelines;
    }

    /**
     * @return \Iterator<Pipeline>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->pipelines);
    }
}
