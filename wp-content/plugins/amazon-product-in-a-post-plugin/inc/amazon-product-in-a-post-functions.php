<?php 
// FUTURE:
//https://images-eu.ssl-images-amazon.com/ will replace http://ecx.images-amazon.com/ for secure images.

function aws_prodinpost_filter_get_excerpt($text){
	global $appip_running_excerpt;
	$appip_running_excerpt = true;
	return $text;
}


if(!function_exists('aws_prodinpost_filter_content_test')){
	function aws_prodinpost_filter_content_test($text){
		global $post,$apipphookcontent, $appip_running_excerpt, $apipphookexcerpt;
		$ActiveProdPostAWS 			= get_post_meta($post->ID,'amazon-product-isactive',true);
		$singleProdPostAWS 			= get_post_meta($post->ID,'amazon-product-single-asin',true);
		$AWSPostLoc 				= get_post_meta($post->ID,'amazon-product-content-location',true);
		$apippContentHookOverride 	= get_post_meta($post->ID,'amazon-product-content-hook-override',true);
		$apippExcerptHookOverride 	= get_post_meta($post->ID,'amazon-product-excerpt-hook-override',true);
		$apippShowSingularonly 		= get_post_meta($post->ID,'amazon-product-singular-only',true);
		$showFormat					= get_post_meta($post->ID,'amazon-product-show-format',true);
		$showDesc 					= get_post_meta($post->ID,'amazon-product-amazon-desc',true);
		$showGallery 				= get_post_meta($post->ID,'amazon-product-show-gallery',true);
		$showFeatures 				= get_post_meta($post->ID,'amazon-product-show-features',true);
		$newWindow 					= get_post_meta($post->ID,'amazon-product-newwindow',true);
		$showList 					= get_post_meta($post->ID,'amazon-product-show-list-price',true);
		$showUsed 					= get_post_meta($post->ID,'amazon-product-show-used-price',true);
		$showSaved 					= get_post_meta($post->ID,'amazon-product-show-saved-amt',true);
		$showTimestamp 				= get_post_meta($post->ID,'amazon-product-timestamp',true);
		$newTitle 					= get_post_meta($post->ID,'amazon-product-new-title',true);
		$useCartURL					= get_post_meta($post->ID,'amazon-product-use-cartURL',true) == '1' ? true : false;
		$newWindow					= $newWindow == '2' ? 1 : 0;
		
		$manualArray = array(
			'desc' 			=> $showDesc,
			'listprice' 	=> $showList,
			'showformat' 	=> $showFormat,
			'features' 		=> $showFeatures ,
			'used_price' 	=> $showUsed,
			'saved_amt'		=> $showSaved,
			'timestamp' 	=> $showTimestamp,
			'gallery' 		=> $showGallery,
			'replace_title' => $newTitle,
			'usecarturl'	=> $useCartURL,
			'newwindow' 	=> $newWindow
		);
		
		/* 
		* Strip Excerpt Shortcodes:
		* this strips the shortcodes out of the excerpt in the event
		* that there is not excerpt and one is created using the content.
		* otherwise you get nonsense text from removed HTML in product.
		*/
		$stripShortcodes = false;
		if(	$appip_running_excerpt == true ){
			if( ( (bool) $apipphookexcerpt && $apippExcerptHookOverride != '3') )
				$stripShortcodes = true;
		}
		/* END Strip Excerpt Shortcodes */

		$scode_attrs 	= array('amazon-element','amazon-elements','amazonproducts','amazonproduct','AMAZONPRODUCTS','AMAZONPRODUCT');
		$pattern 		= get_shortcode_regex();
		$ASINs_Set		= $singleProdPostAWS;
		$allASIN 		= $singleProdPostAWS != '' ? explode(',',str_replace(', ',',',$singleProdPostAWS)): array();
		$grASIN			= array();
		foreach( $scode_attrs as $scode ){
			if( has_shortcode( $text, $scode ) ){
				if ( preg_match_all( '/'. $pattern .'/s', $text, $matches ) && array_key_exists( 2, $matches ) && in_array( $scode, $matches[2] ) ){
					foreach($matches[3] as $a){
						$attrs = shortcode_parse_atts($a);
						if(isset($attrs['asin'])){
							$temp = explode(',',$attrs['asin']);
							foreach($temp as $tempval){
								array_push( $allASIN, $tempval );
							}
						}
					}
				}
			}
		}
		if(!empty($allASIN)){
			foreach($allASIN as $asinl){
				$grASIN[$asinl] = $asinl;
			}
		}
		
		if(!empty($grASIN)){
			$params = array('ItemId' => implode( ',', $grASIN ), 'CacheOnly' => true);
			amazon_plugin_aws_signed_request('',$params);
		}

		$doshort = false;
		foreach( $scode_attrs as $scode ){
			if( has_shortcode( $text, $scode ) ){
				if ( preg_match_all( '/'. $pattern .'/s', $text, $matches ) && array_key_exists( 2, $matches ) && in_array( $scode, $matches[2] ) ){
					if( ( $apippShowSingularonly == '1' && !is_singular()) || $stripShortcodes ){
						foreach( $matches[0] as $scs )
							$text = str_replace( $scs , '', $text );
					}else{
						$doshort = true;
					}
				}
			}
		}
		if($stripShortcodes)
			return $text;
		if($doshort)
			$text = do_shortcode($text);

		if( $apippShowSingularonly == '1' ){
		    if( is_singular() && ( ( $apipphookcontent == true && $apippContentHookOverride != '3') || $apippContentHookOverride == '' || $apipphookcontent == '' ) ){ //if options say to show it, show it
			  	if( $singleProdPostAWS != '' && $ActiveProdPostAWS != '' ){
			  		if($AWSPostLoc=='2'){
			  			//Post Content is the description
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,$text,0,$manualArray);
			  		}elseif($AWSPostLoc=='3'){
			  			//Post Content before product
			  			$theproduct = $text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'',0,$manualArray);
			  		}else{
			  			//Post Content after product - default
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,'',0,$manualArray).'<br />'.$text;
			  		}
			  		return $theproduct;
			  	} else {
			  		return $text;
			  	}
			 }
		}else{
		    if( ( $apipphookcontent == true && $apippContentHookOverride != '3') || $apippContentHookOverride == '' || $apipphookcontent == '' ){ //if options say to show it, show it
			  	if( $singleProdPostAWS != '' && $ActiveProdPostAWS != '' ){
			  		if( $AWSPostLoc == '2' ){
			  			//Post Content is the description
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,$text,0,$manualArray);
			  		}elseif($AWSPostLoc=='3'){
			  			//Post Content before product
			  			$theproduct = $text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'',0,$manualArray);
			  		}else{
			  			//Post Content after product - default
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,'',0,$manualArray).'<br />'.$text;
			  		}
			  		return $theproduct;
			  	} else {
			  		return $text;
			  	}
			 }
		}
		return $text;
	}
}

if(!function_exists('aws_prodinpost_filter_excerpt_test')){
	function aws_prodinpost_filter_excerpt_test($text){
		global $post,$apipphookexcerpt;
		$ActiveProdPostAWS 			= get_post_meta($post->ID,'amazon-product-isactive',true);
		$singleProdPostAWS 			= get_post_meta($post->ID,'amazon-product-single-asin',true);
		$AWSPostLoc 				= get_post_meta($post->ID,'amazon-product-content-location',true);
		$apippExcerptHookOverride 	= get_post_meta($post->ID,'amazon-product-excerpt-hook-override',true);
		$apippShowSingularonly 		= get_option('appip_show_single_only')=='1' ? '1' : '0';
		$apippShowSingularonly2 	= get_post_meta($post->ID,'amazon-product-singular-only',true);
		$showFormat					= get_post_meta($post->ID,'amazon-product-show-format',true);
		$showDesc 					= get_post_meta($post->ID,'amazon-product-amazon-desc',true);
		$showGallery 				= get_post_meta($post->ID,'amazon-product-show-gallery',true);
		$showFeatures 				= get_post_meta($post->ID,'amazon-product-show-features',true);
		$showList 					= get_post_meta($post->ID,'amazon-product-show-list-price',true);
		$showUsed 					= get_post_meta($post->ID,'amazon-product-show-used-price',true);
		$showSaved 					= get_post_meta($post->ID,'amazon-product-show-saved-amt',true);
		$showTimestamp 				= get_post_meta($post->ID,'amazon-product-timestamp',true);
		$useCartURL					= get_post_meta($post->ID,'amazon-product-use-cartURL',true) == '1' ? true : false;
		$newTitle 					= get_post_meta($post->ID,'amazon-product-new-title',true);
		$manualArray = array(
			'desc' 			=> $showDesc,
			'listprice' 	=> $showList,
			'showformat' 	=> $showFormat,
			'features' 		=> $showFeatures ,
			'used_price' 	=> $showUsed,
			'saved_amt'		=> $showSaved,
			'timestamp' 	=> $showTimestamp,
			'gallery' 		=> $showGallery,
			'usecarturl'	=> $useCartURL,
			'replace_title' => $newTitle
		);
		$apippShowSingularonly	= $apippShowSingularonly2 == '1' ? '1' : $apippShowSingularonly;
		$scode_attrs 	= array('amazon-element','amazon-elements','amazonproducts','amazonproduct','AMAZONPRODUCTS','AMAZONPRODUCT');
		$pattern 		= get_shortcode_regex();
		$ASINs_Set		= $singleProdPostAWS;
		$allASIN 		= $singleProdPostAWS != '' ? explode(',',str_replace(', ',',',$singleProdPostAWS)): array();
		$grASIN			= array();
		
		if(( (bool) $apipphookexcerpt && $apippExcerptHookOverride != '3')){ //if options say to show it, show it
			foreach( $scode_attrs as $scode ){
				if( has_shortcode( $text, $scode ) ){
					if ( preg_match_all( '/'. $pattern .'/s', $text, $matches ) && array_key_exists( 2, $matches ) && in_array( $scode, $matches[2] ) ){
						foreach($matches[3] as $a){
							$attrs = shortcode_parse_atts($a);
							if( isset( $attrs['asin'] ) ){
								$temp = explode(',',$attrs['asin']);
								foreach($temp as $tempval){
									array_push( $allASIN, $tempval );
								}
							}
						}
					}
				}
			}
			if( !empty($allASIN) ){
				foreach($allASIN as $asinl){
					$grASIN[$asinl] = $asinl;
				}
			}
			
			if( !empty($grASIN) ){
				$params = array('ItemId' => implode( ',', $grASIN ), 'CacheOnly' => true);
				amazon_plugin_aws_signed_request('',$params);
			}

			//replace short tag here. Handle a bit different than content so they get stripped if they don't want to hook excerpt we don't want to show the [AMAZON-PRODUCT=XXXXXXXX] tag in the excerpt text!
			$doshort = false;
			foreach( $scode_attrs as $scode ){
				if( has_shortcode( $text, $scode ) ){
					if ( preg_match_all( '/'. $pattern .'/s', $text, $matches ) && array_key_exists( 2, $matches ) && in_array( $scode, $matches[2] ) ){
						if( $apippShowSingularonly == '1' && !is_singular() ){
							foreach( $matches[0] as $scs )
								$text = str_replace( $scs , '', $text );
						}else{
							$doshort = true;
						}
					}
				}
			}
			if($doshort)
				$text = do_shortcode($text);
				
			if($apippShowSingularonly=='1'){
			  	if(is_singular()&& ($singleProdPostAWS!='' && $ActiveProdPostAWS!='')){
			  		if($AWSPostLoc=='2'){
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,$text,0,$manualArray);
			  		}elseif($AWSPostLoc=='3'){
			  			$theproduct = $text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'',0,$manualArray);
			  		}else{
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,'',0,$manualArray).'<br />'.$text;
			  		}
			  		return $theproduct;
			  	} else {
			  		return $text;
			  	}
			}else{
			  	if($singleProdPostAWS!='' && $ActiveProdPostAWS!=''){
			  		if($AWSPostLoc=='2'){
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,$text,0,$manualArray);
			  		}elseif($AWSPostLoc=='3'){
			  			$theproduct = $text.'<br />'.getSingleAmazonProduct($singleProdPostAWS,'',0,$manualArray);
			  		}else{
			  			$theproduct = getSingleAmazonProduct($singleProdPostAWS,'',0,$manualArray).'<br />'.$text;
			  		}
			  		return $theproduct;
			  	} else {
			  		return $text;
			  	}
			}
		}else{
			foreach( $scode_attrs as $scode ){
				if( has_shortcode( $text, $scode ) ){
					if ( preg_match_all( '/'. $pattern .'/s', $text, $matches ) && array_key_exists( 2, $matches ) && in_array( $scode, $matches[2] ) ){
						foreach( $matches[0] as $scs )
							$text = str_replace( $scs , '', $text );//take the darn thing out!
					}
				}
			}
		}
		return $text;
	}
}

function amazon_plugin_postlist_detect_and_cache_ASINs($query){
	$cache_ahead = get_option('apipp_amazon_cache_ahead', '0');
	if( !is_singular() && ( is_main_query() && in_the_loop() ) && $cache_ahead != '0' ){
		global $wp_query;
		$old 			= $query;
		$scode_attrs 	= array('amazon-element','amazon-elements','amazonproducts','amazonproduct','AMAZONPRODUCTS','AMAZONPRODUCT');
		$pattern 		= get_shortcode_regex();
		$allASIN 		= array();
		$grASIN			= array();
		if($query->have_posts()){
			foreach( $query->posts as $apposts ){
				//get meta
					$mActv = get_post_meta( $apposts->ID,'amazon-product-isactive',true );
					$mASIN = get_post_meta( $apposts->ID,'amazon-product-single-asin',true );
					if( $mActv == '1' && $mASIN != '' ){
						$newASN = explode( ',', str_replace( ', ', ',', $mASIN ) );
						if( is_array($newASN) && !empty( $newASN ) ){
							foreach($newASN as $Aval){
								array_push( $allASIN, $Aval );
							}
						}
					}
				//get scodes				
				foreach( $scode_attrs as $scode ){
					if( has_shortcode( $apposts->post_content, $scode ) ){
						if ( preg_match_all( '/'. $pattern .'/s', $apposts->post_content, $matches ) && array_key_exists( 2, $matches ) && in_array( $scode, $matches[2] ) ){
							foreach($matches[3] as $a){
								$attrs = shortcode_parse_atts($a);
								if(isset($attrs['asin'])){
									$temp = explode(',',$attrs['asin']);
									foreach($temp as $tempval){
										array_push( $allASIN, $tempval );
									}
								}
							}
						}
					}
				}
			}
		}
		if(!empty($allASIN)){
			foreach($allASIN as $asinl){
				$grASIN[$asinl] = $asinl;
			}
		}
		if(!empty($grASIN)){
			//cache all the ones on the page if possible.
			$params = array( 'ItemId' => implode( ',', $grASIN ), 'CacheOnly' => true );
			amazon_plugin_aws_signed_request('',$params);
		}
		$wp_query = $old;
	}
}
add_action( 'loop_start', 'amazon_plugin_postlist_detect_and_cache_ASINs' ); //testing only

function maybe_convert_encoding($text){
	$encmode_temp 	= mb_detect_encoding( "aeioué",mb_detect_order());
	$encodemode 	= get_bloginfo( 'charset' );
	if($encmode_temp!=$encodemode){
		return mb_convert_encoding($text, $encodemode, $encmode_temp)	;
	}
	return $text;
}

function appip_product_array_processed_add_variants( $resultarr, $newWin='' ){
	$resultArrNew = array();
	if( !(is_array($resultarr) && !empty($resultarr)) )
		return $resultArrNew;
	foreach($resultarr as $key => $val){
		if(isset($val['Offers_TotalOffers']) && $val['Offers_TotalOffers'] == '0'){
			$varLowPrice 	= isset($val['VariationSummary_LowestSalePrice_FormattedPrice']) ? $val['VariationSummary_LowestSalePrice_FormattedPrice'] : (isset($val['VariationSummary_LowestPrice_FormattedPrice']) ? $val['VariationSummary_LowestPrice_FormattedPrice'] : '');
			$varHiPrice		= isset($val['VariationSummary_HighestPrice_FormattedPrice']) ? $val['VariationSummary_HighestPrice_FormattedPrice'] : '';
			$varTotalNum 	= isset($val['Variations_TotalVariations']) ? (int)$val['Variations_TotalVariations'] : 0;
			$hasMainList 	= isset($val['ItemAttributes_ListPrice_FormattedPrice']) ? 1 : 0;
			if($hasMainList == 1){
				$val['ListPrice'] = isset($val['ItemAttributes_ListPrice_FormattedPrice']) ? $val['ItemAttributes_ListPrice_FormattedPrice'] : '';
			}
			if( $varTotalNum > 0 ){
				if($varTotalNum == 1){
					//Set Main Image as first variant Image if product does not have Image
					$val['MediumImage'] = isset($val['LargeImage_URL']) && $val['LargeImage_URL'] != '' ? $val['LargeImage_URL'] : (isset($val['Variations_Item_LargeImage_URL']) ? $val['Variations_Item_LargeImage_URL'] : '');
					$val['LargeImage']	= isset($val['LargeImage_URL']) && $val['LargeImage_URL'] != '' ? $val['LargeImage_URL'] : (isset($val['Variations_Item_LargeImage_URL']) ? $val['Variations_Item_LargeImage_URL'] : ''); ;
				}else{
					//Set Main Image as first variant Image if product does not have Image
					$val['MediumImage'] = isset($val['LargeImage_URL']) && $val['LargeImage_URL'] != '' ? $val['LargeImage_URL'] : (isset($val['Variations_Item_0_LargeImage_URL']) ? $val['Variations_Item_0_LargeImage_URL'] : '');
					$val['LargeImage']	= isset($val['LargeImage_URL']) && $val['LargeImage_URL'] != '' ? $val['LargeImage_URL'] : (isset($val['Variations_Item_0_LargeImage_URL']) ? $val['Variations_Item_0_LargeImage_URL'] : ''); ;
				}
				//Set New price for "from X to Y"
				if($varLowPrice != '' && $varHiPrice != ''){
					$val['LowestNewPrice'] = $varLowPrice.' &ndash; '. $varHiPrice;
				}

				//Set Total New
				$val["TotalNew"] = 1; //needs to be at least one to not show "Out of Stock".
				$val["PriceHidden"] = 0;
				$val["HideStockMsg"] = 1;
				
				//List Varients
				$vartype = isset($val['Variations_VariationDimensions_VariationDimension']) ? $val['Variations_VariationDimensions_VariationDimension'] : '';
				if($vartype != '') {
					$val['VariantHTML'] = '<div class="amazon_variations_wrapper">'.__('Variations:','amazon-product-in-a-post-plugin').' ('.$vartype.'):';
				}else{
					$val['VariantHTML'] = '<div class="amazon_variations_wrapper">'.__('Variations:','amazon-product-in-a-post-plugin').':';
				}
				$target = $newWin == '' ? '' : $newWin ; 
				$ImageSetsArray = array();
				if($varTotalNum == 1){
						$varASIN	= isset($val['Variations_Item_ASIN']) ? $val['Variations_Item_ASIN'] : '';
						if($hasMainList == 0 && isset($val['Variations_Item_ItemAttributes_ListPrice_FormattedPrice'])){
							$val['ListPrice'] = $val['Variations_Item_ItemAttributes_ListPrice_FormattedPrice'];
						}
						//for image sets
						for ($y = 0; $y < 10; $y++){
							if( isset($val['Variations_Item_ImageSets_ImageSet_'.$y.'_LargeImage_URL']) && isset($val['Variations_Item_ImageSets_ImageSet_'.$y.'_SmallImage_URL'])){
								$lgImg 	= $val['Variations_Item_ImageSets_ImageSet_'.$y.'_LargeImage_URL'];
								$swImg	= $val['Variations_Item_ImageSets_ImageSet_'.$y.'_SmallImage_URL'];
								if($lgImg != '' && $swImg !=''){
									$ImageSetsArray[] = '<a rel="appiplightbox-'.$val['ASIN'].'" href="'.$lgImg .'" target="amazonwin"><img src="'.$swImg.'" class="apipp-additional-image" target="amazonwin"/></a>'."\n";
								}
							}else{
								if($y > 9){
									break 1;
								}
							}
						}
						$varT 		= isset($val['Variations_Item_VariationAttributes_VariationAttribute_Value']) ? $val['Variations_Item_VariationAttributes_VariationAttribute_Value'] : '';
						$varC 		= isset($val['Variations_Item_Offers_Offer_OfferAttributes_Condition']) ? $val['Variations_Item_Offers_Offer_OfferAttributes_Condition'] : '' ;
						$varD 		= isset($val['Variations_Item_Offers_Offer_OfferListing_SalePrice_CurrencyCode']) ? get_appipCurrCode($val['Variations_Item_Offers_Offer_OfferListing_SalePrice_CurrencyCode']) : (isset($val['Variations_Item_Offers_Offer_OfferListing_Price_CurrencyCode']) ? get_appipCurrCode($val['Variations_Item_Offers_Offer_OfferListing_Price_CurrencyCode']) : '') ;
						$varP 		= isset($val['Variations_Item_Offers_Offer_OfferListing_SalePrice_FormattedPrice']) ? $val['Variations_Item_Offers_Offer_OfferListing_SalePrice_FormattedPrice'] : (isset($val['Variations_Item_Offers_Offer_OfferListing_Price_FormattedPrice']) ? $val['Variations_Item_Offers_Offer_OfferListing_Price_FormattedPrice'] : '');
						$linkStart 	= $varASIN != '' ? '<a href="'.str_replace($val['ASIN'],$varASIN,$val['URL']).'"'.$target.'>' : '';
						$linkEnd 	= $linkStart != '' ? '</a>' : '';
						$varL 		= $linkStart != '' ? ($linkStart.$varT.$linkEnd) : $varT;
						$photo		= isset($val['Variations_Item_SmallImage_URL']) ? $linkStart.'<img class="amazon-varient-image" src="'.$val['Variations_Item_SmallImage_URL'].'" />'.$linkEnd : '';
						if($varT !='' && $varC !='' && $varP!=''){
							$val['VariantHTML'] .= '<div class="amazon_varients">'.$photo.'<span class="amazon-varient-type-link">'.$varL.'</span> &mdash; <span class="amazon-varient-type-price"><span class="amazon-variant-price-text">'.$varC.' '.__('from','amazon-product-in-a-post-plugin').'</span> '.$varP.$varD.'</span></div>'."\n";
						}
					$val['VariantHTML'] .= '</div>';

					//Make Image Set from the first image for each varient
					if(!empty($ImageSetsArray)){
						if( count( $ImageSetsArray) > 10)
							$ImageSetsArray = array_slice($ImageSetsArray, 0, 10);
						$val['AddlImages'] = implode("\n",$ImageSetsArray);
					}
					
				}else{
					for ($x = 0; $x <= ($varTotalNum-1); $x++) {
						$varASIN	= isset($val['Variations_Item_'.$x.'_ASIN']) ? $val['Variations_Item_'.$x.'_ASIN'] : '';
						if($x == 0 && $hasMainList == 0 && isset($val['Variations_Item_'.$x.'_ItemAttributes_ListPrice_FormattedPrice'])){
							$val['ListPrice'] = $val['Variations_Item_'.$x.'_ItemAttributes_ListPrice_FormattedPrice'];
						}
						//for image sets
						for ($y = 0; $y < 10; $y++){
							if( isset($val['Variations_Item_'.$x.'_ImageSets_ImageSet_'.$y.'_LargeImage_URL']) && isset($val['Variations_Item_'.$x.'_ImageSets_ImageSet_'.$y.'_SmallImage_URL'])){
								$lgImg 	= $val['Variations_Item_'.$x.'_ImageSets_ImageSet_'.$y.'_LargeImage_URL'];
								$swImg	= $val['Variations_Item_'.$x.'_ImageSets_ImageSet_'.$y.'_SmallImage_URL'];
								if($lgImg != '' && $swImg !=''){
									$ImageSetsArray[] = '<a rel="appiplightbox-'.$val['ASIN'].'" href="'.$lgImg .'" target="amazonwin"><img src="'.$swImg.'" class="apipp-additional-image"/></a>'."\n";
								}
							}else{
								if($y > 9){
									break 1;
								}
							}
						}
						$varT 		= isset($val['Variations_Item_'.$x.'_VariationAttributes_VariationAttribute_Value']) ? $val['Variations_Item_'.$x.'_VariationAttributes_VariationAttribute_Value'] : '';
						$varC 		= isset($val['Variations_Item_'.$x.'_Offers_Offer_OfferAttributes_Condition']) ? $val['Variations_Item_'.$x.'_Offers_Offer_OfferAttributes_Condition'] : '' ;
						$varD 		= isset($val['Variations_Item_'.$x.'_Offers_Offer_OfferListing_SalePrice_CurrencyCode']) ? get_appipCurrCode($val['Variations_Item_'.$x.'_Offers_Offer_OfferListing_SalePrice_CurrencyCode']) : (isset($val['Variations_Item_'.$x.'_Offers_Offer_OfferListing_Price_CurrencyCode']) ? get_appipCurrCode($val['Variations_Item_'.$x.'_Offers_Offer_OfferListing_Price_CurrencyCode']) : '') ;
						$varP 		= isset($val['Variations_Item_'.$x.'_Offers_Offer_OfferListing_SalePrice_FormattedPrice']) ? $val['Variations_Item_'.$x.'_Offers_Offer_OfferListing_SalePrice_FormattedPrice'] : (isset($val['Variations_Item_'.$x.'_Offers_Offer_OfferListing_Price_FormattedPrice']) ? $val['Variations_Item_'.$x.'_Offers_Offer_OfferListing_Price_FormattedPrice'] : '');
						$linkStart 	= $varASIN != '' ? '<a href="'.str_replace($val['ASIN'],$varASIN,$val['URL']).'"'.$target.'>' : '';
						$linkEnd 	= $linkStart != '' ? '</a>' : '';
						$varL 		= $linkStart != '' ? ($linkStart.$varT.$linkEnd) : $varT;
						$photo		= isset($val['Variations_Item_'.$x.'_SmallImage_URL']) ? $linkStart.'<img class="amazon-varient-image" src="'.$val['Variations_Item_'.$x.'_SmallImage_URL'].'" />'.$linkEnd : '';
						if($varT !='' && $varC !='' && $varP!=''){
							$val['VariantHTML'] .= '<div class="amazon_varients">'.$photo.'<span class="amazon-varient-type-link">'.$varL.'</span> &mdash; <span class="amazon-varient-type-price"><span class="amazon-variant-price-text">'.$varC.' '.__('from','amazon-product-in-a-post-plugin').'</span> '.$varP.$varD.'</span></div>'."\n";
						}
					} 
					$val['VariantHTML'] .= '</div>';
	
					//Make Image Set from the first image for each varient
					if(!empty($ImageSetsArray)){
						if( count( $ImageSetsArray) > 10)
							$ImageSetsArray = array_slice($ImageSetsArray, 0, 10);
						$val['AddlImages'] = implode("\n",$ImageSetsArray);
					}
				}
	
			}
			
		}
		$resultArrNew[] = $val;
	}
	return $resultArrNew;
}
add_filter('appip_product_array_processed','appip_product_array_processed_add_variants',10,2);


function appip_fix_button_url_for_locale($url='',$button='',$locale=''){
	/*
	if(($button == 'buyamzon-button-'.$locale.'.png') || ($button == 'buyamzon-button.png' && $locale == 'com') ){
		return $url;
	}else{
		$tempURL 	= str_replace($button,'',$url);
		$newURL 	= $tempURL .'new-buyamzon-button-'.$locale.'.png';
		return $newURL;
	}
	*/
	$button = 'new-buyamzon-button-'.$locale.'.png';
	$newurl = plugins_url( '/images/'.$button, dirname(__FILE__) );
	return $newurl;	
}
add_filter('appip_amazon_button_url','appip_fix_button_url_for_locale',5,3);

if(!function_exists('awsImageGrabber')){
	//Amazon Product Image from ASIN function - Returns HTML Image Code
	function awsImageGrabber($imgurl, $class=""){
		if($imgurl != ''){
	    	return '<img src="'.$imgurl.'" class="amazon-image '.$class.'" />';
		}else{
	    	return '<img src="'. plugins_url('/images/noimage.jpg',dirname(__FILE__)).'" class="amazon-image '.$class.'" />';
		}
	}
}

/*
To filter labels:
add_filter('appip_text_newfrom', '_clear_appip_text');
function _clear_appip_text($val=''){
	return 'Your Text Label Here';
}
*/

if(!function_exists('awsImageGrabberURL')){
	//Amazon Product Image from ASIN function - Returns URL only
	function awsImageGrabberURL($asin, $size="M"){
	    $base_url = 'http://images.amazon.com/images/P/'.$asin.'.01.';
	    if (strcasecmp($size, 'S') == 0){
	      $base_url .= '_AA200_SCLZZZZZZZ_';
	    }else if (strcasecmp($size, 'L') == 0){
	      $base_url .= '_AA450_SCSCRM_';
	    }else if (strcasecmp($size, 'H') == 0){ //huge
	      $base_url .= '_SCRM_';
	    }else if (strcasecmp($size, 'P') == 0){ //pop
	      $base_url .= '_AA800_SCRM_';
	    }else{
	      $base_url .= '_AA300_SCLZZZZZZZ_';
	    }
	    $base_url .= '.jpg';
	    return $base_url;
	}
}
	
if(!function_exists('awsImageURLModify')){
	//Amazon Product Image from ASIN function - Returns URL only
	function awsImageURLModify($imgurl, $size="P"){
		//http://ecx.images-amazon.com/images/I/
	    $base_url = str_replace('.jpg','.',$imgurl);
	    if (strcasecmp($size, 'S') == 0){
	      $base_url .= '_SY200_';
	    }else if (strcasecmp($size, 'L') == 0){
	      $base_url .= '_SY450_';
	    }else if (strcasecmp($size, 'H') == 0){ //huge
	      $base_url .= '_SY1200_';
	    }else if (strcasecmp($size, 'P') == 0){ //pop
	      $base_url .= '_SY800_';
	    }else{
	      $base_url .= '_SY300_';
	    }
	    $base_url .= '.jpg';
	    return $base_url;
	}
}

/**
 * Add Styles to HTML Head.
 *
 * Echos the content to the head.
 *
 * @depricated 3.5.3 Replaced with Ajax Call for faster action
 * @since 1.8
 *
 * @echo stylesheet links.
 */
function aws_prodinpost_addhead(){
	global $aws_plugin_version;
	$amazonStylesToUseMine = get_option("apipp_product_styles_mine"); //is box checked?
	echo '<'.'!-- Amazon Product In a Post Plugin Styles & Scripts - Version '.$aws_plugin_version.' -->'."\n";
	if($amazonStylesToUseMine=='true'){ //use there styles
		echo '<link rel="stylesheet" href="'.get_bloginfo('url').'/index.php?apipp_style=custom" type="text/css" media="screen" />'."\n";
	}else{ //use default styles
		echo '<link rel="stylesheet" href="'.get_bloginfo('url').'/index.php?apipp_style=default" type="text/css" media="screen" />'."\n";
	}
	echo '<link rel="stylesheet" href="'.plugins_url('/css/amazon-lightbox.css',dirname(__FILE__)).'" type="text/css" media="screen" />'."\n";
	echo '<'.'!-- End Amazon Product In a Post Plugin Styles & Scripts-->'."\n";
}
	
/**
 * Enqueue styles for plugin.
 * Replaces previous function aws_prodinpost_addhead().
 *
 * @since 3.5.3
 *
 * @return none.
 */
function appip_addhead_new_ajax(){
	if(file_exists(get_stylesheet_directory().'/appip-styles.css')){
		wp_enqueue_style('appip-theme-styles',get_stylesheet_directory_uri().'/appip-styles.css',array(),null);
	}elseif(file_exists(get_stylesheet_directory().'/css/appip-styles.css')){
		wp_enqueue_style('appip-theme-styles',get_stylesheet_directory_uri().'/css/appip-styles.css',array(),null);
	}else{
		$ajax_nonce = wp_create_nonce( 'appip_style_verify' );
		wp_enqueue_style('appip-dynamic-styles',admin_url('admin-ajax.php').'?action=appip_dynaminc_css_custom&nonce='.$ajax_nonce,array(),null);
	}
	wp_enqueue_style('appip-lightbox', plugins_url().'/amazon-product-in-a-post-plugin/css/amazon-lightbox.css', array(),null);
}
add_action('wp_enqueue_scripts', 'appip_addhead_new_ajax',10);

/**
 * Dynamic style creation. Replaces old styles layout which is very slow on large sites.
 *
 * Prints CSS styles out in the browser dynamically.
 *
 * @since 3.5.3
 *
 * @echo css values stored in DB.
 * @return none.
 */
function appip_dynaminc_css_custom() {
	check_ajax_referer( 'appip_style_verify', 'nonce', true );  
	$usemine    = get_option('apipp_product_styles_mine', false);
	$data       = $usemine ? get_option('apipp_product_styles', '') : get_option('apipp_product_styles_default', '') ;
  	header('Content-type: text/css');
  	header('Cache-control: must-revalidate');	
	echo $data;
	exit;
}
add_action('wp_ajax_appip_dynaminc_css_custom', 'appip_dynaminc_css_custom');
add_action('wp_ajax_nopriv_appip_dynaminc_css_custom', 'appip_dynaminc_css_custom');

function appip_delete_cache_ajax(){
	check_ajax_referer( 'appip_cache_delete_nonce_ji9osdjfkjl', 'appip_nonce', true );
	if( !isset( $_POST['appip-cache-id'] ) ){
		echo 'error';
		exit;
	}
	$cacheid = isset( $_POST['appip-cache-id'] ) ? (int) $_POST['appip-cache-id'] : 0;
	global $wpdb;
	if($cacheid == 0){
		$tempswe = $wpdb->query("DELETE FROM {$wpdb->prefix}amazoncache;");
	}else{
		$tempswe = $wpdb->query("DELETE FROM {$wpdb->prefix}amazoncache WHERE Cache_id ='{$cacheid}' LIMIT 1;");
	}
	if($tempswe){
		echo 'deleted';
	}else{
		echo 'error';
	}
	exit;
}
add_action('wp_ajax_appip-cache-del', 'appip_delete_cache_ajax');

/**
 * Delete All product Cache Files.
 * Delete all cache files on options update, so nothing is cached with old variables.
 *
 * @since 3.6.2
 * @global $wpdb
 * @param string	$reason	allowed value is 'option-update'
 */
function amazon_product_delete_all_cache( $reason = '' ){
	if($reason == 'option-update'){
		global $wpdb;
		$tempswe = $wpdb->query("DELETE FROM {$wpdb->prefix}amazoncache;");
	}
}

function add_appip_jquery(){
	wp_register_script('appip-amazonlightbox', plugins_url('/js/amazon-lightbox.js',dirname(__FILE__)));
	wp_enqueue_script('jquery'); 
	wp_enqueue_script('appip-amazonlightbox'); 
	if(!is_admin()){
		wp_enqueue_style( 'amazon-plugin-frontend-styles',plugins_url('/css/amazon-frontend.css',dirname(__FILE__)),null,'13-08-24');
		wp_enqueue_script('amazon-plugin-frontend-script',plugins_url('/js/amazon-frontend.js',dirname(__FILE__)),array('jquery-ui-tooltip'),'15-07-11');
	}
}