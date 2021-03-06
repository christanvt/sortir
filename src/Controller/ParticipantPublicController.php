<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Helper\EtatChangeHelper;
use App\Form\ParticipantPhotoType;
use App\Form\ParticipantMotpasseType;
use App\Form\ParticipantIdentifedType;
use App\Helper\ParticipantHelper;
use App\Helper\SortieHelper;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @Route("/participant")
 */
class ParticipantPublicController extends AbstractController
{


    /**
     * @Route("/my_profil", name="participant_my_profil", methods={"GET"})
     */
    public function my_profil(ParticipantRepository $participantRepository): Response
    {

        return $this->render('participant/show.html.twig', [
            'participant' => $participantRepository->findOneby(['id' => $this->getUser()]),
        ]);
    }

    /**
     * @Route("/{id}", name="participant_show", methods={"GET"})
     */
    public function show(Participant $participant): Response
    {
        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="participant_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (
            $user == $participant //Si l'user est celui du edit
        ) {
            $form = $this->createForm(ParticipantIdentifedType::class, $participant);
            $title = "Modifier mes infos personnelles";
        } elseif (
            $this->isGranted('ROLE_ADMIN') //Si l'admin veut edit
        ) {
            $form = $this->createForm(ParticipantType::class, $participant);
            $title = "Modifier les infos personnelles du membre " . $participant->getPseudo();
        } else {
            throw new AccessDeniedException();
        };
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('participant_show', ['id' => $participant->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
            'title' => $title,
        ]);
    }
    /**
     * @Route("/{id}/edit/photo", name="participant_edit_photo", methods={"GET", "POST"})
     */
    public function editPhoto(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (
            $user == $participant //Si l'user est celui du edit
        ) {
            $form = $this->createForm(ParticipantPhotoType::class, $participant);
            $title = "Modifier ma photo de profil";
        } elseif (
            $this->isGranted('ROLE_ADMIN') //Si l'admin veut edit

        ) {
            $form = $this->createForm(ParticipantPhotoType::class, $participant);
            $title = "Modifier la photo du profil du membre " . $participant->getPseudo();
        } else {
            throw new AccessDeniedException();
        };
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('participant_show', ['id' => $participant->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
            'title' => $title,

        ]);
    }
    /**
     * @Route("/{id}/edit/motdepasse", name="participant_edit_motpasse", methods={"GET", "POST"})
     */
    public function editMotDePasse(Request $request, Participant $participant, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder): Response
    {
        $user = $this->getUser();
        if (
            $user == $participant //Si l'user est celui du edit
        ) {
            $form = $this->createForm(ParticipantMotpasseType::class, $participant);
            $title = "Modifier mon mot de passe";
        } elseif (
            $this->isGranted('ROLE_ADMIN') //Si l'admin veut edit
        ) {
            $form = $this->createForm(ParticipantMotpasseType::class, $participant);
            $title = "Modifier le mot de passe du membre " . $participant->getPseudo();
        } else {
            throw new AccessDeniedException();
        };
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newpwd = $form->get('motpasse')->getData();
            $newEncodedPassword = $passwordEncoder->hashPassword($participant, $newpwd);
            $participant->setMotpasse($newEncodedPassword);
            $entityManager->persist($participant);

            $entityManager->flush();

            return $this->redirectToRoute('participant_show', ['id' => $participant->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
            'title' => $title,

        ]);
    }


    /**
     * Inscrit un participant ?? une sortie, OU le d??sinscrit
     *
     * @Route("/sorties/{id}/participant/", name="participant_toggle")
     */
    public function toggle(Sortie $sortie, EntityManagerInterface $em, EtatChangeHelper $etatChangeHelper, ParticipantHelper $userHelper)
    {
        $participantRepo = $em->getRepository(Participant::class);

        //la sortie doit ??tre dans l'??tat ouverte pour qu'on puisse s'y inscrire
        if ( !$userHelper->peutSinscrireASortie($this->getUser(), $sortie)) {
            $this->addFlash("danger", "Cette sortie n'est pas ouverte aux inscriptions !");
            return $this->redirectToRoute('sortie_detail', ["id" => $sortie->getId()]);
        }

        // @TODO tester que la sortie n'est pas clotur??e

        //d??sincription si on trouve cette inscription
        //on la recherche dans la bdd du coup...
        $foundParticipant = $participantRepo->findOneBy(['id' => $this->getUser()->getId()]);

        if ($sortie->isParticipant($this->getUser())) {
            //supprime l'inscription
            $sortie->removeParticipant($foundParticipant);
            $em->flush();

            $this->addFlash("success", "Vous ??tes d??sinscrit !");
            return $this->redirectToRoute('sortie_detail', ["id" => $sortie->getId()]);
        }

        //sinon,
        // si on ne l'a pas trouv??e dans la DB, alors on s'inscrit
        // la sortie est-elle compl??te ?
        if ($sortie->isFull()) {
            $this->addFlash("danger", "Cette sortie est compl??te !");
            return $this->redirectToRoute('sortie_detail', ["id" => $sortie->getId()]);
        }

        //si on s'est rendu jusqu'ici, c'est que tout est ok. On cr??e et sauvegarde l'inscription.
        $foundParticipant->addSorty($sortie);
        $em->persist($foundParticipant);
        $em->flush();

        //on refresh la sortie pour avoir le bon nombre d'inscrits
        $em->refresh($sortie);

        //maintenant,si c'est complet pour changer son ??tat
        if ($sortie->isFull()) {
            $etatChangeHelper->changeEtatSortie($sortie, EtatChangeHelper::ETAT_CLOTUREE);
        }

        $this->addFlash("success", "Vous ??tes inscrit !");
        return $this->redirectToRoute('sortie_detail', ["id" => $sortie->getId()]);
    }
}
