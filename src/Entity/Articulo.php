<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Articulo
 *
 * @ORM\Table(name="articulo")}
 * @ORM\Entity
 */
class Articulo
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
     * @ORM\Column(name="titulo", type="string", length=255, nullable=false)
     */
    private $titulo;
    
    /**
     * @var string
     *
     * @ORM\Column(name="contenido", type="string", length=255, nullable=false)
     */
    private $contenido;
    
    /**
     * Esta es la propiedad utilizada por el marking_store
     * 
     * @var array
     *
     * @ORM\Column(type="string", nullable=true) 
     */
    private $estado;
         
    public function __construct() {
        
    }
      
    public function getId() {
        return $this->id;
    }

    public function getTitulo() {
        return $this->titulo;
    }

    public function getContenido() {
        return $this->contenido;
    }

    public function getEstado() {
        return $this->estado;
    }


    public function setTitulo($titulo) {
        $this->titulo = $titulo;
        return $this;
    }

    public function setContenido($contenido) {
        $this->contenido = $contenido;
        return $this;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
        return $this;
    }


}
