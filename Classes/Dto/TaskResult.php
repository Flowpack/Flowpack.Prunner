<?php

namespace Flowpack\Prunner\Dto;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
class TaskResult
{

    private string $name;
    private string $status;
    private ?\DateTimeImmutable $start;
    private ?\DateTimeImmutable $end;
    private bool $skipped;
    private int $exitCode;
    private bool $errored;
    private ?string $error;

    public static function fromJsonArray(array $in): self
    {
        $taskResult = new static();
        $taskResult->name = $in['name'];
        $taskResult->status = $in['status'];
        $taskResult->start = isset($in['start']) ? \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC3339, $in['start']) : null;
        $taskResult->end = isset($in['end']) ? \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC3339, $in['end']) : null;
        $taskResult->skipped = $in['skipped'];
        $taskResult->exitCode = $in['exitCode'];
        $taskResult->errored = $in['errored'];
        $taskResult->error = $in['error'];

        return $taskResult;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getStart(): ?\DateTimeImmutable
    {
        return $this->start;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getEnd(): ?\DateTimeImmutable
    {
        return $this->end;
    }

    /**
     * @return bool
     */
    public function isSkipped(): bool
    {
        return $this->skipped;
    }

    /**
     * @return int
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * @return bool
     */
    public function isErrored(): bool
    {
        return $this->errored;
    }

    /**
     * @return string
     */
    public function getError(): ?string
    {
        return $this->error;
    }

}