<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LieuRepository")
 */
class Lieu implements \JsonSerializable
{
    // classe en JSON pour l'API
    public function jsonSerialize()
    {
        $v = $this->getVille();
        return [
            "id" => $this->getId(),
            "nom" => $this->getNom(),
            "rue" => $this->getRue(),
            "codePostal" => $v->getCodePostal(),
            "ville" => $v->getNom(),
            "lat" => $this->getLatitude(),
            "lng" => $this->getLongitude()
        ];
    }

    /**
     * Comment doit-être convertie cette classe en chaîne ?
     *
     * @return string
     */
    public function __toString()
    {
        return $this->nom . "<br>". $this->rue . " " . $this->ville;
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message="Merci de saisir le nom du lieu de la sortie.")
     * @Assert\Length(max=100, maxMessage="Le nom ne peut pas excéder 100 caractères.")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Merci de saisir la rue associée.")
     * @Assert\Length(max=255, maxMessage="La rue ne peut pas excéder 255 caractères.")
     */
    private $rue;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $longitude;

    /**
     * @ORM\ManyToOne(targetEntity=Ville::class, inversedBy="lieus")
     * @ORM\JoinColumn(nullable=false)
     */
    private Ville $ville;

    /**
     * @ORM\OneToMany(targetEntity=Sortie::class, mappedBy="lieu")
     */
    private $sorties;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
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

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(string $rue): self
    {
        $this->rue = $rue;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getCodePostal(): string
    {
        return $this->ville->getCodePostal();
    }

    public function getVille(): Ville
    {
        return $this->ville;
    }

    public function setVille(Ville $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * @return Collection|Sortie[]
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): self
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties[] = $sorty;
            $sorty->setLieu($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): self
    {
        if ($this->sorties->removeElement($sorty)) {
            // set the owning side to null (unless already changed)
            if ($sorty->getLieu() === $this) {
                $sorty->setLieu(null);
            }
        }

        return $this;
    }

}
