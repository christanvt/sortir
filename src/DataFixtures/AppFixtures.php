<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Location;
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
        ini_set('memory_limit', '1024M');
        $this->manager = $manager;
        $this->loadEtat();
        $this->loadVilles();
        $this->loadLieux(2);
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
        $connection = $this->manager->getConnection();
        $stmt = $connection->prepare(file_get_contents(__DIR__ . "/villes_fr.sql"));
        $stmt->execute();
    }
    public function loadLieux(int $num): void
    {
        $faker = Faker\Factory::create('fr_FR');
        $allVille = $this->manager->getRepository(Ville::class)->findAll();
        for ($i = 0; $i < $num; $i++) {
            $lieu = new Lieu();
            $lieu->setNom('Guinguette chez ' . $faker->name());
            $lieu->setRue($faker->streetName);
            $lieu->setVille($faker->randomElement($allVille));
            $this->manager->persist($lieu);
        }
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
        // $fileTypeCheck = './public/img/profils/*.jpg';
        $folderTarget = './public/img/profils/';
        if (file_exists($folderTarget)) {
            foreach (glob("./public/img/profils/*.jpg") as $fileTypeCheck) {
                unlink($fileTypeCheck);
            }
            foreach (glob("./public/img/profils/*.jpeg") as $fileTypeCheck) {
                unlink($fileTypeCheck);
            }
        }

        for ($i = 0; $i < $count; $i++) {

            $participant = new Participant;
            $nom = $faker->lastName();
            $genre = random_int(0, 1);
            if ($genre == 0) {
                $prenom = $faker->firstNameFemale();
                $datas = file_get_contents("https://fakeface.rest/face/json?gender=female&minimum_age=25&maximum_age=40");
                $decodeDatas = json_decode($datas, true);
                $content = file_get_contents($decodeDatas["image_url"]);
                $filename = $decodeDatas["filename"];
                $fp = fopen("./public/img/profils/" . $filename, "w");
                fwrite($fp, $content);
                fclose($fp);
            } else {
                $prenom = $faker->firstNameMale();
                $datas = file_get_contents("https://fakeface.rest/face/json?gender=male&minimum_age=25&maximum_age=40");
                $decodeDatas = json_decode($datas, true);
                $content = file_get_contents($decodeDatas["image_url"]);
                $filename = $decodeDatas["filename"];
                $fp = fopen("./public/img/profils/" . $filename, "w");
                fwrite($fp, $content);
                fclose($fp);
            }
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
                ->setMotpasse($password)
                ->setFilename($filename);
            $this->manager->persist($participant);
        }
        $this->manager->flush();
    }
}
