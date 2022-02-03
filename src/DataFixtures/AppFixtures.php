<?php

namespace App\DataFixtures;

use App\Helper\EtatChangeHelper;
use Faker;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Ville;
use App\Entity\Campus;
use App\Entity\Sortie;
use DateTimeImmutable;
use App\Entity\Participant;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    private $encoder;
    private $etatHelper;

    public function __construct(UserPasswordHasherInterface $encoder, EtatChangeHelper $etatHelper)
    {
        $this->encoder = $encoder;
        $this->etatHelper = $etatHelper;
    }

    public function load(ObjectManager $manager): void
    {
        ini_set('memory_limit', '1024M');
        $this->manager = $manager;
        $this->loadEtat();
        $this->loadVilles();
        $this->loadLieux(1);
        $this->loadCampus();
        $this->loadParticipants(20);
        $this->loadAdmin();
        $this->loadDevUser();
        $this->loadSebastienBaudin();
        $this->loadSorties(30);
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
        try {
            $connection = $this->manager->getConnection();
            $stmt = $connection->prepare(file_get_contents(__DIR__ . "/villes_fr.sql"));
            $stmt->execute();
        } catch (Exception $exception) {
            echo "⚠️ Une erreur est survenue pendant l'injection des données du fichier villes_fr.sql. Raison : " . $exception->getMessage();
            throw $exception;
        }
    }


    public function loadLieux(int $num): void
    {
        $ville = $this->manager->getRepository(Ville::class)->findOneBy(['nom' => "Nantes"]);

        for ($i = 0; $i < $num; $i++) {
            $lieu = new Lieu();
            $lieu->setNom("L'Hâchez-vous")
                ->setRue("Parc du Moulin Neuf, 15 Av. Jacques Cartier ")
                ->setVille($ville)
                ->setLatitude(47.25038459106548)
                ->setLongitude(-1.6436333992611676);
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

    public function loadParticipants(int $count): void
    {
        $faker = Faker\Factory::create('fr_FR');
        $allCampus = $this->manager->getRepository(Campus::class)->findAll();
        $folderTarget = './public/';
        if (file_exists($folderTarget)) {
            foreach (glob("./public/img/*/*.jpg") as $fileTypeCheck) {
                unlink($fileTypeCheck);
            }
            foreach (glob("./public/img/*/*.jpeg") as $fileTypeCheck) {
                unlink($fileTypeCheck);
            }
            foreach (glob("./public/media/cache/thumb/img/profils/*.jpeg") as $fileTypeCheck) {
                unlink($fileTypeCheck);
            }
            foreach (glob("./public/media/cache/thumb/img/profils/*.jpg") as $fileTypeCheck) {
                unlink($fileTypeCheck);
            }
            foreach (glob("./public/media/cache/accu/img/profils/*.jpeg") as $fileTypeCheck) {
                unlink($fileTypeCheck);
            }
            foreach (glob("./public/media/cache/accu/img/profils/*.jpg") as $fileTypeCheck) {
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
                try {
                    $fp = fopen("./public/img/profils/" . $filename, "w");
                    fwrite($fp, $content);
                    fclose($fp);
                } catch (Exception $exception) {
                    echo "⚠️ Une erreur est survenue pendant la création de l'image de profile . Raison : " . $exception->getMessage();
                    fclose($fp);
                    throw $exception;
                }
            } else {
                $prenom = $faker->firstNameMale();
                $datas = file_get_contents("https://fakeface.rest/face/json?gender=male&minimum_age=25&maximum_age=40");
                $decodeDatas = json_decode($datas, true);
                $content = file_get_contents($decodeDatas["image_url"]);
                $filename = $decodeDatas["filename"];
                try {
                    $fp = fopen("./public/img/profils/" . $filename, "w");
                    fwrite($fp, $content);
                    fclose($fp);
                } catch (Exception $exception) {
                    echo "⚠️ Une erreur est survenue pendant la création de l'image de profile . Raison : " . $exception->getMessage();
                    fclose($fp);
                    throw $exception;
                }
            }
            $pseudo = $prenom . $nom;
            $administrateur = 0;
            $actif = 1;
            $telephone = $faker->mobileNumber();
            $mailDomain = $faker->freeEmailDomain();
            $email = $prenom . '.' . $nom . '@' . $mailDomain;
            $campus = $faker->randomElement($allCampus);
            $password = $this->encoder->hashPassword($participant, $pseudo);
            $date = new DateTimeImmutable('now');

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
                ->setFilename($filename)
                ->setUpdatedAt($date);
            $this->manager->persist($participant);
        }
        $this->manager->flush();
    }

    public function loadSebastienBaudin(): void
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
        try {
            $fp = fopen("./public/img/profils/" . $filename, "w");
            fwrite($fp, $content);
            fclose($fp);
        } catch (Exception $exception) {
            echo "⚠️ Une erreur est survenue pendant la création de l'image de profile . Raison : " . $exception->getMessage();
            fclose($fp);
            throw $exception;
        }
        $date = new DateTimeImmutable('now');

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
            ->setFilename($filename)
            ->setUpdatedAt($date);
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
        $content = file_get_contents("https://www.lense.fr/wp-content/uploads/2014/06/steve-jobs-albert-watson-bw.jpg");
        $filename = "image.jpg";
        try {
            $fp = fopen("./public/img/profils/" . $filename, "w");
            fwrite($fp, $content);
            fclose($fp);
        } catch (Exception $exception) {
            echo "⚠️ Une erreur est survenue pendant la création de l'image de profile . Raison : " . $exception->getMessage();
            fclose($fp);
            throw $exception;
        }
        $date = new DateTimeImmutable('now');
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
            ->setFilename($filename)
            ->setUpdatedAt($date);
        $this->manager->persist($participant);
        $this->manager->flush();
    }

    public function loadDevUser(): void
    {
        $participant = new Participant;
        $nom = "user";
        $prenom = "user";
        $pseudo = "user";
        $administrateur = 0;
        $actif = 1;
        $telephone = "0123456789";
        $email = "user@user.fr";
        $nomCampus = "SAINT-HERBLAIN";
        $campus = $this->manager->getRepository(Campus::class)->findOneBy(['nom' => $nomCampus]);
        $password = $this->encoder->hashPassword($participant, 'user');
        $content = file_get_contents("https://www.lense.fr/wp-content/uploads/2014/06/steve-jobs-albert-watson-bw.jpg");
        $filename = "image.jpg";
        try {
            $fp = fopen("./public/img/profils/" . $filename, "w");
            fwrite($fp, $content);
            fclose($fp);
        } catch (Exception $exception) {
            echo "⚠️ Une erreur est survenue pendant la création de l'image de profile . Raison : " . $exception->getMessage();
            fclose($fp);
            throw $exception;
        }
        $date = new DateTimeImmutable('now');
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
            ->setFilename($filename)
            ->setUpdatedAt($date);
        $this->manager->persist($participant);
        $this->manager->flush();
    }

    public function loadSorties(int $count): void
    {
        $faker = Faker\Factory::create('fr_FR');
        $allOganisateurs = $this->manager->getRepository(Participant::class)->findAll();
        $allLieux = $this->manager->getRepository(Lieu::class)->findAll();
        $allEtats = $this->manager->getRepository(Etat::class)->findAll();



        for ($i = 0; $i < $count; $i++) {
            $sortie = new Sortie;

            $dateDebut = $faker->dateTimeBetween($startDate = "- 10 months", "+ 10 months");
            $dateInterval = clone $dateDebut;
            $dateInterval->add(new \DateInterval("P33D"));

            $durée = random_int(30, 180);
            $dateHeureDébut = $faker->dateTimeBetween($dateDebut, $dateInterval);
            $tmp1 = clone $dateHeureDébut;
            $tmp2 = clone $dateHeureDébut;
            $dateLimitInscription = $faker->dateTimeBetween( $tmp1->sub(new \DateInterval("P8D")),  $tmp2->sub(new \DateInterval("P1D")));
            $nbrMaxParticipants = random_int(2, 24);;
            $organisateur = $faker->randomElement($allOganisateurs);
            $lieu = $faker->randomElement($allLieux);
            $nom = "Sortie " . $i . " - " . $lieu->getNom();
            $infos = "Je vous donne rendez vous au " . $nom . " adresse :  " . $lieu->getRue() . " " . $lieu->getVille();
            $campus = $organisateur->getCampus();
            $etat = $faker->randomElement($allEtats);
            $sortie
                ->setNom($nom)
                ->setInfosSortie($infos)
                ->setDateHeureDebut($dateHeureDébut)
                ->setDuree($durée)
                ->setDateLimiteInscription($dateLimitInscription)
                ->setNbInscriptionsMax($nbrMaxParticipants)
                ->setOrganisateur($organisateur)
                ->setCampus($campus)
                ->setEtat($etat)
                ->setLieu($lieu);
            $this->manager->persist($sortie);
        }
        $this->manager->flush();

        //MAJ des états
        $sortieRepo = $this->manager->getRepository(Sortie::class);
        $sorties = $sortieRepo->findAll();
        foreach ($sorties as $s) {
            if ($this->etatHelper->devraitChangerPourCloturee($s)) {
                $this->etatHelper->changeEtatSortie($s, EtatChangeHelper::ETAT_CLOTUREE);
                continue;
            }
            if ($this->etatHelper->doitChangerPourArchivee($s)) {
                $this->etatHelper->changeEtatSortie($s, EtatChangeHelper::ETAT_ARCHIVEE);
                continue;
            }
        }
    }
}
