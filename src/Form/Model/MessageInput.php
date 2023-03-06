<?php

namespace App\Form\Model;

class MessageInput
{
    public function __construct(
        public ?string $id = null,
        public ?string $message = null,
    ) {
    }
}
