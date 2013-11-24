<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 23/11/13
 * Time: 22:23
 */

namespace Uco\ConsignaBundle\Util;

use Uco\ConsignaBundle\Entity\Job;


class ProcessStatus implements \Serializable
{
    private $id;

    private $session;

    private $started = 0;

    private $finished = 0;

    private $step = 0;

    private $percent = 0;

    private $error = 0;

    private $files = 0;

    private $size = 0;

    function __construct($session, $id)
    {
        $this->id = $id;
        $this->session = $session;

        $status = $this->session->get(sprintf("job_%d", $this->id), null);

        if (null !== $status) $this->unserialize($status);

    }

    function start()
    {
        $this->started = 1;
        $this->finished = 0;
        $this->step = 1;
        $this->percent = 0;
        $this->error = 0;
        $this->files = -1;
        $this->size = -1;
    }

    function stop()
    {
        $this->started = 0;
        $this->finished = 1;
    }

    function setStep($step)
    {
        $this->step = $step;
        $this->percent = 0;
    }

    function getStep()
    {
        return $this->step;
    }

    function setPercent($percent)
    {
        $this->percent = $percent > 100 ? 100 : $percent;
    }

    function getPercent()
    {
        return $this->percent;
    }

    function setError()
    {
        $this->error = 1;
    }

    function getError()
    {
        return $this->error;
    }

    function setFiles($files)
    {
        $this->files = $files;
    }

    function getFiles()
    {
        return $this->files;
    }

    function setSize($size)
    {
        $this->size = $size;
    }

    function getSize()
    {
        return $this->size;
    }

    function save()
    {
        $this->session->set(sprintf("job_%d", $this->id), $this->serialize());
    }


    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return json_encode(array (
            'started' => $this->started,
            'finished' => $this->finished,
            'step' => $this->step,
            'error' => $this->error,
            'files' => $this->files,
            'size' => $this->size,
        ));
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $data = json_decode($serialized);
        $this->started = $data->started;
        $this->finished = $data->finished;
        $this->step = $data->step;
        $this->error = $data->error;
        $this->files = $data->files;
        $this->size = $data->size;
    }


}