<?php

namespace App\Helper;

use App\Entity\Etat;
use App\Entity\Sortie;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Ce service aide à gérer les états des sorties
 *
 * Class EtatChangeHelper
 * @package App\Helper
 */
class EtatChangeHelper
{
    private $doctrine;

    // la valeur (string) de ces constantes doit correpondre exactement au libelle de la table etat dans la DB
    const ETAT_CREEE = 'CREEE';         // mais pas ouverte au inscription
    const ETAT_OUVERTE = 'OUVERTE';     // publié on peut s'incrire
    const ETAT_CLOTUREE = 'CLOTUREE';   // le nombre max de participant est atteint
    const ETAT_ACTIVITE_EN_COURS = 'ACTIVITE_EN_COURS';
    const ETAT_PASSEE = 'PASSEE';       // ???
    const ETAT_ANNULEE = 'ANNULEE';     // finalment ça se fait pas
    const ETAT_ARCHIVEE = 'ARCHIVEE';   // date de la sortie passé d'un mois

    /**
     * injection de doctrine dans le service
     *
     * EtatChangeHelper constructor.
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Retourne un objet Etat en fonction de son nom
     *
     * @param string $nom
     * @return Etat|object|null
     */
    public function getEtatByNom(string $nom)
    {
        $etatRepo = $this->doctrine->getRepository(Etat::class);
        $etat = $etatRepo->findOneBy(['libelle' => $nom]);
        return $etat;
    }

    /**
     * Change l'état d'une sortie en bdd
     *
     * @param Sortie $sortie
     * @param string $nouvelEtatNom
     */
    public function changeEtatSortie(Sortie $sortie, string $nouvelEtatNom)
    {
        $nouvelEtat = $this->getEtatByNom($nouvelEtatNom);
        $sortie->setEtat($nouvelEtat);

        $em = $this->doctrine->getManager();
        $em->persist($sortie);
        $em->flush();
    }

    public function updateEtat(Sortie $s)
    {
        if ($this->doitChangerPourOuverte($s)) {
            $this->changeEtatSortie($s, EtatChangeHelper::ETAT_OUVERTE);
        }
        if ($this->doitChangerPourActiviteEnCours($s)) {
            $this->changeEtatSortie($s, EtatChangeHelper::ETAT_ACTIVITE_EN_COURS);
        }
        if ($this->doitChangerPourCloturee($s)) {
            $this->changeEtatSortie($s, EtatChangeHelper::ETAT_CLOTUREE);
        }
        if ($this->doitChangerPourPassee($s)) {
            $this->changeEtatSortie($s, EtatChangeHelper::ETAT_PASSEE);
        }
        if ($this->doitChangerPourArchivee($s)) {
            $this->changeEtatSortie($s, EtatChangeHelper::ETAT_ARCHIVEE);
        }
    }

    /**
     *
     * Retourne un booléen en fonction de si la sortie devrait être archivée
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function doitChangerPourArchivee(Sortie $sortie): bool
    {
        $oneMonthAgo = new \DateTime("-1 month");
        if (
            $sortie->getEtat()->getLibelle() !== self::ETAT_CREEE &&  // est ouverte
            $sortie->getDateHeureFin() < $oneMonthAgo &&    // la date de fin est passée d'un mois
            $sortie->getEtat()->getLibelle() !== self::ETAT_ARCHIVEE    // elle n'est pas déjà archivée
        ) {
            return true;
        }

        return false;
    }


    /**
     *
     * Retourne un booléen en fonction de si la sortie devrait être classée comme "activitée en cours"
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function doitChangerPourActiviteEnCours(Sortie $sortie): bool
    {
        $now = new \DateTime();
        if (
            $sortie->getEtat()->getLibelle() !== self::ETAT_CREEE &&   // elle est créée
            $sortie->getDateHeureDebut() == $now &&  // la date de début c'est maintenant
            $sortie->getEtat()->getLibelle() !== self::ETAT_ANNULEE &&    // elle n'est pas annulée
            $sortie->getEtat()->getLibelle() !== self::ETAT_ACTIVITE_EN_COURS     // elle n'est pas déjà en cours
        ) {
            return true;
        }

        return false;
    }

    public function doitChangerPourPassee(Sortie $sortie): bool
    {
        $now = new \DateTime();
        if (
            $sortie->getDateHeureDebut() < $now &&  // la date de début n'est pas passée
            $sortie->getEtat()->getLibelle() !== self::ETAT_ANNULEE &&    // elle n'est pas annulée
            $sortie->getEtat()->getLibelle() !== self::ETAT_PASSEE     // elle n'est pas déjà passé
        ) {
            return true;
        }

        return false;
    }

    /**
     *
     * Retourne un booléen en fonction de si la sortie devrait être classée comme "cloturee"
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function doitChangerPourCloturee(Sortie $sortie): bool
    {
        $now = new \DateTime();

        if (
            $sortie->getEtat()->getLibelle() !== self::ETAT_CREEE &&  // pas en cours de création
            $sortie->getEtat()->getLibelle() !== self::ETAT_ANNULEE &&  // pas en cours de création
            $sortie->getDateLimiteInscription() < $now               // la date de début  limite est passé
        ) {
            return true;
        }

        return false;
    }

    public function doitChangerPourOuverte(Sortie $sortie): bool
    {
        $now = new \DateTime();

        if (
            $sortie->getEtat()->getLibelle() !== self::ETAT_CREEE &&  // pas en cours de création
            $sortie->getEtat()->getLibelle() !== self::ETAT_OUVERTE &&  // pas en cours de création
            $sortie->getEtat()->getLibelle() !== self::ETAT_ANNULEE &&  // pas en cours de création
            $sortie->getDateLimiteInscription() > $now               // la date de début  limite est passé
        ) {
            return true;
        }

        return false;
    }

    /**
     *
     * Retourne true si la sortie peut être ouverte/publié
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function peutEtreOuverte(Sortie $sortie): bool
    {
        //doit être en statut "créée" pour retourner true
        return $sortie->getEtat()->getLibelle() === self::ETAT_CREEE;
    }

    /**
     *
     * Retourne true si la sortie peut être annulée
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function peutEtreAnnulee(Sortie $sortie): bool
    {
        //doit être en statut "ouverte" ou "creee" pour retourner true
        $lib = $sortie->getEtat()->getLibelle();
        return $lib === self::ETAT_OUVERTE || $lib === self::ETAT_CREEE;
    }



}