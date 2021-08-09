<?php


namespace Flowpack\Prunner\Dto;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
class JobLogs
{
    private string $stderr;
    private string $stdout;

    private function __construct(string $stderr, string $stdout)
    {
        $this->stderr = $stderr;
        $this->stdout = $stdout;
    }

    public static function fromJsonArray(array $in): self
    {
        return new self($in['stderr'], $in['stdout']);
    }

    /**
     * @return string
     */
    public function getStderr(): string
    {
        return $this->stderr;
    }

    /**
     * @return string
     */
    public function getStdout(): string
    {
        return $this->stdout;
    }
}