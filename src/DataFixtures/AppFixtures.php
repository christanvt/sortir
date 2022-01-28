<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Faker;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Ville;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    private $encoder;
    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->loadEtat();
        $this->loadVilles();
        $this->loadLieux();
        $this->loadCampus();
        $this->loadAdmin();
        $this->loadMeSebastienBaudin();
        $this->loadParticipants(10);
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
    public function loadCampus(): void
    {
        $campusNomsArray = [
            'SAINT-HERBLAIN',
            'CHARTRES DE BRETAGNE',
            'LA ROCHE SUR YON',
        ];
        for ($i = 0; $i < count($campusNomsArray); $i++) {
            $nom = $campusNomsArray[$i];
            $campus = new Campus();
            $campus->setNom($nom);
            $this->manager->persist($campus);
        }
        $this->manager->flush();
    }
    public function loadMeSebastienBaudin(): void
    {
        $participant = new Participant;
        $nom = "Baudin";
        $prenom = "Sébastien";
        $pseudo = "bod";
        $administrateur = 0;
        $actif = 1;
        $telephone = "0123456789";
        $email = "sebastien.baudin2021@campus-eni.fr";
        $nomCampus = "SAINT-HERBLAIN";
        $campus = $this->manager->getRepository(Campus::class)->findOneBy(['nom' => $nomCampus]);
        $password = $this->encoder->hashPassword($participant, $pseudo);
        $content = file_get_contents("https://avatars.githubusercontent.com/u/4048286?v=4");
        $filename = 'sebastienbaudin2021.jpeg';
        $fp = fopen("./public/img/profils/" . $filename, "w");
        fwrite($fp, $content);
        fclose($fp);

        $participant
            ->setNom($nom)
            ->setPrenom($prenom)
            ->setPseudo($pseudo)
            ->setAdministrateur($administrateur)
            ->setActif($actif)
            ->setTelephone($telephone)
            ->setEmail($email)
            ->setCampus($campus)
            ->setMotpasse($password)
            ->setFilename($filename);
        $this->manager->persist($participant);
        $this->manager->flush();
    }
    public function loadAdmin(): void
    {
        $participant = new Participant;
        $nom = "ADMIN";
        $prenom = "ADMIN";
        $pseudo = "ADMIN";
        $administrateur = 1;
        $actif = 1;
        $telephone = "0123456789";
        $email = "admin@admin.fr";
        $nomCampus = "SAINT-HERBLAIN";
        $campus = $this->manager->getRepository(Campus::class)->findOneBy(['nom' => $nomCampus]);
        $password = $this->encoder->hashPassword($participant, 'admin');
        $content = file_get_contents("https://www.numerama.com/content/uploads/2015/10/chat-680x680.jpg");
        $filename = "image.jpeg";
        $fp = fopen("./public/img/profils/" . $filename, "w");
        fwrite($fp, $content);
        fclose($fp);
        $participant
            ->setNom($nom)
            ->setPrenom($prenom)
            ->setPseudo($pseudo)
            ->setAdministrateur($administrateur)
            ->setActif($actif)
            ->setTelephone($telephone)
            ->setEmail($email)
            ->setCampus($campus)
            ->setMotpasse($password)
            ->setFilename($filename);
        $this->manager->persist($participant);
        $this->manager->flush();
    }
    public function loadParticipants(int $count): void
    {
        $faker = Faker\Factory::create('fr_FR');
        for ($i = 0; $i < $count; $i++) {

            $participant = new Participant;
            $nom = $faker->lastName();
            $prenom = $faker->firstname();
            $pseudo = $prenom . '.' . $nom;
            $administrateur = 0;
            $actif = 1;
            $telephone = $faker->mobileNumber();
            $mailDomain = $faker->freeEmailDomain();
            $email = $prenom . '.' . $nom . '@' . $mailDomain;
            $campus = $this->manager->getRepository(Campus::class)->findAll()[random_int(0, 2)];
            $password = $this->encoder->hashPassword($participant, $pseudo);
            $participant
                ->setNom($nom)
                ->setPrenom($prenom)
                ->setPseudo($pseudo)
                ->setAdministrateur($administrateur)
                ->setActif($actif)
                ->setTelephone($telephone)
                ->setEmail($email)
                ->setCampus($campus)
                ->setMotpasse($password);
            $this->manager->persist($participant);
        }
        $this->manager->flush();
    }
}
