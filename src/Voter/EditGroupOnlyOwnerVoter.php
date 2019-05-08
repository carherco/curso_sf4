<?php

namespace App\Voter;
use App\Entity\Group;

class EditGroupOnlyOwnerVoter {

  public function supports($attribute, $entity) {
    return ($attribute === 'edit' && $entity instanceof Group);
  }

  public function voteOnAttribute($attribute, $entity, $token) {  
    dump($token->getUser());
    dump($entity->getOwner());
    return $token->getUser() == $entity->getOwner();
  }
}