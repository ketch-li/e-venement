<?php

/**
 * PasswordMD5Service
 *
 * @author Marcos Bezerra de Menezes <marcos.bezerra@libre-informatique.fr>
 */
class PasswordMD5Service
{
    /**
     * @param string $password
     * @param string $salt
     * @return string
     */
    public function encrypt($password, $salt = '')
    {
        $prefix = '$md5$';

        // do not encrypt twice
        if (strpos($password, $prefix) === 0) {
            return $password;
        }

        return $prefix . md5($salt . $password);
    }
}