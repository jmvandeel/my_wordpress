<html>
<head>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	<link rel="stylesheet" href="http://www.vandeel.com/jira/assets/css/jquery.circliful.css">

	<style type="text/css">

	#main{
		position: relative;
		display: block;
	}

	#text-sentiment{
		position: absolute;
  		top: 0;
  		left: 0;
	}
		
	#background-circle{
		/*background-color: rgba(80, 80, 80, 0.6); */
		border-radius: 50%;
		width: 200px;
		height: 200px;
		display: block;
		position: absolute;
  		top: 205;
  		left: 205;
  		z-index: 10;
  		text-align: center;
  		padding-top: 200px;
	}

	input,
	select,
	a#submit {
	    width: 70%;
	    padding: 6px 10px;
	    margin: 4px auto;
	    box-sizing: border-box;
	    border: 1px solid #666;
    	border-radius: 4px;
    	background-color: #eee;
    	color: #666;
	}
	a#submit{
		display: block;
		background-color: #3498DB;
		color: #eee;
		text-decoration: none;
	}

	</style>


</head>
<body style="margin: 0;">
	<div id="main">
		<div id="text-sentiment" class="circliful"
			data-dimension="100" 
			data-targetPercent="100" 
			data-targetTextSize="0"
			data-noPercentageSign="1">
		</div>

		<div id="background-circle">
			<form method="post">
				<input type="text" id="inputtext" name="inputtext" value="Type something great!" onfocus="this.value = '';"><br>
				<select id="lang" name="lang">
					<option value="en" selected>English</option>
					<option value="es">Spanish</option>
					<option value="fr">French</option>
					<option value="pt">Portuguese</option>
				</select><br/>
				<a id="submit" href="#">SEND</a>
			</form>
		</div>
	</div>


	<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

	<script language="javascript">
        function autoResizeDiv()
        {
            document.getElementById('main').style.height = window.innerHeight +'px';
            document.getElementById('text-sentiment').style.height = window.innerHeight +'px';
            document.getElementById('text-sentiment').style.width = window.innerHeight +'px';
            document.getElementById('background-circle').style.height = window.innerHeight * 0.5 +'px';
            document.getElementById('background-circle').style.width = window.innerHeight * 0.5 +'px';
        }
        window.onresize = autoResizeDiv;
        autoResizeDiv();
    </script>

	<script src="http://www.vandeel.com/jira/assets/js/jquery.circliful.js"></script>
	<script>
		$( document ).ready(function() {
			$(window).keydown(function(event){
			    if(event.keyCode == 13) {
			      event.preventDefault();
			      return false;
			    }
			  });
			$('.circliful').circliful({
			   foregroundColor: "#3498DB",
			   backgroundColor: "#eee",
			   pointColor: "none", 				// fill color of point circle
			   fillColor: 'none',				// fill color
			   foregroundBorderWidth: 15,		// width of foreground circle border
			   backgroundBorderWidth: 15,		// width of background circle border
			   pointSize: 10,					// Size of point circle
			   fontColor: '#3498DB',			// font color
			   percent: 81.77,					// from 0 to 100
			   animation: 1,					// if the circle should be animated initialy
			   animationStep: 5,				// how fast or slow the animation should be from 0 to 100
			   icon: 'none',					// font awesome icon classes
			   iconSize: '20px',				// icon size
			   iconColor: '#666',				// icon color
			   iconPosition: 'top',				// top, bottom, left, right or middle
			   target: 0,						// target percentage
			   start: 0,						// start percentage
			   showPercent: 1,					// show percent
			   percentageTextSize: 22,			// font size of the percentage text
			   textAdditionalCss: '',			// additonal css for the percentage text
			   targetPercent: 0,				// draws a circle around the main circle
			   targetTextSize: 14,				// font size of the target percentage
			   targetColor: '#666',				// fill color of the target circle
			   text: null,						// info text shown bellow the percentage in the circle
			   textStyle: null,					// css inline style you wanna add to your info text
			   textColor: '#666',				// font color of the info text
			   percentages: null,
			   textBelow: false,				// aligns string of "text" property centered below the circle
			   noPercentageSign: false,			// to hide the percentage sign
			   replacePercentageByText: null,	// replace the percentage shown in the circle by text
			   halfCircle: false,				// draw half circle see example bellow
			   animateInView: false,			// animate circle on scroll into view
			   decimals: 2,						// number of decimal places to show
			   alwaysDecimals: false			// shows decimals while animating instead of only at the end or if less than 1			  
			});
		});
	</script>

	<script type="text/javascript">

		var hexDigits = new Array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"); 

		function rgb2hex(rgb) {
			rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
			return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
		}
		function hex(x) {
			return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
		}
		
		$("a#submit").on('click',function() {
		    $.ajax({
		    	url:'azure-cognitive-services-text-analytics.php',
		    	type:'post',
		    	data:{
		    		submit : "send",
		    		inputtext : document.getElementById('inputtext').value,
		    		lang : "en"
		    	},
		    	success: function(output) {
                	//alert(output);

                	$Red = Math.round( 255 - (255 * output) );
                	$Green = Math.round( 255 * output );

                	$rgb_color = 'rgb('+$Red+', '+$Green+', 0)';

                	//alert($rgb_color);

                	$hex_color = rgb2hex($rgb_color);

                	//alert($hex_color);


                	// Set Circle Value
                	$('.circliful').circliful({
                		percent: output * 100,
                		decimals: 2,
                		fontColor: $hex_color,
                		foregroundColor: $hex_color

                	});

                	//document.getElementById('background-circle').style.display =  "none";

                }
			});
		});
	</script>

</body>
</html>