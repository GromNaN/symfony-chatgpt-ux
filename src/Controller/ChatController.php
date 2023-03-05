<?php

namespace App\Controller;

use App\Document\Conversation;
use App\Document\Message;
use Doctrine\ODM\MongoDB\DocumentManager;
use OpenAI\Client;
use Psr\Clock\ClockInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class ChatController
{
    public function __construct(
        private readonly Client $openai,
        private readonly ClockInterface $clock,
        private readonly DocumentManager $documentManager,
        private readonly FormFactoryInterface $formFactory,
    )
    {
    }

    #[Route('/', methods: 'GET', name: 'index')]
    #[Route('/conversation/{id}', methods: 'GET', name: 'conversation')]
    #[Template('index.html.twig')]
    public function indexAction(string $id = null): array
    {
        $conversation = $this->getConversation($id);

        return [
            'form' => $this->getForm()->setData(['id' => $id])->createView(),
            'conversation' => $conversation,
        ];
    }

    #[Route('/', methods: 'POST', name: 'submit')]
    public function submitAction(Request $request): Response
    {
        $form = $this->getForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return new RedirectResponse('/');
        }
        /** @var array{id: string, message: string} $data */
        $data = $form->getData();

        // Gets the conversation from the session
        $conversation = $this->getConversation($data['id']);

        // Adds the user's message to the conversation
        $conversation->addMessage('user', $data['message'], $this->clock->now());

        // Generates a response from OpenAI
        $response = $this->openai->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $conversation->getMessages()->map(
                fn (Message $message) => ['role' => $message->getRole(), 'content' => $message->getContent()],
            )->toArray(),
        ]);

        // Adds the assistant's response to the conversation
        $conversation->addMessage('assistant', $response->choices[0]->message->content, $this->clock->now());

        // Generates a title for the conversation if it doesn't have one yet
        if ($conversation->isNew()) {
            $response = $this->openai->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => array_merge(
                    $conversation->getMessages()->map(
                        fn (Message $message) => ['role' => $message->getRole(), 'content' => $message->getContent()],
                    )->toArray(),
                    [['role' => 'user', 'content' => 'What is the topic of this conversation? Short answer that can be used to find it later.']]
                ),
            ]);
            $conversation->setTitle($response->choices[0]->message->content);
        }

        // Persists the conversation
        $this->documentManager->persist($conversation);
        $this->documentManager->flush();
        $request->getSession()->set('conversation_id', $conversation->getId());

        return new RedirectResponse('/conversation/'.$conversation->getId());
    }

    private function getConversation(string $id = null): Conversation
    {
        $conversation = null;
        if ($id) {
            $conversation = $this->documentManager->find(Conversation::class, $id);
        }
        if (!$conversation) {
            $conversation = new Conversation();
        }

        return $conversation;
    }

    private function getForm(): FormInterface
    {
        return $this->formFactory->createBuilder()
            ->add('id', HiddenType::class)
            ->add('message', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Write your message…',
                    'autocomplete' => 'off',
                    'aria-label' => 'message',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Send',
            ])
            ->getForm();
    }
}
