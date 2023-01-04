<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Message\CommentMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/admin')]
class AdminController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface    $bus,
    )
    {
    }

    #[Route('/comment/review/{id}', name: 'review_comment')]
    public function reviewComment(
        Request $request,
        Comment $comment,
        WorkflowInterface $commentStateMachine,

    ): Response
    {

        $accepted = !$request->query->get('reject');

        if ($commentStateMachine->can($comment, 'publish')) {
            $transition = $accepted ? 'publish' : 'reject';
        } elseif ($commentStateMachine->can($comment, 'publish_ham')) {
            $transition = $accepted ? 'publish_ham' : 'reject_ham';
        } else {
            return new Response('Comment already reviewed or not in right state.');
        }

        $commentStateMachine->apply($comment, $transition);

        $this->entityManager->flush();

        if ($accepted) {
            $reviewUrl = $this->generateUrl('review_comment', ['id' => $comment->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->bus->dispatch(new CommentMessage($comment->getId(), $reviewUrl));
        }

        return $this->render('admin/review.html.twig', [
            'transition' => $transition,
            'comment' => $comment,
        ]);
    }

    #[Route('/http-cache/{uri}', requirements: ['uri' => '.*'], methods: ['PURGE'])]
    public function purgeHttpCache(
        KernelInterface $kernel,
        Request $request,
        string $uri,
        StoreInterface $store,
    ): Response
    {
        if ($kernel->getEnvironment() === 'prod') {
            return new Response('KO', 400);
        }

        $store->purge($request->getSchemeAndHttpHost() . '/' . $uri);

        return new Response('Done');
    }
}
