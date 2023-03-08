<?php

namespace App\Twig\Components;

use App\Document\Conversation;
use App\Form\Model\MessageInput;
use App\Form\Type\MessageInputType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('chatbox')]
final class ChatboxComponent
{
    use ComponentWithFormTrait;

    public Conversation $conversation;

    public function __construct(
        private readonly DocumentManager $documentManager,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    public function mount(
        string $id = null,
    ): void {
        if ($id) {
            // @todo catch not found exception
            $this->conversation = $this->documentManager->find(Conversation::class, $id);
        } else {
            $this->conversation = new Conversation();
        }
    }

    protected function instantiateForm(): FormInterface
    {
        $form = $this->formFactory->create(MessageInputType::class);
        if (!$this->conversation->isNew()) {
            $form->setData(new MessageInput(id: $this->conversation->getId()));
        }

        return $form;
    }
}
