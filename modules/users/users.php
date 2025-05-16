<?php
class users
{
    const role_admin = 0;
    static public $curUser;

    static function auth()
    {
        if (isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER["PHP_AUTH_PW"])) {
            return users::authByPWD($_SERVER['PHP_AUTH_USER'], $_SERVER["PHP_AUTH_PW"]);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return users::authByPWD("", "");
        } else {
            baseApp::baseReturn("auth failure", true);
        }
    }

    static function getUserToken()
    {
        return users::$curUser;
    }

    static function authByPWD($usr, $pwd)
    {
        $pUsr = baseApp::$cnn->real_escape_string($usr);
        $rUsr = baseApp::$cnn->query(<<<END
SELECT 
	users.Name AS Name,
    users.PWD AS PWD,
    users.Role AS Role,
    users.Salt AS Salt,
    HEX(users.Ref) AS Ref
FROM 
	users AS users
WHERE
	users.Name = '{$pUsr}'
END)->fetch_assoc();

        if (!$rUsr)
            baseApp::baseReturn("auth failure", true);

        if (hash("sha256", ($pwd . $rUsr["Salt"]), true) != $rUsr["PWD"])
            baseApp::baseReturn("auth failure", true);

        unset($rUsr["PWD"]);
        unset($rUsr["Salt"]);

        return $rUsr;
    }

    static function init()
    {
        users::$curUser = users::auth();
    }

    static function createDB($DBname)
    {
        baseApp::$cnn->query(<<<END
CREATE TABLE users (
 Ref binary(16) NOT NULL PRIMARY KEY,
 Name VARCHAR(255),
 Role TINYINT, 
 PWD binary(32),
 Salt binary(3),
 Hash VARCHAR(32),
 LData INT UNSIGNED             
)        
END);

        users::addUser("admin", "admin");
    }

    static function addUser($Login, $PWD, $Role = users::role_admin)
    {
        $Salt = random_bytes(3);

        baseApp::addRow("users", [
            "ref"  => baseApp::Row(random_bytes(16), "hex"),
            "Name" => baseApp::Row($Login),
            "Role" => baseApp::Row($Role),
            "PWD"  => baseApp::Row(hash("sha256", ($PWD . $Salt), true), "hex"),
            "Salt" => baseApp::Row($Salt, "hex")
        ]);
    }
}
