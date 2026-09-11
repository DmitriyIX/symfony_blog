<?php

namespace App\Repository;

use App\Entity\Post;
use App\Service\File;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\DTO\PostDto;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry, 
        private Filesystem $filesystem, 
        #[Autowire(env: 'DEFAULT_URI')] private string $defaultUri,
        #[Autowire(param: 'kernel.project_dir')] private string $projectDir,
        private File $fileService
    )
    {
        parent::__construct($registry, Post::class);
    }

    //    /**
    //     * @return Post[] Returns an array of Post objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Post
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = 1')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

    }

    public function updatePost(Post $post, PostDto $postDto): bool
    {
        $query = $this->createQueryBuilder('p')
            ->update()
            ->where('p.id = :postId')
            ->setParameter('postId', $post->getId());
        if ($postDto->title !== null) {
            $query->set('p.title', ':title')->setParameter('title', $postDto->title);
        }
        if ($postDto->content !== null) {
            $query->set('p.content', ':content')->setParameter('content', $postDto->content);
        }
        if ($postDto->preview !== null) {
            $this->updatePreview($post, $postDto);
        }
        $query->set('p.updated_at', ':updatedAt')->setParameter('updatedAt', new \DateTimeImmutable());
        return $query->getQuery()->execute() > 0;
    }

    private function updatePreview(Post $post, PostDto $postDto): void
    {
        $isPreviewS3 = preg_match('/https:\/\/storage.yandexcloud.net\/test-d45\/([0-9a-z]*\.[jpg|png]*)/', $post->getPreview(), $matches);
        $oldPreview = $isPreviewS3 ? $matches[1] : "";
        $this->filesystem->remove($this->projectDir . '/public/images/posts/' . $post->getId() . '/preview');
        $this->fileService->uploadPostPreview($post, $postDto->preview, $oldPreview);        
    }
}
