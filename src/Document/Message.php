<?php

namespace App\Document;

use DateTimeImmutable;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbeddedDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;

#[EmbeddedDocument]
class Message
{
    #[Field]
    private string $role;

    #[Field]
    private string $content;

    #[Field]
    private DateTimeImmutable $timestamp;

    public function __construct(string $role, string $content, DateTimeImmutable $timestamp)
    {
        $this->role = $role;
        $this->content = $content;
        $this->timestamp = $timestamp;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }
}
