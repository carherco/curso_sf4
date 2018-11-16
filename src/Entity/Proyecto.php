<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProyectoRepository")
 */
class Proyecto
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="date")
     */
    private $fechaInicio;

    /**
     * @ORM\Column(type="date")
     */
    private $fechaFinPrevista;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $FechaFin;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tipo", inversedBy="proyectos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tipo;

    public function getId()
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

    public function getFechaInicio(): ?\DateTimeInterface
    {
        return $this->fechaInicio;
    }

    public function setFechaInicio(\DateTimeInterface $fechaInicio): self
    {
        $this->fechaInicio = $fechaInicio;

        return $this;
    }

    public function getFechaFinPrevista(): ?\DateTimeInterface
    {
        return $this->fechaFinPrevista;
    }

    public function setFechaFinPrevista(\DateTimeInterface $fechaFinPrevista): self
    {
        $this->fechaFinPrevista = $fechaFinPrevista;

        return $this;
    }

    public function getFechaFin(): ?\DateTimeInterface
    {
        return $this->FechaFin;
    }

    public function setFechaFin(?\DateTimeInterface $FechaFin): self
    {
        $this->FechaFin = $FechaFin;

        return $this;
    }

    public function getTipo(): ?Tipo
    {
        return $this->tipo;
    }

    public function setTipo(?Tipo $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

}
