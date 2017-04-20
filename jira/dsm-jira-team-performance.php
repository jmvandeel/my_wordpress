<?php
	function JiraRequest($url){
		if(ini_get('allow_url_fopen'))
		{
			//respect headers
			$opts = array('http' =>array(
				'method'=>'GET',
				'header'=>'Accept:application/json'
				) , ); 

			// To enable authenticated search: 
			$url .= "&os_username=852605&os_password=1.Januari2017";
			$url .= '&_='. time();

			$response = @file_get_contents($url);
			
			if($response === false)
			{
				throw new Exception("nothing returned");
			}
			$response = json_decode($response);

			$issues = $response->issues;

			return $response;
		}
		else
		{
			$error = "not correctly configured"; 
			echo("not correctly configured");
		}
	}

	// base url
	$baseUrl = 'http://buildserver.qa.dsm.com/jira';

	$jiraApiUrl = '/rest/api/2/search?maxResults=500&jql=';
	$agileApiUrl = '/rest/agile/1.0'; //https://docs.atlassian.com/jira-software/REST/latest/
	$boardId = 442;



	// ######################################## GET ALL SPRINTS ###############################
	//: http://buildserver.qa.dsm.com/jira/rest/agile/1.0/board/442/sprint?state=active,closed
	$getAllSprints = $baseUrl.$agileApiUrl.'/board/'.$boardId.'/sprint?state=active,closed'; //echo($getAllSprints);
	$allSprints = JiraRequest($getAllSprints)->values; //echo('<hr/><pre>');print_r($allSprints);echo('</pre>');

if( isset($_POST["sprint"]) ){
	// ######################################## GET ACTIVE SPRINT #############################
	//: http://buildserver.qa.dsm.com/jira/rest/agile/1.0/board/442/sprint?state=active
	//$getActiveSprint = $baseUrl.$agileApiUrl.'/board/'.$boardId.'/sprint?state=active'; //echo($getActiveSprint);
	//$activeSprint = JiraRequest($getActiveSprint)->values; //echo('<hr/><pre>');print_r($activeSprint);echo('</pre>');
	//$activeSprintId = $activeSprint[0]->id; //echo("<br/>activeSprintId: ".$activeSprintId);
	//$activeSprintName = $activeSprint[0]->name; //echo("<br/>activeSprintName: ".$activeSprintName);
	
	$activeSprintId = $_POST["sprint"];
	// ######################################## GET ACTIVE SPRINT ISSUES #############################
	$getActiveSprintIssues = $baseUrl.$jiraApiUrl.urlencode('sprint in ('.$activeSprintId.') AND (status in (Closed, Done, "PROD - Ready for deploy") OR "Epic Link" in (WCMR-502, WCMR-503, WCMR-1724) )'); //echo($getActiveSprintIssues);
	$activeSprintIssues = JiraRequest($getActiveSprintIssues)->issues; //echo('<hr/><pre>');print_r($activeSprintIssues);echo('</pre>');

	// ######################################## GET EPICS #############################
	$getEpics = $baseUrl.$jiraApiUrl.urlencode('project = IWAS-WCM-Release and issuetype = Epic'); //echo($getEpics);
	$Epics = JiraRequest($getEpics)->issues; //echo('<hr/><pre>');print_r($Epics);echo('</pre>');
	//$Epic = $Epics[18];
	//$Epic = array_search('WCMR-104', $Epics);
	$Epic = array_search('WCMR-104', array_column($Epics, 'key'));
	//echo("-> ".$Epic->fields->summary);


	$keys = array_column($Epics, 'key');
	//echo('<hr/><pre>');print_r($keys);echo('</pre>');

	// ######################################## ACTIVE SPRINT TOTALS #################################
	$TotalNumberofItems = 0;
	$TotalNumberofStories = 0;
	$TotalNumberofIncidents = 0;
	$TotalNumberofImprovements = 0;
	$TotalNumberofServiceRequest = 0;
	// STORY POINTS
	$TargetStoryPoints = 95;
	$TotalStoryPoints = 0;
	$TotalStoryPointsDevelopment = 0;
	$TotalStoryPointsIncidents = 0;
	$TotalStoryPointsImprovements = 0;
	$TotalStoryPointsServiceRequest = 0;
	// TIME SPENT
	$TargetTimespentonSprintInHours = 500;
	$TotalTimespentonSprint = 0;
	$TotalTimespentonDevelopmentPlusDeployment = 0;
	$TotalTimespentonIncidents = 0;
	$TotalTimespentonImprovements = 0;
	$TotalTimespentonServiceRequest = 0;
	$TotalTimespentonScrum = 0;
	$TotalTimespentonDeployment = 0;
	

	foreach($activeSprintIssues as $JiraIssue) {

		// filter In Progress issues, these will count for next sprint
		if($JiraIssue->fields->status->name == "In Progress"){continue;}


		
		$TotalStoryPoints += $JiraIssue->fields->customfield_10002;
		$TotalTimespentonSprint += $JiraIssue->fields->aggregatetimespent;

		switch ($JiraIssue->fields->issuetype->name) {
		    case "Story":
		        if($JiraIssue->fields->customfield_10006 == "WCMR-429"){
		        	$TotalNumberofItems ++;
		        	$TotalNumberofImprovements ++;
		        	$TotalStoryPointsImprovements += $JiraIssue->fields->customfield_10002;
		        	$TotalTimespentonImprovements += $JiraIssue->fields->aggregatetimespent;
		        } else {
		        	$TotalNumberofItems ++;
		        	$TotalNumberofStories ++;
		        	$TotalStoryPointsDevelopment += $JiraIssue->fields->customfield_10002;
		        	$TotalTimespentonDevelopmentPlusDeployment += $JiraIssue->fields->aggregatetimespent;
		        }
		        break;
		    case "Incident":
		        if($JiraIssue->fields->customfield_10006 == "WCMR-429"){
		        	$TotalNumberofItems ++;
		        	$TotalNumberofImprovements ++;
		        	$TotalStoryPointsImprovements += $JiraIssue->fields->customfield_10002;
		        	$TotalTimespentonImprovements += $JiraIssue->fields->aggregatetimespent;
		        } else {
		        	$TotalNumberofItems ++;
		        	$TotalNumberofIncidents ++;
		        	$TotalStoryPointsIncidents += $JiraIssue->fields->customfield_10002;
		        	$TotalTimespentonIncidents += $JiraIssue->fields->aggregatetimespent;
		        }
		        break;
		    case "Service Request":
		        if($JiraIssue->fields->customfield_10006 == "WCMR-502"):
		        		// SCRUM
		        		$TotalTimespentonScrum += $JiraIssue->fields->aggregatetimespent;
		        	elseif($JiraIssue->fields->customfield_10006 == "WCMR-1724"):
		        		// DEPLOYMENT
		        		$TotalTimespentonDeployment += $JiraIssue->fields->aggregatetimespent;
		        		$TotalTimespentonDevelopmentPlusDeployment += $JiraIssue->fields->aggregatetimespent;
		        	else:
		        		$TotalNumberofItems ++;
		        		$TotalNumberofServiceRequest ++;
		        		$TotalTimespentonServiceRequest += $JiraIssue->fields->aggregatetimespent;
		        endif;
		        break;
		}

		// numbers
		$PercentageofStories = round( ((100 / $TotalNumberofItems) * $TotalNumberofStories), 2);
		$PercentageofIncidents = round( ((100 / $TotalNumberofItems) * $TotalNumberofIncidents), 2);
		$PercentageofImprovements = round( ((100 / $TotalNumberofItems) * $TotalNumberofImprovements), 2);
		$PercentageofServiceRequest = round( ((100 / $TotalNumberofItems) * $TotalNumberofServiceRequest), 2);
		// storypoints
		$PercentageStoryPointsDevelopment = round( (100 / $TotalStoryPoints) * $TotalStoryPointsDevelopment, 2);
		$PercentageStoryPointsIncidents = round( (100 / $TotalStoryPoints) * $TotalStoryPointsIncidents, 2);
		$PercentageStoryPointsImprovements = round( (100 / $TotalStoryPoints) * $TotalStoryPointsImprovements, 2);
		$PercentageStoryPointsServiceRequest = round( (100 / $TotalStoryPoints) * $TotalStoryPointsServiceRequest, 2);
		// timespent
		$PercentageTimespentonDevelopmentPlusDeployment = round( (100 / $TotalTimespentonSprint) * $TotalTimespentonDevelopmentPlusDeployment, 1);
		$PercentageTimespentonIncidents = round( (100 / $TotalTimespentonSprint) * $TotalTimespentonIncidents, 1);
		$PercentageTimespentonImprovements = round( (100 / $TotalTimespentonSprint) * $TotalTimespentonImprovements, 1);
		$PercentageTimespentonServiceRequest = round( (100 / $TotalTimespentonSprint) * $TotalTimespentonServiceRequest, 1);
		$PercentageTimespentonScrum = round( (100 / $TotalTimespentonSprint) * $TotalTimespentonScrum, 1);
		$PercentageTimespentonDeployment = round( (100 / $TotalTimespentonSprint) * $TotalTimespentonDeployment, 1);



		// CONSOLE DEBUG
		//echo ("<script>console.log('".$JiraIssue->key .": ".$JiraIssue->fields->customfield_10002. " - Total: " . $TotalStoryPoints."')</script>");
	}

	$TotalTimespentonSprintInHours = $TotalTimespentonSprint / 60 /60;
	// PRODUCTIVITY
	$Target 		= ($TargetStoryPoints / $TargetTimespentonSprintInHours);  //echo("<br/>Target (100%): ".$Target . " = ". $TargetStoryPoints . " / ". $TargetTimespentonSprintInHours);
	$Actual			= ($TotalStoryPoints / $TotalTimespentonSprintInHours);  //echo("<br/>Actual: ".$Actual);
	$Factor 		= 100 / $Target; //echo("<br/>Factor: ".$Factor);
	$DeviationOnHours = $TotalTimespentonSprintInHours / $TargetTimespentonSprintInHours; //echo("<br/>DeviationOnHours: ".$DeviationOnHours); 
	$Productivity 	= $Actual * $Factor * $DeviationOnHours; //echo("<br/>Productivity: ".$Productivity);
} //end if isset
?>
<html>
<head>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	<link rel="stylesheet" href="assets/css/jquery.circliful.css">
	<style>
		html, body {
    		max-width: 100%;
    		overflow-x: hidden;
		}
		html{
			background-color: #ccc;
		}
		body{
			margin: 10px;
			border: solid 1px #aaa;
		}
		p, h1, h2, h3, .table-responsibe{
			margin: 5px 10px;
		}
		h3{
			font-size: 16px;
		}
		tbody{
			border: solid 1px #ccc;
		}

		.right{
			text-align:right;
		}
		.center{
			text-align:center;
		}
		.IssueOverview{
			display: none;
		}

		.development{
			color: #00b159;
			background-color: #00b159;
		}
		.incidents{
			color: #d11141;
			background-color: #d11141;
		}
		.serviceRequests{
			color: #f37735;
			background-color: #f37735;
		}
		.improvements{
			color: #00aedb;
			background-color: #00aedb;
		}
		.scrum{
			color: #ffc425;
			background-color: #ffc425;
		}
		.deployment{
			color: #8d5524;
			background-color: #8d5524
		}
		span.label{
			color: #ffffff;

		}
		td{
			background: none;
		}
		canvas{
			display: block;
			margin: 0 auto;
			text-align: center;
		}
		form{
			background-color: #aaa;
			color: #fff;
			padding:10px;
		}
		select, input{
			color: #000;
		}
		h3 td{
			padding: 2px 3px;
			text-align: center;
		}


	</style>
</head>
<body>

<form method="post">
	<label>Selecteer een sprint</label>
	<select name="sprint">
		<option value="">Kies een sprint</option>
		<?php 
			foreach (array_reverse($allSprints) as $sprint) {
				echo('<option value="'.$sprint->id.'">'.$sprint->name.'</option>');
			}
		?>
	</select>
	<input type="submit" name="submit" value="Generate" />
</form>

<?php
	if( !isset($_POST["submit"]) ){ 
		echo("</body></html>"); 
		exit;
	} // endif

	//echo("Sprintnr: ".$_POST["sprint"]);
?>

	<div class="table-responsibe">
		<table class="table table-striped IssueOverview">

			<tr>
				<th class="">#</th>
				<th class="">Type</th>
				<th class="">Key</th>
				<th class="">Status</th>
				<th class="">Summary</th>
				<th class="">Epic</th>
				<th class="right">Time Spent</th>
				<th class="">Story Points</th>
				<th class="">Created</th>
				<th class="">Resolved</th>
				<th class="">Priority</th>
			</tr>

			<?php 
				$i = 0;
				foreach($activeSprintIssues as $JiraIssue) {
				
				// filter In Progress issues, these will count for next sprint
				if($JiraIssue->fields->status->name == "In Progress"){continue;}

				$i ++;
				?>

			<tr>
				<td><?php echo $i ?></td>
				<td class="issuetype"><?php echo $JiraIssue->fields->issuetype->name; ?></td>
				<td class="key"><?php echo $JiraIssue->key; ?></td>
				<td class="status"><?php echo $JiraIssue->fields->status->name; ?></td>
				<td class="summary"><?php echo $JiraIssue->fields->summary; ?></td>
				<td class="epic"><?php echo $JiraIssue->fields->customfield_10006; ?></td>
				<td class="timespent right"><?php echo( gmdate("H:i", $JiraIssue->fields->aggregatetimespent) ); ?> (h:m)</td>
				<td class="storypoints"><?php echo $JiraIssue->fields->customfield_10002; ?></td>
				<td class="created"><?php echo $JiraIssue->fields->created; ?></td>
				<td class="resolution"><?php echo $JiraIssue->fields->resolutiondate; ?></td>
				<td class="priority"><?php echo $JiraIssue->fields->priority->name; ?></td>
			</tr>
			
			<?php } ?>

			<tr>
				<th class="">#</th>
				<th class="">Type</th>
				<th class="">Key</th>
				<th class="">Status</th>
				<th class="">Summary</th>
				<th class="">Epic</th>
				<th class="right">Time Spent</th>
				<th class="">Story Points</th>
				<th class="">Created</th>
				<th class="">Resolved</th>
				<th class="">Priority</th>
			</tr>

		</table>
	</div>
	
	<p>Report generated: <?php echo( date("F j, Y, G:i") ); ?></p>

	<div class="row">
        <div class="col-md-4">
        	<h2 class="center">Story Points</h2>
        	<h3 class="center">The output of the team delivery (velocity)</h3>
        	<div id="circStoryPoints" class="circliful"
				data-dimension="100"  
				data-percent="<?php echo( round($TotalStoryPoints)); ?>"
				data-targetPercent="<?php echo( round($TargetStoryPoints)); ?>" 
				data-noPercentageSign="1">
			</div>
        </div>
        <div class="col-md-4">
        	<h2 class="center">Productivity</h2>
        	<h3>
        		<table>
        		<tr>
        			<td>(SP / TimeSpent)</td>
        			<td> x </td>
        			<td>( 100 / (TargetSP / TargetTimeSpent) )</td>
        			<td>x</td>
        			<td>( % TimeLogged )</td>
        		</tr>
        		<tr>
        			<td>(<?php echo($TotalStoryPoints . " / " . $TotalTimespentonSprintInHours); ?>)</td>
        			<td> x </td>
        			<td>(100 / <?php echo($Target); ?>)</td>
        			<td> x </td>
        			<td>(<?php echo($DeviationOnHours); ?>)</td>
        		</tr>
        		</table>
        	</h3>        	
        	<div id="circProductivity" class="circliful" 
				data-dimension="100"  
				data-percent="<?php echo( round($Productivity)); ?>"
				data-targetPercent="100" 
				data-noPercentageSign="0"
				data-percentageTextSize="20">
			</div>
        </div>
        <div class="col-md-4">
        	<h2 class="center">Time Spent</h2>
        	<h3 class="center">Sum of the hours logged in JIRA on Sprint scope</h3>
        	<div id="circTimeSpent" class="circliful"
				data-dimension="100" 
				data-percent="<?php echo( round((100 / $TargetTimespentonSprintInHours) * $TotalTimespentonSprintInHours) ); ?>"
				data-replacePercentageByText="<?php echo( round($TotalTimespentonSprintInHours) ); ?>"
				data-targetPercent="100" 
				data-targetTextSize="0"
				data-text="<?php echo( round($TargetTimespentonSprintInHours) ); ?>"
				data-noPercentageSign="1">
			</div>
        </div>
        
      </div>

	<div class="row">
        <div class="col-md-4">
        	<h2 class="center">Number of</h2>
        	<table class="donutchart">
				<tr><th>sortOrder</th><th>value</th><th>color</th><th>description</th></tr>
				<tr><td>0</td><td><?php echo( $TotalNumberofStories ); ?></td><td>#00b159</td><td>DEV</td></tr>
				<tr><td>1</td><td><?php echo( $TotalNumberofIncidents); ?></td><td>#d11141</td><td>OPS</td></tr>
				<tr><td>2</td><td><?php echo( $TotalNumberofServiceRequest ); ?></td><td>#f37735</td><td>Service</td></tr>
				<tr><td>3</td><td><?php echo( $TotalNumberofImprovements ); ?></td><td>#00aedb</td><td>IMP</td></tr>
			</table>
        </div>
        <div class="col-md-4">
        	<h2 class="center">Story Points</h2>
        	<table class="donutchart">
				<tr><th>sortOrder</th><th>value</th><th>color</th><th>description</th></tr>
				<tr><td>0</td><td><?php echo( $TotalStoryPointsDevelopment ); ?></td><td>#00b159</td><td>DEV</td></tr>
				<tr><td>1</td><td><?php echo( $TotalStoryPointsIncidents); ?></td><td>#d11141</td><td>INC</td></tr>
				<tr><td>2</td><td><?php echo( $TotalStoryPointsImprovements ); ?></td><td>#00aedb</td><td>IMP</td></tr>
			</table>
        </div>
        <div class="col-md-4">
        	<h2 class="center">Time Spent</h2>
        	<table class="donutchart">
				<tr><th>sortOrder</th><th>value</th><th>color</th><th>description</th></tr>
				<tr><td>0</td><td><?php echo( $TotalTimespentonDevelopmentPlusDeployment ); ?></td><td>#00b159</td><td>DEV</td></tr>
				<tr><td>1</td><td><?php echo( $TotalTimespentonIncidents); ?></td><td>#d11141</td><td>INC</td></tr>
				<tr><td>2</td><td><?php echo( $TotalTimespentonServiceRequest ); ?></td><td>#f37735</td><td>Service</td></tr>
				<tr><td>3</td><td><?php echo( $TotalTimespentonImprovements ); ?></td><td>#00aedb</td><td>IMP</td></tr>
				<tr><td>3</td><td><?php echo( $TotalTimespentonScrum ); ?></td><td>#ffc425</td><td>Scrum</td></tr>
			</table>
        </div>
      </div>


      <div class="row">
        <div class="col-md-12">

      	<h2>Sprint Metrics</h2>
        <div class="table-responsibe">
			<table class="table table-striped SprintMetrics">
				<tr>
					<th class="right">&nbsp;</th>
					<th class="right">#</th>
					<th class="right"># %</th>
					<th class="right">SP</th>
					<th class="right">SP %</th>
					<th class="right">Time Spent</th>
					<th class="right">Time Spent %</th>
				</tr>
				<tr>
					<td>Total</td>
					<td class="right"><?php echo( $TotalNumberofItems ); ?></td>
					<td class="right">100%</td>
					<td class="right"><?php echo( $TotalStoryPoints ); ?></td>
					<td class="right">100%</td>
					<td class="right"><?php echo( round(($TotalTimespentonSprint / 60 / 60), 0, PHP_ROUND_HALF_DOWN) . gmdate(":i", $TotalTimespentonSprint) ); ?> (h:m)</td>
					<td class="right">100%</td>
				</tr>
				<tr>
					<td><span class="label development">Development</span> + <span  class="label deployment">Deployment</span></td>
					<td class="right"><?php echo( $TotalNumberofStories ); ?></td>
					<td class="right"><?php echo( $PercentageofStories ); ?>%</td>
					<td class="right"><?php echo( $TotalStoryPointsDevelopment ); ?></td>
					<td class="right"><?php echo( $PercentageStoryPointsDevelopment ); ?>%</td>
					<td class="right"><?php echo( round(($TotalTimespentonDevelopmentPlusDeployment / 60 / 60), 0, PHP_ROUND_HALF_DOWN) . gmdate(":i", $TotalTimespentonDevelopmentPlusDeployment) ); ?> (h:m)</td>
					<td class="right"><?php echo( $PercentageTimespentonDevelopmentPlusDeployment ); ?>%</td>
				</tr>
				<tr>
					<td><span class="label incidents">Incidents</span></td>
					<td class="right"><?php echo( $TotalNumberofIncidents ); ?></td>
					<td class="right"><?php echo( $PercentageofIncidents ); ?>%</td>
					<td class="right"><?php echo( $TotalStoryPointsIncidents ); ?></td>
					<td class="right"><?php echo( $PercentageStoryPointsIncidents ); ?>%</td>
					<td class="right"><?php echo( round(($TotalTimespentonIncidents / 60 / 60), 0, PHP_ROUND_HALF_DOWN) . gmdate(":i", $TotalTimespentonIncidents) ); ?> (h:m)</td>
					<td class="right"><?php echo( $PercentageTimespentonIncidents ); ?>%</td>
				</tr>
				<tr>
					<td><span class="label serviceRequests">Service Requests</span></td>
					<td class="right"><?php echo( $TotalNumberofServiceRequest ); ?></td>
					<td class="right"><?php echo( $PercentageofServiceRequest ); ?>%</td>
					<td class="right">-</td>
					<td class="right">-</td>
					<td class="right"><?php echo( round(($TotalTimespentonServiceRequest / 60 / 60), 0, PHP_ROUND_HALF_DOWN) . gmdate(":i", $TotalTimespentonServiceRequest) ); ?> (h:m)</td>
					<td class="right"><?php echo( $PercentageTimespentonServiceRequest ); ?>%</td>
				</tr>
				<tr>
					<td><span class="label improvements">Improvements</span></td>
					<td class="right"><?php echo( $TotalNumberofImprovements ); ?></td>
					<td class="right"><?php echo( $PercentageofImprovements ); ?>%</td>
					<td class="right"><?php echo( $TotalStoryPointsImprovements ); ?></td>
					<td class="right"><?php echo( $PercentageStoryPointsImprovements ); ?>%</td>
					<td class="right"><?php echo( round(($TotalTimespentonImprovements / 60 / 60), 0, PHP_ROUND_HALF_DOWN) . gmdate(":i", $TotalTimespentonImprovements) ); ?> (h:m)</td>
					<td class="right"><?php echo( $PercentageTimespentonImprovements ); ?>%</td>
				</tr>
				<tr>
					<td><span class="label scrum">Scrum</span></td>
					<td class="right">-</td>
					<td class="right">-</td>
					<td class="right">-</td>
					<td class="right">-</td>
					<td class="right"><?php echo( round(($TotalTimespentonScrum / 60 / 60), 0, PHP_ROUND_HALF_DOWN) . gmdate(":i", $TotalTimespentonScrum) ); ?> (h:m)</td>
					<td class="right"><?php echo( $PercentageTimespentonScrum ); ?>%</td>
				</tr>
				<tr>
					<td><span class="label deployment">Deployment</span></td>
					<td class="right">-</td>
					<td class="right">-</td>
					<td class="right">-</td>
					<td class="right">-</td>
					<td class="right"><?php echo( round(($TotalTimespentonDeployment / 60 / 60), 0, PHP_ROUND_HALF_DOWN) . gmdate(":i", $TotalTimespentonDeployment) ); ?> (h:m)</td>
					<td class="right"><?php echo( $PercentageTimespentonDeployment ); ?>%</td>
				</tr>

				</table>
			</div>
		</div>
	



	<script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha256-/SIrNqv8h6QGKDuNoLGA4iret+kyesCkHGzVUUV0shc=" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>


	<script type="text/javascript" src="assets/js/jquery.chart.js"></script>
	<script type="text/javascript">
		$(function(){
			if(!(/^\?noconvert/gi).test(location.search))
			$(".donutchart").donutChart({
				width: 400,
				height:550,
				legendSize: 20,
				legendSizePadding: 1,
				label: "",
				hasBorder: false,
				hasShadow: false
			})

		});		
	</script>
	<script src="assets/js/jquery.circliful.js"></script>
	<script>
		$( document ).ready(function() {
			$('.circliful').circliful({
			   foregroundColor: "#3498DB",
			   backgroundColor: "#eee",
			   pointColor: "none", 				// fill color of point circle
			   fillColor: 'none',				// fill color
			   foregroundBorderWidth: 15,		// width of foreground circle border
			   backgroundBorderWidth: 15,		// width of background circle border
			   pointSize: 28.5,					// Size of point circle
			   fontColor: '#3498DB',			// font color
			   percent: 75,						// from 0 to 100
			   animation: 1,					// if the circle should be animated initialy
			   animationStep: 5,				// how fast or slow the animation should be from 0 to 100
			   icon: 'none',					// font awesome icon classes
			   iconSize: '20px',				// icon size
			   iconColor: '#999',				// icon color
			   iconPosition: 'top',				// top, bottom, left, right or middle
			   target: 0,						// target percentage
			   start: 0,						// start percentage
			   showPercent: 1,					// show percent
			   percentageTextSize: 22,			// font size of the percentage text
			   textAdditionalCss: '',			// additonal css for the percentage text
			   targetPercent: 0,				// draws a circle around the main circle
			   targetTextSize: 14,				// font size of the target percentage
			   targetColor: '#aaa',				// fill color of the target circle
			   text: null,						// info text shown bellow the percentage in the circle
			   textStyle: null,					// css inline style you wanna add to your info text
			   textColor: '#aaa',				// font color of the info text
			   percentages: null,
			   textBelow: false,				// aligns string of "text" property centered below the circle
			   noPercentageSign: false,			// to hide the percentage sign
			   replacePercentageByText: null,	// replace the percentage shown in the circle by text
			   halfCircle: false,				// draw half circle see example bellow
			   animateInView: false,			// animate circle on scroll into view
			   decimals: 0,						// number of decimal places to show
			   alwaysDecimals: false			// shows decimals while animating instead of only at the end or if less than 1			  
			});
		});
	</script>
</body>
</html>