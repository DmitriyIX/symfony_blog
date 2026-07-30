<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\DTO\PostDto;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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

    public function updatePost(int $postId, PostDto $postDto): bool
    {
        $query = $this->createQueryBuilder('p')
            ->update()
            ->where('p.id = :postId')
            ->setParameter('postId', $postId);
        if ($postDto->title !== null) {
            $query->set('p.title', ':title')->setParameter('title', $postDto->title);
        }
        if ($postDto->content !== null) {
            $query->set('p.content', ':content')->setParameter('content', $postDto->content);
        }
        $query->set('p.updated_at', ':updatedAt')->setParameter('updatedAt', new \DateTimeImmutable());
        return $query->getQuery()->execute() > 0;
    }
}
