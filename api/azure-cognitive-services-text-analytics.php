<?php
// This sample uses the Apache HTTP client from HTTP Components (http://hc.apache.org/httpcomponents-client-ga/)
require_once('HTTP/Request2.php');

require_once('Medoo.php');

function GetSentimentScore($text, $lang="en", $unique_id="jmvandeel") {

	$request = new Http_Request2('https://westus.api.cognitive.microsoft.com/text/analytics/v2.0/sentiment');
	$url = $request->getUrl();

	$headers = array(
	    // Request headers
	    'Content-Type' => 'application/json',
	    'Ocp-Apim-Subscription-Key' => '9ef3d24d9af6459c99518791a1ff92d1',
	);

	$request->setHeader($headers);

	$parameters = array(
		// Request paramenters
	);

	$url->setQueryVariables($parameters);

	$request->setMethod(HTTP_Request2::METHOD_POST);

	// Request body
	$request->setBody('{
		"documents": [
			{
			  "language": "'.$lang.'",
			  "id": "'.$unique_id.'",
			  "text": "'.$text.'"
			}
			]
		}');

	try
	{
	    $response = $request->send();
	    $response_body = $response->getBody();
	    $response_data = json_decode($response_body, true);

	    //echo("<pre>"); print_r($response_data); echo("</pre>");

	    echo "<script> console.log('PHP: ',",json_encode($response_data["documents"][0]),");</script>";

	    return $response_data["documents"][0]["score"];
	}
	catch (HttpException $ex)
	{
	    return $ex;
	}


}


//echo ( GetSentimentScore("Marjolein vind ik lief") );

if( isset($_POST['inputtext']) && isset($_POST['lang']) && isset($_POST['submit']))
{
	echo( GetSentimentScore( $_POST['inputtext'], $_POST['lang']) );
	echo("<hr/>");
}

?>

<html>
	<head>

	</head>
	<body>

	<form action="azure-cognitive-services-text-analytics.php" method="post">

	Input text:<br>
	<input type="text" name="inputtext"><br>
	<select name="lang">
		<option value="en" selected>English</option>
		<option value="es">Spanish</option>
		<option value="fr">French</option>
		<option value="pt">Portuguese</option>
	</select>
	<input type="submit" name="submit" value="Send">

	</form>

	</body>
</html>