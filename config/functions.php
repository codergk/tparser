<?php
/**
 * All core functions used will be defined there.
 */

function generateUniqueId($primaryKey) {
    $randomNumber = mt_rand(100000, 999999);
    return ($randomNumber + $primaryKey);
}

function build_category_tree(&$output, $parentId=0){
    global $tbl_prefix, $connection;
    $sql = "SELECT * from " . $tbl_prefix . "template_categories where spid={$parentId}";
    //echo  $sql ;
    $query = mysqli_query($connection, $sql);

    while($row = mysqli_fetch_assoc($query)){
        //
        $has_template = (int) $row["has_template"];
        //
            if($row["id"] != $parentId){
                $output .= "<ul>";
                    $output .= "<li >" . $row["parent"];
                        build_category_tree($output, $row["id"]);
                        if($has_template)
                            $output .= "<ul>";
                            if($has_template) {
                                $sql2 = "SELECT * from " . $tbl_prefix . "templates where category_id={$row["id"]}";
                                $query2 = mysqli_query($connection, $sql2);
                                while ($templateRow = mysqli_fetch_assoc($query2)) {
                                    $output .= "<li data-jstree='{\"icon\":\"glyphicon glyphicon-file\"}' onclick='loadTreeFile({$templateRow["id"]});'>" . $templateRow["name"] . "</li>";
                                }
                            }
                        if($has_template)
                            $output .= "</ul>";
                    $output .= "</li>";
                $output .= "</ul>";
            } else {
                $output .= "<li >" . $row["parent"];
                if($has_template)
                    $output .= "<ul>";
                if($has_template) {
                    $sql2 = "SELECT * from " . $tbl_prefix . "templates where category_id={$row["id"]}";
                    $query2 = mysqli_query($connection, $sql2);
                    while ($templateRow = mysqli_fetch_assoc($query2)) {
                        $output .= "<li data-jstree='{\"icon\":\"glyphicon glyphicon-file\"}' onclick='loadTreeFile($templateRow[id]);'>" . $templateRow["name"] . "</li>";
                    }
                    $output .= "</ul>";
                }
                $output .= "</li>";
            }
    }
}

function syncDirectoriesWithDb($base_dir, $level = 0, $parentUniqueId = 0) {
    global $tbl_prefix, $connection;
    $directories = array();
    foreach(scandir($base_dir) as $file) {
        if($file == '.' || $file == '..') continue;

        //path
            $dir = $base_dir.DIRECTORY_SEPARATOR.$file;

        if(is_dir($dir)) {
            if($level == 0 && !empty($file)) {
                $sql = "INSERT INTO " . $tbl_prefix . "template_categories (parent, uid, chid, spid, has_template) 
                            values('{$file}', 0, 0, 0, 0)";
                //
                if (mysqli_query($connection, $sql)) {
                    //update parent with generated number
                        $subParentId = mysqli_insert_id($connection);
                        $parentUniqueId = generateUniqueId($subParentId);
                        $sql = "UPDATE " . $tbl_prefix . "template_categories SET uid={$parentUniqueId} WHERE id = {$subParentId}";
                        (mysqli_query($connection, $sql)) ? '' : '';
                }
            } else {
                //echo $dir ."<br/>";
                $parentName = basename(dirname($dir));
                $childId = $parentUniqueId+$level;
                //$parentChildId = $parentUniqueId+($level-1);
                $sql1 = "SELECT id from " . $tbl_prefix . "template_categories WHERE uid=$parentUniqueId and parent like '%$parentName%'";
                $query = mysqli_query($connection, $sql1);
                $row = mysqli_fetch_array($query);
                $parentId = (isset($query)) ? $row[0] : 0;
                $sql2 = "INSERT INTO " . $tbl_prefix . "template_categories (parent, uid, chid, spid, has_template) 
                            values('{$file}', {$parentUniqueId}, {$childId}, '{$parentId}', 0)";

                (mysqli_query($connection, $sql2)) ? '' : '';
            }

            //call for sub directories
            syncDirectoriesWithDb($dir, $level +1, $parentUniqueId);
        } else {
            //var_dump(getcwd()); exit();
            if((pathinfo($dir, PATHINFO_EXTENSION) == 'html') || (pathinfo($dir, PATHINFO_EXTENSION) == 'htm')) {
                $parentName = basename($base_dir);
                //var_dump($parentName, $parentUniqueId);
                $sql1 = "SELECT id from " . $tbl_prefix . "template_categories WHERE uid=$parentUniqueId and parent like '%$parentName%'";
                $query = mysqli_query($connection, $sql1);
                $row = mysqli_fetch_array($query);
                $parentId = (isset($query)) ? $row[0] : 0;
                //
                $sql = "UPDATE " . $tbl_prefix . "template_categories SET has_template=1 WHERE id = {$parentId}";
                (mysqli_query($connection, $sql)) ? '' : '';
                //
                if($parentId != 0) {
                    $rootUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
                    //store templates using Html Parser Lib
                    $path = $rootUrl . DIRECTORY_SEPARATOR . str_replace(' ', '%20', $dir);
                    $directoryPath = SERVER_PATH . DIRECTORY_SEPARATOR . $dir;
                    //if (is_file($directoryPath)) {//check if file exists in templates path
                        $html = file_get_html($path);
                        $bodyContent = "";
                        $styleContent = "";
                        $storedFileName = md5(uniqid(rand(), true)) . '.css';
                        $inlineStyleContent = '';

                        foreach ($html->find('body') as $key => $e) {
                            $bodyContent .= $e->innertext;
                        }

                        $domd = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $domd->loadHTML($bodyContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                        libxml_use_internal_errors(false);

                        $domx = new DOMXPath($domd);
                        $items = $domx->query("//p|//table|//td|//tr|//span|//div");

                        foreach ($items as $key => $item) {
                            $currentTagId = 'genId_' . $key;
                            $item->setAttribute('id', $currentTagId);
                            if ($item->hasAttribute("style") ||
                                $item->hasAttribute("align") ||
                                $item->hasAttribute("ALIGN") ||
                                $item->hasAttribute("width")) {//generate only if contains styles
                                    $inlineStyleContent .= '#' . $currentTagId . '{';
                                    if ($item->hasAttribute("style"))
                                        $inlineStyleContent .= strtolower($item->getAttribute('style'));

                                    if ($item->hasAttribute("align") || $item->hasAttribute("ALIGN")) {
                                        $inlineStyleContent .= 'text-align: ' . strtolower($item->getAttribute('align'));
                                    }
                                    //
                                    if ($item->hasAttribute("width")) {
                                        $inlineStyleContent .= 'width: ' . strtolower($item->getAttribute('width')) . 'px';
                                    }
                                    //
                                    $inlineStyleContent .= '}';
                            }
                            $item->removeAttribute("style");
                            $item->removeAttribute("align");
                            $item->removeAttribute("width");
                        }

                        $bodyContent = str_replace('"', '\"', $domd->saveHTML());
                        $bodyContent = str_replace("'", "\'", $bodyContent);

                        //extract styles
                        foreach ($html->find('style') as $e)
                            $styleContent .= strtolower($e->innertext);
                        //remove !--
                        $styleContent = str_replace('<!--', '', $styleContent);
                        $styleContent = str_replace('-->', '', $styleContent);
                        $styleContent .= $inlineStyleContent;

                        //create css file
                        $fp = fopen(TEMPLATE_STYLES_PATH . $storedFileName, "wb");
                        fwrite($fp, $styleContent);
                        fclose($fp);

                        //save template into db
                            $sql = "INSERT INTO " . $tbl_prefix . "templates (category_id, name, content) values ('{$parentId}', '{$file}', '{$bodyContent}')";
                           // echo $sql;
                            (mysqli_query($connection, $sql));
                        $templateId = mysqli_insert_id($connection);

                        if ($templateId != 0) {
                            //save template stylesheets into db also
                            $sql = "INSERT INTO " . $tbl_prefix . "template_stylesheets (template_id, path) values ({$templateId}, '{$storedFileName}')";
                            (mysqli_query($connection, $sql));
                        }

                        echo "---" . $dir . " - Installed!<br/>";
                        //var_dump($bodyContent);exit();
                    //}
                }
            }
        }
    }
    return $directories;
}