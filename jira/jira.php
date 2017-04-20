<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>JIRA Search in PHP &middot; AppFusions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 20px;
        padding-bottom: 40px;
      }

      /* Custom container */
      .container-narrow {
        margin: 0 auto;
        max-width: 700px;
      }
      .container-narrow > hr {
        margin: 30px 0;
      }

      /* Main marketing message and sign up button */
      .jumbotron {
        margin: 60px 0;
        text-align: center;
      }
      .jumbotron h1 {
        font-size: 72px;
        line-height: 1;
      }
      .jumbotron .btn {
        font-size: 21px;
        padding: 14px 24px;
      }

      /* Supporting marketing content */
      .marketing {
        margin: 60px 0;
      }
      .marketing p + h4 {
        margin-top: 28px;
      }
      .api-call {
	    white-space: nowrap;
	    overflow: hidden;
	    text-overflow: ellipsis;
      }
      .marketing .description {
	    font-size: 90%;
		min-height: 30px;
		max-height: 200px;
		position: relative;
		overflow: hidden;
		margin-bottom: 10px;
	  }
	  .marketing .description .stop { 
		position: absolute; 
		bottom: 0; left: 0;
		width: 100%; 
		text-align: center; 
		margin: 0; padding: 30px 0; 

		/* "transparent" only works here because == rgba(0,0,0,0) */ 
		background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, transparent),color-stop(1, #fff));
	        background-image: -webkit-linear-gradient(top, transparent, #fff);
	        background-image: -moz-linear-gradient(top, transparent, #fff);
	        background-image: -ms-linear-gradient(top, transparent, #fff);
	        background-image: -o-linear-gradient(top, transparent, #fff);
	  }
	  .marketing .meta {
	  	font-size: 90%;
	    color: #999;
	  }

	  input.input-large[type=search] {
    		width: 400px;
    		height:40px;
		}
    </style>
    <link href="assets/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="assets/js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
      <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
                    <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">
                                   <link rel="shortcut icon" href="assets/ico/favicon.png">
  </head>

  <body>

    <div class="container-narrow">

      <div class="masthead">
        <ul class="nav nav-pills pull-right">
           <li><a href="#">Blogpost</a></li>
          <li><a target="_blank" href="https://bitbucket.org/dvdsmpsn/php-based-jira-search/src">Source code</a></li>
        </ul>
        <h3 class="muted">A PHP based website</h3>
      </div>

      <hr>










<?php 
	
		$query = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING,!FILTER_FLAG_STRIP_LOW);
	
?>


      <div class="jumbotron">
        <h1>JIRA Search</h1>
        <p class="lead">Search JIRA from an external PHP based website.</p>
 
		<form class="form-search">
		<div class="input-append">
		  <input type="search" name="q" value="<?php echo isset($query)?$query:""; ?>" placeholder="Search JIRA" class="input-large search-query" />
		  <button type="submit" class="btn btn-success">Search</button>
		</div>
		</form>
      </div>

      <hr>



<?php if (!is_null($query) && $query) {
		try{
		$query = rawurlencode("summary ~ \"$query\" OR description ~ \"$query\" OR comment ~ \"$query\"");

		$baseUrl = 'http://buildserver.qa.dsm.com/jira';
		$url = $baseUrl.'/rest/api/2/search?jql='.$query.'&maxResults=10&_='. time(); 

		// To enable authenticated search: 
		$url .= "&os_username=852605&os_password=1.Oktober2016";
		
		if(ini_get('allow_url_fopen'))
		{
			//respect headers
			$opts = array('http' =>array(
				'method'=>'GET',
				'header'=>'Accept:application/json'
				) , ); 

			$response = @file_get_contents($url);
			
			if($response === false)
			{
				throw new Exception("nothing returned");
			}
			$response = json_decode($response);

			$results = $response->issues;
		}
		else
		{
			$error = "not correctly configured"; 
			// @todo could try with curl thoughts?
		}
	}
	catch(Exception $e)
	{
			$error = "problem searching Jira nothing returned";
	}
?>	
      <div class="row-fluid marketing">	
	
		<h3>Results</h3>
		<div>
			
			<p class="api-call">Searching: <a target="_blank" href="<?php echo $url; ?>"><?php echo $url; ?></a></p>
			<?php if(empty($error)):?>

			<p>Showing the first <?php echo count($results) ?> results of <?php echo $response->total?>:</p>	
	
			<ol>
			<?php foreach($results as $item) {?>	
				<li>
					<strong><a href="<?php echo $baseUrl.'/browse/' . $item->key ?>"><?php echo $item->key . ' ' . $item->fields->summary ?></a></strong> 
					in project <a href="<?php echo $baseUrl.'/browse/' . $item->fields->project->key ?>"><?php echo $item->fields->project->name ?></a>
					<div class="meta">
						<?php echo $item->fields->issuetype->name?> &middot; 
						Opened by <?php echo $item->fields->reporter->displayName?> &middot; 
						<?php if (isset($item->fields->assignee->displayName)): ?> Assigned to <?php echo $item->fields->assignee->displayName?> 
						<?php else: ?> Unassigned <?php endif; ?>
					</div>
					<div class="description">
						<?php echo $item->fields->description ?>
						<div class="stop"></div>
					</div>
				</li>
			<?php } ?>
			</ol>
      </div>
  <?php else:?>
  	<div class="alert alert-error">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <h4>Shucks!</h4>
  something went wrong ... <?php echo $error;?>
</div>
  <?php endif;?>
      <hr>
<?php } ?>










      <div class="footer">
        <p>&copy; 2013 <a href="http://www.appfusions.com/">AppFusions</a> &middot; <a href="http://davidsimpson.me">David Simpson</a> &middot; <a href="http://getbootstrap.com/2.3.2/examples/marketing-narrow.html">Page layout</a>.</p>
      </div>

    </div> <!-- /container -->


    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap-transition.js"></script>
    <script src="assets/js/bootstrap-alert.js"></script>
    <script src="assets/js/bootstrap-modal.js"></script>
    <script src="assets/js/bootstrap-dropdown.js"></script>
    <script src="assets/js/bootstrap-scrollspy.js"></script>
    <script src="assets/js/bootstrap-tab.js"></script>
    <script src="assets/js/bootstrap-tooltip.js"></script>
    <script src="assets/js/bootstrap-popover.js"></script>
    <script src="assets/js/bootstrap-button.js"></script>
    <script src="assets/js/bootstrap-collapse.js"></script>
    <script src="assets/js/bootstrap-carousel.js"></script>
    <script src="assets/js/bootstrap-typeahead.js"></script>



	<!-- modal example from http://bootply.com/61676 -->
	<script>
	  $(function() {
	    $('.marketing li a').click(function(e){
		  var link = $(this).attr('href');		
		  var title = $(this).text();		
	      $('#myModal').on('show', function () {
	        $('iframe').attr("src", link);
	        $('#myModal h3').text(title);
			
		  });
	      $('#myModal').modal({show:true});
		  e.preventDefault();
		  return false;
	    });
      });
	</script>
	<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">×</button>
				<h3>Result page</h3>
		</div>
		<div class="modal-body">
	      <iframe src="" style="zoom:0.60" width="99.6%" height="900px" frameborder="0"></iframe>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal">OK</button>
		</div>
	</div>
	<style>
	  #myModal 
	  {
	    width: 80%; 
	    margin-left: -40%; 
	  } 

	  #myModal .modal-body {
	    max-height: 1000px;
	  }
	</style>

  </body>
</html>
