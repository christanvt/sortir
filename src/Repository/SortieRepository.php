<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Helper\EtatChangeHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
        $qb = $this->createQueryBuilder('e');
        $qb
            ->andWhere('e.id = :id')->setParameter(':id', $id)

            ->leftJoin('e.etat', 's')->addSelect('s')
            ->leftJoin('e.organisateur', 'o')->addSelect('o')
            ->leftJoin('e.participants', 'p')->addSelect('p')
            ->leftJoin('e.lieu', 'l')->addSelect('l');

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Requête perso à la bdd pour filtrer et rechercher les sorties
     * Reçoit les données du form sous forme de tableau associatif
     *
     * @param array|null $searchData
     * @return array|mixed
     */
    public function searchTemporaire(int $page = 1, int $numPerPage = 10, ?array $searchData)
    {
        //un seul query builder, alias de event => e
        $qb = $this->createQueryBuilder('e');
        //on sélectionne les event
        $qb->select('e');

        $etatRepo = $this->getEntityManager()->getRepository(Etat::class);

        //que les sorties ouvertes par défaut + sorties créées par moi
        $openState = $etatRepo->findOneBy(['libelle' => EtatChangeHelper::ETAT_OUVERTE]);
        $createdState = $etatRepo->findOneBy(['libelle' => EtatChangeHelper::ETAT_CREEE]);
        $closedState = $etatRepo->findOneBy(['libelle' => EtatChangeHelper::ETAT_CLOTUREE]);
        $canceledState = $etatRepo->findOneBy(['libelle' => EtatChangeHelper::ETAT_ANNULEE]);

        //ajoute des clauses where par défaut, toujours présentes
        $qb->andWhere('e.etat = :openState 
            OR e.etat = :closedState 
            OR e.etat = :createdState
        ')
            ->setParameter('openState', $openState)
            ->setParameter('closedState', $closedState)
            ->setParameter('createdState', $createdState);
            //->setParameter('canceledState', $canceledState);

        //jointures toujours présentes, pour éviter que doctrine fasse 10000 requêtes
        $qb->leftJoin('e.participants', 'p')
            ->addSelect('p')
            ->leftJoin('e.organisateur', 'o')
            ->addSelect('o');

        //la plus proche dans le temps en premier
        $qb->orderBy('e.dateHeureDebut', 'ASC');
/*
        //recherche par mot-clef, si applicable
        if (!empty($searchData['keyword'])){
            $qb->andWhere('e.nom LIKE :kw')
                ->setParameter('kw', '%'.$searchData['keyword'].'%');
        }

        //filtre par campus, si applicable
        if (!empty($searchData['campus'])){
            $qb->andWhere('o.campus = :campus')
                ->setParameter('campus', $searchData['campus']);
        }

        //filtre par date de début minimum
        if (!empty($searchData['start_at_min_date'])){
            $qb->andWhere('e.dateHeureDebut >= :start_at_min_date')
                ->setParameter('start_at_min_date', $searchData['start_at_min_date']);
        }
        //et date de début maximum
        if (!empty($searchData['start_at_max_date'])){
            $qb->andWhere('e.dateHeureDebut <= :start_at_max_date')
                ->setParameter('start_at_max_date', $searchData['start_at_max_date']);
        }

        // crée un ensemble de condition OR entre parenthèses
        // on y ajoute dynamiquement des WHERE plus loin
        $checkBoxesOr = $qb->expr()->orX();

        //récupère l'ids des sorties auxquelles je suis inscrit dans une autre requête
        //ça nous donne un array contenant les ids, qui sera utile pour les IN ou NOT IN plus loin
        $subQueryBuilder = $this->createQueryBuilder('e');
        $subQueryBuilder
            ->from(Participant::class, 'p')->select("DISTINCT(s.id)")
            ->join('p.inscritAuxSorties', 's');
        $result = $subQueryBuilder->getQuery()->getScalarResult();


        $sortiesDuUser = array_column($result, "1");

        //inclure les sorties auxquelles je suis inscrit
        if (!empty($searchData['subscribed_to'])){
            $checkBoxesOr->add($qb->expr()->in('p.inscritAuxSorties', $sortiesDuUser));
        }
        //inclure les sorties auxquelles je ne suis pas inscrit
        if (!empty($searchData['not_subscribed_to'])){
            $checkBoxesOr->add($qb->expr()->notIn('p.inscritAuxSorties', $sortiesDuUser));
        }

        //maintenant que nos clauses OR regroupées sont créées, on les ajoute à la requête dans un grand AND()
        $qb->andWhere($checkBoxesOr);
*/

        $count = count($qb->getQuery()->getResult());

        //on récupère les résultats, en fonction des filtres précédent
        $query = $qb->getQuery();

        $pagination = $this->paginator->paginate($query, $page, $numPerPage);

        return $pagination;
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
        //un seul query builder, alias de event => e
        $qb = $this->createQueryBuilder('e');
        //on sélectionne les event
        $qb->select('e');

        $etatRepo = $this->getEntityManager()->getRepository(Etat::class);

        //que les sorties ouvertes par défaut + sorties créées par moi
        $openState = $etatRepo->findOneBy(['nom' => EtatChangeHelper::ETAT_OUVERTE]);
        $createdState = $etatRepo->findOneBy(['nom' => EtatChangeHelper::ETAT_CREEE]);
        $closedState = $etatRepo->findOneBy(['nom' => EtatChangeHelper::ETAT_CLOTUREE]);
        $canceledState = $etatRepo->findOneBy(['nom' => EtatChangeHelper::ETAT_ANNULEE]);

        //ajoute des clauses where par défaut, toujours présentes
        $qb->andWhere('(e.etat = :openState 
            OR e.etat = :closedState 
            OR (e.etat = :createdState AND e.organisateur = :user))
            AND e.etat != :canceledState
        ')
            ->setParameter('openState', $openState)
            ->setParameter('closedState', $closedState)
            ->setParameter('user', $user)
            ->setParameter('createdState', $createdState)
            ->setParameter('canceledState', $canceledState);

        //jointures toujours présentes, pour éviter que doctrine fasse 10000 requêtes
        $qb->leftJoin('e.participants', 'p')
            ->addSelect('p')
            ->leftJoin('e.organisateur', 'o')
            ->addSelect('o');

        //la plus proche dans le temps en premier
        $qb->orderBy('e.dateHeureDebut', 'ASC');

        //recherche par mot-clef, si applicable
        if (!empty($searchData['keyword'])){
            $qb->andWhere('e.nom LIKE :kw')
                ->setParameter('kw', '%'.$searchData['keyword'].'%');
        }

        //filtre par campus, si applicable
        if (!empty($searchData['campus'])){
            $qb->andWhere('o.campus = :campus')
                ->setParameter('campus', $searchData['campus']);
        }

        //filtre par date de début minimum
        if (!empty($searchData['start_at_min_date'])){
            $qb->andWhere('e.dateHeureDebut >= :start_at_min_date')
                ->setParameter('start_at_min_date', $searchData['start_at_min_date']);
        }
        //et date de début maximum
        if (!empty($searchData['start_at_max_date'])){
            $qb->andWhere('e.dateHeureDebut <= :start_at_max_date')
                ->setParameter('start_at_max_date', $searchData['start_at_max_date']);
        }

        // crée un ensemble de condition OR entre parenthèses
        // on y ajoute dynamiquement des WHERE plus loin
        $checkBoxesOr = $qb->expr()->orX();

        //récupère l'ids des sorties auxquelles je suis inscrit dans une autre requête
        //ça nous donne un array contenant les ids, qui sera utile pour les IN ou NOT IN plus loin
        $subQueryBuilder = $this->createQueryBuilder('e');
        $subQueryBuilder
            ->from(Participant::class, 'p')->select("DISTINCT(s.id)")
            ->join('p.inscritAuxSorties', 's')->setParameter("userId", $user->getId())
            ->andWhere('p.id = :userId');
        $result = $subQueryBuilder->getQuery()->getScalarResult();
        $sortiesDuUser = array_column($result, "1");

        //inclure les sorties auxquelles je suis inscrit
        if (!empty($searchData['subscribed_to'])){
            $checkBoxesOr->add($qb->expr()->in('p.inscritAuxSorties', $sortiesDuUser));
        }
        //inclure les sorties auxquelles je ne suis pas inscrit
        if (!empty($searchData['not_subscribed_to'])){
            $checkBoxesOr->add($qb->expr()->notIn('p.inscritAuxSorties', $sortiesDuUser));
        }
        //inclure les sorties dont je suis l'organisateur
        if (!empty($searchData['is_organizer'])){
            $checkBoxesOr->add($qb->expr()->eq('e.organisateur', $user->getId()));
        }

        //maintenant que nos clauses OR regroupées sont créées, on les ajoute à la requête dans un grand AND()
        $qb->andWhere($checkBoxesOr);


        $count = count($qb->getQuery()->getResult());

        //on récupère les résultats, en fonction des filtres précédent
        $query = $qb->getQuery();

        $pagination = $this->paginator->paginate($query, $page, $numPerPage);

        return $pagination;
    }
}
