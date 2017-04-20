<?php

require_once 'excel_reader2.php';

if(isset($_POST["Import"]))
{
       
    $filename=$_FILES["file"]["tmp_name"];
    if($_FILES["file"]["size"] > 0)
    {
        $file = fopen($filename, "r");
		
        $data = new Spreadsheet_Excel_Reader($filename);
		
		$counter = 1;
		$i = 1;
		$j = 1;
		$arr = array();
		$columsn = array();

		
		while (!$data->val($i,$j) == NULL){

				do {
					//echo $data->val($i,$j)." | ";
					$arr[$i][$j] = $data->val($i, $j);			
					$j++;
				} while (!$data->val($i,$j) == NULL);	
			$i++; $j = 1; //echo "<br>";
		}
		$json = json_encode($arr);
		
		//echo "The Array<br>";
		echo("<pre>");
		print_r($arr).PHP_EOL;
		echo("</pre>");
		//echo "<br>The json<br>";
		//print_r($json);

        fclose($file);
        //echo 'CSV File has been successfully Imported';
        //header('Location: test.php');
    }
    else
        echo 'Invalid File:Please Upload CSV File';
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="nl-nl" >
<head>
	<meta charset="UTF-8"/>

	<title>Assurance Map</title>

	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes"/>

	<meta name="keywords" content=""/>
	<meta name="description" content=""/>
	<meta name="robots" content="noindex,nofollow">
	<link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico"/>
	<link rel="shortcut icon" type="image/vnd.microsoft.icon" href="favicon.ico"/>
	<script src="https://code.jquery.com/jquery-1.12.0.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
	<script type="text/javascript" src="markerclusterer.js"></script>
	<script src="data.json"></script>
	<script type="text/javascript">//var data = <?php echo($json) ?></script>
	<link rel="stylesheet" href="style.css" type="text/css">
	<script type="text/javascript" src="script.js"></script>
	
</head>
<body>
	
	<div id="main">
		<div id="map"></div>
		<div id="top">
			<form enctype="multipart/form-data" method="post" role="form">
			    <div class="form-group">
			        <label for="exampleInputFile">File Upload</label>
			        <input type="file" name="file" id="file" size="150">
			        <button type="submit" class="btn btn-default" name="Import" value="Import">Upload</button>
			    </div>
			</form>
		</div>
		<div id="top_shadow"></div>
		<div id="right">
		
			
		</div>

	</div>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyALJbPGz7G7Uz3P_dpmrwDk1GMlrHGuDmU&callback=initMap"></script>
</body>
</html>



