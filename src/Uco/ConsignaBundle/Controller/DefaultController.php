<?php

namespace Uco\ConsignaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use \Dropbox as dbx;
use Uco\ConsignaBundle\Util\ConsignaValueStore;

class DefaultController extends Controller
{
    private $cltid = "ConsignaDB/1.0";


    private function getWebAuth()
    {
        $app = new \Dropbox\AppInfo(
            $this->container->getParameter('dropbox.app.key'),
            $this->container->getParameter('dropbox.app.secret')
        );

        $redirect = $this->container->get('router')->generate('dropbox-auth', array(), true);
        $csrfTokenStore = new ConsignaValueStore($this->getRequest()->getSession(), 'dropbox-auth-csrf-token');
        $auth = new \Dropbox\WebAuth($app, $this->cltid, $redirect, $csrfTokenStore, 'es');

        return $auth;
    }

    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("dropbox-start", name="dropbox-start")
     * @Template()
     */
    public function startAction()
    {
        return $this->redirect($this->getWebAuth()->start());
    }

    /**
     * @Route("dropbox-auth", name="dropbox-auth")
     * @Template()
     */
    public function authAction()
    {
        $auth  = $this->getWebAuth();

        try {
            list($accessToken, $userId, $urlState) = $auth->finish($_GET);
        } catch (\Dropbox\WebAuthException_BadState $ex) {
            // Auth session expired.  Restart the auth process.
            throw $this->createNotFoundException($ex->getMessage());
            // return $this->redirect($this->generateUrl('homepage'));
        } catch (\Dropbox\WebAuthException_Csrf $ex) {
            error_log("/dropbox-auth-finish: CSRF mismatch: " . $ex->getMessage());
            // Respond with HTTP 403 and display error page...
            throw $this->createNotFoundException($ex->getMessage());
        }

        $client = new \Dropbox\Client($accessToken, $this->cltid, "es");
        var_dump($client->getAccountInfo());

        var_dump($accessToken);
        var_dump($userId);
        var_dump($urlState);

        die();
        return $this->render(
          array(
              'accessToken' => $accessToken,
              'userId' => $userId,
              'urlState' => $urlState,
          )
        );
    }
}
