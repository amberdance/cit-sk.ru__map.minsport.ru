<?php

namespace Citsk\Interfaces;

interface Controllerable
{
    /**
     * @return void
     */
    public function initializeController(): void;

    /**
     * @return array|null
     */
    public function callRequestedMethod(): ?array;

}
