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
            'Créee',
            'Ouverte',
            'Clôturée',
            "Activité en cour",
            'Passée',
            'Annulée',
            'Historisée',
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
