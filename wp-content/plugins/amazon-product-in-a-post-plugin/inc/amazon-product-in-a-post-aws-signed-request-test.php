<?php
class amazonAPPIP_NewRequest{
	var $type;
	function __construct($type ='ajax'){
		$this->type = $type;
		if($type == 'ajax'){
			add_action( 'wp_ajax_action_appip_do_test', array($this,'appip_do_settings_test_ajax') );// register ajax test
		}elseif($type ='debug'){
			
		}elseif($type ='parent'){
			add_action( 'wp_ajax_action_appip_do_test', array($this,'appip_do_settings_test_parent') );// register ajax test
		}
	}
	function appip_do_product_ajax(){
		check_ajax_referer( 'appip_ajax_do_product', 'security', true );
		if( current_user_can( 'manage_options' ) ){
			$test = $this->test_API();
			global $wp_scripts;
			global $wp_styles;
			if (is_a($wp_scripts, 'WP_Scripts')) {
			  $wp_scripts->queue = array();
			}	
			if (is_a($wp_styles, 'WP_Styles')) {
			  $wp_styles->queue = array();
			}			
			wp_enqueue_style( 'plugin-install' );
			wp_enqueue_style( 'wp-admin' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'plugin-install' );
			add_thickbox();
			?>
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Test</title>
<?php wp_print_scripts();wp_print_styles();?>
<style>
<?php echo get_option("apipp_product_styles", '');?>  
	.amazon-price-button > a img.amazon-price-button-img:hover {opacity: .75;}
	#plugin-information .appip-multi-divider{border-bottom: 1px solid #EAEAEA;margin: 4% 0 !important;}
	#plugin-information a img.amazon-image.amazon-image {max-width: 100%;border: 1px solid #ccc;box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.24); }
	#plugin-information h2.amazon-asin-title { border-bottom: 1px solid #ccc !important; padding-bottom: 2%; margin-bottom: 3% important; }
	#plugin-information hr { display: none; }
</style>
</head>
<body id="plugin-information" class="wp-admin wp-core-ui no-js iframe plugin-install-php locale-en-us">
<div id="plugin-information-scrollable">
	<div id='plugin-information-title'>
		<div class="vignette"></div>
		<h2>Add an Amazon Product</h2>
	</div>
	<div id="plugin-information-tabs" class="without-banner">
		<a name="test" href="<?php echo admin_url('admin.php?page=apipp_plugin_admin&amp;tab=plugin-information&amp;plugin=amazon-product-in-a-post-plugin&amp;section=tab1');?>" class="current">Tab1</a>
		<a name="debug" href="<?php echo admin_url('admin.php?page=apipp_plugin_admin&amp;tab=plugin-information&amp;plugin=amazon-product-in-a-post-plugin&amp;section=tab2');?>">Tab2</a>
	</div>
	<div id="plugin-information-content" class="with-banner">
		<div id="section-holder" class="wrap">
			<div id="section-tab1" class="section" style="display: block;"></div>
			<div id="section-tab2" class="section" style="display: none;"></div>
		</div>
	</div>
</div>
</body>
</html><?php		
		}else{
			echo 'no permission';	
		}
		exit;
	}
	function test_API(){
		$error 			= '';
		$aws_partner_id	= get_option('apipp_amazon_associateid','');
		$region 		= get_option('apipp_amazon_locale',''); 
		$publickey 		= get_option('apipp_amazon_publickey','');
		$privatekey 	= get_option('apipp_amazon_secretkey','');
		if( $aws_partner_id == '' || $region == '' || $publickey == '' || $privatekey == '' )
			$error .= '<span style="color:red;">Error: Some Required Data is missing.</span><br/>';
		if( strlen($publickey) != 20 )
			$error .= '<span style="color:red;">Error: <strong>Amazon Access Key ID</strong> is not the correct length (should be 20 characters, not '.strlen($publickey).').</span><br/>';
		if( strlen($privatekey) != 40 )
			$error .= '<span style="color:red;">Error: <strong>Amazon Secret Access Key</strong> is not the correct length (should be 40 characters, not '.strlen($privatekey).').</span><br/>';
		
		if( $error != '' )
			return $error;
		$keyword						= array('disney','kids','free','latest','donald duck');
		shuffle($keyword);
		$params 						= array();
		$params["AWSAccessKeyId"] 		= $publickey;
		$params['AssociateTag'] 		= $aws_partner_id;
		$params['Condition']			= 'All';
		$params['IdType']				= 'ASIN';
		$params['IncludeReviewsSummary']= 'True';
		$params['ItemPage']				= '1';
		$params['Keywords']				= $keyword[0];
		$params['Operation']			= 'ItemSearch';//'ItemLookup';
		$params['ResponseGroup']		= 'Large';
		$params['SearchIndex']			= 'All'; 
		$params["Service"] 				= "AWSECommerceService";
		$params["Timestamp"] 			= gmdate("Y-m-d\TH:i:s\Z");
		$params["Version"] 				= "2013-08-01";//"2011-08-01"; //"2009-03-31";
		$params["TruncateReviewsAt"]	= '1';
		$canonicalized_query 			= array();
		ksort($params);
		foreach ($params as $param => $value){
		    $param = str_replace("%7E", "~", rawurlencode($param));
		    $value = str_replace("%7E", "~", rawurlencode($value));
		    $canonicalized_query[] = $param."=".$value;
		}
		$canonicalized_query 			= implode("&", $canonicalized_query);
		$result 						= $this->get_Result( $canonicalized_query , true );
		return $result;
	}
	function check_CURL_FOPEN(){
		$hasfgc 	= ini_get('allow_url_fopen') ? true : false;
		$hasfin 	= ini_get('allow_url_include') ? true : false;
		$hascurl 	= function_exists('curl_version') ? true : false;
		$setis		= array('fopen' => $hasfgc, 'include'=> $hasfin, 'curl' => $hascurl);
		return $setis;
	}
	function API_file_get_contents( $request = '', $allowCurl = false ){
		$error = '';
		$response = @file_get_contents($request);
		if( $response === false){
			$error .= '<br/><span style="color:red;"><strong>Error: Invalid Request (file_get_contents)</strong><br/>Please check your Access Key ID and Secret Access Key for errors.</span><br/>';
			if( $allowCurl === true){
				$response = $this->API_CURL( $request );
				//try CURL 
				if( $response === false){
					$error .= '<br/><span style="color:red;"><strong>Error: Invalid Request</strong> (CURL & file_get_contents)<br/>Please check your Access Key ID and Secret Access Key for errors.</span><br/>';
				}else{
					$xml = simplexml_load_string($response);
					if(isset($xml->Error)){
						$error .= '<br/><span style="color:red;"><strong>Error: (CURL) '.$xml->Error->Code.'</strong><br/>'.$xml->Error->Message.'</span>';
					}
				}
			}
		}
		if($error != '')
			return $error;
		return $response;
	}
	function API_CURL( $request = '' ){
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		if(curl_exec($ch) === false){
			$data = '<span style="color:red;">Error: (CURL) ' . curl_error($ch).'</span>';
		}else{
			$data = curl_exec($ch);
		}
		curl_close($ch);
		return $data;
	}
	function appip_do_settings_test_debug(){
		$test = $this->test_API();
		global $wp_scripts,$wp_styles;
		if (is_a($wp_scripts, 'WP_Scripts'))
		  $wp_scripts->queue = array();
		if (is_a($wp_styles, 'WP_Styles'))
		  $wp_styles->queue = array();
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'common' );
		?>
<?php wp_print_scripts();wp_print_styles();?>
<style>
<?php echo get_option("apipp_product_styles", '');?>  
	.amazon-product-table{width:auto !important;}
	.amazon-price-button > a img.amazon-price-button-img:hover {opacity: .75;}
	#plugin-information-debug .appip-multi-divider{border-bottom: 1px solid #EAEAEA;margin: 4% 0 !important;}
	#plugin-information-debug a img.amazon-image.amazon-image {max-width: 100%;border: 1px solid #ccc;box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.24); }
	#plugin-information-debug h2.amazon-asin-title { border-bottom: 1px solid #ccc !important; padding-bottom: 2%; margin-bottom: 3% important; }
	#plugin-information-debug hr { display: none; }
</style>
<div id="plugin-information-debug" class="wp-admin wp-core-ui plugin-install-php locale-en-us">
<div id="plugin-information-scrollable">
	<div id="plugin-information-content" class="with-banner">
		<div id="section-holder" class="wrap">
			<div id="section-test" class="section" style="display: block;">
				<h3>Amazon Product API Settings Test</h3>
				<p>If you can see products listed below, then the test was successful.</p>
				<?php echo $test;?>
			</div>
			<div id="section-debug" class="section" style="display: block;">
				<h3>Amazon Product Debug Info</h3>
				<div style="background:#EAEAEA;margin-top: -12px;margin-bottom: 10px;padding: 4px 10px;">
					<p>Your site has the following server capabilities:</p>
					<ul>
						<li>allow_url_fopen: <?php $fopen = ini_get('allow_url_fopen'); echo strtolower($fopen) == 'on' || $fopen == '1'  ? '<span style="color:#390;">On</span>' : '<span style="color:#FF0000;">Off</span>' ;?></li>
						<li>allow_url_include: <?php $fincl = ini_get('allow_url_include');  echo strtolower($fincl) == 'on' || $fincl == '1' ? '<span style="color:#390;">On</span>' : '<span style="color:#FF0000;">Off</span>' ;?></li>
						<li>CURL: <?php echo function_exists('curl_version') ? '<span style="color:#390;">Installed</span>' : '<span style="color:#FF0000;">Not Installed</span>';?></li>
					</ul>
					<?php $settings = $this->check_CURL_FOPEN();?>
					<?php $noIncl = ($settings['include'] == false && $settings['fopen'] == true) ? '*<br><em>*You may have issues with remote URLs for the API when using fopen, as `allow_url_include` is not turned on.</em>' : ''; ?>
					<?php if($settings['fopen'] == true && $settings['curl'] == true ){ ?>
						<p>You can use fopen (file_get_contents) OR CURL for the API requests.<?php echo $noIncl;?></p>
					<?php }elseif($settings['fopen'] == true && $settings['curl'] == false ){ ?>
						<p>You can ONLY use fopen (file_get_contents) for the API requests.<?php echo $noIncl;?></p>
					<?php }elseif($settings['fopen'] == false && $settings['curl'] == true ){ ?>
						<p>You can ONLY use CURL for the API requests.</p>
					<?php }else{ ?>
						<p>You cannot use this plugin until either CURL or fopen are installed. Contact your host for help.</p>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php		
	}
	function appip_do_settings_test_ajax(){
		check_ajax_referer( 'appip_ajax_do_settings_test', 'security', true );
		if( current_user_can( 'manage_options' ) ){
			$test = $this->test_API();
			global $wp_scripts;
			global $wp_styles;
			if (is_a($wp_scripts, 'WP_Scripts')) {
			  $wp_scripts->queue = array();
			}	
			if (is_a($wp_styles, 'WP_Styles')) {
			  $wp_styles->queue = array();
			}			
			wp_enqueue_style( 'plugin-install' );
			wp_enqueue_style( 'wp-admin' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'plugin-install' );
			add_thickbox();
			?>
<!DOCTYPE html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Test</title>
<?php wp_print_scripts();wp_print_styles();?>
<style>
<?php echo get_option("apipp_product_styles", '');?>  
	.amazon-price-button > a img.amazon-price-button-img:hover {opacity: .75;}
	#plugin-information .appip-multi-divider{border-bottom: 1px solid #EAEAEA;margin: 4% 0 !important;}
	#plugin-information a img.amazon-image.amazon-image {max-width: 100%;border: 1px solid #ccc;box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.24); }
	#plugin-information h2.amazon-asin-title { border-bottom: 1px solid #ccc !important; padding-bottom: 2%; margin-bottom: 3% important; }
	#plugin-information hr { display: none; }
</style>
</head>
<body id="plugin-information" class="wp-admin wp-core-ui no-js iframe plugin-install-php locale-en-us">
<div id="plugin-information-scrollable">
	<div id='plugin-information-title'>
		<div class="vignette"></div>
		<h2>Amazon Product API Settings Test</h2>
	</div>
	<div id="plugin-information-tabs" class="without-banner">
		<a name="test" href="<?php echo admin_url('admin.php?page=apipp_plugin_admin&amp;tab=plugin-information&amp;plugin=amazon-product-in-a-post-plugin&amp;section=test');?>" class="current">Test Results</a>
		<a name="debug" href="<?php echo admin_url('admin.php?page=apipp_plugin_admin&amp;tab=plugin-information&amp;plugin=amazon-product-in-a-post-plugin&amp;section=debug');?>">Debug</a>
	</div>
	<div id="plugin-information-content" class="with-banner">
		<div id="section-holder" class="wrap">
			<div id="section-test" class="section" style="display: block;">
				<h3>Amazon Product API Settings Test</h3>
				<p>If you can see products listed below, then the test was successful.</p>
				<?php echo $test;?>
			</div>
			<div id="section-debug" class="section" style="display: none;">
				<h3>Amazon Product Debug Info</h3>
				<div style="background:#EAEAEA;margin-top: -12px;margin-bottom: 10px;padding: 4px 10px;">
					<p>Your site has the following server capabilities:</p>
					<ul>
						<li>allow_url_fopen: <?php $fopen = ini_get('allow_url_fopen'); echo strtolower($fopen) == 'on' || $fopen == '1'  ? '<span style="color:#390;">On</span>' : '<span style="color:#FF0000;">Off</span>' ;?></li>
						<li>allow_url_include: <?php $fincl = ini_get('allow_url_include');  echo strtolower($fincl) == 'on' || $fincl == '1' ? '<span style="color:#390;">On</span>' : '<span style="color:#FF0000;">Off</span>' ;?></li>
						<li>CURL: <?php echo function_exists('curl_version') ? '<span style="color:#390;">Installed</span>' : '<span style="color:#FF0000;">Not Installed</span>';?></li>
					</ul>
					<?php $settings = $this->check_CURL_FOPEN();?>
					<?php $noIncl = ($settings['include'] == false && $settings['fopen'] == true) ? '*<br><em>*You may have issues with remote URLs for the API when using fopen, as `allow_url_include` is not turned on.</em>' : ''; ?>
					<?php if($settings['fopen'] == true && $settings['curl'] == true ){ ?>
						<p>You can use fopen (file_get_contents) OR CURL for the API requests.<?php echo $noIncl;?></p>
					<?php }elseif($settings['fopen'] == true && $settings['curl'] == false ){ ?>
						<p>You can ONLY use fopen (file_get_contents) for the API requests.<?php echo $noIncl;?></p>
					<?php }elseif($settings['fopen'] == false && $settings['curl'] == true ){ ?>
						<p>You can ONLY use CURL for the API requests.</p>
					<?php }else{ ?>
						<p>You cannot use this plugin until either CURL or fopen are installed. Contact your host for help.</p>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php /*wp_footer();*/?>
</body>
</html><?php		
		}else{
			echo 'no permission';	
		}
		exit;
	}
	function get_Result( $canonicalized_query = array(), $test = false){
		$region 				= get_option('apipp_amazon_locale',''); 
		$privatekey 			= get_option('apipp_amazon_secretkey','');
		$method 				= "GET";
		$host 					= "webservices.amazon.".$region; //new API 12-2011
		$uri 					= "/onca/xml";
		$string_to_sign 		= $method."\n".$host."\n".$uri."\n".$canonicalized_query;
		$signature 				= base64_encode( appip_plugin_aws_hash_hmac( "sha256", $string_to_sign, $privatekey, true ) );
		$signature 				= str_replace("%7E", "~", rawurlencode($signature));
		$request 				= "https://".$host.$uri."?".$canonicalized_query."&Signature=".$signature;
		if($this->type == 'debug')
			echo '<span style="font-weight:bold;font-family:courier;display:inline-block;width:225px;">Sample Request:</span> '.$request;
		$APIGet 				= $this->check_CURL_FOPEN();
		if( $APIGet['fopen'] === true ){
			$allowCurl = $APIGet['curl'] === true ? true : false;
			$response = $this->API_file_get_contents( $request,$allowCurl);
		}elseif($APIGet['curl'] === true ){
			$response = $this->API_CURL( $request );
		}else{
			return '<span style="color:red;">Error: Cannot make request - no transport</span>';
		}
		$xbody = trim(addslashes($response));
		if($xbody =='' || strpos($xbody, 'Error:') !== false ){
			if($xbody ==''){
				return '<span style="color:red;">Error: Empty Result.<br/>Something when wrong with the request. If you continue to have this problem, check your API keys for accuracy. If you still have the issue, send your Debug key and site URL to plugins@fischercreativemedia.com for help.</span>';
			}else{
				return stripslashes($xbody);
			}
		}
		$pxml = appip_get_XML_structure_new( $response, 0 );
		
		if(!is_array($pxml)){
			return 'Error:'. $pxml2;
		}else{
			$resultarr1	= appip_plugin_FormatASINResult($pxml);
			$resultarr2 = appip_plugin_FormatASINResult($pxml,1);
			foreach($resultarr1 as $key1 => $result1):
				$mainAArr 			= (array)$result1;
				$otherArr 			= (array)$resultarr2[$key1];
				$resultarr[$key1] 	= (array)$mainAArr + $otherArr;
			endforeach;
			$returnval 	= '<span style="color:#390;font-size:20px;font-weight:bold;">Test Successful!</span><br/>';
			$resultarr 	= has_filter('appip_product_array_processed') ? apply_filters('appip_product_array_processed',$resultarr,$apippnewwindowhtml,$resultarr1,$resultarr2,$template) : $resultarr;
			$resultarr 	= !is_array($resultarr) ? (array) $resultarr : $resultarr;
			$thedivider = '<div class="appip-multi-divider"></div>';
			$totaldisp  = 2;
			$i  		= 0;

			foreach($resultarr as $key => $result):
				if($i >= $totaldisp)
					break;
				if(isset($result['NoData']) && $result['NoData'] == '1'):
					$returnval .=  $result['Error'];
					if($extratext != ''):
						$returnval .= $extratext;
					endif;
				else:
					$returnval .= '	<br /><table cellpadding="0" class="amazon-product-table">'."\n";
					$returnval .= '		<tr>'."\n";
					$returnval .= '			<td valign="top">'."\n";
					$returnval .= '				<div class="amazon-image-wrapper">'."\n";
					$returnval .= '					<a href="' . $result['URL'] . '" '. $apippnewwindowhtml .'>' . awsImageGrabber($result['MediumImage'],'amazon-image') . '</a><br />'."\n";
					if($result['LargeImage']!=''){
						$returnval .= '					<a rel="appiplightbox-'.$result['ASIN'].'" href="'.$result['LargeImage'] .'" target="amazonwin"><span class="amazon-tiny">'.$appip_text_lgimage.'</span></a>'."\n";
					}
					if($result['AddlImages']!='' && $show_gallery == 1){
						$returnval .= ' 					<div class="amazon-additional-images-wrapper"><span class="amazon-additional-images-text">Additional Images:</span>'.$result['AddlImages'].'</div>';
					}	
					$returnval .= '				</div>'."\n";
					$returnval .= '				<div class="amazon-buying">'."\n";
					if($replace_title!=''){$title = $replace_title;}else{$title = maybe_convert_encoding($result["Title"]);}
					if(strtolower($title) != 'null'){ 
						$returnval .= '					<h2 class="amazon-asin-title"><a href="' . $result['URL'] . '" '. $apippnewwindowhtml .'><span class="asin-title">'.$title.'</span></a></h2>'."\n";
					}
					$returnval .= '				<hr noshade="noshade" size="1" />'."\n";
					if($result["Department"]=='Video Games' || $result["ProductGroup"]=='Video Games'){
						$returnval .= '					<span class="amazon-manufacturer"><span class="appip-label">'.($appip_text_manufacturer != '' ? $appip_text_manufacturer .':' : '').'</span> '.maybe_convert_encoding($result["Manufacturer"]).'</span><br />'."\n";
						$returnval .= '					<span class="amazon-ESRB"><span class="appip-label">'.($appip_text_ESRBAgeRating != '' ? $appip_text_ESRBAgeRating .':' : '').'</span> '.maybe_convert_encoding($result["ESRBAgeRating"]).'</span><br />'."\n";
						$returnval .= '					<span class="amazon-platform"><span class="appip-label">'.($appip_text_platform != '' ? $appip_text_platform .':' : '').'</span> '.maybe_convert_encoding($result["Platform"]).'</span><br />'."\n";
						$returnval .= '					<span class="amazon-system"><span class="appip-label">'.($appip_text_genre != '' ? $appip_text_genre .':' : '').'</span> '.maybe_convert_encoding($result["Genre"]).'</span><br />'."\n";
						if($show_features != 0){
							$returnval .= '					<span class="amazon-feature"><span class="appip-label">'.($appip_text_feature != '' ? $appip_text_feature .':' : '').'</span> '.maybe_convert_encoding($result["Feature"]).'</span>'."\n";
						}							
					}elseif($show_features != 0 && $result["Feature"] != ''){
						$returnval .= '					<span class="amazon-feature"><span class="appip-label">'.($appip_text_feature != '' ? $appip_text_feature .':' : '').'</span> '.maybe_convert_encoding($result["Feature"]).'</span>'."\n";
					}
					if($show_features != 0){
						if(trim($result["Author"])!=''){
							$returnval .= '					<span class="amazon-author">'.($appip_text_author != '' ? $appip_text_author .': ': '').'</span> '.maybe_convert_encoding($result["Author"]).'</span><br />'."\n";
						}
						if(trim($result["Director"])!=''){
							$returnval .= '					<span class="amazon-director-label">'.($appip_text_director != '' ? $appip_text_director .': ' : '').' </span><span class="amazon-director">'.maybe_convert_encoding($result["Director"]).'</span><br />'."\n";
						}
						if(trim($result["Actor"])!=''){
							$returnval .= '					<span class="amazon-starring-label">'.($appip_text_starring != '' ? $appip_text_starring.': ' : '').'</span><span class="amazon-starring">'.maybe_convert_encoding($result["Actor"]).'</span><br />'."\n";
						}
						if(trim($result["AudienceRating"])!=''){
							$returnval .= '					<span class="amazon-rating-label">Rating: </span><span class="amazon-rating">'.$result["AudienceRating"].'</span><br />'."\n";
						}
					}
					if(!empty($result["ItemDesc"]) && $description == 1){
						if(is_array($result["ItemDesc"])){
							$desc 		= str_replace('<![CDATA[','', $result["ItemDesc"][0]['Content'] );
							$desc 		= str_replace(']]>','', $desc );
							$desc 		= str_replace(']]&gt;','', $desc );
							$returnval .= '				<div class="amazon-description">'.maybe_convert_encoding($desc).'</div>'."\n";
						}else{
							$desc 		= str_replace('<![CDATA[','', $result["ItemDesc"]['Content'] );
							$desc 		= str_replace(']]>','', $desc );
							$desc 		= str_replace(']]&gt;','', $desc );
							$returnval .= '				<div class="amazon-description">'.maybe_convert_encoding($desc).'</div>'."\n";
						}
					}
					$returnval .= '				<div align="left" class="amazon-product-pricing-wrap">'."\n";
					$returnval .= '					<table class="amazon-product-price" cellpadding="0">'."\n";
					if($extratext!=''){
						$returnval .= '						<tr>'."\n";
						$returnval .= '							<td class="amazon-post-text" colspan="2">'.$extratext.'</td>'."\n";
						$returnval .= '						</tr>'."\n";
					}
					if($show_list == 1){
						if($result["PriceHidden"]== '1' ){
							$returnval .= '						<tr>'."\n";
							$returnval .= '							<td class="amazon-list-price-label">'. __( 'List Price:', 'amazon-product-in-a-post-plugin' ) . '</td>'."\n";
							$returnval .= '							<td class="amazon-list-price-label">'.$amazonhiddenmsg.'</td>'."\n";
							$returnval .= '						</tr>'."\n"; 
						}elseif($result["ListPrice"]!= '0'){
							$returnval .= '						<tr>'."\n";
							$returnval .= '							<td class="amazon-list-price-label">'. __( 'List Price:', 'amazon-product-in-a-post-plugin' ) . '</td>'."\n";
							$returnval .= '							<td class="amazon-list-price">'.  maybe_convert_encoding($result["ListPrice"]) .'</td>'."\n";
							$returnval .= '						</tr>'."\n";
						}
					}
					if(isset($result["LowestNewPrice"])){
						if($result["Binding"] == 'Kindle Edition'){
							$returnval .= '						<tr>'."\n";
							$returnval .= '							<td class="amazon-new-label">Kindle Edition:</td>'."\n";
							$returnval .= '							<td class="amazon-new">Check Amazon for Pricing <span class="instock">Digital Only</span></td>'."\n";
							$returnval .= '						</tr>'."\n";
						}else{
							if($result["LowestNewPrice"] == 'Too low to display'){
								$newPrice = 'Check Amazon For Pricing';
							}else{
								$newPrice = $result["LowestNewPrice"];
							}
							$returnval .= '						<tr>'."\n";
							$returnval .= '							<td class="amazon-new-label">'. __( 'New from:', 'amazon-product-in-a-post-plugin' ) . '</td>'."\n";
							if(!(isset($result["HideStockMsg"]) && isset($result["HideStockMsg"]) == '1')){
								$stockIn = $appip_text_instock;
								$stockOut = $appip_text_outofstock;
							}else{
								$stockIn = '';
								$stockOut = '';
							}
								if($result["TotalNew"]>0){
									$returnval .= '							<td class="amazon-new">'. maybe_convert_encoding($newPrice ).' <span class="instock">'.$stockIn.'</span></td>'."\n";
								}else{
									$returnval .= '							<td class="amazon-new">'. maybe_convert_encoding($newPrice ).' <span class="outofstock">'.$stockOut.'</span></td>'."\n";
								}
								$returnval .= '						</tr>'."\n";
							
						}
					}
					if($show_used == 1){
						if(isset($result["LowestUsedPrice"]) && $result["Binding"] != 'Kindle Edition'){
							$returnval .= '						<tr>'."\n";
							$returnval .= '							<td class="amazon-used-label">'.($appip_text_usedfrom != '' ? $appip_text_usedfrom .':' : '').'</td>'."\n";
							if($result["TotalUsed"] > 0){
								$returnval .= '						<td class="amazon-used">'. maybe_convert_encoding($result["LowestUsedPrice"]) .' <span class="instock">'.$appip_text_instock.'</span></td>'."\n";
							}else{
								if($result["LowestUsedPrice"] == '' || $result["LowestUsedPrice"] =="0"){
									$usedfix = '';
								}else{
									$usedfix = maybe_convert_encoding($result["LowestUsedPrice"]);
								}
								$returnval .= '						<td class="amazon-used">'. $usedfix . ' <span class="outofstock">'.$appip_text_outofstock.'</span></td>'."\n";
							}
							$returnval .= '						</tr>'."\n";
						}
					}
					if(isset($result["VariantHTML"]) && $result["VariantHTML"] != ''){
						$returnval .= '						<tr>'."\n";
						$returnval .= '							<td colspan="2" class="amazon-list-variants">'.$result["VariantHTML"].'</td>'."\n";
						$returnval .= '						</tr>'."\n"; 
					}
					$returnval .= '						<tr>'."\n";
					$returnval .= '							<td valign="top" colspan="2">'."\n";
					$returnval .= '								<div class="amazon-dates">'."\n";
					if($result["ReleaseDate"] != ''){	
						$nowdatestt = strtotime(date("Y-m-d",time()));
						$nowminustt = strtotime("-60 days");
						$reldatestt = strtotime($result["ReleaseDate"]);
						if($reldatestt > $nowdatestt){
					$returnval .= '									<span class="amazon-preorder"><br />'.$appip_text_releasedon.' '.date("F j, Y", strtotime($result["ReleaseDate"])).'.</span>'."\n";
						}elseif($reldatestt >= $nowminustt){
					$returnval .= '									<span class="amazon-release-date">'.$appip_text_reldate.' '.date("F j, Y", strtotime($result["ReleaseDate"])).'.</span>'."\n";
						}
					}
					$buttonURL  = apply_filters('appip_amazon_button_url',plugins_url('/images/'.$buyamzonbutton,dirname(__FILE__)),$buyamzonbutton,$region);
					$returnval .= '									<div class="amazon-price-button"><a '. $apippnewwindowhtml .' href="' . $result["URL"] .'"><img class="amazon-price-button-img" src="'.$buttonURL.'" /></a></div>'."\n";
					$returnval .= '								</div>'."\n";
					$returnval .= '							</td>'."\n";
					$returnval .= '						</tr>'."\n";
					if(!isset($result["LowestUsedPrice"]) && !isset($result["LowestNewPrice"]) && !isset($result["ListPrice"])){
						$returnval .= '						<tr>'."\n";
						$returnval .= '							<td class="amazon-price-save-label" colspan="2">'.$appip_text_notavalarea.'</td>'."\n";
						$returnval .= '						</tr>'."\n";
					}
					$returnval .= '					</table>'."\n";
					$returnval .= '					</div>'."\n";
					$returnval .= '				</div>'."\n";
					$returnval .= '			</td>'."\n";
					$returnval .= '		</tr>'."\n";
					$returnval .= '	</table>'."\n";
					if($result["CachedAPPIP"] !='')
						$returnval .= '<'.'!-- APPIP Item Cached ['.$result["CachedAPPIP"].'] -->'."\n";
					$returnval .= $thedivider;
				endif;
				$i++;
			endforeach;
			return apply_filters('appip_single_product_filter',$returnval,$resultarr);
		}
		return 'Nothing';
	}
}

new amazonAPPIP_NewRequest();