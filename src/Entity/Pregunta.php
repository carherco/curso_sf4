<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pregunta
 *
 * @ORM\Table(name="pregunta", uniqueConstraints={@ORM\UniqueConstraint(name="id", columns={"id"})}, indexes={@ORM\Index(name="idencuesta", columns={"idencuesta"}), @ORM\Index(name="idbloque", columns={"idbloque"}), @ORM\Index(name="idtipo", columns={"idtipo"})})
 * @ORM\Entity
 */
class Pregunta
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
     * @var string|null
     *
     * @ORM\Column(name="descripcion", type="string", length=254, nullable=true)
     */
    private $descripcion;

    /**
     * @var string|null
     *
     * @ORM\Column(name="pie", type="string", length=254, nullable=true)
     */
    private $pie;

    /**
     * @var int
     *
     * @ORM\Column(name="salida", type="integer", nullable=false)
     */
    private $salida;

    /**
     * @var \Encuesta
     *
     * @ORM\ManyToOne(targetEntity="Encuesta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idencuesta", referencedColumnName="id")
     * })
     */
    private $idencuesta;

    /**
     * @var \Bloque
     *
     * @ORM\ManyToOne(targetEntity="Bloque")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idbloque", referencedColumnName="id")
     * })
     */
    private $idbloque;

    /**
     * @var \TipoPregunta
     *
     * @ORM\ManyToOne(targetEntity="TipoPregunta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idtipo", referencedColumnName="id")
     * })
     */
    private $idtipo;

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

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getPie(): ?string
    {
        return $this->pie;
    }

    public function setPie(?string $pie): self
    {
        $this->pie = $pie;

        return $this;
    }

    public function getSalida(): ?int
    {
        return $this->salida;
    }

    public function setSalida(int $salida): self
    {
        $this->salida = $salida;

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

    public function getIdbloque(): ?Bloque
    {
        return $this->idbloque;
    }

    public function setIdbloque(?Bloque $idbloque): self
    {
        $this->idbloque = $idbloque;

        return $this;
    }

    public function getIdtipo(): ?TipoPregunta
    {
        return $this->idtipo;
    }

    public function setIdtipo(?TipoPregunta $idtipo): self
    {
        $this->idtipo = $idtipo;

        return $this;
    }


}
