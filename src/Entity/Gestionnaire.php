<?php

namespace App\Entity;

use App\Repository\GestionnaireRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GestionnaireRepository::class)
 */
class Gestionnaire extends User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=13)
     */
    private $nci;

    public function __construct()
    {
        $this->setRoles(["ROLE_GESTIONNAIRE"]);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNci(): ?string
    {
        return $this->nci;
    }

    public function setNci(string $nci): self
    {
        $this->nci = $nci;

        return $this;
    }
}
