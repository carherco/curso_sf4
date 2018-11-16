<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Grado
 *
 * @ORM\Table(name="grado")
 * @ORM\Entity(repositoryClass="App\Repository\GradoRepository")
 */
class Grado
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=255, nullable=false)
     */
    private $nombre;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Asignatura", mappedBy="grado", orphanRemoval=true, cascade={"persist"})
     */
    private $asignaturas;

    public function __construct()
    {
        $this->asignaturas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }
    
    public function __toString() {
        return $this->nombre;
    }

    /**
     * @return Collection|Asignatura[]
     */
    public function getAsignaturas(): Collection
    {
        return $this->asignaturas;
    }

    public function addAsignatura(Asignatura $asignatura): self
    {
        if (!$this->asignaturas->contains($asignatura)) {
            $this->asignaturas->add($asignatura);
            $asignatura->setGrado($this);
        }

        return $this;
    }

    public function removeAsignatura(Asignatura $asignatura): self
    {
        if ($this->asignaturas->contains($asignatura)) {
            $this->asignaturas->removeElement($asignatura);
            // set the owning side to null (unless already changed)
            if ($asignatura->getGrado() === $this) {
                $asignatura->setGrado(null);
            }
        }

        return $this;
    }


}
