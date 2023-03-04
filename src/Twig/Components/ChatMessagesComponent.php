<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('chat_messages')]
final class ChatMessagesComponent
{
    public array $messages;

    public function mount(
        array $messages,
    ): void {
        $this->messages = array_filter($messages, fn ($message) => isset($message['timestamp']));
    }
}
