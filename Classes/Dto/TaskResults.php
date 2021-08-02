<?php


namespace Flowpack\Prunner\Dto;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
class TaskResults implements \IteratorAggregate
{
    protected array $taskResults;

    public function fromJsonArray(array $in): self
    {
        $converted = [];
        foreach ($in as $el) {
            $converted[] = TaskResult::fromJsonArray($el);
        }


        $taskResults = new self();
        $taskResults->taskResults = $converted;
        return $taskResults;
    }

    /**
     * @return \Iterator<TaskResult>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->taskResults);
    }
}