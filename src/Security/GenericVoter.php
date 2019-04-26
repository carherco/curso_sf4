<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Security;

use App\Entity\Equipo;
use App\Entity\Usuario;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Este voter tonto hace falta así como está
 */
class GenericVoter extends Voter
{
    protected function supports($atributo, $entidad)
    {
        //dump('Dentro de GenericVoter:supports');
        return true;
    }

    protected function voteOnAttribute($atributo, $entidad, TokenInterface $token)
    {
        //dump('Dentro de GenericVoter:voteOnAttribute');
        return false;
    }
}




