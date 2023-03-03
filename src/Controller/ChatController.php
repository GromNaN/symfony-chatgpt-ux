<?php

namespace App\Controller;

use OpenAI\Client;
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
        private Client $openai,
    )
    {
    }

    #[Route('/', methods: 'GET', name: 'index')]
    #[Template('index.html.twig')]
    public function indexAction(Request $request): array
    {
        return [
            'messages' => $request->getSession()->get('messages'),
        ];
    }

    #[Route('/', methods: 'POST', name: 'submit')]
    public function submitAction(Request $request): Response
    {
        $messages = $request->getSession()->get('messages', [
            ['role' => 'system', 'content' => 'You are SymfonyGPT - A ChatGPT clone. Answer as concisely as possible.']
        ]);

        $messages[] = ['role' => 'user', 'content' => $request->request->get('message')];

        $response = $this->openai->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages
        ]);

        $messages[] = ['role' => 'assistant', 'content' => $response->choices[0]->message->content];
        $request->getSession()->set('messages', $messages);

        return new RedirectResponse('/');
    }
}