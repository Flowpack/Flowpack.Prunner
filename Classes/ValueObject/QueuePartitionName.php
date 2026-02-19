<?php

declare(strict_types=1);

namespace Flowpack\Prunner\ValueObject;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
class QueuePartitionName
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

    public function getName(): string
    {
        return $this->name;
    }
}
