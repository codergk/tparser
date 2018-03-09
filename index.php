<?php
/*
 * Preview templates with tree view
 */

//include core
    require "config/core.php";

//include libraries
    require_once "config/functions.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Template Preview</title>
    <link rel="stylesheet" href="public/plugins/jstree/dist/themes/default/style.min.css" />
    <link rel="stylesheet" href="public/css/bootstrap.min.css" />
    <script src="public/js/jquery-3.2.1.min.js"></script>
    <style>
        body {
            background: #EEE;
        }
        .row {
            margin: 5px;
            border: 10px solid #BBB;
            -webkit-border-radius: 10px;
            -moz-border-radius: 10px;
            border-radius: 10px;
        }
        #left-box, #right-box {
            min-height: 450px;
        }
        #left-box {
            border-right: 6px solid #CCC;
        }
        #empty-message {
            font-size: 25px;
            text-align: center;
            position: absolute;
            top: 40%;
            left: 40%;
            font-weight: bold;
            color: #666666;
        }
    </style>
</head>
<body>
    <div class="row">
        <div class="col-md-4" id="left-box">
            <div id="html">
                <?php
                    build_category_tree($tree, 0);
                    echo $tree;
                ?>
            </div>
        </div>
        <div class="col-md-8" id="right-box">
            <div id="empty-message">No Template Selected</div>
            <div id="loaded-content"></div>
        </div>
    </div>
    <!-- load Js Tree Plugin -->
        <script src="public/plugins/jstree/dist/jstree.min.js"></script>
        <script>
            // html demo
                $('#html').jstree();
            //load template
                function loadTreeFile(templateId) {
                    $.ajax({
                        url: "load.php?id="+templateId,
                        beforeSend: function(){
                            $('#empty-message').hide();
                        },
                        dataType: "json"
                    }).done(function(data) {
                        if(data.success) {
                            $('#loaded-content').html(data.html);
                        }
                    });
                }

                /*function clearSelection() {
                    //$('#empty-message').show();
                    //$('#loaded-content').html('');
                }*/
        </script>
    </body>
</html>