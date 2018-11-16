<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Encuesta
 *
 * @ORM\Table(name="encuesta", uniqueConstraints={@ORM\UniqueConstraint(name="id", columns={"id"})})
 * @ORM\Entity
 */
class Encuesta
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
     * @ORM\Column(name="idasignatura", type="string", length=10, nullable=false)
     */
    private $idasignatura;

    /**
     * @var string|null
     *
     * @ORM\Column(name="descripcion", type="string", length=128, nullable=true)
     */
    private $descripcion;

    /**
     * @var int|null
     *
     * @ORM\Column(name="curso_academico", type="smallint", nullable=true)
     */
    private $cursoAcademico;

    /**
     * @var string|null
     *
     * @ORM\Column(name="fecha_ini", type="string", length=12, nullable=true)
     */
    private $fechaIni;

    /**
     * @var string|null
     *
     * @ORM\Column(name="fecha_fin", type="string", length=12, nullable=true)
     */
    private $fechaFin;

    /**
     * @var string|null
     *
     * @ORM\Column(name="fecha_cierre", type="string", length=12, nullable=true)
     */
    private $fechaCierre;

    /**
     * @var string|null
     *
     * @ORM\Column(name="gestor", type="string", length=15, nullable=true)
     */
    private $gestor;

    /**
     * @var int|null
     *
     * @ORM\Column(name="estado", type="smallint", nullable=true)
     */
    private $estado;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="modificable", type="boolean", nullable=true)
     */
    private $modificable;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="anonima", type="boolean", nullable=true)
     */
    private $anonima;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="multiconcepto", type="boolean", nullable=true)
     */
    private $multiconcepto;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdasignatura(): ?string
    {
        return $this->idasignatura;
    }

    public function setIdasignatura(string $idasignatura): self
    {
        $this->idasignatura = $idasignatura;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getCursoAcademico(): ?int
    {
        return $this->cursoAcademico;
    }

    public function setCursoAcademico(?int $cursoAcademico): self
    {
        $this->cursoAcademico = $cursoAcademico;

        return $this;
    }

    public function getFechaIni(): ?string
    {
        return $this->fechaIni;
    }

    public function setFechaIni(?string $fechaIni): self
    {
        $this->fechaIni = $fechaIni;

        return $this;
    }

    public function getFechaFin(): ?string
    {
        return $this->fechaFin;
    }

    public function setFechaFin(?string $fechaFin): self
    {
        $this->fechaFin = $fechaFin;

        return $this;
    }

    public function getFechaCierre(): ?string
    {
        return $this->fechaCierre;
    }

    public function setFechaCierre(?string $fechaCierre): self
    {
        $this->fechaCierre = $fechaCierre;

        return $this;
    }

    public function getGestor(): ?string
    {
        return $this->gestor;
    }

    public function setGestor(?string $gestor): self
    {
        $this->gestor = $gestor;

        return $this;
    }

    public function getEstado(): ?int
    {
        return $this->estado;
    }

    public function setEstado(?int $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getModificable(): ?bool
    {
        return $this->modificable;
    }

    public function setModificable(?bool $modificable): self
    {
        $this->modificable = $modificable;

        return $this;
    }

    public function getAnonima(): ?bool
    {
        return $this->anonima;
    }

    public function setAnonima(?bool $anonima): self
    {
        $this->anonima = $anonima;

        return $this;
    }

    public function getMulticoncepto(): ?bool
    {
        return $this->multiconcepto;
    }

    public function setMulticoncepto(?bool $multiconcepto): self
    {
        $this->multiconcepto = $multiconcepto;

        return $this;
    }


}
