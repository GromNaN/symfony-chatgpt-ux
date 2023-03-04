<?php

namespace App\Controller;

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
    )
    {
    }

    #[Route('/', methods: 'GET', name: 'index')]
    #[Template('index.html.twig')]
    public function indexAction(Request $request): array
    {
        if ($request->query->has('reset')) {
            $request->getSession()->remove('messages');
        }

        return [
            'messages' => $request->getSession()->get('messages', []),
        ];
    }

    #[Route('/', methods: 'POST', name: 'submit')]
    public function submitAction(Request $request): Response
    {
        $messages = $request->getSession()->get('messages', [
            ['role' => 'system', 'content' => 'You are SymfonyGPT - A ChatGPT clone. Answer as concisely as possible.']
        ]);

        $messages[] = [
            'role' => 'user',
            'content' => $request->request->get('message'),
            'timestamp' => $this->clock->now(),
        ];

        $response = $this->openai->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => array_map(fn ($message) => ['role' => $message['role'], 'content' => $message['content']], $messages),
        ]);

        $messages[] = [
            'role' => 'assistant',
            'content' => $response->choices[0]->message->content,
            'timestamp' => $this->clock->now(),
        ];
        $request->getSession()->set('messages', $messages);

        return new RedirectResponse('/');
    }
}
