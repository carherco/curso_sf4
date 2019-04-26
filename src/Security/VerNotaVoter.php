<?php

namespace App\Security;

use App\Entity\Nota;
use App\Entity\Usuario;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Description of EditarEventoVoter
 *
 * @author carlos
 */
class VerNotaVoter extends Voter
{
    protected function supports($atributo, $entidad)
    {
        //dump('Dentro de VerNotaVoter:supports',$entidad);
        
        if (!in_array($atributo, array('ver'))) {
            return false;
        }

        // sólo votar en objetos de tipo Nota dentro de este voter
        if ($entidad instanceof Nota) {
            return true;
        }

        return false;
    }

    protected function voteOnAttribute($atributo, $entidad, TokenInterface $token)
    {
        //dump('Dentro de VerNotaVoter:voteOnAttribute');
        $user = $token->getUser();

        //Gracias al método supports ya sabemos que $entidad es un Evento
//        if($user->getId() == $entidad->getCreador()->getId()){
//          return true;
//        }

        return true;

        
    }

}