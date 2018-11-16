<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Respuesta
 *
 * @ORM\Table(name="respuesta", uniqueConstraints={@ORM\UniqueConstraint(name="id", columns={"id"})}, indexes={@ORM\Index(name="idpregunta", columns={"idpregunta"})})
 * @ORM\Entity
 */
class Respuesta
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
     * @ORM\Column(name="orden", type="smallint", nullable=false)
     */
    private $orden;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=254, nullable=false)
     */
    private $descripcion;

    /**
     * @var string|null
     *
     * @ORM\Column(name="valor", type="string", length=32, nullable=true)
     */
    private $valor;

    /**
     * @var \Pregunta
     *
     * @ORM\ManyToOne(targetEntity="Pregunta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idpregunta", referencedColumnName="id")
     * })
     */
    private $idpregunta;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getValor(): ?string
    {
        return $this->valor;
    }

    public function setValor(?string $valor): self
    {
        $this->valor = $valor;

        return $this;
    }

    public function getIdpregunta(): ?Pregunta
    {
        return $this->idpregunta;
    }

    public function setIdpregunta(?Pregunta $idpregunta): self
    {
        $this->idpregunta = $idpregunta;

        return $this;
    }


}
