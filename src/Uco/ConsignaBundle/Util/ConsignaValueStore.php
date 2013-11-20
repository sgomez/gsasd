<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 19/11/13
 * Time: 20:03
 */

namespace Uco\ConsignaBundle\Util;

use \Dropbox\ValueStore;

class ConsignaValueStore implements ValueStore
{
    private $key;
    private $session;

    function __construct($session, $key)
    {
        $this->session = $session;
        $this->key = $key;
    }


    /**
     * Returns the entry's current value or <code>null</code> if nothing is set.
     *
     * @return string
     */
    function get()
    {
        $value = $this->session->get($this->key);
        return $value === false ? null : base64_decode($value);
    }

    /**
     * Set the entry to the given value.
     *
     * @param string $value
     */
    function set($value)
    {
        $this->session->set($this->key, base64_encode($value));
    }

    /**
     * Remove the value.
     */
    function clear()
    {
        $this->session->remove($this->key);
    }
}