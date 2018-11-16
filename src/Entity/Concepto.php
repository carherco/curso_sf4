<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Concepto
 *
 * @ORM\Table(name="concepto", uniqueConstraints={@ORM\UniqueConstraint(name="id", columns={"id"})})
 * @ORM\Entity
 */
class Concepto
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="idencuesta", type="integer", nullable=false)
     */
    private $idencuesta;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo", type="string", length=32, nullable=false)
     */
    private $codigo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="descripcion", type="string", length=254, nullable=true)
     */
    private $descripcion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdencuesta(): ?int
    {
        return $this->idencuesta;
    }

    public function setIdencuesta(int $idencuesta): self
    {
        $this->idencuesta = $idencuesta;

        return $this;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): self
    {
        $this->codigo = $codigo;

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


}
