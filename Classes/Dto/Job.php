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

    public static function fromJsonArray(array $in): self
    {
        $job = new static();
        $job->id = $in['id'];
        $job->pipeline = $in['pipeline'];
        $job->taskResults = TaskResults::fromJsonArray($in['tasks']);;
        $job->completed = $in['completed'];
        $job->canceled = $in['canceled'];
        $job->errored = $in['errored'];
        $job->created = \DateTimeImmutable::createFromFormat(\DateTimeInterface::W3C, $in['created']);
        $job->start = isset($in['start']) ? \DateTimeImmutable::createFromFormat(\DateTimeInterface::W3C, $in['start']) : null;
        $job->end = isset($in['end']) ? \DateTimeImmutable::createFromFormat(\DateTimeInterface::W3C, $in['end']) : null;
        $job->lastError = $in['lastError'];
        $job->variables = $in['variables'] ?? [];
        $job->user = $in['user'];

        return $job;
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