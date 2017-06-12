<?php

/**
 * PasswordPlainTextService
 *
 * @author Marcos Bezerra de Menezes <marcos.bezerra@libre-informatique.fr>
 */
class PasswordPlainTextService
{
    /**
     * @param string $password
     * @param string $salt
     * @return string
     */
    public function encrypt($password, $salt='')
    {
        return $password;
    }
}