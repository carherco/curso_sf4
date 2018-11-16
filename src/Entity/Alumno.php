<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Alumno
 *
 * @ORM\Table(name="alumno")
 * @ORM\Entity
 */
class Alumno
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
     * @ORM\Column(name="n_expediente", type="integer", nullable=false)
     */
    private $nExpediente;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=255, nullable=false)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="apellidos", type="string", length=255, nullable=false)
     */
    private $apellidos;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="fecha_nacimiento", type="date", nullable=true)
     */
    private $fechaNacimiento;

    /**
     * @var bool
     *
     * @ORM\Column(name="sexo", type="boolean", nullable=false)
     */
    private $sexo;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="telefono", type="string", length=255, nullable=true)
     */
    private $telefono;

    /**
     * @var \Grado
     *
     * @ORM\ManyToOne(targetEntity="Grado")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grado_id", referencedColumnName="id")
     * })
     */
    private $grado;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Asignatura", mappedBy="alumno", cascade={"persist"})
     */
    private $asignatura;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->asignatura = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNExpediente(): ?int
    {
        return $this->nExpediente;
    }

    public function setNExpediente(int $nExpediente): self
    {
        $this->nExpediente = $nExpediente;

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

    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }

    public function setApellidos(string $apellidos): self
    {
        $this->apellidos = $apellidos;

        return $this;
    }

    public function getFechaNacimiento(): ?\DateTimeInterface
    {
        return $this->fechaNacimiento;
    }

    public function setFechaNacimiento(?\DateTimeInterface $fechaNacimiento): self
    {
        $this->fechaNacimiento = $fechaNacimiento;

        return $this;
    }

    public function getSexo(): ?bool
    {
        return $this->sexo;
    }

    public function setSexo(bool $sexo): self
    {
        $this->sexo = $sexo;

        return $this;
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

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): self
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getGrado(): ?Grado
    {
        return $this->grado;
    }

    public function setGrado(?Grado $grado): self
    {
        $this->grado = $grado;

        return $this;
    }

    /**
     * @return Collection|Asignatura[]
     */
    public function getAsignatura(): Collection
    {
        return $this->asignatura;
    }

    public function addAsignatura(Asignatura $asignatura): self
    {
        //if (!$this->asignatura->contains($asignatura)) {
            $this->asignatura[] = $asignatura;
            $asignatura->addAlumno($this);
        //}

        return $this;
    }

    public function removeAsignatura(Asignatura $asignatura): self
    {
        if ($this->asignatura->contains($asignatura)) {
            $this->asignatura->removeElement($asignatura);
            $asignatura->removeAlumno($this);
        }

        return $this;
    }
    
    public function __toString() {
        return $this->nombre;
    }

}
