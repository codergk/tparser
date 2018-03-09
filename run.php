<?php
    //include core
        require "config/core.php";

    //include libraries
        require_once "config/functions.php";

/**
 * Step 1 : read directories / sub directories
 * Step 2 : store them in db with templates
 */
    syncDirectoriesWithDb($templatePath);