<?php
add_action( 'init', 'amazon_product_add_shortcodes_funct');
function amazon_product_add_shortcodes_funct(){
	add_shortcode( 'amazon-element', 'amazon_product_shortcode_mini_function');
	add_shortcode( 'amazon-elements', 'amazon_product_shortcode_mini_function');
	add_shortcode( 'amazonproducts', 'amazon_product_shortcode_function');
	add_shortcode( 'amazonproduct', 'amazon_product_shortcode_function');
	add_shortcode( 'AMAZONPRODUCTS', 'amazon_product_shortcode_function');
	add_shortcode( 'AMAZONPRODUCT', 'amazon_product_shortcode_function');
	if(!is_admin()){
		add_filter( 'widget_text', 'do_shortcode');
		//add_filter( 'the_content', 'do_shortcode');
		//add_filter( 'the_excerpt', 'do_shortcode');
	}
}
add_action( 'init', 'amazon_appip_editor_button');

function amazon_product_shortcode_mini_function($atts, $content = ''){
	global $appip_running_excerpt;
	if(	$appip_running_excerpt == true ){
		//return $content;
	}
	global $appip_text_lgimage,$showformat,$public_key,$private_key,$aws_partner_id,$aws_partner_locale,$amazonhiddenmsg,$amazonerrormsg,$apippopennewwindow,$apippnewwindowhtml,$buyamzonbutton,$addestrabuybutton,$post,$validEncModes,$appip_text_lgimage;
	
	$defaults = array(
		'asin'			=> '',
		'locale' 		=> $aws_partner_locale,
		'partner_id' 	=> $aws_partner_id,
		'private_key' 	=> $private_key,
		'public_key' 	=> $public_key, 
		'fields'		=> '',
		'field'			=> '',
		'showformat' 	=> 1,
		'listprice' 	=> 1, 
		'used_price' 	=> 1,
		'replace_title' => '', 
		'template' 		=> 'default',
		'msg_instock' 	=> 'In Stock',
		'msg_outofstock'=> 'Out of Stock',
		'target' 		=> '_blank',
		'button_url' 	=> '',
		'container' 	=> apply_filters('amazon-elements-container','div'),
		'container_class' => apply_filters('amazon-elements-container-class','amazon-element-wrapper'),
		'labels' 		=> '',
		'use_carturl' 	=> false,		
		'show_format' 	=> null,		//added only as a secondary use of showformat
		'list_price' 	=> null, 		//added only as a secondary use of $listprice
		'show_list' 	=> null,		//added only as a secondary use of $listprice 
		'show_used'		=> null,		//added only as a secondary use of $used_price
		'usedprice' 	=> null,		//added only as a secondary use of $used_price
	);
	extract(shortcode_atts($defaults, $atts));
	$listprice 		= (isset($list_price) && $list_price != null ) ? $list_price : $listprice;
	$listprice 		= (isset($show_list)  && $show_list != null ) ? $show_list : $listprice;
	$used_price		= (isset($usedprice)  && $usedprice != null ) ? $usedprice : $used_price; 
	$used_price		= (isset($show_used)  && $show_used != null ) ? $show_used : $used_price;
	$showformat		= (isset($show_format) && $show_format != null ) ? $show_format : $showformat;
	$use_carturl	= (isset($use_carturl) && ( (int) $use_carturl == 1 || $use_carturl == true ) ) ? true : false;

	if($labels != ''){
		$labelstemp = explode(',',$labels);
		unset($labels);
		foreach($labelstemp as $lab){
			$keytemp = explode('::',$lab);
			if(isset($keytemp[0]) && isset($keytemp[1])){
				$labels[$keytemp[0]][] = $keytemp[1];
			}
		}
	}else{
		$labels = array();
	}
	if($button_url != ''){
		$buttonstemp = explode(',',$button_url);
		unset($button_url);
		foreach($buttonstemp as $buttona){
			if(!empty($buttona)){
				$button_url[] = $buttona;
			}
		}
	}else{
		$button_url = array();
	}

	if( $field == '' && $fields != '' ){$field = $fields;}
	if( $aws_partner_locale == '' ){$aws_partner_locale='com';}
	if( $target != '' ){$target = ' target="'.$target.'" ';}
	if( $appip_text_lgimage == ''){$appip_text_lgimage = 'see larger image';}
	if ( $asin != ''){
		$ASIN 			= ( is_array( $asin ) && !empty( $asin ) )? implode(',',$asin) : $asin; //valid ASIN or ASINs 
		$errors 		= '';
		$pxmlNew		= amazon_plugin_aws_signed_request($locale, array("Operation" => "ItemLookup","ItemId" => $ASIN,"ResponseGroup" => "Large","IdType" => "ASIN","AssociateTag" => $partner_id ), $public_key, $private_key);
		$totalResult1 	= array();
		$totalResult2 	= array();
		$errorsArr		= array();

		if( is_array( $pxmlNew ) && !empty( $pxmlNew ) ){
			$pxmle = array();
			foreach($pxmlNew as $pxmlkey => $pxml ){
				if(!is_array($pxml)){
					$pxmle = $pxml;
				}else{
					$r1 = appip_plugin_FormatASINResult( $pxml );
					if(is_array($r1) && !empty($r1)){
						foreach($r1 as $ritem){
							$totalResult1[] = $ritem;
						}
					}
					$r2 = appip_plugin_FormatASINResult( $pxml, 1 );
					if(is_array($r2) && !empty($r2)){
						foreach($r2 as $ritem2){
							$totalResult2[] = $ritem2;
						}
					}
				}
			}
		}
		$resultarr = array();
		if(!empty($pxmle)){
			//$pxml = $pxmle;
			//echo '<div style="display:none;" class="appip-errors">APPIP ERROR:pxml['.str_replace(array('<![CDATA[',']]>',']]&gt;'),array('','',''),$pxml).'</div>';
			return false;
		}else{
			$resultarr1	= isset($totalResult1) && !empty($totalResult1) ? $totalResult1 : array(); //appip_plugin_FormatASINResult( $pxml );
			$resultarr2 = isset($totalResult2) && !empty($totalResult2) ? $totalResult2 : array(); //appip_plugin_FormatASINResult( $pxml, 1 );
			if(is_array($resultarr1) && !empty($resultarr1)){
				foreach($resultarr1 as $key1 => $result1):
					$mainAArr 			= (array) $result1;
					$otherArr 			= (array) $resultarr2[$key1];
					$resultarr[$key1] 	= (array) array_merge($mainAArr,$otherArr);
					ksort($resultarr[$key1]);
				endforeach;
			}
			$arr_position = 0;
			if(is_array($resultarr)):
				$retarr = array();
				$newErr = '';
				foreach($resultarr as $key => $result):
					$currasin = $result['ASIN'];
					if($result['NoData'] == '1' ):
						echo '<div style="display:none;" class="appip-errors">APPIP ERROR:nodata['.str_replace(']-->',']->',$result['Error']).'</div>';
					elseif( empty( $result['ASIN'] ) || $result['ASIN'] == 'Array' ):
						echo '<div style="display:none;" class="appip-errors">APPIP ERROR:nodata[ ('.$key.') NO DATA </div>';
					else:
						$linkURL 	= ($use_carturl) ? str_replace(array('##REGION##','##AFFID##','##SUBSCRIBEID##'),array($locale,$partner_id,$public_key),$result['CartURL'] ) : $result['URL'];
						if($result['Errors'] != '' )
							$newErr = '<div style="display:none;" class="appip-errors">HIDDEN APIP ERROR(S): '.$result['Errors'].'</div>';
						$fielda 	= is_array($field) ? $field :  explode(',',str_replace(' ','',$field));
						foreach($fielda as $fieldarr){
							switch(strtolower($fieldarr)){
								case 'title_clean':
									$retarr[$currasin][$fieldarr] = maybe_convert_encoding($result["Title"]);
									break;
								case 'desc_clean':
								case 'description_clean':
									if(is_array($result["ItemDesc"])){
										$desc 	= preg_replace('/^\s*\/\/<!\[CDATA\[([\s\S]*)\/\/\]\]>\s*\z/','$1', $result["ItemDesc"][0] );
										$retarr[$currasin][$fieldarr] = maybe_convert_encoding($desc['Content']);
									}
									break;
								case 'price_clean':
								case 'new-price_clean':
								case 'new price_clean':
									if("Kindle Edition" == $result["Binding"]){
										$retarr[$currasin][$fieldarr] = 'Check Amazon for Pricing [Digital Only - Kindle]';
									}else{
										if( $result["LowestNewPrice"] == 'Too low to display' ){
											$newPrice = 'Check Amazon For Pricing';
										}else{
											$newPrice = $result["LowestNewPrice"];
										}
										if($result["TotalNew"]>0){
											$retarr[$currasin][$fieldarr] = maybe_convert_encoding($newPrice).' - '.$msg_instock;
										}else{
											$retarr[$currasin][$fieldarr] = maybe_convert_encoding($newPrice).' - '.$msg_instock;
										}
									}
									break;
								case 'image_clean':
								case 'med-image_clean':
									$retarr[$currasin][$fieldarr] = awsImageGrabberURL($currasin,"M");
									break;
								case 'sm-image_clean':
									$retarr[$currasin][$fieldarr] = $result['SmallImage'];
									break;
								case 'lg-image_clean':
								case 'full-image_clean':
									$retarr[$currasin][$fieldarr] = $result['LargeImage'];
									break;
								case 'large-image-link_clean':
									if( awsImageGrabberURL($currasin,"P") != '')
										$retarr[$currasin][$fieldarr] = awsImageURLModify($result['LargeImage'],"P");
									break;
								case 'features_clean':
									$retarr[$currasin][$fieldarr] = maybe_convert_encoding($result["Feature"]);
									break;
								case 'link_clean':
									$retarr[$currasin][$fieldarr] = $linkURL;
									break;
								case 'button_clean':
									if(isset($button_url[$arr_position]))
										$retarr[$currasin][$fieldarr] = $button_url[$arr_position];
									else
										$retarr[$currasin][$fieldarr] = plugins_url('/images/'.$buyamzonbutton,dirname(__FILE__));
									break;
								case 'customerreviews_clean':
									$retarr[$currasin][$fieldarr] = $result['CustomerReviews'];
									break;

								case 'title':
									if ($showformat != '1'){$result["Title"] = str_replace('('.$result["Title"].')','',$result["Title"]);}
									if(!isset($labels['title-wrap'][$arr_position]) && !isset($labels['title'][$arr_position])){
										$labels['title'][$arr_position] = '<h2 class="appip-title"><a href="'.$linkURL.'"'.$target.' rel="nofollow">'. maybe_convert_encoding($result["Title"]).'</a></h2>';
									}elseif(!isset($labels['title-wrap'][$arr_position]) && isset($labels['title'][$arr_position])){
										$labels['title'][$arr_position] = '<h2 class="appip-title"><a href="'.$linkURL.'"'.$target.' rel="nofollow">'.$labels['title'][$arr_position].'</a></h2>';
									}elseif(isset($labels['title-wrap'][$arr_position]) && isset($labels['title'][$arr_position])){
										$labels['title'][$arr_position] = "<{$labels['title-wrap'][$arr_position]} class='appip-title'>{$labels['title'][$arr_position]}</{$labels['title-wrap'][$arr_position]}>";
									}else{
										$labels['title'][$arr_position] = '<h2 class="appip-title"><a href="'.$linkURL.'"'.$target.' rel="nofollow">'. maybe_convert_encoding($result["Title"]).'</a></h2>';
									}
									$retarr[$currasin][$fieldarr] = $labels['title'][$arr_position];
									break;
								case 'desc':
								case 'description':
									if(isset($labels['desc'])){
										$labels['desc'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels['desc'][$arr_position].' </span>';
									}elseif(isset($labels['description'][$arr_position])){
										$labels['desc'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels['description'][$arr_position].' </span>';
									}else{
										$labels['desc'][$arr_position] = '';
									}
									if(is_array($result["ItemDesc"])){
										$desc 	= preg_replace('/^\s*\/\/<!\[CDATA\[([\s\S]*)\/\/\]\]>\s*\z/','$1', $result["ItemDesc"][0] );
										$retarr[$currasin][$fieldarr] = maybe_convert_encoding($labels['desc'][$arr_position].$desc['Content']);
									}
									break;
								case 'gallery':
									if(!isset($labels['gallery'][$arr_position])){$labels['gallery'][$arr_position] = "Additional Images:";}else{$labels['gallery'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels[$fieldarr][$arr_position].' </span>';}
									if($result['AddlImages']!=''){
										$retarr[$currasin][$fieldarr] = '<div class="amazon-image-wrapper"><span class="amazon-additional-images-text">'.$labels['gallery'][$arr_position].'</span><br/>'.$result['AddlImages'].'</div>';
									}	
									break;
								case 'imagesets':
									if(!isset($labels['imagesets'][$arr_position])){$labels['imagesets'][$arr_position] = "Additional Images: ";}else{$labels['imagesets'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels[$fieldarr][$arr_position].' </span>';}
									if($result['AddlImages']!=''){
										$retarr[$currasin][$fieldarr] = '<div class="amazon-image-wrapper"><span class="amazon-additional-images-text">'.$labels['imagesets'][$arr_position].'</span><br/>'.$result['AddlImages'].'</div>';
									}	
									break;
								case 'price':
								case 'new-price':
								case 'new price':
								//case 'listprice':
									if("Kindle Edition" == $result["Binding"]){
										if(isset($labels['price'][$arr_position])){
											$labels['price-new'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels['price'][$arr_position].' </span>';
										}elseif(isset($labels['new-price'][$arr_position])){
											$labels['price-new'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels['new-price'][$arr_position].' </span>';
										}elseif(isset($labels['new price'][$arr_position])){
											$labels['price-new'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels['new price'][$arr_position].' </span>';
										}else{
											$labels['price-new'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.'Kindle Edition:'.' </span>';
										}
										$retarr[$currasin][$fieldarr] = $labels['price-new'][$arr_position].' Check Amazon for Pricing <span class="instock">Digital Only</span>';
									}else{
										if(isset($labels['price'][$arr_position])){
											$labels['price-new'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels['price'][$arr_position].' </span>';
										}elseif(isset($labels['new-price'][$arr_position])){
											$labels['price-new'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels['new-price'][$arr_position].' </span>';
										}elseif(isset($labels['new price'][$arr_position])){
											$labels['price-new'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels['new price'][$arr_position].' </span>';
										}else{
											$labels['price-new'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.'New From:'.' </span>';
										}
										if($result["LowestNewPrice"]=='Too low to display'){
											$newPrice = 'Check Amazon For Pricing';
										}else{
											$newPrice = $result["LowestNewPrice"];
										}
										if((int) $newPrice != 0){
											if($result["TotalNew"] > 0){
												$retarr[$currasin][$fieldarr] = $labels['price-new'][$arr_position].maybe_convert_encoding($newPrice).' <span class="instock">'.$msg_instock.'</span>';
											}else{
												$retarr[$currasin][$fieldarr] = $labels['price-new'][$arr_position].maybe_convert_encoding($newPrice).' <span class="outofstock">'.$msg_instock.'</span>';
											}
										}
									}
									break;
								case 'image':
								case 'med-image':
									$retarr[$currasin][$fieldarr] = '<div class="amazon-image-wrapper"><a href="'.$linkURL.'"'.$target.'>'.awsImageGrabber(awsImageGrabberURL($currasin,"M"),'amazon-image').'</a></div>';
									break;
								case 'sm-image':
									$retarr[$currasin][$fieldarr] = '<div class="amazon-image-wrapper"><a href="'.$linkURL.'"'.$target.'>'.awsImageGrabber($result['SmallImage'],'amazon-image').'</a></div>';
									break;
								case 'lg-image':
								case 'full-image':
									$retarr[$currasin][$fieldarr] = '<div class="amazon-image-wrapper"><a href="'.$linkURL.'"'.$target.'>'.awsImageGrabber($result['LargeImage'],'amazon-image').'</a></div>';
									break;
								case 'large-image-link':
									if(!isset($labels['large-image-link'][$arr_position])){
										$labels['large-image-link'][$arr_position] = $appip_text_lgimage;
									}else{
										$labels['large-image-link'][$arr_position] = $labels[$fieldarr][$arr_position].' ';
									}
									if(awsImageGrabberURL($currasin,"P")!=''){
										$retarr[$currasin][$fieldarr] = '<div class="amazon-image-link-wrapper"><a rel="appiplightbox-'.$result['ASIN'].'" href="'.awsImageURLModify($result['LargeImage'],"P").'"><span class="amazon-element-large-img-link">'.$labels['large-image-link'][$arr_position].'</span></a></div>';
									}
									break;
								case 'features':
									if(!isset($labels['features'][$arr_position])){
										$labels['features'][$arr_position] = '';
									}else{
										$labels['features'][$arr_position] = '<span class="appip-label label-'.$fieldarr.'">'.$labels[$fieldarr][$arr_position].' </span>';
									}
									$retarr[$currasin][$fieldarr] = $labels['features'][$arr_position].maybe_convert_encoding($result["Feature"]);
									break;
								case 'link':
									$retarr[$currasin][$fieldarr] = '<a href="'.$linkURL.'"'.$target.'>'.$linkURL.'</a>';
									break;
								case 'button':
									if(isset($button_url[$arr_position])){
										$retarr[$currasin][$fieldarr] = '<a '.$target.' href="'.$linkURL.'"><img src="'.$button_url[$arr_position].'" border="0" /></a>';
									}else{
										$retarr[$currasin][$fieldarr] = '<a '.$target.' href="'.$linkURL.'"><img src="'.plugins_url('/images/'.$buyamzonbutton,dirname(__FILE__)).'" border="0" /></a>';
									}
									break;
								case 'customerreviews':
									$retarr[$currasin][$fieldarr] = '<iframe src="'.$result['CustomerReviews'].'" class="amazon-customer-reviews" width="100%" seamless="seamless"></iframe>';
									break;
								default:
									if( preg_match( '/\_clean$/', $fieldarr ) ){
										$tempfieldarr = str_replace('_clean','',$fieldarr);
										$retarr[$currasin][$fieldarr] = isset($result[$tempfieldarr]) && $result[$tempfieldarr]!='' ? $result[$tempfieldarr]: '';
									}else{
										if(isset($result[$fieldarr]) && $result[$fieldarr]!='' && $result[$fieldarr]!= '0'){
											if(!isset($labels[$fieldarr][$arr_position])){
												$labels[$fieldarr][$arr_position] = '';
											}else{
												$labels[$fieldarr][$arr_position] = '<span class="appip-label label-'.str_replace(' ','-',$fieldarr).'">'.$labels[$fieldarr][$arr_position].' </span>';
											}
											$retarr[$currasin][$fieldarr] = $labels[$fieldarr][$arr_position].$result[$fieldarr];
										}else{
											$retarr[$currasin][$fieldarr] = '';
										}
									}
									break;
							}
						}
					endif;
					
					$retarr = apply_filters('amazon_product_in_a_post_plugin_elements_filter',$retarr);
					$wrap = str_replace(array('<','>'), array('',''),$container);
					if($wrap != ''){
						$thenewret[] = "<{$wrap} class='{$container_class}'>";
					}
					if(is_array($retarr[$currasin]) && !empty($retarr[$currasin])){
						foreach( $retarr[$currasin] as $key => $val ){
							if($key != '' ){
								if( preg_match( '/\_clean$/', $key ))
									$thenewret[] =  $val;
								else
									$thenewret[] =  '<div class="amazon-element-'.$key.'">'.$val.'</div>';
							}
						}
					}
					if($wrap != ''){
						$thenewret[] = "</{$wrap}>";
					}
					$arr_position++;
				endforeach;
				if($newErr != '' )
					echo $newErr;
				if(is_array($thenewret)){
					return implode("\n",$thenewret);
				}
				return false;
			endif;
		}
	}else{
		return false;
	}

}
function amazon_product_shortcode_function($atts, $content = '') {
	global $aws_partner_locale;
	global $public_key;
	global $private_key; 
	global $aws_partner_id;
	$defaults = array(
		'asin'=> '',
		'locale' => $aws_partner_locale,
		'gallery' => 0, 			//set to 1 to show ectra photos
		'partner_id' => $aws_partner_id,
		'private_key' => $private_key,
		'public_key' => $public_key, 
		'showformat' => 1,			//set to 1 to show or 0 to hide product formats if avail
		'show_format' => null,		//added only as a secondary use of showformat
		'desc' => 0, 				//set to 1 to show or 0 to hide description if avail
		'features' => 0, 			//set to 1 to show or 0 to hide features if avail
		'listprice' => 1, 			//set to 0 to hide list price
		'list_price' => null, 		//added only as a secondary use of $listprice
		'show_list' => null,		//added only as a secondary use of $listprice 
		'used_price' => 1, 			//set to 0 to hide used price
		'show_used' => null,		//added only as a secondary use of $used_price
		'usedprice' => null,		//added only as a secondary use of $used_price
		'replace_title' => '', 		//replace with your own title
		'use_carturl' => false, 	//set to 1 use Cart URL
		'template' => 'default' 	//future feature
	);
	
	if(array_key_exists('0',$atts)){
		extract(shortcode_atts($defaults, $atts));
		$asin = str_replace('=','',$atts[0]);
	}else{
		extract(shortcode_atts($defaults, $atts));
	}
	if(strpos($asin,',')!== false){
		$asin = explode(',', str_replace(' ','',$asin));
	}
	$listprice 		= (isset($list_price) && $list_price != null ) ? $list_price : $listprice;
	$listprice 		= (isset($show_list)  && $show_list != null ) ? $show_list : $listprice;
	$used_price		= (isset($usedprice)  && $usedprice != null ) ? $usedprice : $used_price; 
	$used_price		= (isset($show_used)  && $show_used != null ) ? $show_used : $used_price;
	$showformat		= (isset($show_format)&& $show_format != null ) ? $show_format : $showformat;
	$useCartURL		= (isset($use_carturl) && ((int)$use_carturl == 1 || $use_carturl == true) ) ? true : false;
	$product_array 	= $asin;	 /*$product_array can be array, comma separated string or single ASIN*/
	$amazon_array 	= array(
		'locale' 		=> $locale,
		'partner_id' 	=> $partner_id,
		'private_key' 	=> $private_key,
		'public_key' 	=> $public_key, 
		'gallery'		=> $gallery,
		'features' 		=> $features,
		'listprice' 	=> $listprice,
		'used_price' 	=> $used_price,
		'showformat' 	=> $showformat,
		'desc' 			=> $desc,
		'replace_title' => $replace_title,
		'template' 		=> $template,
		'usecarturl'	=> $useCartURL
	);
	$amazon_array = apply_filters('appip_shortcode_atts_array',$amazon_array);
	return getSingleAmazonProduct($product_array,$content,0,$amazon_array,$desc);
}
function amazon_appip_register_button( $buttons ) {
	array_push( $buttons, "|", "amazon_products" );
	return $buttons;
}
function amazon_appip_add_plugin( $plugin_array ) {
	$plugin_array['amazon_products'] = plugins_url('/js/wysiwyg/amazon_editor.js',dirname(__FILE__));
	return $plugin_array;
}
function amazon_appip_editor_button() {
	if(is_admin()){
		if(!current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
			return;
		}
		if(get_user_option('rich_editing') == 'true' ) {
			add_filter( 'mce_external_plugins', 'amazon_appip_add_plugin' );
			add_filter( 'mce_buttons', 'amazon_appip_register_button' );
		}
	}
}