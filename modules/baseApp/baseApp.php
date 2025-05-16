<?php
class baseApp
{
    static public $config;
    static public $content;
    static public $user;

    /** @var \mysqli */
    static public $cnn;

    static function run()
    {
        baseApp::$config = require "config.php";
        baseApp::$content = file_get_contents('php://input');

        if (baseApp::$content) {
            if ($_SERVER["HTTP_CONTENT_TYPE"] == "application/json")
                baseApp::$content = json_decode(baseApp::$content);

            try {
                baseApp::ModulesInit();

                if (property_exists(baseApp::$content, "mdl") and property_exists(baseApp::$content, "cmd")) {
                    $mdl = baseApp::$cnn->real_escape_string(baseApp::$content->mdl);
                    $cmd = baseApp::$cnn->real_escape_string(baseApp::$content->cmd);

                    baseApp::baseReturn($mdl::$cmd());
                }
            } catch (Throwable $e) {
                baseApp::baseReturn($e->getMessage(), true);
            }
        } else
            require baseApp::$config["mainForm"];
    }

    static function metaTags($ex)
    {
        $res = "";
        $tm = time();

        foreach (scandir("modules") as $dir) {
            $path = "modules/{$dir}/{$dir}.{$ex}";
            if (is_file($path)) {
                switch ($ex) {
                    case 'js':
                        $res .= "<script src='{$path}?tm={$tm}'></script>";
                        break;
                    case 'css':
                        $res .= "<link rel='stylesheet' href='{$path}?tm={$tm}'>";
                        break;
                }
            };
        }

        return $res;
    }

    static function baseReturn($result, $err = false)
    {
        header('Content-Type: application/json');
        echo json_encode(["err" => $err, "result" => $result]);
        exit();
    }

    static function ModulesInit()
    {
        foreach (baseApp::$config["modules"] as $module) {
            $module::init();
        }
    }

    static function init()
    {
        baseApp::$cnn = new mysqli(
            baseApp::$config["sql_server"],
            baseApp::$config["sql_user"],
            baseApp::$config["sql_pass"]
        );
        baseApp::$cnn->set_charset("utf8");
        try {
            $isDB = baseApp::$cnn->select_db(baseApp::$config["sql_DBname"]);
        } catch (Throwable $e) {
            $isDB = false;
        }

        if (!$isDB) {
            foreach (baseApp::$config["modules"] as $module) {
                $module::createDB(baseApp::$config["sql_DBname"]);
            }
        }
    }

    static function createDB($DBname)
    {
        baseApp::$cnn->query("CREATE DATABASE {$DBname} CHARACTER SET utf8");
        baseApp::$cnn->select_db($DBname);
    }

    static function query($text, $param = []) {}

    static function addRow($table, $row)
    {
        $flds = "";
        $vls = "";
        foreach ($row as $fld => $value) {
            $flds .= ($flds == "" ? "" : ",") . $fld;
            $vls .= ($vls == "" ? "" : ",");
            if (array_key_exists("tp", $value) and $value["tp"] == "hex") {
                $vl = bin2hex(baseApp::$cnn->real_escape_string($value["vl"]));
                $vls .= "unhex('$vl')";
            } else if (is_string($value["vl"])) {
                $vl = baseApp::$cnn->real_escape_string($value["vl"]);
                $vls .= "'$vl'";
            } else if (is_int($value["vl"])) $vls .= "{$value["vl"]}";
        }
        baseApp::$cnn->query("INSERT INTO $table($flds) VALUES ($vls)");
    }

    static function Row($vl, $tp = "")
    {
        return ["vl" => $vl, "tp" => $tp];
    }
}
