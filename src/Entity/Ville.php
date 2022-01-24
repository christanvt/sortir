<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 *
 * @ORM\Entity(repositoryClass="VilleRepository")
 */
class Ville implements \JsonSerializable  //le implements \JsonSerializable permet de définir les données restituées si on json_encode une entité de cette classe
{
    //les données retournées ici seront sérialisée en json si on appelle json_encode sur cette classe
    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "nom" => $this->getNom(),
            "codePostal" => $this->getCodePostal(),
        ];
    }

    /**
     * Cette méthode sera appelée si on fait un "echo" sur la classe elle-même
     * Utile dans les formulaires avec le champ EntityType
     *
     * @return mixed
     */
    public function __toString()
    {
        return $this->nom . " " . $this->codePostal;
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nom;
    
    /**
     * @ORM\Column(type="string", length=3)
     */
    private $codePostal;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Lieu", mappedBy="ville")
     */
    private $lieux;

    public function __construct()
    {
        $this->lieux = new ArrayCollection();
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

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }



    /**
     * @return Collection|Lieu[]
     */
    public function getLieux(): Collection
    {
        return $this->lieux;
    }

    public function addLieu(Lieu $lieu): self
    {
        if (!$this->lieux->contains($lieu)) {
            $this->lieux[] = $lieu;
            $lieu->setVille($this);
        }

        return $this;
    }

    public function removeLieu(Lieu $lieu): self
    {
        if ($this->lieux->contains($lieu)) {
            $this->lieux->removeElement($lieu);
            // set the owning side to null (unless already changed)
            if ($lieu->getVille() === $this) {
                $lieu->setVille(null);
            }
        }

        return $this;
    }
}
