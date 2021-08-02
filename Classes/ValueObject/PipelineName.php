<?php

namespace Flowpack\Prunner\ValueObject;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
class PipelineName
{

    private string $name;

    private function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function create(string $name)
    {
        return new self($name);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}