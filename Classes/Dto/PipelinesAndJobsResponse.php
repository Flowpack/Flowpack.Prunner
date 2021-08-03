<?php

namespace Flowpack\Prunner\Dto;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
class PipelinesAndJobsResponse
{

    protected Pipelines $pipelines;
    protected Jobs $jobs;

    protected function __construct(Pipelines $pipelines, Jobs $jobs)
    {
        $this->pipelines = $pipelines;
        $this->jobs = $jobs;
    }

    public static function fromJsonArray(array $in): self
    {
        $pipelines = Pipelines::fromJsonArray($in['pipelines']);
        $jobs = Jobs::fromJsonArray($in['jobs']);

        return new self($pipelines, $jobs);
    }

    /**
     * @return Pipelines
     */
    public function getPipelines(): Pipelines
    {
        return $this->pipelines;
    }

    /**
     * @return Jobs
     */
    public function getJobs(): Jobs
    {
        return $this->jobs;
    }
}
