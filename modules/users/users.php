<?php
class users
{
    const role_admin = 0;

    static function auth()
    {
        if (isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER["PHP_AUTH_PW"])) {
            users::authByPWD($_SERVER['PHP_AUTH_USER'], $_SERVER["PHP_AUTH_PW"]);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        } else {
        }
    }

    static function authByPWD($usr, $pwd)
    {

        baseApp::baseReturn("auth failure", true);
    }

    static function init()
    {
        users::auth();
    }

    static function createDB($DBname)
    {
        baseApp::$cnn->query(<<<END
CREATE TABLE users (
 Ref binary(16) NOT NULL PRIMARY KEY,
 Name VARCHAR(255),
 Role TINYINT, 
 PWD VARCHAR(32),
 Salt VARCHAR(3),
 Hash VARCHAR(32),
 LData INT UNSIGNED             
)        
END);

        users::addUser("admin", "admin");
    }

    static function addUser($Login, $PWD, $Role = users::role_admin)
    {
        baseApp::addRow("users", [
            "ref" => ["vl" => random_bytes(16), "tp" => "hex"],
            "Name" => ["vl" => $Login]
        ]);
    }
}
