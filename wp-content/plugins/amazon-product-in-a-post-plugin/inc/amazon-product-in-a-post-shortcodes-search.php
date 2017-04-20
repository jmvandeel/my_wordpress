<?php
function amazon_add_shortcode_temp(){
	add_shortcode( 'amazon-product-search', 'amazon_product_shortcode_mini_function_new');
}
add_action( 'init', 'amazon_add_shortcode_temp' );

function amazon_product_shortcode_mini_function_new( $atts, $content = ''){
	global $appip_text_lgimage,$showformat,$public_key,$private_key,$aws_partner_id,$aws_partner_locale,$amazonhiddenmsg,$amazonerrormsg,$apippopennewwindow,$apippnewwindowhtml,$buyamzonbutton,$addestrabuybutton,$post,$validEncModes,$appip_text_lgimage;
	$defaults = array(
		'keywords'		=> '',
		'search_index'	=> 'All',
		'sort'			=> 'titlerank',
		'item_page'		=> '1',
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
		'use_cartURL' 	=> false,		
		'show_format' 	=> null,		//added only as a secondary use of showformat
		'list_price' 	=> null, 		//added only as a secondary use of $listprice
		'show_list' 	=> null,		//added only as a secondary use of $listprice 
		'show_used'		=> null,		//added only as a secondary use of $used_price
		'usedprice' 	=> null,		//added only as a secondary use of $used_price
	);
	extract(shortcode_atts($defaults, $atts));
	$item_page		= (int) $item_page;
	//'All','Wine','Wireless','ArtsAndCrafts','Miscellaneous','Electronics','Jewelry','MobileApps','Photo','Shoes','KindleStore','Automotive','MusicalInstruments','DigitalMusic','GiftCards','FashionBaby','FashionGirls','GourmetFood','HomeGarden','MusicTracks','UnboxVideo','FashionWomen','VideoGames','FashionMen','Kitchen','Video','Software','Beauty','Grocery',,'FashionBoys','Industrial','PetSupplies','OfficeProducts','Magazines','Watches','Luggage','OutdoorLiving','Toys','SportingGoods','PCHardware','Movies','Books','Collectibles','VHS','MP3Downloads','Fashion','Tools','Baby','Apparel','Marketplace','DVD','Appliances','Music','LawnAndGarden','WirelessAccessories','Blended','HealthPersonalCare','Classical'	
	$listprice 		= (isset($list_price) && $list_price != null ) ? $list_price : $listprice;
	$listprice 		= (isset($show_list)  && $show_list != null ) ? $show_list : $listprice;
	$used_price		= (isset($usedprice)  && $usedprice != null ) ? $usedprice : $used_price; 
	$used_price		= (isset($show_used)  && $show_used != null ) ? $show_used : $used_price;
	$showformat		= (isset($show_format)&& $show_format != null ) ? $show_format : $showformat;
	$useCartURL		= (isset($use_cartURL) && ($use_cartURL == '1' || $use_cartURL == true) ) ? true : false;

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
	$keywords 	= str_replace(", ",",", $keywords);
	if($keywords != '')
		$keywords = explode(',',$keywords);
	if($field == '' && $fields !=''){$field = $fields;}
	if($aws_partner_locale==''){$aws_partner_locale='com';}
	if($target!=''){$target = ' target="'.$target.'" ';}
	if($appip_text_lgimage == ''){$appip_text_lgimage = 'see larger image';}
	if ( is_array( $keywords ) && !empty( $keywords ) ){
		$errors = '';
		//'salesrank','price','-price','titlerank','-video-release-date','relevancerank','-releasedate'
		$srchArr =  array(
			"Operation" 	=> 'ItemSearch',
			"Condition"		=> 'All', 
			"ResponseGroup" => 'Large', 
			"Keywords"		=> str_replace( " ", '%20', implode( ",", $keywords )), 
			"SearchIndex"	=> $search_index, 
			'ItemPage'		=> '1',
			"AssociateTag" 	=> $partner_id 
		);
		if( ( (int) $item_page >= 1 &&  (int)$item_page <= 10 ) || ( $search_index == 'All' && (int)$item_page >= 1 &&  (int)$item_page <= 5 ) )
			$srchArr['ItemPage'] = (int)$item_page;
		if( $search_index != 'All' )
			$srchArr['Sort'] = $sort;
		$pxmlNew 		= amazon_plugin_aws_signed_request( $locale, $srchArr, $public_key, $private_key);

		$totalResult1 	= array();
		$totalResult2 	= array();
		if( is_array( $pxmlNew ) && !empty( $pxmlNew ) ){
			$pxmle = array();
			foreach($pxmlNew as $pxmlkey => $pxml ){
				if(!is_array($pxml)){
					$pxmle["ItemSearchResponse"]["Errors"]["Code"] .= 'ERROR!';
					$pxmle["ItemSearchResponse"]["Errors"]["Message"] .= $pxml;
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
			$pxml = $pxmle;
			echo '<'.'!-- APPIP ERROR['.str_replace(array('<![CDATA[',']]>',']]&gt;','-->'),array('','','','->'),$pxml).']-->';
			return false;
		}else{
			$resultarr1	= isset($totalResult1) && !empty($totalResult1) ? $totalResult1 : array();//appip_plugin_FormatASINResult( $pxml );
			$resultarr2 = isset($totalResult2) && !empty($totalResult2) ? $totalResult2 : array();//appip_plugin_FormatASINResult( $pxml, 1 );
			if(is_array($resultarr1) && !empty($resultarr1)){
				foreach($resultarr1 as $key1 => $result1):
					$mainAArr 			= (array)$result1;
					$otherArr 			= (array)$resultarr2[$key1];
					$resultarr[$key1] 	= (array)$mainAArr + $otherArr;
				endforeach;
			}
			$arr_position = 0;
			if( is_array( $resultarr ) ):
				$retarr = array();
				$newErr = '';
				foreach($resultarr as $result):
					$currasin = $result['ASIN'];
					if($result['NoData'] == '1'):
						echo '<!-- APPIP ERROR['."\n".str_replace('-->','->',$result['Error']).']-->';
					else:
						$linkURL = ($useCartURL) ? str_replace(array('##REGION##','##AFFID##','##SUBSCRIBEID##'),array($locale,$partner_id,$public_key),$result['CartURL'] ) : $result['URL'];
						if(is_array($field)){
							$fielda = $field;
						}else{
							$fielda = explode(',',str_replace(' ','',$field));
						}
						if($result['Errors'] != '' ){
							$newErr = "<!-- HIDDEN APIP ERROR(S): ".$result['Errors']." -->\n";
						}
						foreach($fielda as $fieldarr){
							switch(strtolower($fieldarr)){
								case 'title':
									if ($showformat != '1'){$result["Title"] = str_replace('('.$result["Title"].')','',$result["Title"]);}
									if(!isset($labels['title-wrap'][$arr_position]) && !isset($labels['title'][$arr_position])){
										$labels['title'][$arr_position] = '<h2 class="appip-title"><a href="'.$linkURL.'"'.$target.'>'. maybe_convert_encoding($result["Title"]).'</a></h2>';
									}elseif(!isset($labels['title-wrap'][$arr_position]) && isset($labels['title'][$arr_position])){
										$labels['title'][$arr_position] = '<h2 class="appip-title"><a href="'.$linkURL.'"'.$target.'>'.$labels['title'][$arr_position].'</a></h2>';
									}elseif(isset($labels['title-wrap'][$arr_position]) && isset($labels['title'][$arr_position])){
										$labels['title'][$arr_position] = "<{$labels['title-wrap'][$arr_position]} class='appip-title'>{$labels['title'][$arr_position]}</a></{$labels['title-wrap'][$arr_position]}>";
									}else{
										$labels['title'][$arr_position] = '<h2 class="appip-title"><a href="'.$linkURL.'"'.$target.'>'. maybe_convert_encoding($result["Title"]).'</a></h2>';
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
										$desc = $result["ItemDesc"][0];
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
										if($result["TotalNew"]>0){
											$retarr[$currasin][$fieldarr] = $labels['price-new'][$arr_position].maybe_convert_encoding($newPrice).' <span class="instock">'.$msg_instock.'</span>';
										}else{
											$retarr[$currasin][$fieldarr] = $labels['price-new'][$arr_position].maybe_convert_encoding($newPrice).' <span class="outofstock">'.$msg_instock.'</span>';
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
									$retarr[$currasin][$fieldarr] = '<div class="amazon-image-wrapper"><a href="'.$linkURL.'"'.$target.'><img src="'./*awsImageGrabber(*/$result['LargeImage']/*,'amazon-image')*/.'" alt="" ></a></div>';
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
									if(isset($result[$fieldarr]) && $result[$fieldarr]!=''){
										if(!isset($labels[$fieldarr][$arr_position])){
											$labels[$fieldarr][$arr_position] = '';
										}else{
											$labels[$fieldarr][$arr_position] = '<span class="appip-label label-'.str_replace(' ','-',$fieldarr).'">'.$labels[$fieldarr][$arr_position].' </span>';
										}
										$retarr[$currasin][$fieldarr] = $labels[$fieldarr][$arr_position].$result[$fieldarr];
									}else{
										$retarr[$currasin][$fieldarr] = '';
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
							if($key!=''){
								$thenewret[] =  '<div class="amazon-element-'.$key.'">'.$val.'</div>';
							}
						}
					}
					if($wrap != ''){
						$thenewret[] = "</{$wrap}>";
					}
					$arr_position++;
				endforeach;
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