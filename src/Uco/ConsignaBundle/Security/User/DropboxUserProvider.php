<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 19/11/13
 * Time: 23:23
 */

namespace Uco\ConsignaBundle\Security\User;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Uco\ConsignaBundle\Entity\User;

class DropboxUserProvider extends OAuthUserProvider
{
    protected $session;
    protected $manager;
    protected $container;
    protected $userRepository;

    function __construct($session, $doctrine, $service_container)
    {
        $this->session = $session;
        $this->manager = $doctrine->getManager();
        $this->userRepository = $doctrine->getManager()->getRepository('UcoConsignaBundle:User');
        $this->container = $service_container;
    }

    /**
     * Loads the user by a given UserResponseInterface object.
     *
     * @param UserResponseInterface $response
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $attr = $response->getResponse();

        if (!$user = $this->userRepository->findOneByEmail($attr['email'])) {

            $user = new User();
            $user->setDisplayName($attr['display_name']);
            $user->setUid($attr['uid']);
            $user->setEmail($attr['email']);
            $this->manager->persist($user);
        }

        if (null === $user) {
            throw new UsernameNotFoundException(sprintf("User '%s' not found.", $attr['email']));
        }

        $this->manager->flush();

        return $user;
    }

    public function supportsClass($class)
    {
        return $class === 'Uco\\ConsignaBundle\\Entity\\User';
    }


}