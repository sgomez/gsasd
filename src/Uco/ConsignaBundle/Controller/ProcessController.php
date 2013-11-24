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
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Uco\ConsignaBundle\Entity\Job;
use Uco\ConsignaBundle\Util\ProcessStatus;

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

    private function getStatus($id)
    {
        return new ProcessStatus($this->get('session'), $id);
    }

    /**
     * @Route("/{id}/_status", name="process_status")
     */
    public function statusAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Job $entity */
        $entity = $em->getRepository('UcoConsignaBundle:Job')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        $status = $this->getStatus($id);

        $response = new Response ($status->serialize());
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/{id}/_calculate", name="process_calculate")
     */
    public function calculateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Job $entity */
        $entity = $em->getRepository('UcoConsignaBundle:Job')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        $status = $this->getStatus($id);

        // Paso 1: Calcular espacio
        $process = new Process(sprintf("find %s | wc -l", $entity->getPaths()));
        $process->run();
        $status->setFiles(sprintf("%d", $process->getOutput()));
        $status->save();

        $process = new Process(sprintf("du -hkc %s | tail -1", $entity->getPaths()));
        $process->run();
        list($size,) = explode("\t", $process->getOutput());
        $status->setSize($size);
        $status->save();

        // Paso 2: Comprimir
        $status->setStep(2);
        $status->save();

        $process = new Process("tar jcvf /tmp/progress.tbz /Users/sergio/Sites/gsasd");
        $process->start();
        $lines = 0;
        while($process->isRunning()) {
            $lines_arr = preg_split('/\n|\r/', $process->getIncrementalErrorOutput());
            $lines += count($lines_arr);
            $status->setPercent($lines * 100 / $status->getFiles());
            $status->save();
        }



        $status->stop();
        $status->save();

        $response = new Response ($status->serialize());
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
} 