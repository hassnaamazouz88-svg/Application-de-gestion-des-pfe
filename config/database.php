<?php

namespace Config;

use PDO;
use PDOException;

class Database
{

    private static $instance = null;

    public static function getConnection()
    {

        if (self::$instance === null)
        {

            try {

                self::$instance = new PDO(

                    "mysql:host=localhost;dbname=gestion_pfe;charset=utf8",

                    "root",

                    "",

                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ]

                );

            } catch (PDOException $e) {

                die(
                    "Erreur de connexion : "
                    . $e->getMessage()
                );
            }
        }

        return self::$instance;
    }
}

?>