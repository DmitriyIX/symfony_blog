<?php
namespace App\EventListener;

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
// #[AsEntityListener(event: Events::postLoad, method: 'postLoad', entity: Post::class)]
// #[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Post::class)]
class PostEventListener
{
    public function __construct(
        private LoggerInterface $logger,
        #[Autowire(env: 'DEFAULT_URI')] private string $defaultUri,
        #[Autowire(env: 'GRPC_GATEWAY_MS')] private string $grpcGatewayMs,
        private RequestStack $requestStack
    ){}

    public function postPersist(Post $post, PostPersistEventArgs $event): void
    {
        $this->logger->info('Post created', ['post_id' => $post->getId()]);
        $request = $this->requestStack->getCurrentRequest();
        if ($request->files->get('preview') !== null) {
            $this->logger->info('Preview loaded', ['post_id' => $post->getId()]);
            $preview = $request->files->get('preview');
        // dd($preview->getClientOriginalName());
            $f = fopen($preview->getPathname(), 'r');
            $client = HttpClient::create(); 
            // $response = $client->request('POST', 'http://' . $this->grpcGatewayMs . '/v1/example/echo', [
            //     'json' => [
            //         'name' => 'giest'
            //     ]
            // ]);
            $response = $client->request('POST', 'http://' . $this->grpcGatewayMs . '/v1/files/upload', [
                'headers' => [
                    'Content-Type' => 'application/octet-stream',
                    'X-Filename' => $preview->getClientOriginalName()
                ],
                'body' => $f
            ]);
            $this->logger->info('Test message', ['response' => $response->toArray()]);
        }
        
        
        // $response = $client->request('POST', 'http://172.19.146.198:8090/v1/example/echo', ['json' => ['name' => $post->getId()], 'timeout' => 300]);
        
        // curl -X 'POST' http://host.docker.internal:8090/v1/example/echo --header 'Content-Type: application/json' -d '{"name": "test"}'
        // curl -X 'POST' http://172.26.0.5:8090/v1/example/echo --header 'Content-Type: application/json' -d '{"name": "test"}'
    }

    // public function postLoad(Post $post): void
    // {
    //     if ($post->getPreview() === null) {
    //         $post->setPreview($this->defaultUri . '/images/posts/default-preview.png');
    //     } else {
    //         $post->setPreview($this->defaultUri . '/images/posts/' . $post->getId() . '/' . $post->getPreview());
    //     }
    // }

    // public function postUpdate(Post $post, PostUpdateEventArgs $args): void
    // {
    //     $this->logger->info('Post test 123');
    //     // dd('chto nado 3');
    //     $client = HttpClient::create(); 
    //     // dd('Tratata 2');
    //     $response = $client->request('POST', 'http://172.19.146.198:8090/v1/example/echo', ['json' => ['name' => '66666']]);
    //     dd($response);
    //     $this->logger->info('Test message', ['response' => $response->toArray()]);
    //     // $request = $this->requestStack->getCurrentRequest();
    //     // if ($request->files->get('preview') !== null) {
    //     //     $this->logger->info('Post preview updated', ['post_id' => $post->getId()]);
    //     // }
    //     $this->logger->info('Post updated', ['post_id' => $post->getId()]);
    // }
}