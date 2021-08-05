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

    private function __construct(string $name, string $status, ?\DateTimeImmutable $start, ?\DateTimeImmutable $end, bool $skipped, int $exitCode, bool $errored, ?string $error)
    {
        $this->name = $name;
        $this->status = $status;
        $this->start = $start;
        $this->end = $end;
        $this->skipped = $skipped;
        $this->exitCode = $exitCode;
        $this->errored = $errored;
        $this->error = $error;
    }


    public static function fromJsonArray(array $in): self
    {
        return new self(
            $in['name'],
            $in['status'],
            isset($in['start']) ? \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC3339, $in['start']) : null,
            isset($in['end']) ? \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC3339, $in['end']) : null,
            $in['skipped'],
            $in['exitCode'],
            $in['errored'],
            $in['error'] ?? ''
        );
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