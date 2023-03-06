<?php

namespace App\Twig\Components;

use App\Document\Conversation;
use App\Document\Message;
use App\Form\Model\MessageInput;
use App\Form\Type\MessageInputType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('chatbox')]
final class ChatboxComponent
{
    public Conversation $conversation;

    public function __construct(
        private readonly DocumentManager      $documentManager,
        private readonly FormFactoryInterface $formFactory,
    )
    {

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

    public function getForm(): FormView
    {
        $form = $this->formFactory->create(MessageInputType::class);
        if (!$this->conversation->isNew()) {
            $form->setData(new MessageInput(id: $this->conversation->getId()));
        }

        return $form->createView();
    }
}
