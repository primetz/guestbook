<?php

namespace App\Service\SpamChecker;

use App\Entity\Comment;

interface SpamCheckerInterface
{

    public function getSpamScore(Comment $comment, array $context): int;
}
