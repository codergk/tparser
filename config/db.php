<?php
/**
 * Created by PhpStorm.
 * User: Ghislain KPOMASSE
 * Date: 01/10/2017
 * Time: 15:21
 */

/**
 * Local settings of database
 */
$host = 'localhost';
$dbName = 'parser';
$username = 'root';
$password = '';
$tbl_prefix = 'vx_';

/**
 * mysqli connect and errors handling
 */

$connection = mysqli_connect($host, $username, $password, $dbName);

if(mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}