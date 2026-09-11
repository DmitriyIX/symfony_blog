<?php
namespace App\EventListener;

use App\Service\File;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use App\Entity\Post;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;


#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Post::class)]
class PostEventListener
{
    public function __construct(
        private LoggerInterface $logger,
        #[Autowire(env: 'DEFAULT_URI')] private string $defaultUri,
        #[Autowire(env: 'GRPC_GATEWAY_MS')] private string $grpcGatewayMs,
        private RequestStack $requestStack,
        private File $fileService
    ){}

    public function postPersist(Post $post, PostPersistEventArgs $event): void
    {
        $this->logger->info('Post created', ['post_id' => $post->getId()]);
        $request = $this->requestStack->getCurrentRequest();
        if ($request->files->get('preview') !== null) {
            $this->fileService->uploadPostPreview($post, $request->files->get('preview'));
        }
    }
}