<?php
namespace App\Controller;

use App\Entity\Post;
use App\Event\CustomEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use App\DTO\PostDto;
use App\DTO\ResponseDto;
use Symfony\Contracts\EventDispatcher\Event;
use Psr\Log\LoggerInterface;

class PostController extends AbstractController
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private LoggerInterface $logger
    ){}

    #[Route('/posts/{id}', methods: 'PATCH')]
    public function update(
        #[MapRequestPayload()] PostDto $postDto,
        Post $post,
        int $id,
        EntityManagerInterface $entityManager
    ): Response
    {
        $result = $entityManager->getRepository(Post::class)->updatePost($id, $postDto);
        if ($result) {
            $response = new ResponseDto(true, 'Обновление поста', $postDto, null);
            return $this->json($response->getResponse(), 200);
        }
        return $this->json($post);
    }

    #[Route('/posts', methods: 'GET')]
    public function list(
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $posts = $entityManager->getRepository(Post::class)->findAllActive();
        $response = new ResponseDto(true, 'Список постов', $posts, null);
        return $this->json($response->getResponse(), 200);
    }

    #[Route('/posts/{id}', methods: 'GET')]
    public function show(
        Post $post
    ): JsonResponse
    {
        $this->logger->info('Tra  ta at');
        $event = new CustomEvent();
        $this->dispatcher->addListener(CustomEvent::class, function(CustomEvent $event) {
            dd('event tratata');
        });
        $this->dispatcher->dispatch($event);
        $response = new ResponseDto(true, 'Получение поста', $post, null);
        return $this->json($response->getResponse(), 200);
    }

    #[Route('/posts', methods: 'POST')]
    public function create(
        #[MapRequestPayload()] PostDto $postDto,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $post = new Post();
        $post->setTitle($postDto->title);
        $post->setContent($postDto->content);
        $post->setPreview($postDto->preview);
        $post->setStatus($postDto->status);
        $post->setCreatedAt(new \DateTimeImmutable());
        $post->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($post);
        $entityManager->flush();

        $response = new ResponseDto(true, 'Пост создан', ['id' => $post->getId()], null);
        return $this->json($response->getResponse(), 201);
    }

    #[Route('/posts/{id}', methods: 'DELETE')]
    public function remove(
        Post $post,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $entityManager->remove($post);
        $entityManager->flush();
        $response = new ResponseDto(true, 'Пост удален', null, null);
        return $this->json($response->getResponse(), Response::HTTP_NO_CONTENT);
        // curl -X 'POST' -k http://host.docker.internal:8090/v1/example/echo -d '{"name": "123"}'
        // curl -X 'POST' -k http://gateway.docker.internal:8090/v1/example/echo -d '{"name": "123"}'

        // curl -X 'POST' -k http://0.0.0.0:8090/v1/example/echo -d '{"name": "123"}'
        // curl -X 'POST' -k http://172.23.53.69:8090/v1/example/echo -d '{"name": "123"}'
    }
}