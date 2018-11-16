<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Resultado
 *
 * @ORM\Table(name="resultado", uniqueConstraints={@ORM\UniqueConstraint(name="id", columns={"id"})}, indexes={@ORM\Index(name="idencuesta", columns={"idencuesta"}), @ORM\Index(name="idpregunta", columns={"idpregunta"}), @ORM\Index(name="idrespuesta", columns={"idrespuesta"})})
 * @ORM\Entity
 */
class Resultado
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
     * @ORM\Column(name="idconcepto", type="string", length=255, nullable=false)
     */
    private $idconcepto;

    /**
     * @var string|null
     *
     * @ORM\Column(name="dni", type="string", length=15, nullable=true)
     */
    private $dni;

    /**
     * @var string|null
     *
     * @ORM\Column(name="valor", type="text", length=65535, nullable=true)
     */
    private $valor;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="fecha_auto", type="datetime", nullable=true)
     */
    private $fechaAuto;

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
     * @var \Pregunta
     *
     * @ORM\ManyToOne(targetEntity="Pregunta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idpregunta", referencedColumnName="id")
     * })
     */
    private $idpregunta;

    /**
     * @var \Respuesta
     *
     * @ORM\ManyToOne(targetEntity="Respuesta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idrespuesta", referencedColumnName="id")
     * })
     */
    private $idrespuesta;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdconcepto(): ?string
    {
        return $this->idconcepto;
    }

    public function setIdconcepto(string $idconcepto): self
    {
        $this->idconcepto = $idconcepto;

        return $this;
    }

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function setDni(?string $dni): self
    {
        $this->dni = $dni;

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

    public function getFechaAuto(): ?\DateTimeInterface
    {
        return $this->fechaAuto;
    }

    public function setFechaAuto(?\DateTimeInterface $fechaAuto): self
    {
        $this->fechaAuto = $fechaAuto;

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

    public function getIdpregunta(): ?Pregunta
    {
        return $this->idpregunta;
    }

    public function setIdpregunta(?Pregunta $idpregunta): self
    {
        $this->idpregunta = $idpregunta;

        return $this;
    }

    public function getIdrespuesta(): ?Respuesta
    {
        return $this->idrespuesta;
    }

    public function setIdrespuesta(?Respuesta $idrespuesta): self
    {
        $this->idrespuesta = $idrespuesta;

        return $this;
    }


}
