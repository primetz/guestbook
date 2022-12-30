<?php

namespace App\Tests\Service\SpamChecker\Akismet;

use App\Entity\Comment;
use App\Service\SpamChecker\Akismet\AkismetSpamChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AkismetSpamCheckerTest extends TestCase
{

    public function testSpamScoreWithInvalidRequest(): void
    {
        $comment = new Comment();
        $comment->setCreatedAtValue();
        $context = [];

        $client = new MockHttpClient([new MockResponse('invalid', ['response_headers' => ['x-akismet-debug-help: Invalid key']])]);

        $spamChecker = new AkismetSpamChecker($client, 'test', 'test');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to check for spam: invalid (Invalid key).');

        $spamChecker->getSpamScore($comment, $context);
    }

    /**
     * @dataProvider provideComments
     */
    public function testSpamScore(
        int $expectedScore,
        ResponseInterface $response,
        Comment $comment,
        array $context,
    ): void
    {
        $client = new MockHttpClient([$response]);

        $spamChecker = new AkismetSpamChecker($client, 'test', 'test');

        $score = $spamChecker->getSpamScore($comment, $context);

        $this->assertSame($expectedScore, $score);
    }

    public static function provideComments(): iterable
    {
        $comment = new Comment();
        $comment->setCreatedAtValue();
        $context = [];

        $response = new MockResponse('', ['response_headers' => ['x-akismet-pro-tip: discard']]);
        yield 'blatant_spam' => [2, $response, $comment, $context];

        $response = new MockResponse('true');
        yield 'spam' => [1, $response, $comment, $context];

        $response = new MockResponse('false');
        yield 'ham' => [0, $response, $comment, $context];
    }
}
