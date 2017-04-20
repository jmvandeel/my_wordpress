<?php
    require_once 'meekrodb.2.3.class.php';
    DB::$user               = 'root';
    DB::$password           = 'Johannes3:16';
    DB::$dbName             = 'ecommerce';

    $client_name_with_hyphen= str_replace(' ', '-', $_GET['client']);
    $client_name_with_spaces= str_replace('-', ' ', $_GET['client']);
    $client                 = DB::queryFirstRow("SELECT * FROM clients WHERE client_name LIKE '%$client_name_with_spaces%'");
    $client_id              = $client['client_id'];
    $client_name            = $client['client_name'];
    $client_logo            = $client['client_logo'];
    $client_website         = $client['client_website'];

    $all_tags               = DB::query("SELECT * FROM tags");
    $client_tags[]          = str_getcsv($client['client_tags'], ',');
    
    $all_attributes         = DB::query("SELECT * FROM attributes");
    $client_attributes[]    = str_getcsv($client['client_attributes'], ',');

    $all_applications       = DB::query("SELECT * FROM software");
    $client_applications[]  = str_getcsv($client['client_applications'], ',');
    

	ob_start();
    include_once("tmpl_client.html");
    $htmlcontent = ob_get_clean();
    $htmlcontent = str_replace("%title%",               "Client: $client_name",     $htmlcontent);
    $htmlcontent = str_replace("%canonical%",           strtolower($client_name_with_hyphen),   $htmlcontent);
    //$htmlcontent = str_replace("</body>", "", $htmlcontent);
    //$htmlcontent = str_replace("</html>", "", $htmlcontent);

    $htmlcontent = str_replace("%client_name%",         $client_name,               $htmlcontent);
    $htmlcontent = str_replace("%client_logo%",         $client_logo,               $htmlcontent);
    $htmlcontent = str_replace("%client_website%",      $client_website,            $htmlcontent);

    foreach ($client_tags[0] as $tagId) {       
        $tagName = $all_tags[$tagId]["tag_name"];
        $tagLink = strtolower(str_replace(' ', '-', "/database/tags/$tagName"));
        $strTags .= "<li><a href='$tagLink' class='tag'>$tagName</a></li>";
    }
    $htmlcontent = str_replace("%client_tags%",         $strTags,                   $htmlcontent);

    foreach ($client_attributes[0] as $attributeId) {       
        $client_attribute = $all_attributes[$attributeId];
        $client_attribute_type = $client_attribute["attribute_type"];
        $client_attribute_content = $client_attribute["attribute_content"];
        $htmlcontent = str_replace("%$client_attribute_type%", $client_attribute_content, $htmlcontent);

    }

    foreach ($client_applications[0] as $applicationId) {       
        //echo("<pre>");print_r($all_applications[$applicationId]); echo("</pre>");
        $application_name = $all_applications[$applicationId]["software_name"];
        $application_logo = ($all_applications[$applicationId]["software_logo"] != "") ? $all_applications[$applicationId]["software_logo"] : "/database/client_logo.gif";
        $application_url = strtolower(str_replace(' ', '-', "/database/application/$application_name"));
        
        $strApplications .= "<div class='application_client'><a href='$application_url'><img width='100%' src=$application_logo /></a><h4>$application_name</h4></div>";
    }
    $htmlcontent = str_replace("%client_applications%", $strApplications, $htmlcontent);


    $htmlcontent .= "<script type='text/javascript'>$('.application_clients').readmore({collapsedHeight: 320, blockCSS: 'display: block; width: 606px;', moreLink: '<a href=\"#\" class=\"application_clients_more\">See more</a>' , lessLink: '<a href=\"#\" class=\"application_clients_less\">See less</a>'});</script>";
    echo($htmlcontent);

    //echo("<p>1. ".$client_id."</p>");
    //echo("<p>2. ".$client_name."</p>");
    //echo("<p>3. ".$client_logo."</p>");
    //echo("<p>4. ".$client_website."</p>");

    //echo("</body>\r\n</html>");
?>