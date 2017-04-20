<?php
    require_once 'meekrodb.2.3.class.php';
    DB::$user               = 'root';
    DB::$password           = 'Johannes3:16';
    DB::$dbName             = 'ecommerce';

    $application_name_with_hyphen= str_replace(' ', '-', $_GET['application']);
    $application_name_with_spaces= str_replace('-', ' ', $_GET['application']);   
    $application            = DB::queryFirstRow("SELECT * FROM software WHERE software_name LIKE '%$application_name_with_spaces%'");
    $application_id         = $application['software_id'];
    $application_name       = $application['software_name'];
    $application_logo       = $application['software_logo'];
    $application_website    = $application['software_website'];
    
    $all_tags               = DB::query("SELECT * FROM tags");
    $application_tags[]     = str_getcsv($application['software_tags'], ',');

    $all_attributes         = DB::query("SELECT * FROM attributes");
    $application_attributes[]= str_getcsv($application['software_attributes'], ',');
        
    $all_clients            = DB::query("SELECT * FROM clients");
    $application_clients[]  = str_getcsv($application['software_clients'], ',');
    

	ob_start();
    include_once("tmpl_application.html");
    $htmlcontent = ob_get_clean();
    $htmlcontent = str_replace("%title%",               "Application: $application_name",   $htmlcontent);
    $htmlcontent = str_replace("%canonical%",           strtolower($application_name_with_hyphen),      $htmlcontent);

    $htmlcontent = str_replace("%application_name%",    $application_name,                  $htmlcontent);
    $htmlcontent = str_replace("%application_logo%",    $application_logo,                  $htmlcontent);
    $htmlcontent = str_replace("%application_website%", $application_website,               $htmlcontent);

    foreach ($application_tags[0] as $tag) {       
        $tagName = $all_tags[$tag]["tag_name"];
        $tagLink = strtolower(str_replace(' ', '-', "/database/tags/$tagName"));
        $strTags .= "<li><a href='$tagLink' class='tag'>$tagName</a></li>";
    }
    $htmlcontent = str_replace("%application_tags%",    $strTags,                           $htmlcontent);

    foreach ($application_attributes[0] as $attributeId) {       
        $application_attribute = $all_attributes[$attributeId];
        $application_attribute_type = $application_attribute["attribute_type"];
        $application_attribute_content = $application_attribute["attribute_content"];

        if( array_key_exists($application_attribute_type, $application_attributes[1]) ){
            $application_attributes[1][$application_attribute_type] = $application_attributes[1][$application_attribute_type] .= $application_attribute_content;
        } else {
            $application_attributes[1][$application_attribute_type] = $application_attribute_content;
        }
    }
    //echo("<pre>1. ");print_r($application_attributes); echo("</pre>");
    foreach ($application_attributes[1] as $key=>$value) {
        $htmlcontent = str_replace("%$key%", $value, $htmlcontent);
    }


    //echo("<pre>2. ");print_r($application_attributes); echo("</pre>");

    foreach ($application_clients[0] as $clientId) {       
        //echo("<pre>");print_r($all_clients[$clientId]); echo("</pre>");
        $client_name = $all_clients[$clientId]["client_name"];
        $client_logo = ($all_clients[$clientId]["client_logo"] != "") ? $all_clients[$clientId]["client_logo"] : "/database/client_logo.gif";
        $client_url  = strtolower(str_replace(' ', '-', "/database/client/$client_name"));
        
        $strClients .= "<div class='application_client'><a href='$client_url'><img width='100%' src=$client_logo /></a><h4>$client_name</h4></div>";
    }
    $htmlcontent = str_replace("%application_clients%", $strClients,            $htmlcontent);



    $htmlcontent .= "<script type='text/javascript'>$('.application_clients').readmore({collapsedHeight: 320, blockCSS: 'display: block; width: 646px;', moreLink: '<a href=\"#\" class=\"application_clients_more\">See more</a>' , lessLink: '<a href=\"#\" class=\"application_clients_less\">See less</a>'});</script>";
    echo($htmlcontent);

    //echo("<pre>");
    //print_r($application_tags[0]);
    //echo("</pre>");

?>