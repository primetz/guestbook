<?php

namespace App\Notification;

use App\Entity\Comment;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;

class CommentReviewedNotification extends Notification implements EmailNotificationInterface
{

    public function __construct(
        private readonly Comment $comment,
        string                   $subject = '',
        array                    $channels = []
    )
    {
        parent::__construct($subject, $channels);
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        $message = EmailMessage::fromNotification($this, $recipient, $transport);

        $message->getMessage()
            ->htmlTEmplate('emails/comment_reviewed_notification.html.twig')
            ->context(['comment' => $this->comment]);

        return $message;
    }
}
