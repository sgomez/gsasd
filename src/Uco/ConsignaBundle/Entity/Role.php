<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 20/11/13
 * Time: 13:04
 */

namespace Uco\ConsignaBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Uco\ConsignaBundle\Entity\Role
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Uco\ConsignaBundle\Entity\RoleRepository")
 * @UniqueEntity("name")
 */
class Role implements RoleInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=120, unique=true)
     */
    private $name;


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
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * Implementation of getRole. RoleInterface.
     *
     * @return string
     */
    public function getRole()
    {
        $this->getName();
    }

    /**
     * Return string representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}