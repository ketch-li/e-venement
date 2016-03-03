<?php

/**
 * PluginnvSessionTable
 *
 * @package    nvDoctrineSessionStoragePlugin
 * @subpackage Doctrine
 * @author     Florian Rey <nervo@nervo.net>
 */
class PluginnvSessionTable extends Doctrine_Table
{
    /**
     * Lock a session specified by its session id.
     * Return the number of record updated (could be interpretated as a
     * boolean result with 0 => false, and 1+ => true)
     *
     * @param string $session_id
     * @return int
     */
    public function lockSession($session_id)
    {
        return $this->createQuery('s')
                    ->update()
                    ->set('s.is_locked', '?', true)
                    ->set('s.lock_time', '?', time())
                    ->where('s.session_id = ?', $session_id)
                    ->andWhere('s.is_locked = ?', false)
                    ->execute();
    }

    /**
     * Unlock sessions with a lock time inferior to a specified time.
     * If no time is specified, unlock all sessions.
     * Return the number of unlocked sessions.
     *
     * @param int $time
     * @return int
     */
    public function unlockSessions($time = null)
    {
        $q = $this->createQuery('s')
                  ->update()
                  ->set('s.is_locked', '?', false)
                  ->where('s.is_locked = ?', true);

        if ($time)
        {
            $q->andWhere('s.lock_time < ?', $time);
        }

        return $q->execute();
    }

    /**
     * Delete sessions with a session time inferior to a specified time.
     * If no time is specified, delete all sessions.
     * Return the number of deleted sessions.
     *
     * @param int $time
     * @return int
     */
    public function deleteSessions($time = null)
    {
        $q = $this->createQuery('s')
                  ->delete();

        if ($time)
        {
            $q->where('s.session_time < ?', date('Y-m-d H:i:s', $time));
        }

        return $q->execute();
    }
}
