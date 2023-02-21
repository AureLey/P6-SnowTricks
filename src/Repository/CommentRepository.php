<?php

declare(strict_types=1);

/*
 * This file is part of Snowtricks
 *
 * (c)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public const COMMENT_LOADED = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * save.
     */
    public function save(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * remove.
     */
    public function remove(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * loadCommentPaginated.
     *
     * @param int $id   trick id
     * @param int $page currentPage
     */
    public function loadCommentPaginated(int $id, int $page): array
    {
        // Empty array returned with data or empty
        $array_comment = [];

        // Request who get all comments, order by creation and trick id
        $query = $this->getEntityManager()->createQueryBuilder()
        ->select('c')
        ->from('App\Entity\Comment', 'c')
        ->where("c.commentTrick = '$id'")
        ->orderBy('c.createdAt', 'DESC')
        ->setFirstResult(($page * self::COMMENT_LOADED) - self::COMMENT_LOADED)
        ->setMaxResults(self::COMMENT_LOADED);

        $paginator = new Paginator($query);

        $data = $paginator->getQuery()->getResult();
        if (null === $data) {
            return $array_comment;
        }

        // Calc number of pages,
        $pages = ceil($paginator->count() / self::COMMENT_LOADED);

        $array_comment['data'] = $data; // Data array
        $array_comment['pages'] = $pages; // Nb of pages
        $array_comment['page'] = $page; // CurrentPage

        return $array_comment;
    }
}
