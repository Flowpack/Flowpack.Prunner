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

    public static function fromJsonArray(array $in): self
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
     * @return Jobs all scheduled jobs not yet started
     */
    public function waiting(): Jobs
    {
        return $this->filter(function (Job $job) {
            return !$job->getStart();
        });
    }

    /**
     * Filter running jobs
     *
     * @return Jobs
     */
    public function running(): Jobs
    {
        return $this->filter(function (Job $job) {
            // running = started jobs which have not finished.
            return $job->getStart() !== null && !$job->getEnd();
        });
    }

    /**
     * @return Jobs successful, canceled and failed jobs
     */
    public function completed(): Jobs
    {
        return $this->filter(function (Job $job) {
            return $job->isCompleted();
        });
    }

    /**
     * @return Jobs successfully completed jobs
     */
    public function successful(): Jobs
    {
        return $this->filter(function (Job $job) {
            return $job->isCompleted() && !$job->isErrored() && !$job->isCanceled();
        });
    }

    /**
     * @return Jobs canceled jobs
     */
    public function canceled(): Jobs
    {
        return $this->filter(function (Job $job) {
            return $job->isCanceled();
        });
    }

    /**
     * @return Jobs failed jobs
     */
    public function errored(): Jobs
    {
        return $this->filter(function (Job $job) {
            return $job->isErrored();
        });
    }

    /**
     * @param callable $predicate function(Job $job): bool
     * @return Jobs all jobs where $predicate($job)
     */
    public function filter(callable $predicate): Jobs
    {
        $result = [];
        foreach ($this->jobs as $job) {
            if ($predicate($job)) {
                $result[] = $job;
            }
        }
        return new self($result);
    }

    /**
     * @return Job[]
     */
    public function getArray(): array
    {
        return $this->jobs;
    }

    /**
     * @return \Iterator<Job>|Job[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->jobs);
    }
}
