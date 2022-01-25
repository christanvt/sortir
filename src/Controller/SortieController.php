<?php

namespace App\Controller;


use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieSearchType;
use App\Form\SortieType;
use App\Helper\EtatChangeHelper;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sortie", name="sortie_")
 */
class SortieController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(SortieRepository $sortieRepository): Response
    {
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sortieRepository->findAll(),
        ]);
    }

    /**
     * Liste des sorties et recherche/filtres
     *
     * @Route("/{page}", name="list", requirements={"page": "\d+"})
     */
    public function list(Request $request, EntityManagerInterface $em, int $page = 1)
    {
        //valeurs par défaut du formulaire de recherche
        //sous forme de tableau associatif, car le form n'est pas associée à une entité
        $searchData = [
            'subscribed_to' => true,
            'not_subscribed_to' => true,
            'is_organizer' => true,
            'start_at_min_date' => new \DateTime("- 1 month"),
            'start_at_max_date' => new \DateTime("+ 1 year"),
        ];
        $searchForm = $this->createForm(SortieSearchType::class, $searchData);

        $searchForm->handleRequest($request);

        //on récupère les données soumises par l'utilisateur
        $searchData = $searchForm->getData();

        //appelle ma méthode de recherche et filtre
        $sortieRepo = $em->getRepository(Sortie::class);
        $paginatedEvents = $sortieRepo->search($page, 20, $this->getUser(), $searchData);

        return $this->render('sortie/index.html.twig', [
            'paginatedEvents' => $paginatedEvents,
            'searchForm' => $searchForm->createView()
        ]);
    }


    /**
     * Création d'une sortie
     *
     * @Route("/ajout", name="create")
     */
    public function create(Request $request, EntityManagerInterface $em, EtatChangeHelper $stateHelper)
    {
        $sortie = new Sortie();

        // heures par défaut
        $sortie->setDateHeureDebut((new \DateTimeImmutable())->setTime(17, 0));
        $sortie->setDateLimiteInscription($sortie->setDateHeureDebut()->sub(new \DateInterval("PT1H")));

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()){
            // donner l'état "créée" à cette sortie
            $sortie->setEtat($stateHelper->getEtatByNom(EtatChangeHelper::ETAT_CREEE));

            //on renseigne son auteur (le user actuel)
            $sortie->setOrganisateur($this->getUser());

            $em->persist($sortie);
            $em->flush();

            //si on publie directement, alors on redirige vers cette page de publication au lieu de dupliquer le code
            /*
            if ($sortieForm->get('publierMaintenant')->getData() === true){
                return $this->redirectToRoute('event_publish', ['id' => $sortie->getId()]);
            }
            */

            $this->addFlash('success', 'Sortie créée, bravo !');
            return $this->redirectToRoute('event_detail', ['id' => $sortie->getId()]);
        }

        //formulaire de lieu, pas traité ici ! Il est en effet soumis en ajax, vers une autre route
        $locationForm = $this->createForm(LocationType::class);

        //on passe les 2 forms pour affichage
        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'locationForm' => $locationForm->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="sortie_show", methods={"GET"})
     */
    public function show(Sortie $sortie): Response
    {
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="sortie_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="sortie_delete", methods={"POST"})
     */
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('sortie_index', [], Response::HTTP_SEE_OTHER);
    }
}
