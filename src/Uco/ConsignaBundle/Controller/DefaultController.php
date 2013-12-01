<?php

namespace Uco\ConsignaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use \Dropbox as dbx;
use Uco\ConsignaBundle\Util\ConsignaValueStore;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        if (true === $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect('profile');
        }

        return array();
    }

    /**
     * @Route("/about", name="about")
     * @Template()
     */
    public function aboutAction()
    {
        return array();
    }
}
