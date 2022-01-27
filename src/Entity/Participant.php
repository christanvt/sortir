<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


/**
 * @ORM\Entity(repositoryClass=ParticipantRepository::class)
 * @Vich\Uploadable
 */
class Participant implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @var File|null
     * @Assert\Image(
     * mimeTypes="image/jpeg")
     * @Vich\UploadableField(mapping="profil_image", fileNameProperty="filename")
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank()
     */
    private ?string $email;


    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private string $motpasse;

    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\NotBlank(message="Merci de saisir votre nom.")
     * @Assert\Length(max=30, maxMessage="Le nom ne peut pas excéder 30 caractères.")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\NotBlank(message="Merci de saisir votre prénom.")
     * @Assert\Length(max=30, maxMessage="Le prénom ne peut pas excéder 30 caractères.")
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\NotBlank(message="Merci de saisir votre numéro de téléphone.")
     * @Assert\Regex("/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/")
     * @Assert\Length(max=30, maxMessage="Le téléphone ne peut pas excéder 30 chiffres.")
     */
    private $telephone;

    /**
     * @ORM\Column(type="boolean")
     */
    private $administrateur;

    /**
     * @ORM\Column(type="boolean")
     */
    private $actif;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     * @Assert\NotBlank(message="Merci de saisir votre pseudo.")
     * @Assert\Regex(pattern="/^[a-zA-Z0-9_-]+$/",
     *     message="Lettres sans accents, nombres, - et _ acceptés.")
     * @Assert\Length(max=50, maxMessage="Le pseudo ne peut pas excéder 50 caractères.")
     */
    private $pseudo;



    /**
     * @ORM\OneToMany(targetEntity=Sortie::class, mappedBy="organisateur", orphanRemoval=true)
     */
    private $organisteurDesSorties;

    /**
     * @ORM\ManyToMany(targetEntity=Sortie::class, inversedBy="participants")
     */
    private $inscritAuxSorties;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="membres")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campus;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $updated_at;

    public function __construct()
    {
        $this->inscritAuxSorties = new ArrayCollection();
        $this->organisteurDesSorties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getUser(): UserInterface
    {
        return $this;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    public function getMotpasse(): string
    {
        return $this->motpasse;
    }

    public function setMotpasse(string $motpasse): self
    {
        $this->motpasse = $motpasse;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->motpasse;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): self
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function getActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }


    /**
     * @return Collection|Sortie[]
     */
    public function getOrganisteurDesSorties(): Collection
    {
        return $this->organisteurDesSorties;
    }

    public function addOrganisateur(Sortie $organisateur): self
    {
        if (!$this->organisteurDesSorties->contains($organisateur)) {
            $this->organisteurDesSorties[] = $organisateur;
            $organisateur->setOrganisateur($this);
        }

        return $this;
    }

    public function removeOrganisateur(Sortie $organisateur): self
    {
        if ($this->organisteurDesSorties->removeElement($organisateur)) {
            // set the owning side to null (unless already changed)
            if ($organisateur->getOrganisateur() === $this) {
                $organisateur->setOrganisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Sortie[]
     */
    public function getInscritAuxSorties(): Collection
    {
        return $this->inscritAuxSorties;
    }

    public function addSorty(Sortie $sorty): self
    {
        if (!$this->inscritAuxSorties->contains($sorty)) {
            $this->inscritAuxSorties[] = $sorty;
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): self
    {
        $this->inscritAuxSorties->removeElement($sorty);

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

    public function getRoles(): array
    {
        return $this->administrateur ? ['ROLE_ADMIN'] : ['ROLE_USER'];
    }

    /**
     * Get the value of filename
     *
     * @return  string|null
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set the value of filename
     *
     * @param  string|null  $filename
     *
     * @return  self
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get the value of imageFile
     *
     * @return  File|null
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * Set the value of imageFile
     *
     * @param  File|null  $imageFile
     *
     * @return  self
     */
    public function setImageFile($imageFile)
    {
        $this->imageFile = $imageFile;
        if ($this->imageFile instanceof UploadedFile) {
            $this->updated_at = new \DateTimeImmutable('now');
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }


    /**
     * Teste si un User est inscrit à une sortie
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function isInscrit(Sortie $sortie): bool
    {
        foreach($this->getInscritAuxSorties() as $s){
            if ($sortie->getId() === $s->getId()){
                return true;
            }
        }

        return false;
    }
}
