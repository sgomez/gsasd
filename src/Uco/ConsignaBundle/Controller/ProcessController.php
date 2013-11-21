<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 21/11/13
 * Time: 22:32
 */

namespace Uco\ConsignaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/process")
 */
class ProcessController extends Controller
{
    /**
     * @Route("/", name="process_test")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    public function getFilesAction()
    {
        $process = new Process('ping -c 10 192.168.1.1');
        $process->start();

        $response = new StreamedResponse();
        $response->setCallback(function() use ($process) {
            echo "<pre>";
            while ($process->isRunning()) {
                echo $process->getIncrementalOutput();
                ob_flush();
                flush();
            }
        });

        return $response->send();
    }
} 