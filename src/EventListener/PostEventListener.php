<?php
namespace App\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use App\Entity\Post;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;


#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Post::class)]
#[AsEntityListener(event: Events::postLoad, method: 'postLoad', entity: Post::class)]
class PostEventListener
{
    public function __construct(
        private LoggerInterface $logger,
        #[Autowire(env: 'DEFAULT_URI')] private string $defaultUri
    ){}

    public function postPersist(Post $post, PostPersistEventArgs $event): void
    {
        $this->logger->info('Post created', ['post_id' => $post->getId()]);
        $client = HttpClient::create(); 
        $response = $client->request('POST', 'http://172.23.53.69:8090/v1/example/echo', ['json' => ['name' => '66666']]);
        $this->logger->info('Test message', ['response' => $response->toArray()]);
    }

    public function postLoad(Post $post): void
    {
        if ($post->getPreview() === null) {
            $post->setPreview($this->defaultUri . '/images/posts/default-preview.png');
        } else {
            $post->setPreview($this->defaultUri . '/images/posts/' . $post->getId() . '/' . $post->getPreview());
        }
    }
}