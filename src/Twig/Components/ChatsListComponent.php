<?php

namespace App\Twig\Components;

use App\Document\Conversation;
use App\MongoDB\Aggregation\Stage\Search;
use Doctrine\ODM\MongoDB\DocumentManager;
use MongoDB\BSON\Regex;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('chats_list')]
final class ChatsListComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?string $query = null;

    public function __construct(
        private readonly DocumentManager $documentManager,
        private readonly bool $useAtlasSearch = false,
    ) {
    }

    /**
     * @return array{title: string, createdAt: \DateTimeImmutable, id: string}[]
     * @todo project only the fields we need, not the whole document with all messages
     *       also use the atlas search for fuzzy search https://www.mongodb.com/docs/atlas/atlas-search/text/#fuzzy-examples
     */
    public function getConversations(): iterable
    {
        if (strlen($this->query) < 2 || !$this->useAtlasSearch) {
            return $this->documentManager->getRepository(Conversation::class)
                ->createQueryBuilder()
                ->field('title')->equals(new Regex($this->query ?? '', 'i'))
                ->sort('createdAt', 'desc')
                ->limit(20)
                ->getQuery()
                ->execute();
        }

        // Use Atlas Search for fuzzy search
        // The operator is not supported by the ODM, so we use the aggregation builder
        $aggBuilder = $this->documentManager->createAggregationBuilder(Conversation::class);
        $aggBuilder->hydrate(Conversation::class);
        $search = new Search($aggBuilder, $this->query, 'title');
        $search->index('autocomplete');
        $aggBuilder
            ->addStage($search)
            ->sort('createdAt', 'desc')
            ->project()
                ->field('id')->expression('$_id')
                ->includeFields(['title', 'createdAt'])
            ->limit(20);

        return $aggBuilder->getAggregation();
    }
}
