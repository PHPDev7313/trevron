<?php

namespace JDS\Handlers;

interface BuilderInterface
{
    public function build(): array;

    public function saveToFile(): array;

}