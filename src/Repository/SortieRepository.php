<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Helper\EtatChangeHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    private PaginatorInterface $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Sortie::class);
        $this->paginator = $paginator;
    }

    /**
     * Récupère une sortie avec plein de jointures, pour éviter les 10000 requêtes à la bdd
     * Ce sont surtout la récupération des utilisateurs inscrits qui posaient problème
     */
    public function findWithJoins(int $id)
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->andWhere('s.id = :id')->setParameter(':id', $id)
            ->leftJoin('s.etat', 'e')->addSelect('e')
            ->leftJoin('s.organisateur', 'o')->addSelect('o')
            ->leftJoin('s.participants', 'p')->addSelect('p')
            ->leftJoin('s.lieu', 'l')->addSelect('l');

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Requête perso à la bdd pour filtrer et rechercher les sorties
     * Reçoit les données du form sous forme de tableau associatif
     *
     * @param UserInterface $user
     * @param array|null $searchData
     * @return array|mixed
     */
    public function search(int $page = 1, int $numPerPage = 10, UserInterface $user, ?array $searchData)
    {
        //un seul query builder, alias de sortie => s
        $qb = $this->createQueryBuilder('s');
        //on sélectionne les event
        $qb->select('s');

        $etatRepo = $this->getEntityManager()->getRepository(Etat::class);

        //que les sorties ouvertes par défaut + sorties créées par moi
        $openState = $etatRepo->findOneBy(['libelle' => EtatChangeHelper::ETAT_OUVERTE]);
        $createdState = $etatRepo->findOneBy(['libelle' => EtatChangeHelper::ETAT_CREEE]);
        $closedState = $etatRepo->findOneBy(['libelle' => EtatChangeHelper::ETAT_CLOTUREE]);
        $canceledState = $etatRepo->findOneBy(['libelle' => EtatChangeHelper::ETAT_ANNULEE]);
        $archivedState = $etatRepo->findOneBy(['libelle' => EtatChangeHelper::ETAT_ARCHIVEE]);
        $goneState = $etatRepo->findOneBy(['libelle' => EtatChangeHelper::ETAT_PASSEE]);

        //ajoute des clauses where par défaut, toujours présentes
        $qb->andWhere('(s.etat = :openState 
            OR s.etat = :canceledState
            OR s.etat = :closedState  
            OR (s.etat = :createdState AND s.organisateur = :user))
            AND s.etat != :archivedState
        ')
            ->setParameter('openState', $openState)
            ->setParameter('closedState', $closedState)
            ->setParameter('createdState', $createdState)
            ->setParameter('canceledState', $canceledState)
            ->setParameter('archivedState', $archivedState)
            ->setParameter('user', $user);

        //jointures toujours présentes, pour éviter que doctrine fasse 10000 requêtes
        $qb->leftJoin('s.participants', 'p')->addSelect('p')
            ->leftJoin('p.inscritAuxSorties', 'i')->addSelect('i')
            ->leftJoin('s.organisateur', 'o')->addSelect('o');

        //la plus proche dans le temps en premier
        $qb->orderBy('s.dateHeureDebut', 'ASC');

        //recherche par mot-clef, si applicable
        if (!empty($searchData['keyword'])) {
            $qb->andWhere('s.nom LIKE :kw')
                ->setParameter('kw', '%' . $searchData['keyword'] . '%');
        }

        //filtre par campus, si applicable
        if (!empty($searchData['campus'])) {
            $qb->andWhere('o.campus = :campus')
                ->setParameter('campus', $searchData['campus']);
        }

        //filtre par date passé
        if (!empty($searchData['gone'])) {
            $qb->andWhere('s.dateHeureDebut < :now')
                ->setParameter('now', new \DateTime());
        }

        //filtre par date de début minimum
        if (!empty($searchData['start_at_min_date'])) {
            $qb->andWhere('s.dateHeureDebut >= :start_at_min_date')
                ->setParameter('start_at_min_date', $searchData['start_at_min_date']);
        }
        //et date de début maximum
        if (!empty($searchData['start_at_max_date'])) {
            $qb->andWhere('s.dateHeureDebut <= :start_at_max_date')
                ->setParameter('start_at_max_date', $searchData['start_at_max_date']);
        }

        //inclure les sorties auxquelles je suis inscrit
        if (!empty($searchData['subscribed_to'])) {
            $qb->orWhere(':u1 MEMBER OF s.participants');
            $qb->setParameter('u1', $user);
        }

        //inclure les sorties auxquelles je ne suis pas inscrit
        if (!empty($searchData['not_subscribed_to'])) {
            $qb->orWhere(':u2 NOT MEMBER OF s.participants');
            $qb->setParameter('u2', $user);
        }

        //inclure les sorties dont je suis l'organisateur
        if (!empty($searchData['is_organizer'])) {
            $qb->orWhere('s.organisateur = :u3');
            $qb->setParameter('u3', $user);
        }

        //inclure les sorties passées
        if (!empty($searchData['gone'])) {
            $qb->orWhere('s.etat = :goneState');
            $qb->setParameter('goneState', $goneState);
        }

        $query = $qb->getQuery();

        $pagination = $this->paginator->paginate($query, $page, $numPerPage);

        return $pagination;
    }
}
