<?php

namespace Flowpack\Prunner\Dto;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
class Jobs implements \IteratorAggregate
{
    /**
     * @var Job[]
     */
    protected array $jobs;

    private function __construct(array $jobs)
    {
        $this->jobs = $jobs;
    }


    public function fromJsonArray(array $in): self
    {
        $converted = [];
        foreach ($in as $el) {
            $converted[] = Job::fromJsonArray($el);
        }
        return new self($converted);
    }

    public function forPipeline(string $pipeline): Jobs
    {
        $filteredJobs = [];
        foreach ($this->jobs as $job) {
            if ($job->getPipeline() === $pipeline) {
                $filteredJobs[] = $job;
            }
        }

        return new self($filteredJobs);
    }

    /**
     * @return \Iterator<Job>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->jobs);
    }
}