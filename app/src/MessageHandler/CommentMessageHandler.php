<?php

namespace App\MessageHandler;

use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Service\SpamChecker\SpamCheckerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;

#[AsMessageHandler]
final class CommentMessageHandler
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CommentRepository      $commentRepository,
        private readonly SpamCheckerInterface   $akismetSpamChecker,
        private readonly MessageBusInterface    $bus,
        private readonly WorkflowInterface      $commentStateMachine,
        private readonly ?LoggerInterface       $logger = null,
    )
    {
    }

    public function __invoke(CommentMessage $message): void
    {

        $comment = $this->commentRepository->find($message->getId());

        if ($this->commentStateMachine->can($comment, 'accept')) {

            $score = $this->akismetSpamChecker->getSpamScore($comment, $message->getContext());

            $transition = match ($score) {
                2 => 'reject_spam',
                1 => 'might_be_spam',
                default => 'accept',
            };

            $this->commentStateMachine->apply($comment, $transition);

            $this->entityManager->flush();

            $this->bus->dispatch($message);

        } elseif ($this->commentStateMachine->can($comment, 'publish') || $this->commentStateMachine->can($comment, 'publish_ham')) {

            $this->commentStateMachine->apply(
                $comment,
                $this->commentStateMachine->can($comment, 'publish') ? 'publish' : 'publish_ham'
            );

            $this->entityManager->flush();

        } elseif ($this->logger) {

            $this->logger->debug('Dropping comment message', [
                'comment' => $comment->getId(),
                'score' => $comment->getState(),
            ]);
        }
    }
}
