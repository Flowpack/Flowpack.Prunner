<?php

namespace Flowpack\Prunner\Dto;

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
final class TaskResults implements \IteratorAggregate, \Countable, ProtectedContextAwareInterface
{
    /**
     * @var TaskResult[]
     */
    protected array $taskResults;

    private function __construct(array $taskResults)
    {
        $this->taskResults = $taskResults;
    }


    public static function fromJsonArray(array $in): self
    {
        $converted = [];
        foreach ($in as $el) {
            $converted[] = TaskResult::fromJsonArray($el);
        }
        return new self($converted);
    }

    /**
     * return a new, immutable list containing only the tasks matching a certain prefix. Especially useful if you want to filter
     * the lists before rendering them in Fusion
     *
     * @param string $prefix
     * @return $this
     */
    public function filteredByPrefix(string $prefix): self
    {
        $filtered = [];
        foreach ($this->taskResults as $taskResult) {
            if (strpos($taskResult->getName(), $prefix) === 0) {
                $filtered[] = $taskResult;
            }
        }
        return new self($filtered);
    }

    /**
     * return a new, immutable list containing all the tasks EXCEPT $taskNamesToSkip. Especially useful if you want to filter
     * the lists before rendering them in Fusion
     *
     * @param string $prefix
     * @return $this
     */
    public function withoutTasks(string ...$taskNamesToSkip): self
    {
        $filtered = [];
        foreach ($this->taskResults as $taskResult) {
            if (!in_array($taskResult->getName(), $taskNamesToSkip)) {
                $filtered[] = $taskResult;
            }
        }
        return new self($filtered);
    }

    /**
     * an aggregated status string - useful for rendering a status in a UI:
     * - If one of the elements is ERROR, it is failed.
     * - If one of the elements is RUNNING, it is running.
     * - If one element is CANCELED, it is cancelled.
     * - If all elements are DONE, it is done.
     * - If all elements are WAITING, it is waiting.
     * - If all elements are SKIPPED, it is skipped.
     * - otherwise, UNKNOWN
     *
     * @return string
     */
    public function getAggregatedStatus(): string
    {
        foreach ($this->taskResults as $taskResult) {
            if ($taskResult->getStatus() === TaskResult::STATUS_ERROR) {
                return TaskResult::STATUS_ERROR;
            }
        }

        foreach ($this->taskResults as $taskResult) {
            if ($taskResult->getStatus() === TaskResult::STATUS_RUNNING) {
                return TaskResult::STATUS_RUNNING;
            }
        }

        foreach ($this->taskResults as $taskResult) {
            if ($taskResult->getStatus() === TaskResult::STATUS_CANCELED) {
                return TaskResult::STATUS_CANCELED;
            }
        }

        $allDone = true;
        foreach ($this->taskResults as $taskResult) {
            if ($taskResult->getStatus() !== TaskResult::STATUS_DONE) {
                $allDone = false;
            }
        }
        if ($allDone) {
            return TaskResult::STATUS_DONE;
        }

        foreach ($this->taskResults as $taskResult) {
            if ($taskResult->getStatus() === TaskResult::STATUS_WAITING) {
                return TaskResult::STATUS_WAITING;
            }
        }

        $allSkipped = true;
        foreach ($this->taskResults as $taskResult) {
            if ($taskResult->getStatus() !== TaskResult::STATUS_SKIPPED) {
                $allSkipped = false;
            }
        }
        if ($allSkipped) {
            return TaskResult::STATUS_SKIPPED;
        }

        return 'unknown';
    }

    public function get(string $taskName): ?TaskResult
    {
        foreach ($this->taskResults as $taskResult) {
            if ($taskResult->getName() === $taskName) {
                return $taskResult;
            }
        }
        return null;

    }

    /**
     * @return TaskResult[]
     */
    public function getArray(): array  {
        return $this->taskResults;
    }

    /**
     * @return \Iterator<TaskResult>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->taskResults);
    }

    public function count()
    {
        return count($this->taskResults);
    }

    public function allowsCallOfMethod($methodName)
    {
        return true;
    }

}
