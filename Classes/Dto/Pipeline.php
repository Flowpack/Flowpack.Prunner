<?php


namespace Flowpack\Prunner\Dto;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
class Pipeline
{
    private string $pipeline;
    private bool $schedulable;
    private bool $running;

    public static function fromJsonArray(array $in): self
    {
        $pipeline = new static();
        $pipeline->pipeline = $in['pipeline'];
        $pipeline->schedulable = $in['schedulable'];
        $pipeline->running = $in['running'];

        return $pipeline;
    }

    public function getPipeline(): string
    {
        return $this->pipeline;
    }

    public function isSchedulable(): bool
    {
        return $this->schedulable;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

}