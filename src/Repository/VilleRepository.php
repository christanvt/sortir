<?php

namespace App\Repository;

use App\Entity\Ville;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method Ville|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ville|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ville[]    findAll()
 * @method Ville[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VilleRepository extends ServiceEntityRepository
{
    private PaginatorInterface $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Ville::class);
        $this->paginator = $paginator;
    }

    public function findPaginatedVilles(int $page = 1, int $numPerPage = 50)
    {
        $qb = $this->createQueryBuilder('v')
            ->addOrderBy('v.nom', 'ASC');

        return $this->paginator->paginate($qb, $page, $numPerPage);
    }

    public function findByCodePostalStartWith(string $cp)
    {
        $villeRepo = $this->getEntityManager()->getRepository(Ville::class);
        $result = $villeRepo->createQueryBuilder('v')
            ->where('v.codePostal LIKE :cp')
            ->setParameter('cp', $cp.'%')
            ->orderBy('v.nom', 'ASC')
            ->getQuery()
            ->getResult();
        return $result;
    }
}
