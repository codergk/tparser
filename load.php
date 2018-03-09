<?php
//include core
    require "config/core.php";

    $templateId = $_GET['id'];//get current template id

    $sql = "SELECT id, content from " . $tbl_prefix . "templates where id={$templateId}";
    $query = mysqli_query($connection, $sql);
    $row = mysqli_fetch_array($query);

    //find related stylesheets
        $sql2 = "SELECT path from " . $tbl_prefix . "template_stylesheets where template_id={$row[0]}";
        $query2 = mysqli_query($connection, $sql2);
        $row2 = mysqli_fetch_array($query2);

    //get style path
        $rootUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

    //
        $stylePath = $rootUrl . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . $row2[0];

    //
        $output = "<link rel=\"stylesheet\" href=\"{$stylePath}\" />";
        $output .= $row[1];
    echo json_encode([
        'success'=>true,
        'html'=>$output
    ]);