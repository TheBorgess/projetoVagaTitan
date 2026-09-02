<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'projeto_titan');

/**
 * @return mysqli
 */
function getConnection(): mysqli
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die('Erro de conexão com o banco de dados: ' . $conn->connect_error);
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}
