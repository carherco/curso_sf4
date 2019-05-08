<?php

namespace App\Tests\Voter;

use PHPUnit\Framework\TestCase;
use App\Voter\EditGroupOnlyOwnerVoter;
use App\Entity\Group;
use App\Entity\Post;
use App\Entity\User;
use \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class EditGroupOnlyOwnerVoterTest extends TestCase
{
    private $voter;
    private $group; 

    public function setup() {
      $this->voter = new EditGroupOnlyOwnerVoter();
      $this->group = new Group();
    }

    public function testSupportsAttributeEditAndEntityGroup() {  
      $result = $this->voter->supports('edit', $this->group);
      $this->assertTrue($result);
    }

    public function testNotSupportsAttributeCreateAndEntityGroup() {
      $result = $this->voter->supports('create', $this->group);
      $this->assertFalse($result);
    }

    public function testNotSupportsAttributeDeleteAndEntityGroup() {
      $result = $this->voter->supports('delete', $this->group);
      $this->assertFalse($result);
    }

    public function testNotSupportsAttributeHiddenAndEntityGroup() {
      $result = $this->voter->supports('hidden', $this->group);
      $this->assertFalse($result);
    }

    public function testSupportsOnlyGroup() {
      $notGroup = new Post();
      $result = $this->voter->supports('edit', $notGroup);
      $this->assertFalse($result);
    }

    public function testVoteTrueWhenUserIsOwner() {

      $user = new User();
      $group = new Group($user);
      $mock = $this->createMock(UsernamePasswordToken::class);
      $mock->method('getUser')
           ->willReturn($user);
      
      $result = $this->voter->voteOnAttribute('edit', $this->group, $mock);

      $this->assertTrue($result);
    }

    public function testVoteTrueWhenUserIsNotOwner() {

      $user = new User();
      $user2 = new User();
      $group = new Group($user);

      $mock = $this->createMock(UsernamePasswordToken::class);
      $mock->method('getUser')
           ->willReturn($user2);
      
      $result = $this->voter->voteOnAttribute('edit', $this->group, $mock);

      $this->assertFalse($result);
    }

}


// 'create', 'delete', 'edit', 'hidden'
