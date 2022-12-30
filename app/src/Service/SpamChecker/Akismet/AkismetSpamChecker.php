<?php

namespace App\Service\SpamChecker\Akismet;

use App\Entity\Comment;
use App\Service\SpamChecker\SpamCheckerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AkismetSpamChecker implements SpamCheckerInterface
{
    private string $endpoint;

    private string $apiKey;

    public function __construct(
        private readonly HttpClientInterface          $httpClient,
        #[Autowire('%env(AKISMET_ENDPOINT)%')] string $endpoint,
        #[Autowire('%env(AKISMET_KEY)%')] string      $apiKey,
    )
    {
        $this->apiKey = $apiKey;

        $this->endpoint = $endpoint;
    }

    public function getSpamScore(Comment $comment, array $context): int
    {
        $response = $this->httpClient->request('POST', $this->endpoint, [
            'body' => array_merge($context, [
                'api_key' => $this->apiKey,
                'blog' => 'https://dev.guestbook.com/',
                'comment_type' => 'comment',
                'comment_author' => $comment->getAuthor(),
                'comment_author_email' => $comment->getEmail(),
                'comment_content' => $comment->getText(),
                'comment_date_gmt' => $comment->getCreatedAt()->format('c'),
                'blog_lang' => 'en',
                'blog_charset' => 'UTF-8',
                'is_test' => true,
            ]),
        ]);

        $headers = $response->getHeaders();
        if ('discard' === ($headers['x-akismet-pro-tip'][0] ?? '')) {
            return 2;
        }

        $content = $response->getContent();
        if (isset($headers['x-akismet-debug-help'][0])) {
            throw new \RuntimeException(
                sprintf('Unable to check for spam: %s (%s).', $content, $headers['x-akismet-debug-help'][0])
            );
        }

        return 'true' === $content ? 1 : 0;
    }
}
