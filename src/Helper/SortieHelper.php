<?php

namespace App\Helper;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Ce service aide à gérer les actions possibles sur les sorties
 *
 * Class SortieHelper
 * @package App\Helper
 */
class SortieHelper
{
    private $doctrine;

    /**
     * injection de doctrine dans le service
     *
     * SortieHelper constructor.
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     *
     * Retourne true si la sortie peut être publie
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function peutEtrePublie(Sortie $sortie, Participant $user): bool
    {
        //doit être en statut "créée" pour retourner true
        return $sortie->getEtat()->getLibelle() === EtatChangeHelper::ETAT_CREEE
            && $sortie->isOrganisteur($user);
    }

    public function peutEtreModifie(Sortie $sortie, Participant $user): bool
    {
        //doit être en statut "créée" pour retourner true
        return $sortie->getEtat()->getLibelle() === EtatChangeHelper::ETAT_CREEE
            && $sortie->isOrganisteur($user);
    }

    public function peutEtreAnnule(Sortie $sortie, Participant $user): bool
    {
        //doit être en statut "créée" pour retourner true
        return ($sortie->getEtat()->getLibelle() === EtatChangeHelper::ETAT_CREEE
            || $sortie->getEtat()->getLibelle() === EtatChangeHelper::ETAT_OUVERTE)
            && $sortie->isOrganisteur($user);
    }

}