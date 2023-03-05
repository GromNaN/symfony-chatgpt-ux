<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('chats_list')]
final class ChatsListComponent
{
    public array $conversations;
}
