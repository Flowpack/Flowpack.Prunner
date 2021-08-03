<?php

namespace Flowpack\Prunner\Dto;

use Flowpack\Prunner\ValueObject\PipelineName;
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

    public function forPipeline(PipelineName $pipeline): Jobs
    {
        $filteredJobs = [];
        foreach ($this->jobs as $job) {
            if ($job->getPipeline() === $pipeline->getName()) {
                $filteredJobs[] = $job;
            }
        }

        return new self($filteredJobs);
    }

    /**
     * Filter running jobs
     *
     * @return Jobs
     */
    public function running(): Jobs
    {
        $filteredJobs = [];
        foreach ($this->jobs as $job) {
            // running = started jobs which have not finished.
            if ($job->getStart() !== null && !$job->getEnd()) {
                $filteredJobs[] = $job;
            }
        }

        return new self($filteredJobs);
    }

    /**
     * @return \Iterator<Job>|Job[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->jobs);
    }
}