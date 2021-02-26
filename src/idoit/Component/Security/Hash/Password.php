<?php

namespace idoit\Component\Security\Hash;

/**
 * i-doit Password hashing compoonente
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Password
{

    /**
     * The salt
     *
     * @var string
     */
    private $salt = null;

    /**
     * Uncrypted Pasword
     *
     * @var string
     */
    private $uncryptedPassword = '';

    /**
     * Password after hashing
     *
     * @var string
     */
    private $hashedPassword = '';

    /**
     * @var int
     */
    private $hashAlgorithm = PASSWORD_BCRYPT;

    /**
     * Creates a password hash using a user specific unique salt
     *
     * @return string
     */
    public function hash()
    {
        $options = [];
        if ($this->salt) {
            $options['salt'] = $this->salt;
        }

        $this->hashedPassword = password_hash($this->uncryptedPassword, $this->hashAlgorithm, $options);

        return $this->hashedPassword;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hashedPassword;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->uncryptedPassword = $password;

        return $this;
    }

    /**
     * Set Hash algorithm
     *
     * @param string $algorithm
     *
     * @return $this
     */
    public function setAlgorithm($algorithm)
    {
        $this->hashAlgorithm = $algorithm;

        return $this;
    }

    /**
     * @param string $salt
     *
     * @return $this
     */
    public function setSalt($salt)
    {
        $this->salt = md5($salt); // md5 is needed

        return $this;
    }

    /**
     * @param array $options
     *
     * @return Password
     */
    public static function instance($options = [])
    {
        $self = new self();

        if (isset($options['salt'])) {
            $self->setSalt($options['salt']);
        }

        if (isset($options['password'])) {
            $self->setPassword($options['password']);
        }

        if (isset($options['algorithm'])) {
            $self->setAlgorithm($options['algorithm']);
        }

        return $self;
    }

}