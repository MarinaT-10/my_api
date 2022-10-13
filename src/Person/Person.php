<?php

namespace GeekBrains\LevelTwo\Person;

class Person
{
    private Name $name;
    private \DateTimeImmutable $registeredOn;

    /**
     * @param Name $name
     * @param \DateTimeImmutable $registeredOn
     */
    public function __construct(Name $name, \DateTimeImmutable $registeredOn)
    {
        $this->registeredOn = $registeredOn;
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->name . '(на сайте с ' . $this->registeredOn->format('Y-m-d') . ')';
    }
}