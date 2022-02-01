<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SortieRepository::class)
 */
class Sortie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Merci de renseigner le nom de votre sortie.")
     * @Assert\Length(max=255, maxMessage="Le nom ne peut pas excéder 255 caractères.")
     */
    private $nom;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="Merci de choisir une date pour votre sortie.")
     */
    private ?\DateTimeInterface $dateHeureDebut;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Merci de renseigner une durée en minutes.")
     * @Assert\GreaterThanOrEqual(0)
     */
    private ?int $duree;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="Merci de choisir une date limite d'inscription.")
     */
    private ?\DateTimeInterface $dateLimiteInscription;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThan(0)
     * @Assert\NotBlank(message="Merci d'indiquer le nombre maximal d'inscrits.")
     */
    private ?int $nbInscriptionsMax;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $infosSortie;

    /**
     * @ORM\ManyToOne(targetEntity=Etat::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private Etat $etat;

    /**
     * @ORM\ManyToOne(targetEntity=Participant::class, inversedBy="organisteurDesSorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Participant $organisateur;

    /**
     * @ORM\ManyToMany(targetEntity=Participant::class, mappedBy="inscritAuxSorties")
     */
    private $participants;

    /**
     * @ORM\ManyToOne(targetEntity=Lieu::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Lieu $lieu;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campus;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $motifAnnulation;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeInterface $dateHeureDebut): self
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    /**
     * Calcule la date de fin de la sortie en fonction de sa durée
     *
     * @return \DateTime
     * @throws \Exception
     */
    public function getDateHeureFin(): \DateTimeInterface
    {
        $endDate = clone $this->getDateHeureDebut();

        if ($this->getDuree()){
            $durationInterval = new \DateInterval("PT".$this->getDuree()."H");
            $endDate = $endDate->add($durationInterval);
        }
        else {
            $endDate->setTime(23, 59, 59);
        }
        return $endDate;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeInterface
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeInterface $dateLimiteInscription): self
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionsMax(): ?int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(int $nbInscriptionsMax): self
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(?string $infosSortie): self
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getOrganisateur(): ?Participant
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?UserInterface $organisateur): self
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    /**
     * @return Collection|Participant[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
            $participant->addSorty($this);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        if ($this->participants->removeElement($participant)) {
            $participant->removeSorty($this);
        }

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }


    public function getMotifAnnulation(): ?string
    {
        return $this->motifAnnulation;
    }

    public function setMotifAnnulation(?string $motifAnnulation): self
    {
        $this->motifAnnulation = $motifAnnulation;

        return $this;
    }

    /**
     * Teste si un User est inscrit à cette sortie
     *
     * @param UserInterface $user
     * @return bool
     */
    public function isParticipant(UserInterface $user): bool
    {
        foreach($this->getParticipants() as $p){
            if ($p->getUser() === $user){
                return true;
            }
        }

        return false;
    }

    /**
     * Teste si cette sortie est complète
     *
     * @return bool
     */
    public function isFull(): bool
    {
        if ($this->getNbInscriptionsMax() && $this->getParticipants()->count() >= $this->getNbInscriptionsMax()){
            return true;
        }

        return false;
    }

}
