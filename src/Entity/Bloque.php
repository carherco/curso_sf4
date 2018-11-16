<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Bloque
 *
 * @ORM\Table(name="bloque", uniqueConstraints={@ORM\UniqueConstraint(name="id", columns={"id"})}, indexes={@ORM\Index(name="idencuesta", columns={"idencuesta"})})
 * @ORM\Entity
 */
class Bloque
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
     * @var string|null
     *
     * @ORM\Column(name="descripcion", type="string", length=254, nullable=true)
     */
    private $descripcion;

    /**
     * @var int
     *
     * @ORM\Column(name="orden", type="integer", nullable=false)
     */
    private $orden;

    /**
     * @var \Encuesta
     *
     * @ORM\ManyToOne(targetEntity="Encuesta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idencuesta", referencedColumnName="id")
     * })
     */
    private $idencuesta;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(int $orden): self
    {
        $this->orden = $orden;

        return $this;
    }

    public function getIdencuesta(): ?Encuesta
    {
        return $this->idencuesta;
    }

    public function setIdencuesta(?Encuesta $idencuesta): self
    {
        $this->idencuesta = $idencuesta;

        return $this;
    }


}
