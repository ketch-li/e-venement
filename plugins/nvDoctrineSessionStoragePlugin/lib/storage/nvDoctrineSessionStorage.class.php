<?php

/**
 * nvDoctrineSessionStorage
 *
 * @package    nvDoctrineSessionStoragePlugin
 * @subpackage Storage
 * @author     Florian Rey <nervo@nervo.net>
 */
class nvDoctrineSessionStorage extends sfSessionStorage
{
    /**
     * Session Doctrine Table
     *
     * @var nvSessionTable
     */
    protected $table = null;

    /**
     * Session Doctrine Record
     *
     * @var nvSession
     */
    protected $session = null;

    /**
     * Initializes this Storage instance.
     *
     * Available options:
     *
     *  * session_gc_set:         Set PHP Garbage Collection parameters (true by default)
     *  * session_gc_maxlifetime: Garbage Collection maxlifetime (1440 by default)
     *  * session_gc_probability: Garbage Collection probability (1 by default)
     *  * session_gc_divisor:     Garbage Collection divisor (100 by default)
     *  * lock_enable:            Enable lock mechanism (true by default)
     *  * lock_lifetime:          Lock lifetime in seconds (300 by default)
     *  * lock_loop_delay:        Lock loop delay in microseconds (10000 by default)
     *  * lock_loop_timeout:      Lock loop timeout in seconds (10 by default)
     *
     * @param array $options  An associative array of options
     * @see sfSessionStorage
     */
    public function initialize($options = array())
    {
        $options = array_merge(array(
            'session_gc_set'         => true,
            'session_gc_maxlifetime' => 1440,
            'session_gc_probability' => 1,
            'session_gc_divisor'     => 100,
            'lock_enable'            => true,
            'lock_lifetime'          => 300,
            'lock_loop_delay'        => 10000,
            'lock_loop_timeout'      => 10
        ), $options);

        // Disable auto_start
        $options['auto_start'] = false;

        // Initialize the parent
        parent::initialize($options);

        // Use this object as session handler
        session_set_save_handler(
            array($this, 'sessionOpen'),
            array($this, 'sessionClose'),
            array($this, 'sessionRead'),
            array($this, 'sessionWrite'),
            array($this, 'sessionDestroy'),
            array($this, 'sessionGC')
        );

        // Not defining Garbage Collection parameters can lead to issues,
        // especially in debian based linux distributions.
        // That's why they are defined by default, with default values from php.ini
        if ($this->options['session_gc_set'])
        {
            ini_set('session.gc_maxlifetime', $this->options['session_gc_maxlifetime']);
            ini_set('session.gc_probability', $this->options['session_gc_probability']);
            ini_set('session.gc_divisor'    , $this->options['session_gc_divisor']);
        }

        // Start session
        session_start();
    }

    /**
     * Opens a session.
     * As it's the first invoked method in the php session workflow, get the
     * doctrine session table from there.
     * Always return true.
     *
     * @param string $path (ignored)
     * @param string $name (ignored)
     * @return boolean
     */
    public function sessionOpen($path = null, $name = null)
    {
        // Get Session Table
        $this->table = Doctrine::getTable('nvSession');

        return true;
    }

    /**
     * Close a session.
     * Do nothing.
     * Always return true.
     *
     * @return boolean
     */
    public function sessionClose()
    {
        return true;
    }

    /**
     * Read a session.
     * Find the session record by its session id, eventually create it, and
     * get its session data.
     * Include lock mechanism to avoid overlap issues.
     * Return the session data.
     *
     * @param string $id
     * @return string
     */
    public function sessionRead($id)
    {
        // Use lock mechanism
        if ($this->options['lock_enable'])
        {
            // First, unlock all lifetimed sessions
            $this->table->unlockSessions(time() - $this->options['lock_lifetime']);

            // Try to lock the session specified by its session id
            if (!$this->table->lockSession($id))
            {
                // If the session could not be locked, that means it maybe does
                // not exists. To be sure, we try to create it.
                $this->session = new nvSession();
                $this->session->setSessionId($id);
                $this->session->lock();

                // If the session already exists, the save() method will
                // throw an exception, as the session_id is unique.
                try {
                    $this->session->save();
                }
                catch (Exception $e)
                {
                    // At this point, we are sure the session already exists
                    // and is locked.
                    // So, we start a loop, waiting it to be unlocked by
                    // another process.

                    $time = time();

                    do {
                        // Sleep to let the another process unlock
                        // the session itself.
                        usleep($this->options['lock_loop_delay']);
                        // The session can be still locked by - for instance -
                        // a crashed process. So we try to unlock all the lifetimed
                        // sessions, hoping its in there.
                        $this->table->unlockSessions(time() - $this->options['lock_lifetime']);
                    }
                    // Two things can happened to get out of the waiting loop :
                    // - The session can finally be locked by our process, that
                    //   means that the another process has gently unlocked it.
                    // - Timeout is reached, that means that something bad happened.
                    //   We can be pretty sure that in that case, the another process
                    //   does not need the session anymore, so we leave it in its
                    //   lock state, and pass through.
                    while(  (!$this->table->lockSession($id)) &&
                            (time() < ($time + $this->options['lock_loop_timeout'])) );

                    // Finally, as the session is now locked by our process (or
                    // release in a locked state by another process), we just
                    // get it by its session id.
                    $this->session = $this->table->findOneBySessionId($id);
                }
            }
            else
            {
                // If the session could be locked, that means
                // it exists, so, just get it by its session id
                $this->session = $this->table->findOneBySessionId($id);
            }
        }
        // Don't use lock mechanism
        else
        {
            // Get the session record by its session id
            $this->session = $this->table->findOneBySessionId($id);

            // If not exists, create it
            if (!$this->session)
            {
                $this->session = new nvSession();
                $this->session->setSessionId($id);
                $this->session->save();
            }
        }

        // Use symfony event system to notify potential listeners that
        // a session is about to be reading.
        sfContext::getInstance()->getEventDispatcher()->notify(
            new sfEvent(
                $this,
                'session.read',
                array(
                    'id'      => $id,
                    'session' => $this->session
                )
            )
        );

        // Return session record data
        return $this->session->getSessionData();
    }

    /**
     * Write a session.
     * Set the session data, eventually unlock it, and save the record to db.
     * Always return true.
     *
     * @param string $id
     * @param string $data
     * @return boolean
     */
    public function sessionWrite($id, $data)
    {
        $this->session->setSessionData($data);

        if ($this->options['lock_enable'])
        {
            $this->session->unlock();
        }

        $this->session->save();

        return true;
    }

    /**
     * Destroy a session.
     * Delete the current session record.
     * Always return true.
     *
     * @param string $id (ignored)
     * @return boolean
     */
    public function sessionDestroy($id)
    {
        $this->session->delete();

        return true;
    }

    /**
     * Cleans up old sessions.
     * Always return true.
     *
     * @param int $lifetime
     * @return true
     */
    public function sessionGC($lifetime)
    {
        $this->table->deleteSessions(time() - $lifetime);

        return true;
    }

    /**
     * Regenerates id that represents this storage.
     *
     * @param boolean $destroy
     * @return void
     * @see sfSessionStorage
     */
    public function regenerate($destroy = false)
    {
        // Already regenerated ?
        if (self::$sessionIdRegenerated)
        {
          return;
        }

        // Let the parent do the job...
        parent::regenerate($destroy);

        /// ... and update the session record with the new session id
        $this->session->setSessionId(session_id());
    }

    /**
     * Get the current session doctrine record.
     *
     * @return nvSession
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Use symfony event system to allow sfUser to access the session doctrine
     * record with getSession() method.
     * This static method is called if a sfUser method can not be find, passing
     * the method name by parameter. If the method name is 'getSession', we trap
     * the event, get the current storage, and finally return the result of the
     * internal getSession() method.
     * (see config.php)
     *
     * @param sfEvent $event
     * @return void
     */
    static public function listenToUserMethodNotFoundEvent(sfEvent $event)
    {
        $parameters = $event->getParameters();

        if ($parameters['method'] == 'getSession')
        {
            $event->setReturnValue(
                sfContext::getInstance()->getStorage()->getSession()
            );
            $event->setProcessed(true);
        }
    }
}
