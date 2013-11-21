<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 21/11/13
 * Time: 12:10
 */

namespace Uco\ConsignaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

/**
 * @Route("/profile")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="profile")
     * @Template()
     */
    public function indexAction()
    {
        /** @var OAuthToken $token */
        $token = $this->get('security.context')->getToken();

        $client = new \Dropbox\Client($token->getAccessToken(), "DropboxDB/1.0", "es");
        $accountInfo = $client->getAccountInfo();
        $quotaInfo = $accountInfo['quota_info'];

        return array(
            'profile' => $accountInfo,
            'quota' => ($quotaInfo['quota'] - $quotaInfo['shared'] - $quotaInfo['normal']),
            'shared' => $quotaInfo['shared'],
            'normal' => $quotaInfo['normal'],
        );
    }
} 