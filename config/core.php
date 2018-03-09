<?php
    //define server path
        define('SERVER_PATH', getcwd());
        define('TEMPLATE_STYLES_PATH', getcwd() . "/build/");

    //set template path
        $templatePath = 'templates'; //can be changed if needed

    //load libraries
        include('libs/simple_html_dom.php');

    //increase max execution time
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '256M');

    //include database connection
        include_once "config/db.php";