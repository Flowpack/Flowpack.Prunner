<?php


namespace Flowpack\Prunner\Dto;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
class Job
{
    private string $id;
    /**
     * @var string Pipeline name
     */
    private string $pipeline;
    private TaskResults $taskResults;
    private bool $completed;
    private bool $canceled;
    private bool $errored;
    private \DateTimeImmutable $created;
    private ?\DateTimeImmutable $start;
    private ?\DateTimeImmutable $end;
    private ?string $lastError;
    private array $variables;
    private string $user;

    private function __construct(string $id, string $pipeline, TaskResults $taskResults, bool $completed, bool $canceled, bool $errored, \DateTimeImmutable $created, ?\DateTimeImmutable $start, ?\DateTimeImmutable $end, ?string $lastError, array $variables, string $user)
    {
        $this->id = $id;
        $this->pipeline = $pipeline;
        $this->taskResults = $taskResults;
        $this->completed = $completed;
        $this->canceled = $canceled;
        $this->errored = $errored;
        $this->created = $created;
        $this->start = $start;
        $this->end = $end;
        $this->lastError = $lastError;
        $this->variables = $variables;
        $this->user = $user;
    }


    public static function fromJsonArray(array $in): self
    {
        return new self(
            $in['id'],
            $in['pipeline'],
            TaskResults::fromJsonArray($in['tasks']),
            $in['completed'],
            $in['canceled'],
            $in['errored'],
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::W3C, $in['created']),
            isset($in['start']) ? \DateTimeImmutable::createFromFormat(\DateTimeInterface::W3C, $in['start']) : null,
            isset($in['end']) ? \DateTimeImmutable::createFromFormat(\DateTimeInterface::W3C, $in['end']) : null,
            $in['lastError'] ?? '',
            $in['variables'] ?? [],
            $in['user']
        );
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function getPipeline(): string
    {
        return $this->pipeline;
    }

    public function getTaskResults(): TaskResults
    {
        return $this->taskResults;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function isCanceled(): bool
    {
        return $this->canceled;
    }

    public function isErrored(): bool
    {
        return $this->errored;
    }

    public function getCreated(): \DateTimeImmutable
    {
        return $this->created;
    }

    public function getStart(): ?\DateTimeImmutable
    {
        return $this->start;
    }

    public function getEnd(): ?\DateTimeImmutable
    {
        return $this->end;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getUser(): string
    {
        return $this->user;
    }
}