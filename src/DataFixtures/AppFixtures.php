<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->loadEtat();
        $this->loadVilles();
        $this->loadLieux();
    }
    public function loadEtat(): void
    {
        $etatNomsArray = [
            'CREEE',
            'OUVERTE',
            'CLOTUREE',
            'ACTIVITE_EN_COURS',
            'PASSEE',
            'ANNULEE',
            'ARCHIVEE',
        ];
        for ($i = 0; $i < count($etatNomsArray); $i++) {
            $nom = $etatNomsArray[$i];
            $etat = new Etat();
            $etat->setLibelle($nom);
            $this->manager->persist($etat);
        }
        $this->manager->flush();
    }
    public function loadVilles(): void
    {
        $villesNomsArray = [
            'SAINT-HERBLAIN',
            'CHARTRES DE BRETAGNE',
            'LA ROCHE SUR YON',
        ];
        $villesCodesArray = [
            '44800',
            '35131',
            '85000',
        ];
        for ($i = 0; $i < count($villesNomsArray); $i++) {
            $nom = $villesNomsArray[$i];
            $code = $villesCodesArray[$i];
            $ville = new Ville();
            $ville
                ->setNom($nom)
                ->setCodePostal($code);
            $this->manager->persist($ville);
        }
        $this->manager->flush();
    }
    public function loadLieux(): void
    {
        $nom = "Le Coin";
        $rue = "28 Route de Vannes";
        $nomVille = 'SAINT-HERBLAIN';
        $ville = $this->manager->getRepository(Ville::class)->findOneBy(['nom' => $nomVille]);
        $longitude = "47.24574087955902";
        $latitude = "-1.601311456115227";
        $lieu = new Lieu;
        $lieu
            ->setNom($nom)
            ->setRue($rue)
            ->setVille($ville)
            ->setLongitude($longitude)
            ->setLatitude($latitude);
        $this->manager->persist($lieu);
        $this->manager->flush();
    }
}
