<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 21/11/13
 * Time: 22:32
 */

namespace Uco\ConsignaBundle\Controller;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
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
     * @var ProcessStatus
     */
    private $status;
    /**
     * @var Job
     */
    private $job;


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
     * Comprueba los archivos a comprimir
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     */
    private function checkFiles()
    {
        // Paso 1: Calcular espacio
        if (false === is_dir($this->job->getPaths())) {
            $this->status->setError('El directorio no existe');
            $this->status->save();
            throw new FileNotFoundException("El directorio no existe:" . $this->job->getPaths());
        }

        $process = new Process(sprintf("find %s | wc -l", $this->job->getPaths()));
        $process->run();
        $this->status->setFiles(sprintf("%d", $process->getOutput()));
        $this->status->save();

        $process = new Process(sprintf("du -hkc %s | tail -1", $this->job->getPaths()));
        $process->run();
        list($size,) = explode("\t", $process->getOutput());
        $this->status->setSize($size);
        $this->status->save();
    }

    /**
     * Comprime los archivos
     */
    private function compressFiles()
    {
        $this->status->setStep(2);
        $this->status->save();

        // Archivo temporal y una función anónima para borrarla
        $save_to = tempnam('/tmp', 'ucodb-');
        register_shutdown_function(create_function('', "unlink('{$save_to}');"));

        $process = new Process(sprintf ("tar jcvf %s %s", $save_to, $this->job->getPaths() ) );
        $process->start();
        $lines = 0;

        // Vamos contando los archivos que van siendo comprimidos para calcular el porcentaje
        while($process->isRunning()) {
            $buffer = trim($process->getIncrementalErrorOutput());
            if ($buffer) {
                $lines_arr = preg_split('/\n/', $buffer);
                $lines += count($lines_arr);
                $this->status->setPercent(intval ($lines * 100 / $this->status->getFiles() ) );
                $this->status->save();
            }
        }

        return $save_to;
    }

    private function uploadFiles($files)
    {
        $this->status->setStep(3);
        $this->status->save();

        $token = $this->get('security.context')->getToken()->getAccessToken();
        $client = new \Dropbox\Client($token, "DropboxDB/1.0", "es");

        $filesize = filesize($files);
        $fd = fopen($files, 'r');

        $chunksize = 512*1024; // 512KB
        $buffer = fread($fd, $chunksize);
        $upload_id = $client->chunkedUploadStart($buffer);
        $offset = strlen($buffer);
        while (!feof($fd)) {
            $buffer = fread($fd, $chunksize);
            $client->chunkedUploadContinue($upload_id, $offset, $buffer);
            $offset += strlen($buffer);
            $this->status->setPercent(intval ($offset * 100 / $filesize));
            $this->status->save();
        }
        fclose($fd);

        $save_as = sprintf("/%s-%s.tar.bz2", $this->job->getFilename(), date('YmdHis'));
        $result = $client->chunkedUploadFinish($upload_id, $save_as, \Dropbox\WriteMode::add());
    }

    /**
     * @Route("/{id}/_run", name="process_run")
     */
    public function calculateAction(Request $request, $id)
    {
        ignore_user_abort(true);
        set_time_limit(0);

        $em = $this->getDoctrine()->getManager();

        /** @var Job $entity */
        $this->job = $em->getRepository('UcoConsignaBundle:Job')->find($id);

        if (!$this->job) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        $this->status = $this->getStatus($id);

        try {
            // Paso 1: Comprobar
            $this->checkFiles();

            // Paso 2: Comprimir
            $files = $this->compressFiles();

            // Paso 3: Enviar
            $this->uploadFiles($files);

        } catch (Exception $e) {

            $this->status->setError($e->getMessage());
            $this->status->save();
            return new Response("{ 'error': 1}");

        }

        $this->status->stop();
        $this->status->save();

        $this->job->setLastRun(new \DateTime());
        $em->persist($this->job);
        $em->flush();

        $response = new Response ($this->status->serialize());
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
} 