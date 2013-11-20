<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 19/11/13
 * Time: 23:23
 */

namespace Uco\ConsignaBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Uco\ConsignaBundle\Entity\User;
use Uco\ConsignaBundle\Util\ConsignaValueStore;
use Doctrine\Bundle\DoctrineBundle\Registry;

class DropboxUserProvider implements UserProviderInterface
{
    private $cltid = "ConsignaDB/1.0";
    private $em;

    /**
     * @param $doctrine Registry
     */
    function __construct($doctrine)
    {
        $this->em = $doctrine->getManager();
    }


    public function loadUserByUsername($username)
    {
        // make a call to your webservice here
        $userData = 1;
        // pretend it returns an array on success, false if there is no user

        if ($userData) {
            $password = '...';

            // ...

            return new User($username, $password, $salt, $roles);
        }

        throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Uco\ConsignaBundle\Entity\User';
    }
} 