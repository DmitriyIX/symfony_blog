<?php
namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use App\Entity\Post;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Post::class)]
class PostEventListener
{
    public function __construct(
        private LoggerInterface $logger
    ){}

    public function postPersist(Post $post, PostPersistEventArgs $event): void
    {
        $this->logger->info('Post created', ['post_id' => $post->getId()]);
        $client = HttpClient::create(); 
        $response = $client->request('POST', 'http://172.23.53.69:8090/v1/example/echo', ['json' => ['name' => '66666']]);
        $this->logger->info('Test message', ['response' => $response->toArray()]);
    }
}