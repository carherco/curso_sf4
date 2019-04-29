<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Asignatura
 *
 * @ORM\Table(name="asignatura", uniqueConstraints={@ORM\UniqueConstraint(name="UNIQ_9243D6CE20332D99", columns={"codigo"})}, indexes={@ORM\Index(name="IDX_9243D6CE91A441CC", columns={"grado_id"})})
 * @ORM\Entity
 */
class Asignatura
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
     * @var int
     *
     * @ORM\Column(name="codigo", type="integer", nullable=false)
     */
    private $codigo;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=255, nullable=false)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre_ingles", type="string", length=255, nullable=false)
     */
    private $nombreIngles;

    /**
     * @var int|null
     *
     * @ORM\Column(name="credects", type="integer", nullable=true)
     */
    private $credects;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Grado", inversedBy="asignaturas", cascade={"persist"}))
     * @ORM\JoinColumn(nullable=false)
     */
    private $grado;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Alumno", inversedBy="asignatura", cascade={"persist"})
     * @ORM\JoinTable(name="alumnos_asignaturas",
     *   joinColumns={
     *     @ORM\JoinColumn(name="asignatura_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="alumno_id", referencedColumnName="id")
     *   }
     * )
     */
    private $alumno;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Nota", mappedBy="asignatura")
     * 
     */
    private $notas;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->alumno = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodigo(): ?int
    {
        return $this->codigo;
    }

    public function setCodigo(int $codigo): self
    {
        $this->codigo = $codigo;

        return $this;
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

    public function getNombreIngles(): ?string
    {
        return $this->nombreIngles;
    }

    public function setNombreIngles(string $nombreIngles): self
    {
        $this->nombreIngles = $nombreIngles;

        return $this;
    }

    public function getCredects(): ?int
    {
        return $this->credects;
    }

    public function setCredects(?int $credects): self
    {
        $this->credects = $credects;

        return $this;
    }

    public function getGrado(): ?Grado
    {
        return $this->grado;
    }

    public function setGrado(?Grado $grado): self
    {
        $this->grado = $grado;
        $this->grado->addAsignatura($this);

        return $this;
    }

    /**
     * @return Collection|Alumno[]
     */
    public function getAlumno(): Collection
    {
        return $this->alumno;
    }

    public function addAlumno(Alumno $alumno): self
    {
        if (!$this->alumno->contains($alumno)) {
             $this->alumno[] = $alumno;
        }

        return $this;
    }

    public function removeAlumno(Alumno $alumno): self
    {
        if ($this->alumno->contains($alumno)) {
            $this->alumno->removeElement($alumno);
        }

        return $this;
    }
    
    public function __toString() {
        return $this->nombre;
    }

}
