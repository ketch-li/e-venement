<?php

/**
 * PluginnvSession
 *
 * @package    nvDoctrineSessionStoragePlugin
 * @subpackage Doctrine
 * @author     Florian Rey <nervo@nervo.net>
 */
abstract class PluginnvSession extends BasenvSession
{
    /**
     * Use Doctrine preSave hook to store current time each time
     * the record is updated.
     *
     * @param Doctrine_Event $event
     * @return void
     */
    public function preSave($event)
    {
        $this->setSessionTime(time());
    }

    /**
     * Lock the record by setting its lock flag to true, and set
     * the lock time.
     * If no time is given, current time is used.
     *
     * @param int $time
     * @return void
     */
    public function lock($time = null)
    {
        $this->_set('is_locked', true);
        $this->_set('lock_time', $time ? $time : time());
    }

    /**
     * Unlock the record by setting its lock flag to false.
     *
     * @return void
     */
    public function unlock()
    {
        $this->_set('is_locked', false);
    }

    /**
     * Set the record session data.
     *
     * @param string $data
     * @return void
     */
    public function setSessionData($data)
    {
        $this->_set('session_data', $data);
    }

    /**
     * Get the record session data
     *
     * @return string
     */
    public function getSessionData()
    {
        return $this->_get('session_data');
    }

    /**
     * Set the record session time.
     *
     * @param int $time
     * @return void
     */
    public function setSessionTime($time)
    {
        $this->_set('session_time', date('Y-m-d H:i:s', $time));
    }

    /**
     * Set the record session id.
     *
     * @param string $id
     * @return void
     */
    public function setSessionId($id)
    {
        $this->_set('session_id', $id);
    }
}
