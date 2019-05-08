<?php 

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class Token implements TokenInterface {

    public function __toString() {}


    public function getRoles() {}


    public function getCredentials() {}


    public function getUser() {
      $user = new App\Entity\User();
      return $user;
    }


    public function setUser($user) {}


    public function getUsername() {}

    public function isAuthenticated() {}

    public function setAuthenticated($isAuthenticated) {}

    public function eraseCredentials() {}

    public function getAttributes() {}

    /**
     * Sets the token attributes.
     *
     * @param array $attributes The token attributes
     */
    public function setAttributes(array $attributes) {}

    /**
     * Returns true if the attribute exists.
     *
     * @param string $name The attribute name
     *
     * @return bool true if the attribute exists, false otherwise
     */
    public function hasAttribute($name) {}

    /**
     * Returns an attribute value.
     *
     * @param string $name The attribute name
     *
     * @return mixed The attribute value
     *
     * @throws \InvalidArgumentException When attribute doesn't exist for this token
     */
    public function getAttribute($name) {}

    /**
     * Sets an attribute.
     *
     * @param string $name  The attribute name
     * @param mixed  $value The attribute value
     */
    public function setAttribute($name, $value) {}
}