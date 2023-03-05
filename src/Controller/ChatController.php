<?php

namespace App\Controller;

use App\Document\Conversation;
use App\Document\Message;
use Doctrine\ODM\MongoDB\DocumentManager;
use OpenAI\Client;
use Psr\Clock\ClockInterface;
use Symfony\Bridge\Twig\Attribute\Template;
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
    )
    {
    }

    #[Route('/', methods: 'GET', name: 'index')]
    #[Route('/conversation/{id}', methods: 'GET', name: 'conversation')]
    #[Template('index.html.twig')]
    public function indexAction(string $id = null): array
    {
        $conversation = $this->getConversation($id);
        $lastConversations = $this->documentManager->getRepository(Conversation::class)
            ->findBy([], ['createdAt' => 'DESC'], 20);

        return [
            'conversation' => $conversation,
            'lastConversations' => $lastConversations,
        ];
    }

    #[Route('/', methods: 'POST', name: 'submit')]
    public function submitAction(Request $request): Response
    {
        // Gets the conversation from the session
        $conversation = $this->getConversation($request->request->get('id'));

        // Adds the user's message to the conversation
        $conversation->addMessage('user', $request->request->get('message'), $this->clock->now());

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
}
