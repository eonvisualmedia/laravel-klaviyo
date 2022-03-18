<?php

namespace EonVisualMedia\LaravelKlaviyo;

class Event
{
    public function __construct(protected string $name, protected array $properties = [])
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}
