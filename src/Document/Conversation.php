<?php

namespace App\Document;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;

#[Document(collection: 'conversations')]
class Conversation
{
    #[Id]
    private string $id;

    #[EmbedMany(targetDocument: Message::class)]
    private Collection $messages;

    #[Field]
    private ?string $title = null;

    #[Field]
    private DateTimeImmutable $createdAt;

    public function __construct(\DateTimeImmutable $createdAt = null)
    {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->messages = new ArrayCollection();
        $this->addMessage('system', 'You are SymfonyGPT - A ChatGPT clone. Answer as concisely as possible.', $this->createdAt);
    }

    public function isNew(): bool
    {
        return !isset($this->id);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(string $role, string $content, DateTimeImmutable $timestamp): void
    {
        $this->messages->add(new Message($role, $content, $timestamp));
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
