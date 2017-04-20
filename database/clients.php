<?php
    require_once 'meekrodb.2.3.class.php';
    DB::$user = 'root';
    DB::$password = 'Johannes3:16';
    DB::$dbName = 'ecommerce';

	$clients = DB::query("SELECT * FROM clients ORDER BY client_name ASC");


	ob_start();
    include_once("tmpl_clients.html");
    $htmlcontent = ob_get_clean();
    $htmlcontent = str_replace("%title%", "Clients", $htmlcontent);
    $htmlcontent = str_replace("</body>", "", $htmlcontent);
    $htmlcontent = str_replace("</html>", "", $htmlcontent);
    echo($htmlcontent);


    echo("<div class=\"layout\">\r\n");
        echo("<div class=\"filter-items\">\r\n");
            echo("<div class=\"filter-label\" >All block</div>\r\n");
            foreach ($clients as $client) {
                $all_firstchars;
                $firstchar = substr($client['client_name'], 0,1);
                if(strpos($all_firstchars, $firstchar) === false){
                    echo("<div class=\"filter-label\" data-filter=.$firstchar>$firstchar</div>\r\n");
                    $all_firstchars.=$firstchar;
                }
            }
        echo("</div>\r\n");

        echo("<div id=\"freewall\" class=\"free-wall\">\r\n");
            foreach ($clients as $client) {
                $firstchar = substr($client['client_name'], 0,1);
                $client_name = $client['client_name'];
                $client_logo = ($client['client_logo'] != "") ? $client['client_logo'] : "client_logo.gif";
                $client_website = ($client['client_website'] != "") ? $client['client_website'] : "#";
                $client_link = strtolower(str_replace(' ', '-', "client/$client_name"));
                echo("<div class='brick $firstchar' style='width:150px;'>\r\n<div class='client' width='100%'>\r\n<img class='client_logo' width='90%' height='100%' src='$client_logo'/>\r\n<h2 class='client_name'>$client_name</h2>\r\n<a class='client_detail' href='$client_link' rel='external'>More info</a>\r\n<a class='client_website' rel='external' href='$client_website'>&nbsp;link</a>\r\n</div></div>\r\n");
            }
        echo("</div>\r\n");
    echo("</div>\r\n");

    echo("<script>\r\n");
    echo("$(function() {\r\n");
        echo("var wall = new Freewall(\"#freewall\");\r\n");
        echo("wall.reset({\r\n\tselector: \".brick\",animate: true,cellW: 160,cellH: 'auto',fixSize: 0,onResize: function() {wall.refresh();}\r\n});\r\n");
        //echo("wall.filter(\".size23\");\r\n");
        echo("$(\".filter-label\").click(function() {\r\n\t$(\".filter-label\").removeClass(\"active\");\r\n");
        echo("\tvar filter = $(this).addClass('active').data('filter');\r\n");
        echo("\tif (filter) {\r\n\t\twall.filter(filter);}\r\n\t else {\r\n\t\twall.unFilter();}\r\n\t});\r\n");
        echo("wall.fitWidth();\r\n");
    echo("});\r\n");
    echo("wall.refresh();\r\n");
    echo("</script>\r\n");

	//echo("<table data-role='table id='my-table' data-mode='reflow'><thead><tr>");
    //echo("<th><abbr title='ID of the client'>ID</abbr></th>");
    //echo("<th><abbr title='Name of the client'>Name</abbr></th>");
    //
    //echo("</tr></thead><tbody>");
    //
    //  foreach ($results as $row) {
    //  	echo("<tr>");
	//	 echo "<td>" . $row['client_id'] . "</td>";
	//	 echo "<td>" . $row['client_name'] . "</td>";
	//	echo("</tr>");
	//	}
    
    



    echo("</body>\r\n</html>");
?>