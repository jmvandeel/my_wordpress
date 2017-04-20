<?php
//Single Product API Call - Returns One Product Data
if(!function_exists('getSingleAmazonProduct')){
	function getSingleAmazonProduct( $asin='', $extratext='', $extrabutton=0, $manual_array = array(), $desc = 0 ){
		global $public_key; 
		global $private_key; 
		global $aws_partner_id;
		global $aws_partner_locale;
		global $amazonhiddenmsg;
		global $amazonerrormsg;
		global $apippopennewwindow;
		global $apippnewwindowhtml;
		global $appip_text_lgimage;
		global $appip_text_listprice; 
		global $appip_text_newfrom; 
		global $appip_text_usedfrom;
		global $appip_text_instock;
		global $appip_text_outofstock; 
		global $appip_text_author;
		global $appip_text_starring;
		global $appip_text_director;
		global $appip_text_reldate;
		global $appip_text_preorder;
		global $appip_text_releasedon;
		global $appip_text_notavalarea;
		global $appip_text_manufacturer;
		global $appip_text_ESRBAgeRating;
		global $appip_text_feature;
		global $appip_text_platform;
		global $appip_text_genre;
		global $buyamzonbutton;
		global $addestrabuybutton;
		global $encodemode;
		global $post;
		global $validEncModes;
		global $show_format;
		global $appip_templates; 
		global $appipTimestampMsgPrinted;
		$extratext 			= apply_filters('getSingleAmazonProduct_extratext',$extratext);
		$extrabutton		= apply_filters('getSingleAmazonProduct_extrabutton',$extrabutton);
		$manual_array		= apply_filters('getSingleAmazonProduct_manual_array',$manual_array);
		$manual_public_key 	= isset($manual_array['public_key'])	&& $manual_array['public_key'] !='' 	? $manual_array['public_key'] 	: $public_key ;
		$manual_private_key	= isset($manual_array['private_key'])	&& $manual_array['private_key'] !='' 	? $manual_array['private_key'] 	: $private_key ;
		$manual_locale 		= isset($manual_array['locale']) 		&& $manual_array['locale']!='' 			? $manual_array['locale'] 		: $aws_partner_locale ;
		$manual_partner_id	= isset($manual_array['partner_id']) 	&& $manual_array['partner_id'] !='' 	? $manual_array['partner_id'] 	: $aws_partner_id ;
		$manual_new_window	= isset($manual_array['newwindow'])		&& (int) $manual_array['newwindow']!= 0 ? 1 : $apippopennewwindow;
		$apippopennewwindow = $manual_new_window;
		$apippnewwindowhtml	= (int) $manual_new_window == 1 ? ' target="amazonwin" ' : $apippnewwindowhtml;
		if($manual_partner_id == ''){$manual_partner_id = 'wolvid-20';} //have to give it some user id or it will fail.
		if ($asin!='' && $manual_public_key!='' && $manual_private_key!=''){
			// Main Amazon API Call
			$ASIN 					= apply_filters('getSingleAmazonProduct_asin',(is_array($asin) ? implode(',',$asin) : $asin)); //valid ASIN or ASINs 
			$errors 				= '';
			$appip_responsegroup 	= apply_filters('getSingleAmazonProduct_response_group',"Large,Reviews,Offers,Variations");
			$appip_operation 		= apply_filters('getSingleAmazonProduct_operation',"ItemLookup");
			$appip_idtype	 		= apply_filters('getSingleAmazonProduct_type',"ASIN");
			$description			= isset($manual_array['desc'])? $manual_array['desc'] : 0 ; //set to no by default - too many complaints!
			$show_list				= isset($manual_array['listprice'])? $manual_array['listprice'] : 0 ;
			$show_used				= isset($manual_array['used_price'])? $manual_array['used_price'] : 0 ;
			$show_used_price		= $show_used;
			$show_saved_amt			= isset($manual_array['saved_amt'])? $manual_array['saved_amt'] : 0 ;
			$show_format			= isset($manual_array['showformat'])? $manual_array['showformat'] : 1 ;
			$show_features			= isset($manual_array['features'])? $manual_array['features'] : 0 ;
			$show_gallery			= isset($manual_array['gallery'])? $manual_array['gallery'] : 0 ;
			$replace_title			= isset($manual_array['replace_title']) && $manual_array['replace_title'] != '' ? $manual_array['replace_title'] : '' ;
			$template				= isset($manual_array['template']) && $manual_array['template'] != '' ? $manual_array['template'] : 'default' ;
			$show_timestamp			= isset($manual_array['timestamp'])? $manual_array['timestamp'] : 0 ;
			$title_wrap				= isset($manual_array['title_wrap'])? $manual_array['title_wrap'] : 0 ;
			$useCartURL				= (isset($manual_array['usecarturl']) && $manual_array['usecarturl'] == true) || ( get_option('apipp_use_cartURL', '' ) == '1' ) ? true : false ;

			$array_for_templates	= array(  //these are shortcode variables to pass to template functions
				'apippnewwindowhtml'		=> $apippnewwindowhtml,
				'amazonhiddenmsg'			=> $amazonhiddenmsg,
				'amazonerrormsg'			=> $amazonerrormsg,
				'apippopennewwindow'		=> $apippopennewwindow,
				'appip_text_lgimage'		=> $appip_text_lgimage,
				'appip_text_listprice'		=> $appip_text_listprice,
				'appip_text_newfrom'		=> $appip_text_newfrom,
				'appip_text_usedfrom'		=> $appip_text_usedfrom,
				'appip_text_instock'		=> $appip_text_instock,
				'appip_text_outofstock'		=> $appip_text_outofstock,
				'appip_text_author'			=> $appip_text_author,
				'appip_text_starring'		=> $appip_text_starring,
				'appip_text_director'		=> $appip_text_director,
				'appip_text_reldate'		=> $appip_text_reldate,
				'appip_text_preorder'		=> $appip_text_preorder,
				'appip_text_releasedon'		=> $appip_text_releasedon,
				'appip_text_notavalarea'	=> $appip_text_notavalarea,
				'appip_text_manufacturer'	=> $appip_text_manufacturer,
				'appip_text_ESRBAgeRating'	=> $appip_text_ESRBAgeRating,
				'appip_text_feature'		=> $appip_text_feature,
				'appip_text_platform'		=> $appip_text_platform,
				'appip_text_genre'			=> $appip_text_genre,
				'buyamzonbutton'			=> $buyamzonbutton,
				'addestrabuybutton'			=> $addestrabuybutton,
				'description'				=> $description,
				'encodemode'				=> $encodemode,
				'replace_title'				=> $replace_title,
				'show_list'					=> $show_list,
				'show_format'				=> $show_format,
				'show_features'				=> $show_features,
				'show_used_price'			=> $show_used_price,
				'show_saved_amt'			=> $show_saved_amt,
				'show_timestamp'			=> $show_timestamp,
				'show_gallery'				=> $show_gallery,
				'template'					=> $template,
				'title_wrap'				=> $title_wrap,
				'validEncModes'				=> $validEncModes,
			);
			$set_array				= array("Operation" => $appip_operation,"ItemId" => $ASIN,"ResponseGroup" => $appip_responsegroup,"IdType" => $appip_idtype,"AssociateTag" => $manual_partner_id );
			$api_request_array		= array('locale'=>$manual_locale,'public_key'=>$manual_public_key,'private_key'=>$manual_private_key,'api_request_array'=>$set_array);
			$request_array			= apply_filters('appip_pre_request_array',$api_request_array);
			$pxmlNew				= amazon_plugin_aws_signed_request($request_array['locale'],$request_array['api_request_array'],$request_array['public_key'],$request_array['private_key']);

			$returnval 		= '';
			$totalResult1 	= array();
			$totalResult2 	= array();
			if( is_array( $pxmlNew ) && !empty( $pxmlNew ) ){
				$pxmle = array();
				foreach($pxmlNew as $pxmlkey => $pxml ){
					if(!is_array($pxml)){
						$pxmle[] = '<div style="display:none;" class="appip-errors">[APPIP:Error]'.$pxml.'</div>';
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
				$pxml = implode("\n",$pxmle);
				return $pxml;
			}else{
				$resultarr1	= isset($totalResult1) && !empty($totalResult1) ? $totalResult1 : array(); //appip_plugin_FormatASINResult( $pxml );
				$resultarr2 = isset($totalResult2) && !empty($totalResult2) ? $totalResult2 : array(); //appip_plugin_FormatASINResult( $pxml, 1 );
				if(is_array($resultarr1) && !empty($resultarr1)){
					foreach($resultarr1 as $key1 => $result1):
						$mainAArr 			= (array)$result1;
						$otherArr 			= (array)$resultarr2[$key1];
						$resultarr[$key1] 	= (array)$mainAArr + $otherArr;
					endforeach;
				}

				$resultarr 	= has_filter('appip_product_array_processed') ? apply_filters('appip_product_array_processed',$resultarr,$apippnewwindowhtml,$resultarr1,$resultarr2,$template) : $resultarr;

				if( !is_array( $resultarr ) )
					$resultarr = (array) $resultarr;
				if( !empty( $resultarr ) ):
					$array_for_templates['timestamp_printed'] = $appipTimestampMsgPrinted; 
					if($show_timestamp!=0 && $appipTimestampMsgPrinted != 1){
						$appipTimestampMsgPrinted = 1;
						$array_for_templates['timestamp_printed'] = $appipTimestampMsgPrinted; 
					}
					if(count($resultarr) >=1){
						$thedivider = '<div class="appip-multi-divider"></div>';
					}
					foreach($resultarr as $key => $result):
						if(isset($result['NoData']) && (int) $result['NoData'] == 1):
							$check = array();
							if(is_array($result['Error']) && !empty($result['Error'])){
								if(isset($result['Error'][0])){
									foreach($result['Error'] as $k => $v ){
										if( !in_array(implode(':',$v),$check) ){
											$returnval .=  '<div style="display:none;" class="appip-errors">'.implode(":",$v).'</div>';
											$check[] = implode(":",$v);
										}
									}
								}else{
									if( !in_array( implode( ':', $v ),$check ) ){
										$returnval .=  '<div style="display:none;" class="appip-errors">'.implode(":",$result['Error']).'</div>';
										$check[] = $implode(":",$result['Error']);
									}
								}
							}else{
								$returnval .=  '<div style="display:none;" class="appip-errors">'.$result['Error'].'</div>';
							}
							//if($extratext != '')
								//$returnval .= $extratext;
						else:
							$linkURL = $useCartURL ? str_replace(array('##REGION##','##AFFID##','##SUBSCRIBEID##'),array($manual_locale,$manual_partner_id,$manual_public_key),$result['CartURL'] ) : $result['URL'];

							unset($temppart);
							$temppart[] = '<div>';
							$temppart[] = '	<div class="amazon-image-wrapper"><a href="[!URL!]" [!TARGET!]>[!IMAGE!]</a></div>';
							$temppart[] = '	<a rel="appiplightbox-[!ASIN!]" href="[!LARGEIMAGE!]" target="amazonwin"><span class="amazon-tiny">[!LARGEIMAGETXT!]</span></a>';
							if($result['AddlImages']!='' && $show_gallery == 1){
								$temppart[] = '	<div class="amazon-additional-images-wrapper"><span class="amazon-additional-images-text">[!LBL-ADDL-IMAGES!]:</span>[!ADDL-IMAGES!]</div>';
							}	
							$temppart[] = '	<h2 class="amazon-asin-title"><a href="[!URL!]" [!TARGET!]><span class="asin-title">[!TITLE!]</span></a></h2>';
							$temppart[] = '	<div class="amazon-description">[!CONTENT!]</div>';
							
							if($result["Department"]=='Video Games' || $result["ProductGroup"]=='Video Games'){
								$temppart[] = '	<div>';
								$temppart[] = '		<span class="amazon-manufacturer"><span class="appip-label">[!LBL-MANUFACTURER!]:</span> [!MANUFACTURER!]</span><br />';
								$temppart[] = '		<span class="amazon-ESRB"><span class="appip-label">[!LBL-ESRBA!]:</span> [!ESRBA!]</span><br />';
								$temppart[] = '		<span class="amazon-platform"><span class="appip-label">[!LBL-PLATFORM!]:</span> [!PLATFORM!]</span><br />';
								$temppart[] = '		<span class="amazon-system"><span class="appip-label">[!LBL-GENRE!]:</span> [!GENRE!]</span><br />';
								
								if($show_features != 0){
									$temppart[] = '		<span class="amazon-feature"><span class="appip-label">[!LBL-FEATURE!]:</span> [!FEATURE!]</span><br />';
								}		
								$temppart[] = '	</div>';					
							}elseif($show_features != 0 && $result["Feature"] != ''){
								$temppart[] = '		<span class="amazon-feature"><span class="appip-label">[!LBL-FEATURE!]:</span> [!FEATURE!]</span><br />';
							}
							if($result["ReleaseDate"] != ''){	
								$nowdatestt = strtotime(date("Y-m-d",time()));
								$nowminustt = strtotime("-180 days");
								$reldatestt = strtotime($result["ReleaseDate"]);
								if($reldatestt > $nowdatestt){
									$temppart[] = '<span class="amazon-preorder"><br />[!LBL-RELEASED-ON-DATE!] [!RELEASE-DATE!]</span>';
								}elseif($reldatestt >= $nowminustt){
									$temppart[] = '<span class="amazon-release-date">[!LBL-RELEASE-DATE!] [!RELEASE-DATE!]</span>';
								}
							}
							$temppart[] = '<div><a style="display:block;margin-top:8px;margin-bottom:5px;width:165px;" [!TARGET!] href="[!URL!]"><img src="[!AMZ-BUTTON!]" border="0" style="border:0 none !important;margin:0px !important;background:transparent !important;" /></a></div>';
							$temppart[] = '</div>';
							$temppart[] = '<div><hr noshade="noshade" size="1" /></div>';
							$appip_templates['fluffy'] = implode("\n",$temppart);
							$appip_templates = apply_filters('appip-template-filter',$appip_templates, $result);
							if($template !='default' && isset($appip_templates[$template])){
								if($replace_title!=''){$title = $replace_title;}else{$title = maybe_convert_encoding($result["Title"]);}
								$newdesc 	= '';
								if(is_array($result["ItemDesc"]) && $description == 1){
									$desc 	= preg_replace('/^\s*\/\/<!\[CDATA\[([\s\S]*)\/\/\]\]>\s*\z/','$1', $result["ItemDesc"][0]);
									$newdesc = maybe_convert_encoding($desc['Content']);
								}

								$findarr 	= array(
									'[!URL!]',
									'[!TARGET!]',
									'[!IMAGE!]',
									'[!TITLE!]',
									'[!LARGEIMAGE!]',
									'[!LARGEIMAGETXT!]',
									'[!ASIN!]',
									'[!CONTENT!]',
									'[!LBL-MANUFACTURER!]',
									'[!MANUFACTURER!]',
									'[!LBL-ESRBA!]',
									'[!ESRBA!]',
									'[!LBL-PLATFORM!]',
									'[!PLATFORM!]',
									'[!LBL-GENRE!]',
									'[!GENRE!]',
									'[!LBL-FEATURE!]',
									'[!FEATURE!]',
									'[!AMZ-BUTTON!]',
									'[!LBL-RELEASED-ON-DATE!]',
									'[!LBL-RELEASE-DATE!]',	
									'[!RELEASE-DATE!]',	
									'[!LBL-ADDL-IMAGES!]',	
									'[!ADDL-IMAGES!]',	
								);
								$replacearr = array(
									$linkURL,
									$apippnewwindowhtml,
									awsImageGrabber($result['LargeImage'],'amazon-image'),
									$title,
									$result['LargeImage'],
									$appip_text_lgimage,
									$result['ASIN'],
									$newdesc,
									$appip_text_manufacturer,
									maybe_convert_encoding($result["Manufacturer"]),
									$appip_text_ESRBAgeRating,
									maybe_convert_encoding($result["ESRBAgeRating"]),
									$appip_text_platform,
									maybe_convert_encoding($result["Platform"]),
									$appip_text_genre,
									maybe_convert_encoding($result["Genre"]),
									$appip_text_feature,
									maybe_convert_encoding($result["Feature"]),
									plugins_url('/images/'.$buyamzonbutton,dirname(__FILE__)),
									$appip_text_releasedon,
									date("F j, Y", strtotime($result["ReleaseDate"])),
									$appip_text_reldate,
									'Additional Images',
									$result['AddlImages'],
									
								);
								$findarr = apply_filters('appip_template_find_array',$findarr,$template,$result);
								$replacearr = apply_filters('appip_template_replace_array',$replacearr,$template,$result,$title,$desc);
								$returnval	.= str_replace($findarr,$replacearr,$appip_templates[$template]);
							
							}else{

								$returnval .= '	<br /><table cellpadding="0" class="amazon-product-table">'."\n";
								$returnval .= '		<tr>'."\n";
								$returnval .= '			<td valign="top">'."\n";
								$returnval .= '				<div class="amazon-image-wrapper">'."\n";
								$returnval .= '					<a href="' . $linkURL . '" '. $apippnewwindowhtml .'>' . awsImageGrabber($result['MediumImage'],'amazon-image') . '</a><br />'."\n";
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
										$returnval .= '					<h2 class="amazon-asin-title"><a href="' . $linkURL . '" '. $apippnewwindowhtml .'><span class="asin-title">'.$title.'</span></a></h2>'."\n";
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
										//$returnval .= '				<hr noshade="noshade" size="1" />'."\n";
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
											$returnval .= '							<td class="amazon-list-price-label">'.($appip_text_listprice != '' ? $appip_text_listprice .':' : '').'</td>'."\n";
											$returnval .= '							<td class="amazon-list-price-label">'.$amazonhiddenmsg.'</td>'."\n";
											$returnval .= '						</tr>'."\n"; 
										}elseif($result["ListPrice"]!= '0'){
											$returnval .= '						<tr>'."\n";
											$returnval .= '							<td class="amazon-list-price-label">'.($appip_text_listprice != '' ? $appip_text_listprice .':' : '').'</td>'."\n";
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
											$returnval .= '							<td class="amazon-new-label">'.($appip_text_newfrom != '' ? $appip_text_newfrom .':' : '').'</td>'."\n";
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
									$buttonURL  = apply_filters('appip_amazon_button_url',plugins_url('/images/'.$buyamzonbutton,dirname(__FILE__)),$buyamzonbutton,$manual_locale);
									$returnval .= '									<div class="amazon-price-button"><a '. $apippnewwindowhtml .' href="' . $linkURL .'"><img class="amazon-price-button-img" src="'.$buttonURL.'" /></a></div>'."\n";
									if($extrabutton==1 && $aws_partner_locale!='.com'){
									//$returnval .= '									<br /><div><a style="display:block;margin-top:8px;margin-bottom:5px;width:165px;" '. $apippnewwindowhtml .' href="' . $linkURL .'"><img src="'.plugins_url('/images/buyamzon-button.png',dirname(__FILE__)).'" border="0" style="border:0 none !important;margin:0px !important;background:transparent !important;"/></a></div>'."\n";
									}
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
								if($result["CachedAPPIP"] !=''){
									$returnval .= '<'.'!-- APPIP Item Cached ['.$result["CachedAPPIP"].'] -->'."\n";
								}
								$returnval .= $thedivider;
							}//template
						endif;
					endforeach;
				endif;
				return apply_filters('appip_single_product_filter',$returnval,$resultarr);
			}
		}
	}
}