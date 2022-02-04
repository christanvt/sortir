<?php

namespace App\Helper;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Ce service aide à gérer les actions possibles sur les sorties
 *
 * Class ParticipantHelper
 * @package App\Helper
 */
class ParticipantHelper
{
    private $doctrine;

    /**
     * injection de doctrine dans le service
     *
     * ParticipantHelper constructor.
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     *
     * Retourne true si la sortie peut être ouverte
     *
     * @param UserInterface $user
     * @param Sortie $sortie
     * @return bool
     */
    public function peutSinscrireASortie(UserInterface $user, Sortie $sortie): bool
    {
        // la sortir doit être en statut "ouverte" et pas déjà inscrit
        return $sortie->getEtat()->getLibelle() === EtatChangeHelper::ETAT_OUVERTE
            && !$sortie->isParticipant($user);
    }

    public function peutSeDesinscrireASortie(UserInterface $user, Sortie $sortie): bool
    {
        // la sortir doit être en statut "ouverte" et déjà inscrit
        return $sortie->getEtat()->getLibelle() === EtatChangeHelper::ETAT_OUVERTE
            && $sortie->isParticipant($user);
    }



}