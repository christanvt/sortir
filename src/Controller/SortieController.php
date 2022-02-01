<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\LieuType;
use App\Form\SortieAnnulationType;
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
        $paginationSortie = $sortieRepo->search($page, 20, $this->getUser(), $searchData);

        return $this->render('sortie/list.html.twig', [
            'paginationSortie' => $paginationSortie,
            'searchForm' => $searchForm->createView()
        ]);
    }

    /**
     * Affichage d'une sortie
     *
     * @Route("/details/{id}", name="detail")
     */
    public function detail($id, EntityManagerInterface $em, SortieRepository $sortieRepo)
    {
        $sortie = $sortieRepo->findWithJoins($id);

        $u = $this->getUser();
<<<<<<< HEAD
        if ($u == null) {
            // parce que pour l'instant on ne se connecte pas au site avec un login
            // donc je feinte
            $u = $em->getRepository(Participant::class)->findOneBy(['email' => 'admin@admin.fr']);
        }

        //seuls les admins et l'auteur peuvent passer ici
        if (!$this->isGranted("ROLE_ADMIN")) {
            if (
                $sortie->getEtat()->getLibelle() === EtatChangeHelper::ETAT_CREEE
                && $sortie->getOrganisateur() !== $u
            ) {
=======

        //seuls les admins et l'auteur peuvent passer ici
        if (!$this->isGranted("ROLE_ADMIN")) {
            if ($sortie->getEtat()->getLibelle() === EtatChangeHelper::ETAT_CREEE
                && $sortie->getOrganisateur() !== $u) {
>>>>>>> 9ade3c107cca313ccfa4be18ebe79d719ec1480d
                throw $this->createNotFoundException("Cette sortie n'existe pas encore !");
            }
        }

        if (!$sortie) {
            throw $this->createNotFoundException("Cette sortie n'existe pas !");
        }

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    /**
     * Création d'une sortie
     *
     * @Route("/ajout", name="create")
     */
    public function create(Request $request, EntityManagerInterface $em, EtatChangeHelper $stateHelper, SortieRepository $sortieRepo)
    {
        $sortie = new Sortie();

        // heures par défaut
        $sortie->setDateHeureDebut((new \DateTimeImmutable())->setTime(17, 0));
        // https://en.wikipedia.org/wiki/ISO_8601#Durations
        // To resolve ambiguity, "P1M" is a one-month duration and "PT1M" is a one-minute duration
        // (note the time designator, T, that precedes the time value).
        // PnYnMnDTnHnMnS
        $sortie->setDateLimiteInscription($sortie->getDateHeureDebut()->sub(new \DateInterval("PT1H")));

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            // donner l'état "créée" à cette sortie
            $sortie->setEtat($stateHelper->getEtatByNom(EtatChangeHelper::ETAT_CREEE));

            //on renseigne son auteur (le user actuel)
            $u = $this->getUser();
            if ($u == null) {
                // parce que pour l'instant on ne se connecte pas au site avec un login
                // donc je feinte
                $u = $em->getRepository(Participant::class)->findOneBy(['email' => 'admin@admin.fr']);
            }

            $sortie->setOrganisateur($u);
            $sortie->setCampus($u->getCampus());

            $em->persist($sortie);
            $em->flush();

            //si on publie directement, alors on redirige vers cette page de publication au lieu de dupliquer le code
            if ($sortieForm->get('publierMaintenant')->getData() === true) {
                return $this->redirectToRoute('sortie_publier', ['id' => $sortie->getId()]);
            }


            $this->addFlash('success', 'Vous venez de créée une sortie !');
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        //formulaire de lieu, pas traité ici ! Il est en effet soumis en ajax, vers une autre route
        $lieuForm = $this->createForm(LieuType::class);

        //on passe les 2 forms pour affichage
        return $this->render('sortie/create.html.twig', ['id' => 0,
            'sortieForm' => $sortieForm->createView(),
            'lieuForm' => $lieuForm->createView()
        ]);
    }

    /**
     * Modification d'une sortie
     *
     * @Route("/update/{id}", name="update")
     */
    public function update($id, Request $request, EntityManagerInterface $em, EtatChangeHelper $stateHelper, SortieRepository $sortieRepo)
    {
        $sortie = $sortieRepo->findWithJoins($id);
        $u = $this->getUser();

        //seuls les admins et l'auteur peuvent passer ici
        if (!$this->isGranted("ROLE_ADMIN")) {
            if ($sortie->getEtat()->getLibelle() === EtatChangeHelper::ETAT_CREEE
                && $sortie->getOrganisateur() !== $u) {
                throw $this->createNotFoundException("Vous n'êtes pas le créateur de cette sortie !");
            }
        }

        if (!$sortie) {
            throw $this->createNotFoundException("Cette sortie n'existe pas !");
        }

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $em->persist($sortie);
            $em->flush();

            //si on publie directement, alors on redirige vers cette page de publication au lieu de dupliquer le code
            if ($sortieForm->get('publierMaintenant')->getData() === true) {
                return $this->redirectToRoute('sortie_publier', ['id' => $sortie->getId()]);
            }

            $this->addFlash('success', 'Vous venez de modifier la sortie !');
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        //formulaire de lieu, pas traité ici ! Il est en effet soumis en ajax, vers une autre route
        $lieuForm = $this->createForm(LieuType::class);

        //on passe les 2 forms pour affichage
        return $this->render('sortie/create.html.twig', ['id' => $sortie->getId(),
            'sortieForm' => $sortieForm->createView(),
            'lieuForm' => $lieuForm->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('sortie_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/publier", name="publier")
     */
    public function publier(Sortie $sortie, EtatChangeHelper $etatHelper)
    {
        //vérifie que c'est bien l'auteur (ou un admin) qui est en train de publier
        if ($this->getUser() !== $sortie->getOrganisateur() && !$this->isGranted("ROLE_ADMIN")) {
            throw $this->createAccessDeniedException("Seul l'organisateur de cette sortie peut la publier !");
        }

        //vérifie que ça peut être publié (pas annulée, pas closed, etc.)
        if (!$etatHelper->peutEtreOuverte($sortie)) {
            $this->addFlash('danger', 'Cette sortie ne peut pas être publiée !');
            return $this->redirectToRoute('sortie_list');
        }

        $etatHelper->changeEtatSortie($sortie, EtatChangeHelper::ETAT_OUVERTE);
        $this->addFlash('success', 'La sortie est publiée !');

        return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
    }


    /**
     * @Route("/{id}/annuler", name="annuler")
     */
    public function annulation(sortie $sortie, EntityManagerInterface $em, EtatChangeHelper $etatHelper, Request $request)
    {
        //vérifie que la sortie n'est pas déjà annulée ou autre
        if (!$etatHelper->peutEtreAnnulee($sortie)) {
            $this->addFlash('warning', 'Cette sortie ne peut pas être annulée !');
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

<<<<<<< HEAD
        if ($this->isCsrfTokenValid('annuler' . $sortie->getId(), $request->request->get('_token'))) {
=======
        $annulationSortieForm = $this->createForm(SortieAnnulationType::class, $sortie);

        $annulationSortieForm->handleRequest($request);

        if ($annulationSortieForm->isSubmitted() && $annulationSortieForm->isValid()) {

>>>>>>> 9ade3c107cca313ccfa4be18ebe79d719ec1480d
            $etatHelper->changeEtatSortie($sortie, EtatChangeHelper::ETAT_ANNULEE);
            $em->persist($sortie);
            $em->flush();

<<<<<<< HEAD
        $this->addFlash('success', 'La sortie a bien été annulée.');
        return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
    }
    /**
     * @Route("/index", name="index", methods={"GET"})
     */
    public function index(SortieRepository $sortieRepository): Response
    {
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sortieRepository->findAll(),
=======
            $this->addFlash('success', 'La sortie a bien été annulée.');
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/annulation.html.twig', [
            'sortie' => $sortie,
            'annulationSortieForm' => $annulationSortieForm->createView()
>>>>>>> 9ade3c107cca313ccfa4be18ebe79d719ec1480d
        ]);
    }
}
