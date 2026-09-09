<?php
namespace App\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use App\DTO\PostDto;
use App\DTO\ResponseDto;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PostController extends AbstractController
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private LoggerInterface $logger,
        private Filesystem $filesystem,
        private SerializerInterface $serializer,
        private NormalizerInterface $normalizer,
        private DenormalizerInterface $denormalizer
    ){}

    #[Route('/posts/{id}', methods: 'POST')]
    public function update(
        #[MapRequestPayload()] ?PostDto $postDto,
        Post $post,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response
    {
        if ($postDto == null) {
            $previewFile = $request->files->get('preview');
            $postDto = new PostDto(null, null, $previewFile);
        }
        $result = $entityManager->getRepository(Post::class)->updatePost($post, $postDto);
        $entityManager->flush();
        if ($result) {
            $entityManager->refresh($post);
            $response = new ResponseDto(true, 'Обновление поста', $post, null);
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
        
        $data = $this->normalizer->normalize($post, null);
        $postDto = $this->denormalizer->denormalize($data, PostDto::class, null, [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true]);
        $response = new ResponseDto(true, 'Получение поста', $postDto, null);
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
        $post->setStatus($postDto->status);
        $post->setCreatedAt(new \DateTimeImmutable());
        $post->setUpdatedAt(new \DateTimeImmutable());
        
        $entityManager->persist($post);
        $entityManager->flush();
        
        if ($postDto->preview !== null) {
            $postsFilepath = $this->getParameter('kernel.project_dir') . '/public/images/posts';
            $postFilepath = $postsFilepath . '/' . $post->getId() . '/preview';
            if ($this->filesystem->exists($postFilepath) == false) {
                $this->filesystem->mkdir($postFilepath, 0755);
            }
            $postDto->preview->move($postFilepath, $postDto->preview->getClientOriginalName());
            $post->setPreview($postDto->preview->getClientOriginalName());
            $entityManager->persist($post);
            $entityManager->flush();
        }

        $response = new ResponseDto(true, 'Пост создан', ['id' => $post->getId()], null);
        return $this->json($response->getResponse(), 201);
    }

    #[Route('/posts/{id}', methods: 'DELETE')]
    public function delete(
        Post $post,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $entityManager->remove($post);
        $entityManager->flush();
        $response = new ResponseDto(true, 'Пост удален', null, null);
        return $this->json($response->getResponse(), Response::HTTP_NO_CONTENT);
    }
}