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
        ignore_user_abort(true);
        set_time_limit(0);

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

        $process = new Process(sprintf ("tar jcvf /tmp/progress.tbz %s", $entity->getPaths() ) );
        $process->start();
        $lines = 0;

        while($process->isRunning()) {
            $buffer = trim($process->getIncrementalErrorOutput());
            if ($buffer) {
                $lines_arr = preg_split('/\n/', $buffer);
                $lines += count($lines_arr);
                $status->setPercent(intval ($lines * 100 / $status->getFiles() ) );
                $status->save();
            }
        }

        // Paso 3: Enviar
        $status->setStep(3);
        $status->save();

        $token = $this->get('security.context')->getToken()->getAccessToken();
        $client = new \Dropbox\Client($token, "DropboxDB/1.0", "es");

        $filesize = filesize('/tmp/progress2.tbz');
        $fd = fopen('/tmp/progress2.tbz', 'r');

        $chunksize = 1*1024*1024; // 4MB
        $buffer = fread($fd, $chunksize);
        $upload_id = $client->chunkedUploadStart($buffer);
        $offset = strlen($buffer);
        while (!feof($fd)) {
            $buffer = fread($fd, $chunksize);
            $client->chunkedUploadContinue($upload_id, $offset, $buffer);
            $offset += strlen($buffer);
            $status->setPercent(intval ($offset * 100 / $filesize));
            $status->save();
        }
        fclose($fd);
        $result = $client->chunkedUploadFinish($upload_id, "/progress.tgz", \Dropbox\WriteMode::add());

        $status->stop();
        $status->save();
        $response = new Response ($status->serialize());
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
} 