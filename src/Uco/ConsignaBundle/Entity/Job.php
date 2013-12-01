<?php

namespace Uco\ConsignaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Uco\ConsignaBundle\Validator\Constraints as UcoAssert;


/**
 * Job
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Uco\ConsignaBundle\Entity\JobRepository")
 */
class Job
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255)
     */
    private $filename;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_run", type="datetimetz", nullable=true)
     */
    private $lastRun;

    /**
     * @var string
     *
     * @ORM\Column(name="paths", type="string", length=1024)
     * @UcoAssert\PathExists()
     */
    private $paths;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Job
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set filename
     *
     * @param string $filename
     * @return Job
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    
        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set lastRun
     *
     * @param \DateTime $lastRun
     * @return Job
     */
    public function setLastRun($lastRun)
    {
        $this->lastRun = $lastRun;
    
        return $this;
    }

    /**
     * Get lastRun
     *
     * @return \DateTime 
     */
    public function getLastRun()
    {
        return $this->lastRun;
    }

    /**
     * Set paths
     *
     * @param string $paths
     * @return Job
     */
    public function setPaths($paths)
    {
        $this->paths = $paths;
    
        return $this;
    }

    /**
     * Get paths
     *
     * @return string 
     */
    public function getPaths()
    {
        return $this->paths;
    }

    function __toString()
    {
        return $this->getName();
    }


}
