<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->loadEtat();
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
}
