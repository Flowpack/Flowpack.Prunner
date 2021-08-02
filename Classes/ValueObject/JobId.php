<?php

namespace Flowpack\Prunner\ValueObject;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
class JobId
{

    private string $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function create(string $id)
    {
        return new self($id);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}