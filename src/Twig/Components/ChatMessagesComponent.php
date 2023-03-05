<?php

namespace App\Twig\Components;

use App\Document\Message;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('chat_messages')]
final class ChatMessagesComponent
{
    public array $messages;

    public function mount(
        array $messages,
    ): void {
        $this->messages = array_filter($messages, fn (Message $message) => $message->getRole() !== 'system');
    }
}
