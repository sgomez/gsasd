<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 19/11/13
 * Time: 23:23
 */

namespace Uco\ConsignaBundle\Security\User;

use Doctrine\Common\Persistence\ManagerRegistry;
use FOS\UserBundle\Model\UserInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Uco\ConsignaBundle\Entity\User;


class DropboxUserProvider extends EntityUserProvider
{
    protected $em;

    public function __construct(ManagerRegistry $registry, $class, array $properties, $managerName = null)
    {
        parent::__construct($registry, $class, $properties, $managerName);
        $this->em = $registry->getManager($managerName);
    }


    public function refreshUser(UserInterface $user)
    {
        return $user;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        try {
            return parent::loadUserByOAuthUserResponse($response);
        } catch (UsernameNotFoundException $e) {
            $rawResponse = $response->getResponse();

            $user = new User($rawResponse['uid']);
            $user->setUsername($rawResponse['uid']);
            $user->setUid($rawResponse['uid']);
            $user->setEmail($rawResponse['email']);
            $user->setDisplayName($rawResponse['display_name']);
            $user->setEnabled(true);
            $user->setPassword(md5(rand()));
            $this->em->persist($user);
            $this->em->flush();

            return $user;
        }
    }


    public function supportsClass($class)
    {
        return $class === 'Uco\ConsignaBundle\Entity\User';
    }
}