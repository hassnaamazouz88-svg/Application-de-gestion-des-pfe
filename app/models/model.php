<?php
namespace App\Models;

use Config\Database;
use PDO;

abstract class Model
{
    protected static function getDB()
    {
        return Database::getConnection();
    }
}

?>