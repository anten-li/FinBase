<?php
spl_autoload_register(function ($class) {
    require "modules/" . $class . "/" . $class . ".php";
});

baseApp::run();
