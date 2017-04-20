<?php
if(!function_exists('appip_plugin_aws_hash_hmac')){
	function appip_plugin_aws_hash_hmac($algo, $data, $key, $raw_output=False){
		// original hash_hmac code from comment by Ulrich in http://mierendo.com/software/aws_signed_query/
		// RFC 2104 HMAC implementation for php. Creates a HMAC.
		// Eliminates the need to install mhash to compute a HMAC. Hacked by Lance Rushing. source: http://www.php.net/manual/en/function.mhash.php. modified by Ulrich Mierendorff
		$b = 64; // block size
		if (strlen($key) > $b){
			$key = pack("H*",$algo($key));
		}
		$key 	= str_pad($key, $b, chr(0x00));
		$ipad 	= str_pad('', $b, chr(0x36));
		$opad 	= str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad ;
		$k_opad = $key ^ $opad;
		$hmac 	= $algo($k_opad . pack("H*", $algo($k_ipad . $data)));
		if ($raw_output){
			return pack("H*", $hmac);
		}else{
			return $hmac;
		}
	} 
}

function XMLToArrayFlat($xml, &$return, $path='', $root=false){ 
    $children = array(); 
    if ($xml instanceof SimpleXMLElement) { 
        $children = $xml->children(); 
        if ($root){
            $path .= $xml->getName(); 
        } 
    } 
    if ( count($children) == 0 ){ 
        $return[$path] = (string)$xml; 
        return; 
    } 
    $seen	= array(); 
    foreach ($children as $child => $value) { 
        $childname = ($child instanceof SimpleXMLElement) ? $child->getName() : $child; 
        if ( !isset($seen[$childname])){ 
            $seen[$childname] = 0; 
        } 
        $seen[$childname]++; 
        XMLToArrayFlat($value, $return, $path.'_'.$child.'_'.$seen[$childname]); 
    } 
    return true;
} 

if(!function_exists('appip_plugin_GetChildren')){
	function appip_plugin_GetChildren ($vals, &$i, $type){
		if ($type == 'complete') {
			if (isset ($vals [$i]['value']))
				return ($vals [$i]['value']);
			else
				return '';
		}
		$children = array (); // Contains node data
		/* Loop through children */
		while ($vals [++$i]['type'] != 'close') {
			$type = $vals [$i]['type'];
			// first check if we already have one and need to create an array
			if (isset ($children [$vals [$i]['tag']])) {
				if (is_array ($children [$vals [$i]['tag']])) {
					$temp = array_keys ($children [$vals [$i]['tag']]);
					// there is one of these things already and it is itself an array
					if (is_string ($temp [0])) {
						$a = $children [$vals [$i]['tag']];
						unset ($children [$vals [$i]['tag']]);
						$children [$vals [$i]['tag']][0] = $a;
					}
				} else {
					$a = $children [$vals [$i]['tag']];
					unset ($children [$vals [$i]['tag']]);
					$children [$vals [$i]['tag']][0] = $a;
				}
				$children [$vals [$i]['tag']][] = appip_plugin_GetChildren ($vals, $i, $type);
			} else
				$children [$vals [$i]['tag']] = appip_plugin_GetChildren ($vals, $i, $type);
			// I don't think I need attributes but this is how I would do them:
			if (isset ($vals [$i]['attributes'])) {
				$attributes = array ();
				foreach (array_keys ($vals [$i]['attributes']) as $attkey)
				$attributes [$attkey] = $vals [$i]['attributes'][$attkey];
				// now check: do we already have an array or a value?
				if (isset ($children [$vals [$i]['tag']])) {
					// case where there is an attribute but no value, a complete with an attribute in other words
					if ($children [$vals [$i]['tag']] == '') {
						unset ($children [$vals [$i]['tag']]);
						$children [$vals [$i]['tag']] = $attributes;
					}
					// case where there is an array of identical items with attributes
					elseif (is_array ($children [$vals [$i]['tag']])) {
						$index = count ($children [$vals [$i]['tag']]) - 1;
						// probably also have to check here whether the individual item is also an array or not or what... all a bit messy
						if ($children [$vals [$i]['tag']][$index] == '') {
							unset ($children [$vals [$i]['tag']][$index]);
							$children [$vals [$i]['tag']][$index] = $attributes;
						}
						if(!is_array($children [$vals [$i]['tag']][$index])){
							$children [$vals [$i]['tag']][$index] = $attributes;
						}else{
							$children [$vals [$i]['tag']][$index] = array_merge ($children [$vals [$i]['tag']][$index], $attributes);
						}
					} else {
						$value = $children [$vals [$i]['tag']];
						unset ($children [$vals [$i]['tag']]);
						$children [$vals [$i]['tag']]['value'] = $value;
						$children [$vals [$i]['tag']] = array_merge ($children [$vals [$i]['tag']], $attributes);
					}
				} else
					$children [$vals [$i]['tag']] = $attributes;
			}
		}
	
		return $children;
	}
}

function FormatASINResult($Result,$cResult = 0){
	return appip_plugin_FormatASINResult($Result,$cResult);
}

if(!function_exists('appip_plugin_FormatASINResult')){
	//main function for single product created by Don Fischer http://www.fischercreativemedia.com
	function appip_plugin_FormatASINResult($Result, $cResult = 0){
		$requestType = isset($Result['RequestType']) && $Result['RequestType'] != '' ? 1 : 0; 
		if( isset( $Result['ItemSearchResponse'] ) && is_array( $Result['ItemSearchResponse'] ) && !empty( $Result['ItemSearchResponse'] ) )
			$requestType = 2;
		if($requestType == 1){
			$Item = isset($Result['Items']['Item']) ? $Result['Items']['Item'] : false;
			$cache = isset($Result['CachedAPPIP']) ? $Result['CachedAPPIP'] : 0 ;
		}elseif($requestType == 2){
			$Item 	= isset($Result['ItemSearchResponse']['Items']['Item']) ? $Result['ItemSearchResponse']['Items']['Item'] : false;
			$cache 	= isset($Result['CachedAPPIP']) ? $Result['CachedAPPIP'] : 0 ;
			$errors = array();
			
			if(isset($Result['ItemSearchErrorResponse']['Error']['Code'])){
				$errors[$Result['ItemSearchResponse']['Error']['Code']] = $Result['ItemSearchResponse']['Error']['Code'].":\n".$Result['ItemSearchResponse']['Error']['Message'];
			}elseif(isset($Result['ItemSearchErrorResponse']['Errors'][0])){
				foreach($Result['ItemSearchErrorResponse']['Error'] as $temperr){
					$errors[$temperr['Code']] = $temperr['Code'].":\n".$temperr['Message'];
				}
			}elseif( isset( $Result['ItemSearchResponse']['Items']['Request']['Errors']['Error'] ) ){
				if( !empty( $Result['ItemSearchResponse']['Items']['Request']['Errors']['Error'] ) ){
					foreach( $Result['ItemSearchResponse']['Items']['Request']['Errors']['Error'] as $error){
						$errors[$error['Code']] = $error['Code'].":\n" . $error['Message'];
					}
				}
			}
			if(!empty($errors)){
				$newErr = implode( "\n", $errors );
			}
			if($Item !== false && count( $Item ) > 0 ){
				$Itema = $Item;
				if(is_array($Itema) && !empty($Itema)){
					foreach($Itema as $Item){
						$Item['CachedAPPIP'] = $cache;
						if($cResult === 1){
							$RetValNew[] = (object) appip_blowoffarr($Item);
						}else{
							$RetValNew[] = GetAPPIPReturnValArray($Item,$newErr);
						}
					}
				}
			}elseif($Item !== false ){
				$Item['CachedAPPIP'] = $cache;
				if($cResult === 1){
					$RetValNew[] = (object) appip_blowoffarr($Item);
				}else{
					$RetValNew[] = GetAPPIPReturnValArray($Item,$newErr);
				}
			}else{
				$RetValNew[] = array('Error'=> "{$newErr}",'NoData' => 1);
			}

		}else{
			$Item 	= isset($Result['ItemLookupResponse']['Items']['Item']) ? $Result['ItemLookupResponse']['Items']['Item'] : false;
			$cache 	= isset($Result['CachedAPPIP']) ? $Result['CachedAPPIP'] : 0 ;
			$errors = array();
			
			/*
			if(isset($Result['ItemLookupErrorResponse']['Error']['Code'])){
				$errors[$Result['ItemLookupErrorResponse']['Error']['Code']] = $Result['ItemLookupErrorResponse']['Error']['Code'].": ".$Result['ItemLookupErrorResponse']['Error']['Message'];
			}
			*/
			
			// this is how errors are returned for invalid products
			if(isset($Result['Errors']) && is_array($Result['Errors']) && !empty($Result['Errors']) ){
				foreach($Result['Errors'] as $k => $temperr){
					$errors[$k][$temperr['Code']] =   $temperr['Code'].": ".$temperr['Message'];
				}
			}
			
			// this is also how errors are returned for invalid products
			if(isset($Result['ItemLookupErrorResponse']['Errors']) && is_array($Result['ItemLookupErrorResponse']['Errors']) && !empty($Result['ItemLookupErrorResponse']['Errors']) ){
				foreach($Result['ItemLookupErrorResponse']['Errors'] as $k => $temperr){
					$errors[$k][$temperr['Code']] =   $temperr['Code'].": ".$temperr['Message'];
				}
			}
			
			/*
			if(isset($Result['ItemLookupErrorResponse']['Items']['Request']['Errors']['Error'])){
				if(!empty($Result['ItemLookupErrorResponse']['Items']['Request']['Errors'])){
					foreach($Result['ItemLookupErrorResponse']['Items']['Request']['Errors'] as $error){
						$errors[$error['Code']] = $error['Code'].': '.$error['Message'];
					}
				}
			}
			*/
			if(count($Item) > 0){
				$Itema = $Item;
				if(is_array($Itema) && !empty($Itema)){
					foreach( $Itema as $key => $Item ){
						if(empty($Item)){
							$RetValNew[] = array('Error'=> $errors,'NoData' => 1);
						}else{
							if($cResult === 1){
								$RetValNew[] = (object) appip_blowoffarr($Item);
							}else{
								$RetValNew[] = GetAPPIPReturnValArray((array)$Item, $newErr);
							}
						}
						$Item['CachedAPPIP'] = $cache;
					}
				}

			}elseif($Item != false ){
				$Item['CachedAPPIP'] = $cache;
				if($cResult === 1){
					$RetValNew[] = (object) appip_blowoffarr($Item);
				}else{
					$RetValNew[] = GetAPPIPReturnValArray($Item,$newErr);
				}
			}else{
				$RetValNew[] = array('Error'=> $errors,'NoData' => 1);
			}
		}
		return $RetValNew;  
	}
}

function appip_blowoffarr($Item,$key = "",$blowoffArr = array()){
	$dontuse = apply_filters('amazon_product_in_a_post_blowoffarr_dontuse',array('BrowseNodes','SimilarProducts'));
	foreach($Item as $var => $val){
		if(!in_array($var,$dontuse)){
			if($key==""){
				if(is_array($val)){
					$blowoffArr = appip_blowoffarr2($val,$var,$blowoffArr);
				}else{
					$blowoffArr[$var] = $val;
				}
			}else{
				if(is_array($val)){
					$blowoffArr = appip_blowoffarr2($val,$key.'_'.$var,$blowoffArr);
				}else{
					$blowoffArr[$key.'_'.$var] = $val;
				}
			}
		}
    }
	return $blowoffArr;
}

function appip_blowoffarr2($Item,$key="",$blowoffArr = array()){
	$dontuse = apply_filters('amazon_product_in_a_post_blowoffarr_dontuse',array('BrowseNodes','SimilarProducts'));
	foreach($Item as $var => $val){
		if(!in_array($var,$dontuse)){
			if($key==""){
				if(is_array($val)){
					$blowoffArr = appip_blowoffarr2($val,$var,$blowoffArr);
				}else{
					$blowoffArr[$var] = $val;
				}
			}else{
				if(is_array($val)){
					$blowoffArr = appip_blowoffarr2($val,$key.'_'.$var,$blowoffArr);
				}else{
					$blowoffArr[$key.'_'.$var] = $val;
				}
			}
		}
    }
	return $blowoffArr;
}

function checkImplodeValues($value,$impval=',',$rerun=0){
	if(!empty($value) && is_array($value)){
		$value2 = array();
		foreach($value as $key => $val ){
			if(!empty($val) && is_array($val)){
				$value2[] = checkImplodeValues($val,',',$rerun);
				$rerun++;
			}else{
				$value2[] = $val;
				$rerun = 0;
			}
		}
		if($rerun == 0){
			return implode( "{$impval} ", $value2 );
		}elseif($rerun == 1){
			return implode( "{$impval} ", $value2 );
		}else{
			return implode( "; ", $value2 );
		}
	}else{
		return $value;
	}
}

function appip_setup_nodes($node='',$val = array()){
	$nodearray = array();
	switch($node){
		case 'ItemLinks':
			if(is_array($val['ItemLink']) && !empty($val['ItemLink']) && isset($val['ItemLink'])){
				foreach($val['ItemLink'] as $valtemp){
					$nodearray[] = array('Description'=>$valtemp['Description'],'URL'=>$valtemp['URL']);
				}
			}
			return $nodearray;
			break;
		case 'ImageSets':
			if(isset($val['ImageSet']) && !empty($val['ImageSet']) && isset($val['ImageSet'])){
				if(is_array($val['ImageSet'])){
					if(isset($val['ImageSet'][0])){// has more than one set
						$nodearray = $val['ImageSet'];
					}else{//only one set
						$nodearray[0] = $val['ImageSet'];
					}
				}
			}
			return $nodearray;
			break;
		case 'ItemAttributes':
			return $val;
			break;
		case 'OfferSummary':
			return $val;
			break;
		case 'Offers':
			return $val;
			break;
		case '':
		default:
			return $val;
			break;
	}	
}

function get_appipCurrCode($field=''){
	$allowed = array('USD','GBP');
	if(isset($field) && $field != '' && in_array($field,$allowed)){
		return ' '.$field;
	}	
	return '';
}

function GetAPPIPReturnValArray($Item,$Errors){
 	//processor function for product created by Don Fischer http://www.fischercreativemedia.com
	$ItemAttr 				= isset($Item['ItemAttributes']) ? $Item['ItemAttributes'] : array();
	$ItemOffSum 			= isset($Item['OfferSummary']) ? $Item['OfferSummary'] : array();
	$ItemOffers 			= isset($Item['Offers']) ? $Item['Offers'] : array();
	$ItemAmazOffers			= isset($Item['Offers']) ? $Item['Offers'] : array();
$ItemAmazVarSummary			= isset($Item['VariationSummary']) ? $Item['VariationSummary'] : array();
	$ImageSM				= isset($Item['SmallImage']['URL']) ? $Item['SmallImage']['URL'] : '';
	$ImageMD 				= isset($Item['MediumImage']['URL']) ? $Item['MediumImage']['URL'] : '';
	$ImageLG 				= isset($Item['LargeImage']['URL']) ? $Item['LargeImage']['URL'] : '';
	$ImageSets				= isset($Item['ImageSets']['ImageSet']) ? $Item['ImageSets']['ImageSet'] : '';
	$DetailPageURL 			= isset($Item['DetailPageURL']) ? $Item['DetailPageURL'] : array();
	$ASIN 					= isset($Item['ASIN']) ? $Item['ASIN'] : array();
	$ItemRev				= isset($Item['CustomerReviews']) ? $Item['CustomerReviews'] : array();
	$DescriptionAmz			= isset($Item["EditorialReviews"]["EditorialReview"]) ? $Item["EditorialReviews"]["EditorialReview"] : array();
	$cached					= isset($Item["CachedAPPIP"]) ? $Item["CachedAPPIP"] : 0;

// IMAGES
	if($ImageSM == '' && $ImageSets != ''){
		if(isset($ImageSets[0])){
			$ImageSM = isset($ImageSets[0]['SmallImage']['URL']) ? $ImageSets[0]['SmallImage']['URL'] : '';
		}else{
			$ImageSM = isset($ImageSets['SmallImage']['URL']) ? $ImageSets['SmallImage']['URL'] : '' ;
		}
	}
	if($ImageMD == '' && $ImageSets != ''){
		if(isset($ImageSets[0])){
			$ImageMD = isset($ImageSets[0]['MediumImage']['URL']) ? $ImageSets[0]['MediumImage']['URL'] : '';
		}else{
			$ImageMD = isset($ImageSets['MediumImage']['URL']) ? $ImageSets['MediumImage']['URL'] : '' ;
		}
	}
	if($ImageLG == '' && $ImageSets != ''){
		if(isset($ImageSets[0])){
			$ImageLG = isset($ImageSets[0]['LargeImage']['URL']) ? $ImageSets[0]['LargeImage']['URL'] : '';
		}else{
			$ImageLG = isset($ImageSets['LargeImage']['URL']) ? $ImageSets['LargeImage']['URL'] : '' ;
		}
	}
	
// REVIEWS
	$appHasReviews 								= isset($ItemRev['HasReviews']) ? isset($ItemRev['HasReviews']) : 'false';
	$appCustomerReviews 						= $appHasReviews == 'true' ? $ItemRev['IFrameURL'] : '';

//ITEM ATTRIBS
	$appActor 									= isset($ItemAttr['Actor']) ? ( is_array( $ItemAttr["Actor"] ) ? checkImplodeValues( $ItemAttr["Actor"] ) : $ItemAttr["Actor"] ) : '';
	$appArtist 									= isset($ItemAttr['Artist']) ? ( is_array( $ItemAttr["Artist"] ) ? checkImplodeValues( $ItemAttr["Artist"] ) : $ItemAttr["Artist"] ) : '';
	$appAspectRatio 							= isset($ItemAttr['AspectRatio']) ? ( is_array( $ItemAttr["AspectRatio"] ) ? checkImplodeValues( $ItemAttr["AspectRatio"] ) : $ItemAttr["AspectRatio"] ) : '';
	$appAudienceRating 							= isset($ItemAttr['AudienceRating']) ? ( is_array( $ItemAttr["AudienceRating"] ) ? checkImplodeValues( $ItemAttr["AudienceRating"] ) : $ItemAttr["AudienceRating"] ) : '';
	$appAudioFormat 							= isset($ItemAttr['AudioFormat']) ? ( is_array( $ItemAttr["AudioFormat"] ) ? checkImplodeValues( $ItemAttr["AudioFormat"] ) : $ItemAttr["AudioFormat"] ) : '';
	$appAuthor 									= isset($ItemAttr['Author']) ? ( is_array( $ItemAttr["Author"] ) ? checkImplodeValues( $ItemAttr["Author"] ) : $ItemAttr["Author"] ) : '';
	$appBinding 								= isset($ItemAttr['Binding']) ? ( is_array( $ItemAttr["Binding"] ) ? checkImplodeValues( $ItemAttr["Binding"] ) : $ItemAttr["Binding"] ) : '';
	$appBrand 									= isset($ItemAttr['Brand']) ? ( is_array( $ItemAttr["Brand"] ) ? checkImplodeValues( $ItemAttr["Brand"] ) : $ItemAttr["Brand"] ) : '';
	$appCatalogNumberList 						= isset($ItemAttr['CatalogNumberList']['CatalogNumberListElement']) ? (( is_array( $ItemAttr['CatalogNumberList']['CatalogNumberListElement'] ) && !empty($ItemAttr['CatalogNumberList']['CatalogNumberListElement']))? checkImplodeValues( $ItemAttr['CatalogNumberList']['CatalogNumberListElement'] ) : $ItemAttr['CatalogNumberList']['CatalogNumberListElement'] ) : '';
	$appCategory 								= isset($ItemAttr['Category']) ? ( is_array( $ItemAttr["Category"] ) ? checkImplodeValues( $ItemAttr["Category"] ) : $ItemAttr["Category"] ) : '';
	$appCEROAgeRating 							= isset($ItemAttr['CEROAgeRating']) ? ( is_array( $ItemAttr["CEROAgeRating"] ) ? checkImplodeValues( $ItemAttr["CEROAgeRating"] ) : $ItemAttr["CEROAgeRating"] ) : '';
	$appClothingSize 							= isset($ItemAttr['ClothingSize']) ? ( is_array( $ItemAttr["ClothingSize"] ) ? checkImplodeValues( $ItemAttr["ClothingSize"] ) : $ItemAttr["ClothingSize"] ) : '';
	$appColor 									= isset($ItemAttr['Color']) ? ( is_array( $ItemAttr["Color"] ) ? checkImplodeValues( $ItemAttr["Color"] ) : $ItemAttr["Color"] ) : '';
	$appCreator 								= isset($ItemAttr['Creator']) ? ( is_array( $ItemAttr["Creator"] ) ? checkImplodeValues($ItemAttr["Creator"]) : $ItemAttr["Creator"] ) : '';
	$appDepartment 								= isset($ItemAttr['Department']) ? ( is_array( $ItemAttr["Department"] ) ? checkImplodeValues( $ItemAttr["Department"] ) : $ItemAttr["Department"] ) : '';
	$appDirector 								= isset($ItemAttr['Director']) ? ( is_array( $ItemAttr["Director"] ) ? checkImplodeValues( $ItemAttr["Director"] ) : $ItemAttr["Director"] ) : '';
	$appEAN 									= isset($ItemAttr['EAN']) ? ( is_array( $ItemAttr["EAN"] ) ? checkImplodeValues( $ItemAttr["EAN"] ) : $ItemAttr["EAN"] ) : '';
	$appEANList									= isset($ItemAttr['EANList']['EANListElement']) ? ( is_array( $ItemAttr['EANList']['EANListElement'] ) ? checkImplodeValues( $ItemAttr['EANList']['EANListElement'] ) : $ItemAttr['EANList']['EANListElement'] ) : '';
	$appEdition 								= isset($ItemAttr['Edition']) ? ( is_array( $ItemAttr["Edition"] ) ? checkImplodeValues( $ItemAttr["Edition"] ) : $ItemAttr["Edition"] ) : '';
	$appEISBN 									= isset($ItemAttr['EISBN']) ? ( is_array( $ItemAttr["EISBN"] ) ? checkImplodeValues( $ItemAttr["EISBN"] ) : $ItemAttr["EISBN"] ) : '';
	$appEpisodeSequence 						= isset($ItemAttr['EpisodeSequence']) ? ( is_array( $ItemAttr["EpisodeSequence"] ) ? checkImplodeValues( $ItemAttr["EpisodeSequence"] ) : $ItemAttr["EpisodeSequence"] ) : '';
	$appESRBAgeRating 							= isset($ItemAttr['ESRBAgeRating']) ? ( is_array( $ItemAttr["ESRBAgeRating"] ) ? checkImplodeValues( $ItemAttr["ESRBAgeRating"] ) : $ItemAttr["ESRBAgeRating"] ) : '';
	$appFeature 								= isset($ItemAttr['Feature']) ? ( is_array( $ItemAttr["Feature"] ) ? checkImplodeValues( $ItemAttr["Feature"] ) : $ItemAttr["Feature"] ) : '';
	$appFormat 									= isset($ItemAttr['Format']) ? ( is_array( $ItemAttr["Format"] ) ? checkImplodeValues( $ItemAttr["Format"] ) : $ItemAttr["Format"] ) : '';
	$appGenre 									= isset($ItemAttr['Genre']) ? ( is_array( $ItemAttr["Genre"] ) ? checkImplodeValues( $ItemAttr["Genre"] ) : $ItemAttr["Genre"] ) : '';
	$appHardwarePlatform 						= isset($ItemAttr['HardwarePlatform']) ? ( is_array( $ItemAttr["HardwarePlatform"] ) ? checkImplodeValues( $ItemAttr["HardwarePlatform"] ) : $ItemAttr["HardwarePlatform"] ) : '';
	$appHazardousMaterialType 					= isset($ItemAttr['HazardousMaterialType']) ? ( is_array( $ItemAttr["HazardousMaterialType"] ) ? checkImplodeValues( $ItemAttr["HazardousMaterialType"] ) : $ItemAttr["HazardousMaterialType"] ) : '';
	$appIsAdultProduct 							= isset($ItemAttr['IsAdultProduct']) ? ( is_array( $ItemAttr["IsAdultProduct"] ) ? checkImplodeValues( $ItemAttr["IsAdultProduct"] ) : $ItemAttr["IsAdultProduct"] ) : '';
	$appIsAutographed 							= isset($ItemAttr['IsAutographed']) ? ( is_array( $ItemAttr["IsAutographed"] ) ? checkImplodeValues( $ItemAttr["IsAutographed"] ) : $ItemAttr["IsAutographed"] ) : '';
	$appISBN 									= isset($ItemAttr['ISBN']) ? ( is_array( $ItemAttr["ISBN"] ) ? checkImplodeValues( $ItemAttr["ISBN"] ) : $ItemAttr["ISBN"] ) : '';
	$appIsEligibleForTradeIn 					= isset($ItemAttr['IsEligibleForTradeIn']) ? ( is_array( $ItemAttr["IsEligibleForTradeIn"] ) ? checkImplodeValues( $ItemAttr["IsEligibleForTradeIn"] ) : $ItemAttr["IsEligibleForTradeIn"] ) : '';
	$appIsMemorabilia 							= isset($ItemAttr['IsMemorabilia']) ? ( is_array( $ItemAttr["IsMemorabilia"] ) ? checkImplodeValues( $ItemAttr["IsMemorabilia"] ) : $ItemAttr["IsMemorabilia"] ) : '';
	$appIssuesPerYear 							= isset($ItemAttr['IssuesPerYear']) ? ( is_array( $ItemAttr["IssuesPerYear"] ) ? checkImplodeValues( $ItemAttr["IssuesPerYear"] ) : $ItemAttr["IssuesPerYear"] ) : '';
	$appItemDimensions 							= isset($ItemAttr['ItemDimensions']) ? ( is_array( $ItemAttr["ItemDimensions"] ) ? $ItemAttr["ItemDimensions"] : $ItemAttr["ItemDimensions"] ) : '';
	$appItemPartNumber 							= isset($ItemAttr['ItemPartNumber']) ? ( is_array( $ItemAttr["ItemPartNumber"] ) ? checkImplodeValues( $ItemAttr["ItemPartNumber"] ) : $ItemAttr["ItemPartNumber"] ) : '';
	$appLabel 									= isset($ItemAttr['Label']) ? ( is_array( $ItemAttr["Label"] ) ? checkImplodeValues( $ItemAttr["Label"] ) : $ItemAttr["Label"] ) : '';
	$appLanguages 								= isset($ItemAttr['Languages']["Language"]) ? $ItemAttr["Languages"]["Language"] : '';
	$appLegalDisclaimer 						= isset($ItemAttr['LegalDisclaimer']) ? ( is_array( $ItemAttr["LegalDisclaimer"] ) ? checkImplodeValues( $ItemAttr["LegalDisclaimer"] ) : $ItemAttr["LegalDisclaimer"] ) : '';
	$appListPrice 								= isset($ItemAttr['ListPrice']) ? ( $ItemAttr["ListPrice"]["FormattedPrice"] . get_appipCurrCode($ItemAttr["ListPrice"]["CurrencyCode"] )) : 0;
	$appMagazineType 							= isset($ItemAttr['MagazineType']) ? ( is_array( $ItemAttr["MagazineType"] ) ? checkImplodeValues( $ItemAttr["MagazineType"] ) : $ItemAttr["MagazineType"] ) : '';
	$appManufacturer 							= isset($ItemAttr['Manufacturer']) ? ( is_array( $ItemAttr["Manufacturer"] ) ? checkImplodeValues( $ItemAttr["Manufacturer"] ) : $ItemAttr["Manufacturer"] ) : '';
	$appManufacturerMaximumAge 					= isset($ItemAttr['ManufacturerMaximumAge']) ? ( is_array( $ItemAttr["ManufacturerMaximumAge"] ) ? checkImplodeValues( $ItemAttr["ManufacturerMaximumAge"] ) : $ItemAttr["ManufacturerMaximumAge"] ) : '';
	$appManufacturerMinimumAge 					= isset($ItemAttr['ManufacturerMinimumAge']) ? ( is_array( $ItemAttr["ManufacturerMinimumAge"] ) ? checkImplodeValues( $ItemAttr["ManufacturerMinimumAge"] ) : $ItemAttr["ManufacturerMinimumAge"] ) : '';
	$appManufacturerPartsWarrantyDescription 	= isset($ItemAttr['ManufacturerPartsWarrantyDescription']) ? ( is_array( $ItemAttr["ManufacturerPartsWarrantyDescription"] ) ? checkImplodeValues( $ItemAttr["ManufacturerPartsWarrantyDescription"] ) : $ItemAttr["ManufacturerPartsWarrantyDescription"] ) : '';
	$appMediaType 								= isset($ItemAttr['MediaType']) ? ( is_array( $ItemAttr["MediaType"] ) ? checkImplodeValues( $ItemAttr["MediaType"] ) : $ItemAttr["MediaType"] ) : '';
	$appModel 									= isset($ItemAttr['Model']) ? ( is_array( $ItemAttr["Model"] ) ? checkImplodeValues( $ItemAttr["Model"] ) : $ItemAttr["Model"] ) : '';
	$appModelYear 								= isset($ItemAttr['ModelYear']) ? ( is_array( $ItemAttr["ModelYear"] ) ? checkImplodeValues( $ItemAttr["ModelYear"] ) : $ItemAttr["ModelYear"] ) : '';
	$appMPN										= isset($ItemAttr['MPN']) ? ( is_array( $ItemAttr["MPN"] ) ? checkImplodeValues( $ItemAttr["MPN"] ) : $ItemAttr["MPN"] ) : '';
	$appNumberOfDiscs 							= isset($ItemAttr['NumberOfDiscs']) ? ( is_array( $ItemAttr["NumberOfDiscs"] ) ? checkImplodeValues( $ItemAttr["NumberOfDiscs"] ) : $ItemAttr["NumberOfDiscs"] ) : '';
	$appNumberOfIssues 							= isset($ItemAttr['NumberOfIssues']) ? ( is_array( $ItemAttr["NumberOfIssues"] ) ? checkImplodeValues( $ItemAttr["NumberOfIssues"] ) : $ItemAttr["NumberOfIssues"] ) : '';
	$appNumberOfItems 							= isset($ItemAttr['NumberOfItems']) ? ( is_array( $ItemAttr["NumberOfItems"] ) ? checkImplodeValues( $ItemAttr["NumberOfItems"] ) : $ItemAttr["NumberOfItems"] ) : '';
	$appNumberOfPages 							= isset($ItemAttr['NumberOfPages']) ? ( is_array( $ItemAttr["NumberOfPages"] ) ? checkImplodeValues( $ItemAttr["NumberOfPages"] ) : $ItemAttr["NumberOfPages"] ) : '';
	$appNumberOfTracks 							= isset($ItemAttr['NumberOfTracks']) ? ( is_array( $ItemAttr["NumberOfTracks"] ) ? checkImplodeValues( $ItemAttr["NumberOfTracks"] ) : $ItemAttr["NumberOfTracks"] ) : '';
	$appOperatingSystem 						= isset($ItemAttr['OperatingSystem']) ? ( is_array( $ItemAttr["OperatingSystem"] ) ? checkImplodeValues( $ItemAttr["OperatingSystem"] ) : $ItemAttr["OperatingSystem"] ) : '';
	$appPackageDimensions 						= isset($ItemAttr['PackageDimensions']) ? ( is_array( $ItemAttr["PackageDimensions"] ) ? $ItemAttr["PackageDimensions"] : $ItemAttr["PackageDimensions"] ) : '';
	$appPackageDimensionsWidth 					= isset($ItemAttr['PackageDimensions']['Width']) ?  is_array( $ItemAttr["PackageDimensions"]['Width']) ? strpos($ItemAttr["PackageDimensions"]['Width']['Units'],'hundredths-') !== false ? ( $ItemAttr["PackageDimensions"]['Width']['value'] / 100 ) .' '. str_replace( 'hundredths-','', $ItemAttr["PackageDimensions"]['Width']['Units'] ) : $ItemAttr["PackageDimensions"]['Width']['value'] .' '. $ItemAttr["PackageDimensions"]['Width']['Units'] : '' : '';
	$appPackageDimensionsHeight 				= isset($ItemAttr['PackageDimensions']['Height']) ?  is_array( $ItemAttr["PackageDimensions"]['Height']) ? strpos($ItemAttr["PackageDimensions"]['Height']['Units'],'hundredths-') !== false ? ( $ItemAttr["PackageDimensions"]['Height']['value'] / 100 ) .' '. str_replace( 'hundredths-','', $ItemAttr["PackageDimensions"]['Height']['Units'] ) : $ItemAttr["PackageDimensions"]['Height']['value'] .' '. $ItemAttr["PackageDimensions"]['Height']['Units'] : '' : '';
	$appPackageDimensionsLength 				= isset($ItemAttr['PackageDimensions']['Length']) ?  is_array( $ItemAttr["PackageDimensions"]['Length']) ? strpos($ItemAttr["PackageDimensions"]['Length']['Units'],'hundredths-') !== false ? ( $ItemAttr["PackageDimensions"]['Length']['value'] / 100 ) .' '. str_replace( 'hundredths-','', $ItemAttr["PackageDimensions"]['Length']['Units'] ) : $ItemAttr["PackageDimensions"]['Length']['value'] .' '. $ItemAttr["PackageDimensions"]['Length']['Units'] : '' : '';
	$appPackageDimensionsWeight					= isset($ItemAttr['PackageDimensions']['Weight']) ?  is_array( $ItemAttr["PackageDimensions"]['Weight']) ? strpos($ItemAttr["PackageDimensions"]['Weight']['Units'],'hundredths-') !== false ? ( $ItemAttr["PackageDimensions"]['Weight']['value'] / 100 ) .' '. str_replace( 'hundredths-','', $ItemAttr["PackageDimensions"]['Weight']['Units'] ) : $ItemAttr["PackageDimensions"]['Weight']['value'] .' '. $ItemAttr["PackageDimensions"]['Weight']['Units'] : '' : '';
	$appPackageQuantity 						= isset($ItemAttr['PackageQuantity']) ? ( is_array( $ItemAttr["PackageQuantity"] ) ? checkImplodeValues( $ItemAttr["PackageQuantity"] ) : $ItemAttr["PackageQuantity"] ) : '';
	$appPartNumber 								= isset($ItemAttr['PartNumber']) ? ( is_array( $ItemAttr["PartNumber"] ) ? checkImplodeValues( $ItemAttr["PartNumber"] ) : $ItemAttr["PartNumber"] ) : '';
	$appPictureFormat 							= isset($ItemAttr['PictureFormat']) ? ( is_array( $ItemAttr["PictureFormat"] ) ? checkImplodeValues( $ItemAttr["PictureFormat"] ) : $ItemAttr["PictureFormat"] ) : '';
	$appPlatform 								= isset($ItemAttr['Platform']) ? ( is_array( $ItemAttr["Platform"] ) ? checkImplodeValues( $ItemAttr["Platform"] ) : $ItemAttr["Platform"] ) : '';
	$appProductGroup 							= isset($ItemAttr['ProductGroup']) ? ( is_array( $ItemAttr["ProductGroup"] ) ? checkImplodeValues( $ItemAttr["ProductGroup"] ) : $ItemAttr["ProductGroup"] ) : '';
	$appProductTypeName 						= isset($ItemAttr['ProductTypeName']) ? ( is_array( $ItemAttr["ProductTypeName"] ) ? checkImplodeValues( $ItemAttr["ProductTypeName"] ) : $ItemAttr["ProductTypeName"] ) : '';
	$appProductTypeSubcategory 					= isset($ItemAttr['ProductTypeSubcategory']) ? ( is_array( $ItemAttr["ProductTypeSubcategory"] ) ? checkImplodeValues( $ItemAttr["ProductTypeSubcategory"] ) : $ItemAttr["ProductTypeSubcategory"] ) : '';
	$appPublicationDate 						= isset($ItemAttr['PublicationDate']) ? ( is_array( $ItemAttr["PublicationDate"] ) ? checkImplodeValues( $ItemAttr["PublicationDate"] ) : $ItemAttr["PublicationDate"] ) : '';
	$appPublisher 								= isset($ItemAttr['Publisher']) ? ( is_array( $ItemAttr["Publisher"] ) ? checkImplodeValues( $ItemAttr["Publisher"] ) : $ItemAttr["Publisher"] ) : '';
	$appRating 									= isset($ItemAttr['Rating']) ? ( is_array( $ItemAttr["Rating"] ) ? checkImplodeValues( $ItemAttr["Rating"] ) : $ItemAttr["Rating"] ) : '';
	$appRegionCode 								= isset($ItemAttr['RegionCode']) ? ( is_array( $ItemAttr["RegionCode"] ) ? checkImplodeValues( $ItemAttr["RegionCode"] ) : $ItemAttr["RegionCode"] ) : '';
	$appReleaseDate 							= isset($ItemAttr['ReleaseDate']) ? ( is_array( $ItemAttr["ReleaseDate"] ) ? checkImplodeValues( $ItemAttr["ReleaseDate"] ) : $ItemAttr["ReleaseDate"] ) : '';
	$appRunningTime 							= isset($ItemAttr['RunningTime']) ? ( is_array( $ItemAttr["RunningTime"] ) ? implode( " ", $ItemAttr["RunningTime"] ) : $ItemAttr["RunningTime"] ) : '';
	$appSeikodoProductCode 						= isset($ItemAttr['SeikodoProductCode']) ? ( is_array( $ItemAttr["SeikodoProductCode"] ) ? checkImplodeValues( $ItemAttr["SeikodoProductCode"] ) : $ItemAttr["SeikodoProductCode"] ) : '';
	$appShoeSize 								= isset($ItemAttr['ShoeSize']) ? ( is_array( $ItemAttr["ShoeSize"] ) ? checkImplodeValues( $ItemAttr["ShoeSize"] ) : $ItemAttr["ShoeSize"] ) : '';
	$appSize 									= isset($ItemAttr['Size']) ? ( is_array( $ItemAttr["Size"] ) ? checkImplodeValues( $ItemAttr["Size"] ) : $ItemAttr["Size"] ) : '';
	$appSKU 									= isset($ItemAttr['SKU']) ? ( is_array( $ItemAttr["SKU"] ) ? checkImplodeValues( $ItemAttr["SKU"] ) : $ItemAttr["SKU"] ) : '';
	$appStudio 									= isset($ItemAttr['Studio']) ? ( is_array( $ItemAttr["Studio"] ) ? checkImplodeValues( $ItemAttr["Studio"] ) : $ItemAttr["Studio"] ) : '';
	$appSubscriptionLength 						= isset($ItemAttr['SubscriptionLength']) ? ( is_array( $ItemAttr["SubscriptionLength"] ) ? checkImplodeValues( $ItemAttr["SubscriptionLength"] ) : $ItemAttr["SubscriptionLength"] ) : '';
	$appTitle 									= isset($ItemAttr['Title']) ? ( is_array( $ItemAttr["Title"] ) ? checkImplodeValues( $ItemAttr["Title"] ) : $ItemAttr["Title"] ) : '';
	$appTrackSequence 							= isset($ItemAttr['TrackSequence']) ? ( is_array( $ItemAttr["TrackSequence"] ) ? checkImplodeValues( $ItemAttr["TrackSequence"] ) : $ItemAttr["TrackSequence"] ) : '';
	$appTradeInValue 							= isset($ItemAttr['TradeInValue']) ? ( is_array( $ItemAttr["TradeInValue"] ) ? checkImplodeValues( $ItemAttr["TradeInValue"] ) : $ItemAttr["TradeInValue"] ) : '';
	$appUPC 									= isset($ItemAttr['UPC']) ? ( is_array( $ItemAttr["UPC"] ) ? checkImplodeValues( $ItemAttr["UPC"] ) : $ItemAttr["UPC"] ) : '';
	$appUPCList 								= isset($ItemAttr['UPCList']['UPCListElement']) ? ( is_array( $ItemAttr["UPCList"]['UPCListElement'] ) ? checkImplodeValues( $ItemAttr["UPCList"]['UPCListElement'] ) : $ItemAttr["UPCList"]['UPCListElement'] ) : '';
	$appWarranty 								= isset($ItemAttr['Warranty']) ? ( is_array( $ItemAttr["Warranty"] ) ? checkImplodeValues( $ItemAttr["Warranty"] ) : $ItemAttr["Warranty"] ) : '';
	$appWEEETaxValue  							= isset($ItemAttr['WEEETaxValue ']) ? ( is_array( $ItemAttr["WEEETaxValue "] ) ? checkImplodeValues( $ItemAttr["WEEETaxValue "] ) : $ItemAttr["WEEETaxValue "] ) : '';

 //OFFER SUMMARY
	$appTotalNew 								= isset($ItemOffSum['TotalNew']) ? ( is_array( $ItemOffSum["TotalNew"] ) ? checkImplodeValues( $ItemOffSum["TotalNew"] ) : $ItemOffSum["TotalNew"] ) : '';
	$appTotalUsed 								= isset($ItemOffSum['TotalUsed']) ? ( is_array( $ItemOffSum["TotalUsed"] ) ? checkImplodeValues( $ItemOffSum["TotalUsed"] ) : $ItemOffSum["TotalUsed"] ) : '';
	$appTotalRefurbished 						= isset($ItemOffSum['TotalRefurbished']) ? ( is_array( $ItemOffSum["TotalRefurbished"] ) ? checkImplodeValues( $ItemOffSum["TotalRefurbished"] ) : $ItemOffSum["TotalRefurbished"] ) : '';
	$appTotalCollectible 						= isset($ItemOffSum['TotalCollectible']) ? ( is_array( $ItemOffSum["TotalCollectible"] ) ? checkImplodeValues( $ItemOffSum["TotalCollectible"] ) : $ItemOffSum["TotalCollectible"] ) : '';
	$appLowestNewCurrCode 						= isset($ItemOffSum["LowestNewPrice"]["CurrencyCode"]) ? get_appipCurrCode($ItemOffSum["LowestNewPrice"]["CurrencyCode"]) : '';
	$appLowestNewPrice 							= isset($ItemOffSum['LowestNewPrice']['FormattedPrice']) ?  $ItemOffSum["LowestNewPrice"]['FormattedPrice'] .$appLowestNewCurrCode: 0;
	$appLowestUsedCurrCode 						= isset($ItemOffSum["LowestUsedPrice"]["CurrencyCode"]) ? get_appipCurrCode($ItemOffSum["LowestUsedPrice"]["CurrencyCode"]) : '';
	$appLowestUsedPrice							= isset($ItemOffSum['LowestUsedPrice']['FormattedPrice']) ?  $ItemOffSum["LowestUsedPrice"]['FormattedPrice'] .$appLowestUsedCurrCode : 0;
	$appLowestRefCurrCode 						= isset($ItemOffSum["LowestRefurbishedPrice"]["CurrencyCode"]) ? get_appipCurrCode($ItemOffSum["LowestRefurbishedPrice"]["CurrencyCode"]) : '';
	$appLowestRefurbishedPrice 					= isset($ItemOffSum['LowestRefurbishedPrice']['FormattedPrice']) ?  $ItemOffSum["LowestRefurbishedPrice"]['FormattedPrice'] .$appLowestRefCurrCode : 0;
	$appLowestCollCurrCode 						= isset($ItemOffSum["LowestCollectiblePrice"]["CurrencyCode"]) ? get_appipCurrCode($ItemOffSum["LowestCollectiblePrice"]["CurrencyCode"]) : '';
	$appLowestCollectiblePrice 					= isset($ItemOffSum['LowestCollectiblePrice']['FormattedPrice']) ?  $ItemOffSum["LowestCollectiblePrice"]['FormattedPrice'] .$appLowestCollCurrCode : 0;

// VARIATION SUMMARY
	$appvLowestPrice		= isset($ItemAmazVarSummary['LowestPrice']) ? $ItemAmazVarSummary['LowestPrice']['FormattedPrice'] : '';
	$appvHighestPrice	 	= isset($ItemAmazVarSummary['HighestPrice']) ? $ItemAmazVarSummary['HighestPrice']['FormattedPrice'] : '';
	$appvLowestSalePrice	= isset($ItemAmazVarSummary['LowestSalePrice']) ? $ItemAmazVarSummary['LowestSalePrice']['FormattedPrice'] : '';
	$appvHighestSalePrice 	= isset($ItemAmazVarSummary['HighestSalePrice']) ? $ItemAmazVarSummary['HighestSalePrice']['FormattedPrice'] : '';

 //OFFERS
	$appTotalOffers 							= isset($ItemOffers['TotalOffers']) ? ( is_array( $ItemOffers["TotalOffers"] ) ? checkImplodeValues( $ItemOffers["TotalOffers"] ) : $ItemOffers["TotalOffers"] ) : '';
	$appMoreOffersUrl 							= isset($ItemOffers['MoreOffersUrl']) ? $ItemOffers["MoreOffersUrl"]  : '';		
	$appTotalOfferPages							= isset($ItemOffers['TotalOfferPages']) ? ( is_array( $ItemOffers["TotalOfferPages"] ) ? checkImplodeValues( $ItemOffers["TotalOfferPages"] ) : $ItemOffers["TotalOfferPages"] ) : '';
	$isPriceHidden 								= ($appLowestNewPrice == 'Too low to display') ? 1 : 0;
	$newAmzPricing 								= array();
	if(!isset($ItemAmazOffers['Offers'][0])){
		$ItemAmazOfftemp = isset($ItemAmazOffers['Offer']) ? $ItemAmazOffers['Offer'] :'';
		unset($ItemAmazOffers['Offer']);
		$ItemAmazOffers['Offer'][0] = $ItemAmazOfftemp; 
	}
	if(isset($ItemAmazOffers['Offer']) && is_array($ItemAmazOffers['Offer']) && !empty($ItemAmazOffers['Offer'])){
		foreach($ItemAmazOffers['Offer'] as $amzOffers){
			if(isset($amzOffers['OfferAttributes'])){
				if(isset($amzOffers['OfferListing']['Price']['FormattedPrice']) && $amzOffers['OfferListing']['Price']['FormattedPrice'] == '0 Out of Stock'){$amzOffers['OfferListing']['Price']['FormattedPrice'] = 'Out of Stock';}
				if(isset($amzOffers['OfferListing']['AmountSaved']['FormattedPrice']) && $amzOffers['OfferListing']['AmountSaved']['FormattedPrice'] =='0'){$amzOffers['OfferListing']['AmountSaved']['FormattedPrice'] = '';}
				$atype 									= (isset($amzOffers['OfferAttributes']['Condition']) ? $amzOffers['OfferAttributes']['Condition'] : '');
				$newAmzPricing[$atype]['List'] 			=  $appListPrice;
				$newAmzPricing[$atype]['Price'] 		=  (isset($amzOffers['OfferListing']['Price']['FormattedPrice']) && isset($amzOffers['OfferListing']['Price']['CurrencyCode']) ? $amzOffers['OfferListing']['Price']['FormattedPrice'] . ' ' . $amzOffers['OfferListing']['Price']['CurrencyCode'] : '');
				$newAmzPricing[$atype]['Saved'] 		=  (isset($amzOffers['OfferListing']['AmountSaved']['FormattedPrice']) && isset($amzOffers['OfferListing']['AmountSaved']['CurrencyCode']) ?  $amzOffers['OfferListing']['AmountSaved']['FormattedPrice']. ' ' . $amzOffers['OfferListing']['AmountSaved']['CurrencyCode'] : '');
				$newAmzPricing[$atype]['SavedPercent'] 	=  (isset($amzOffers['OfferListing']['PercentageSaved']) ? $amzOffers['OfferListing']['PercentageSaved'] : '') ;
				$newAmzPricing[$atype]['IsEligibleForSuperSaverShipping'] = (isset($amzOffers['OfferListing']['IsEligibleForSuperSaverShipping']) ? $amzOffers['OfferListing']['IsEligibleForSuperSaverShipping'] : '');
			}
		}
	}
	if($appBinding == "Kindle Edition"){
		/* Set pricing to 0 for Kindle Items (no data returned for Kindle Pricing in API as of 6/12/2013)*/
		$appLowestUsedPrice 		= $newAmzPricing['Used']['Price'] = 0;
		$appLowestRefurbishedPrice 	= $newAmzPricing['Refurbished']['Price'] = 0;
		$appLowestCollectiblePrice 	= $newAmzPricing['Collectible']['Price'] = 0;
		$appTotalCollectible = $appTotalRefurbished = $appTotalUsed = 0;
	}
	if($appTotalNew > 0){ 
		$newAmzPricing['NewFrom']['List'] = $appListPrice;
		if(strpos($appLowestNewPrice,'Too low to display')!== false && isset($newAmzPricing['New']['Price'])){
			$newAmzPricing['NewFrom']['Price'] = 'New from '.$newAmzPricing['New']['Price'];
			$appLowestNewPrice = $newAmzPricing['New']['Price'];
		}else{
			$newAmzPricing['NewFrom']['Price'] = 'New from '.$appLowestNewPrice;
		}
	}
	if($appTotalUsed > 0){ 
		$newAmzPricing['UsedFrom']['List'] = $appListPrice;
		if(strpos($appLowestUsedPrice,'Too low to display')!== false && isset($newAmzPricing['Used']['Price'])){
			$newAmzPricing['UsedFrom']['Price'] = 'Used from '.$newAmzPricing['Used']['Price'];
			$appLowestUsedPrice = $newAmzPricing['Used']['Price'];
		}else{
			$newAmzPricing['UsedFrom']['Price'] = 'Used from '.$appLowestUsedPrice;
		}
	}
	if($appTotalRefurbished > 0){ 
		$newAmzPricing['RefurbishedFrom']['List'] = $appListPrice;
		if($appLowestRefurbishedPrice == 'Too low to display' && isset($newAmzPricing['Refurbished']['Price'])){
			$newAmzPricing['RefurbishedFrom']['Price'] = 'Refurbished from '.$newAmzPricing['Refurbished']['Price'];
			$appLowestRefurbishedPrice = $newAmzPricing['Refurbished']['Price'];
		}else{
			$newAmzPricing['RefurbishedFrom']['Price'] = 'Refurbished from '.$appLowestRefurbishedPrice;
		}
	}
	if($appTotalCollectible > 0){ 
		$newAmzPricing['CollectibleFrom']['List'] = $appListPrice;
		if($appLowestCollectiblePrice == 'Too low to display' && isset($newAmzPricing['Collectible']['Price'])){
			$newAmzPricing['CollectibleFrom']['Price'] = 'Collectible from '. $newAmzPricing['Collectible']['Price'];
			$appLowestCollectiblePrice = $newAmzPricing['Collectible']['Price'];
		}else{
			$newAmzPricing['CollectibleFrom']['Price'] = 'Collectible from '. $appLowestCollectiblePrice;
		}
	}
	if(isset($ItemOffers['OfferListing']['Price'])){
		$SalePrice = $ItemOffers['OfferListing']['Price'];
	}else{
		$SalePrice = isset($ItemOffSum['LowestNewPrice']['Amount']) ? $ItemOffSum['LowestNewPrice']['Amount'] : '';
	}

	$OfferListingId = isset($ItemOffers['OfferListing']['OfferListingId']) ? $ItemOffers['OfferListing']['OfferListingId'] :'';
	if(is_array($appLanguages)){
		$appipLantemp2 = array();
		foreach($appLanguages as $appipLantemp){
			if(isset($appipLantemp["Name"]) && isset($appipLantemp["Type"])){
				$appipLantemp2[] =  $appipLantemp["Name"] .' ('. $appipLantemp["Type"] .')';
			}
		}
		$appLanguages = checkImplodeValues( $appipLantemp2 );
	} 
	
	if(isset($ItemAttr["ListPrice"]["Amount"]) && isset($SalePrice['Amount']) ){
		$SavingsPrice =  number_format(($ItemAttr["ListPrice"]["Amount"]/ 100.0),2) - number_format(($SalePrice['Amount'] / 100.0), 2);
		if($ItemAttr["ListPrice"]["Amount"]!=0){
			$SavingsPercent = ($SavingsPrice / number_format($ItemAttr["ListPrice"]["Amount"]/100,2))*100;
		}else{
			$SavingsPercent = 0;
		}
	}else{
		$SavingsPrice =  0;
		$SavingsPercent = 0;
	}
	global $show_format;
	if($show_format==1){
		$appTitle = ($appBinding != '' ) ? $appTitle .' ('.$appBinding.')' : $appTitle;
	}
	if(isset($DescriptionAmz[0])){
		foreach($DescriptionAmz as $descarr){
			$tmpsrc = (isset($descarr['Source']) ? $descarr['Source'] : '');
			$tmpcon = (isset($descarr['Content']) ? $descarr['Content'] : '');
			$EDescprition[]	= array('Source'=>$tmpsrc,'Content'=>$tmpcon);
		}
	}else{
		$tmpsrc = (isset($DescriptionAmz['Source']) ? $DescriptionAmz['Source'] : '' );
		$tmpcon = (isset($DescriptionAmz['Content']) ? $DescriptionAmz['Content'] : '' );
		$EDescprition[]	= array('Source'=>$tmpsrc,'Content'=>$tmpcon);
	}
	$ImageSetsArray = array();
	if(isset($ImageSets[0])){
		foreach($ImageSets as $imgset){
			if(isset($imgset['LargeImage']['URL']) && $imgset['LargeImage']['URL'] != $ImageLG){
				$ImageSetsArray[] = '<a rel="appiplightbox-'.$ASIN.'" href="'.$imgset['LargeImage']['URL'] .'"><img src="'.$imgset['SmallImage']['URL'].'" class="apipp-additional-image"/></a>'."\n";
			}
		}
	}elseif(isset($ImageSets['SmallImage']['URL'])){
		if(isset($ImageSets['LargeImage']['URL']) && $ImageSets['LargeImage']['URL'] != $ImageLG){
			$ImageSetsArray[] = '<a rel="appiplightbox-'.$ASIN.'" href="'.$ImageSets['LargeImage']['URL'] .'"><img src="'.$ImageSets['SmallImage']['URL'].'" class="apipp-additional-image"/></a>'."\n";
		}
	}

if(isset($appLowestSalePrice) && $appLowestSalePrice	 == '0' && $appvLowestSalePrice	!= ''){$appLowestSalePrice	= $appvLowestSalePrice;}
if($appTotalNew == '0' && ($appvLowestSalePrice!='' || $appvHighestSalePrice !='') ) {$appTotalNew = 'unknown';}
if($appListPrice == '0' && $appvHighestPrice != ''){$appListPrice = $appvHighestPrice;}

    $RetVal = array(
//default items
	'ASIN' 									=> "{$ASIN}",
	'Errors' 								=> "{$Errors}",
    'URL' 									=> "{$DetailPageURL}",
	'CartURL'								=> "https://www.amazon.##REGION##/gp/aws/cart/add.html?AssociateTag=##AFFID##&SubscriptionId=##SUBSCRIBEID##&ASIN.1={$ASIN}&Quantity.1=1",
	'Title' 								=> "{$appTitle}",
	'SmallImage' 							=> "{$ImageSM}",
	'MediumImage' 							=> "{$ImageMD}",
	'LargeImage' 							=> "{$ImageLG}",
	'AddlImages'							=> implode('',$ImageSetsArray),
	'PriceHidden' 							=> "{$isPriceHidden}",
	'CustomerReviews' 						=> "{$appCustomerReviews}",
	
//item attribs
	"Actor" 								=> "{$appActor}",
	"Artist" 								=> "{$appArtist}",
	"AspectRatio" 							=> "{$appAspectRatio}",
	"AudienceRating" 						=> "{$appAudienceRating}",
	"AudioFormat" 							=> "{$appAudioFormat}",
	"Author" 								=> "{$appAuthor}",
	"Binding" 								=> "{$appBinding}",
	"Brand" 								=> "{$appBrand}",
	"CatalogNumberList" 					=> "{$appCatalogNumberList}",
	"Category" 								=> "{$appCategory}",
	"CEROAgeRating" 						=> "{$appCEROAgeRating}",
	"ClothingSize" 							=> "{$appClothingSize}",
	"Color" 								=> "{$appColor}",
	"Creator" 								=> "{$appCreator}",
	"Department" 							=> "{$appDepartment}",
	"Director" 								=> "{$appDirector}",
	"EAN" 									=> "{$appEAN}",
	"EANList" 								=> "{$appEANList}",
	"Edition" 								=> "{$appEdition}",
	"EISBN" 								=> "{$appEISBN}",
	"EpisodeSequence" 						=> "{$appEpisodeSequence}",
	"ESRBAgeRating" 						=> "{$appESRBAgeRating}",
	"Feature" 								=> "{$appFeature}",
	"Format" 								=> "{$appFormat}",
	"Genre" 								=> "{$appGenre}",
	"HardwarePlatform" 						=> "{$appHardwarePlatform}",
	"HazardousMaterialType" 				=> "{$appHazardousMaterialType}",
	"IsAdultProduct" 						=> "{$appIsAdultProduct}",
	"IsAutographed" 						=> "{$appIsAutographed}",
	"ISBN" 									=> "{$appISBN}",
	"IsEligibleForTradeIn" 					=> "{$appIsEligibleForTradeIn}",
	"IsMemorabilia" 						=> "{$appIsMemorabilia}",
	"IssuesPerYear" 						=> "{$appIssuesPerYear}",
	'ItemDesc' 								=> $EDescprition,
	"ItemDimensions" 						=> $appItemDimensions,
	"ItemPartNumber" 						=> "{$appItemPartNumber}",
	"Label" 								=> "{$appLabel}",
	"Languages" 							=> "{$appLanguages}",
	"LegalDisclaimer" 						=> "{$appLegalDisclaimer}",
	"ListPrice" 							=> "{$appListPrice}",
	"MagazineType" 							=> "{$appMagazineType}",
	"Manufacturer" 							=> "{$appManufacturer}",
	"ManufacturerMaximumAge"				=> "{$appManufacturerMaximumAge}",
	"ManufacturerMinimumAge" 				=> "{$appManufacturerMinimumAge}",
	"ManufacturerPartsWarrantyDescription" 	=> "{$appManufacturerPartsWarrantyDescription}",
	"MediaType" 							=> "{$appMediaType}",
	"Model" 								=> "{$appModel}",
	"ModelYear" 							=> "{$appModelYear}",
	"MPN" 									=> "{$appMPN}",
	"NumberOfDiscs" 						=> "{$appNumberOfDiscs}",
	"NumberOfIssues" 						=> "{$appNumberOfIssues}",
	"NumberOfItems" 						=> "{$appNumberOfItems}",
	"NumberOfPages" 						=> "{$appNumberOfPages}",
	"NumberOfTracks" 						=> "{$appNumberOfTracks}",
	"OperatingSystem" 						=> "{$appOperatingSystem}",
	"PackageDimensions"						=> "{$appPackageDimensions}",
	"PackageDimensionsWidth" 				=> "{$appPackageDimensionsWidth}",
	"PackageDimensionsHeight" 				=> "{$appPackageDimensionsHeight}",
	"PackageDimensionsLength" 				=> "{$appPackageDimensionsLength}",
	"PackageDimensionsWeight" 				=> "{$appPackageDimensionsWeight}", 
	"PackageQuantity" 						=> "{$appPackageQuantity}",
	"PartNumber" 							=> "{$appPartNumber}",
	"PictureFormat" 						=> "{$appPictureFormat}",
	"Platform" 								=> "{$appPlatform}",
	"ProductGroup" 							=> "{$appProductGroup}",
	"ProductTypeName" 						=> "{$appProductTypeName}",
	"ProductTypeSubcategory" 				=> "{$appProductTypeSubcategory}",
	"PublicationDate" 						=> "{$appPublicationDate}",
	"Publisher" 							=> "{$appPublisher}",
	"RegionCode" 							=> "{$appRegionCode}",
	"Rating" 								=> "{$appRating}",
	"ReleaseDate" 							=> "{$appReleaseDate}",
	"RunningTime" 							=> "{$appRunningTime}",
	"SeikodoProductCode" 					=> "{$appSeikodoProductCode}",
	"ShoeSize" 								=> "{$appShoeSize}",
	"Size" 									=> "{$appSize}",
	"SKU" 									=> "{$appSKU}",
	"Studio" 								=> "{$appStudio}",
	"SubscriptionLength" 					=> "{$appSubscriptionLength}",
	"TrackSequence" 						=> "{$appTrackSequence}",
	"TradeInValue" 							=> "{$appTradeInValue}",
	"UPC" 									=> "{$appUPC}",
	"UPCList" 								=> "{$appUPCList}",
	"Warranty" 								=> "{$appWarranty}",
	"WEEETaxValue " 						=> "{$appWEEETaxValue}",

// Offers
	"LowestNewPrice" 						=> "{$appLowestNewPrice}",
	"LowestUsedPrice" 						=> "{$appLowestUsedPrice}",
	"LowestRefurbishedPrice" 				=> "{$appLowestRefurbishedPrice}",
	"LowestCollectiblePrice" 				=> "{$appLowestCollectiblePrice}",
	"TotalCollectible" 						=> "{$appTotalCollectible}",
	"TotalNew" 								=> "{$appTotalNew}",
	"TotalOfferPages" 						=> "{$appTotalOfferPages}",
	"TotalOffers" 							=> "{$appTotalOffers}",
	"TotalRefurbished"						=> "{$appTotalRefurbished}",
	"TotalUsed"							 	=> "{$appTotalUsed}",
	"NewAmazonPricing" 						=> $newAmzPricing,
	"TotalAmzOffers" 						=> $appTotalOffers,
	"VarHighestPrice"						=> $appvHighestPrice,
	"VarLowestPrice"						=> $appvLowestPrice,
	"VarLowestSalePrice"					=> $appvLowestSalePrice,	 	
	"VarHighestSalePrice"					=> $appvHighestSalePrice, 	
	"MoreOffersUrl" 						=> $appMoreOffersUrl,
	"TotalOfferPages" 						=> $appTotalOfferPages,
	"CachedAPPIP"							=> $cached,
     );
     return apply_filters('apipp_amazon_product_array_filter',$RetVal,$Item);  
} 
  

function appip_plugin_FormatSearchResult($Result){
	//FormatSearchResult by Don Fischer
	return; //not used at this time
} 

function appip_plugin_check_CURL_FOPEN(){
	global $apip_usefileget;
	global $apip_usecurlget;
	$hasfgc 	= ini_get('allow_url_fopen') && ini_get('allow_url_include') ? true : false;
	$hascurl 	= (function_exists('curl_version')) ? true : false;
	$apip_usefileget = (bool)$apip_usefileget;
	$apip_usecurlget = (bool)$apip_usecurlget;
	$usefgc = $usecurl = '0';
	if($apip_usefileget){ // if setting says use fopen
		if($hasfgc){ // and the server allows
			$usefgc  = '1'; // use fopen
			$usecurl = '0'; // don't use curl
		}elseif($hascurl){ // if server does not have fopen but has curl
			$usefgc  = '0'; // dont use fopen
			$usecurl = '1'; // use curl
		}else{ //otherwise can't use either so it will fail
			$usefgc 	= '0';
			$usecurl 	= '0';
		}
	}elseif($apip_usecurlget){ //if setting is curl
		if($hascurl){ // and the server allows
			$usefgc  = '0'; // dont use fopen
			$usecurl = '1'; // use curl
		}elseif($hasfgc){ // if server does not have curl but has fopen
			$usefgc  = '1'; // use fopen
			$usecurl = '0'; // dont use curl
		}else{ //otherwise can't use either so it will fail
			$usefgc 	= '0';
			$usecurl 	= '0';
		}
	}
	$setis = array('curl'=>$usecurl,'fopen'=>$usefgc);
	return $setis;
}
function aws_signed_request($region, $params, $publickey, $privatekey){
	return amazon_plugin_aws_signed_request($region, $params, $publickey, $privatekey);
}

if(!function_exists('amazon_plugin_aws_signed_request')){
	/*
	original amazon_plugin_aws_signed_request code from http://mierendo.com/software/aws_signed_query/ Copyright (c) 2009 Ulrich Mierendorff
	Parameters:
	    $region - the Amazon(r) region (ca,com,co.uk,de,fr,co.jp,com.br,es)
	    $params - an array of parameters, eg. array("Operation"=>"ItemLookup", "ItemId"=>"B000X9FLKM", "ResponseGroup"=>"Small")
	    $publickey - your "Access Key ID"
	    $privatekey - your "Secret Access Key"
	*/
	function amazon_plugin_aws_signed_request($region = '', $params = array(), $publickey = '', $privatekey = ''){
		global $wpdb,$public_key,$private_key,$aws_partner_locale,$aws_partner_id;
		$cachetime = (int) apply_filters('amazon_product_post_cache',6000);
		if($publickey == '')
			$publickey = $public_key;
		if($privatekey == '')
			$privatekey = $private_key;
		if($region == '')
			$region = $aws_partner_locale;
		if(!isset($params['AssociateTag']))
			$params['AssociateTag'] = $aws_partner_id;
		if(!isset($params['Operation']) || $params['Operation'] == '' )
			$params['Operation'] = 'ItemLookup';
		if(!isset($params['ResponseGroup']))
			$params['ResponseGroup'] = 'Large,Reviews,Offers,Variations';
		if(!isset($params['IdType']))
			$params['IdType'] = 'ASIN';
		$allASINs							= array('requested' => array(),'togetAPI' => array(),'cached' => array(),'needed' => array());	
		$body 								= "";
		$maxage 							= 1;
		$checkReqType						= appip_plugin_check_CURL_FOPEN();
		$usecurl							= $checkReqType['curl'];
		$usefgc								= $checkReqType['fopen'];
		$method 							= "GET";
		//$host 							= 'sha256.webservices.amazon.'.$region; //Testing for SHA256 compliance 10/09/2015
		$host 								= "webservices.amazon.".$region; //new API 12-2011
		$uri 								= "/onca/xml";
		$params["Service"] 					= "AWSECommerceService";
		$params["AWSAccessKeyId"] 			= $publickey;
		$params["Timestamp"] 				= gmdate("Y-m-d\TH:i:s\Z");
		$params["Version"] 					= "2013-08-01";//"2011-08-01"; //"2009-03-31";
		$params["TruncateReviewsAt"] 		= "1";
		$params["IncludeReviewsSummary"]	= "True";
		$params["ItemPage"]					= isset($params["ItemPage"]) && ((int) $params["ItemPage"] <= 10 &&(int) $params["ItemPage"] > 0) ? (int) $params["ItemPage"] : 1;
		$cacheOnly							= isset($params["CacheOnly"]) && (bool) $params["CacheOnly"] === true ? true : false;
		$isSearch							= isset($params['Operation']) && $params['Operation'] == 'ItemSearch' ? true : false;
		$keyurl								= 'page'.$params["ItemPage"].'_'.$region.'_'.$params['AssociateTag'].'_'.$params['Keywords'].'_'.$params['SearchIndex'].'_'.$params['Operation'];
		$keystr								= $isSearch ? ('page'.$params["ItemPage"].'_'.$region.'_'.$params['AssociateTag'].'_'.$params['Keywords'].'_'.$params['SearchIndex'].'_'.$params['Operation']) : $region.'_'.$params['AssociateTag'].'_'.$params['Operation'].'_'.$params['IdType'].':';
		$asinArr							= isset($params["ItemId"]) ? explode(',',$params["ItemId"]) : array();
		$allASINs['requested'] 				= !empty($asinArr) ? $asinArr : array();
		$allASINs['needed'] 				= $allASINs['requested'];
		$start 								= 1;
		$indx								= 0;
		$cacheString 						= "{$region}_{$params['AssociateTag']}_{$params['Operation']}_{$params['IdType']}:";
		if( $isSearch )
			unset($params['IdType']);
		unset($params["CacheOnly"]);	
		ksort($params);
		$allASINs['needed'] 				= array_flip($allASINs['needed']);
		$completeCache						= array();
		/***************
		* Check if ASINs are in CACHE already
		***************/
		$userauth 		= 'spade';
		$skip_cache 	= isset($_GET['purge-cache']) &&  isset($_GET['auth']) && $_GET['purge-cache'] == '1' && $_GET['auth'] == $userauth ? true : false; 
		if( $cacheOnly ){
			//take the time to delete old cache files when doing just a cache only request.
			$checksql 	= "DELETE FROM {$wpdb->prefix}amazoncache WHERE (NOW() - `updated`) > '{$cachetime}';";
			$wpdb->get_results($checksql);
			if( $skip_cache )
				return array();
		}
		
		if( $params['Operation'] == 'ItemSearch' ){
			$pxmlerrors = array();
			$addlResponse = array();
			$checksql 	= "SELECT `body`, ( NOW() - `updated` ) as Age, `URL` FROM {$wpdb->prefix}amazoncache WHERE (`URL` LIKE '{$keyurl}' ) AND NOT( `body` LIKE '%AccountLimitExceeded%') AND NOT( `body` LIKE '%SignatureDoesNotMatch%') AND (NOW() - `updated`) <= '{$cachetime}';";
			$result 	= $wpdb->get_results($checksql);
			if(is_array($result) && !empty($result) /*&& !$skip_cache*/ ){
				//return cache
				foreach($result as $resultkey => $resultvalue){
					$pxml 	= appip_get_XML_structure_new($resultvalue->body, $resultvalue->Age);
					$items	= $pxml['ItemSearchResponse']['Items'];
					$tempS 	= $pxml;
					unset($tempS['ItemSearchResponse']['Items']['Item']);
					$addlResponse[] = $tempS['ItemSearchResponse']['OperationRequest'];
					$items	= isset($pxml['ItemSearchResponse']['Items']) && !empty($pxml['ItemSearchResponse']['Items']) ? $pxml['ItemSearchResponse']['Items'] : array();
					if(isset($pxml["ItemSearchResponse"]["Errors"])){
						$pxmlerrors[] = $pxml["ItemSearchResponse"]["Errors"];
					}elseif(isset($pxml["ItemSearchResponse"]['Items']['Request']["Errors"])){
						$pxmlerrors[] = $pxml["ItemSearchResponse"]['Items']['Request']["Errors"]['Error'];
					}
					if( isset($pxml['ItemSearchResponse']['Items']) ) 
						unset( $pxml['ItemSearchResponse']['Items'] );
					if( isset($items['Item']['ASIN']) ){ // only one and not an array;
						$asin = $items['Item']['ASIN'];
						$pxml['ItemSearchResponse']['Items']['Item'][$asin] = $items['Item'];
					}else{
						if( isset( $items['Item'] ) && !empty( $items['Item'] ) ){
							foreach( $items['Item'] as $aitem ){
								$asin = $aitem['ASIN'];
								$pxml['ItemSearchResponse']['Items']['Item'][$asin] = $aitem;
							}
						}
					}
					$newpxml[$resultvalue->URL] = $pxml;
				}
				return $newpxml;
			}else{
				//get new request
				$getArr = array(
					'region'	 => $region,
					'method' 	 => $method,
					'host' 		 => $host,
					'uri'		 => $uri,
					'asins' 	 => array(),
					'params' 	 => $params,
					'privatekey' => $privatekey,
					'reqtype'	 => 'search',
				);
				$tempRequest 	= get_appip_signature_requests( $getArr );
				$request		= $tempRequest['requests'];	
				$newREQ 		= amazon_product_do_API_request( $request, $keystr, $usefgc, $usecurl, array() );
				if( is_array( $newREQ ) && !empty( $newREQ ) ){
					foreach( $newREQ as $cCkey => $cCvalue){
						$pxml 	= appip_get_XML_structure_new( $cCvalue['body'], $cCvalue['Age'] );
						$items	= $pxml['ItemSearchResponse']['Items'];
						$tempS 	= $pxml;
						unset($tempS['ItemSearchResponse']['Items']['Item']);
						$addlResponse[] = $tempS['ItemSearchResponse']['OperationRequest'];
						$items	= isset($pxml['ItemSearchResponse']['Items']) && !empty($pxml['ItemSearchResponse']['Items']) ? $pxml['ItemSearchResponse']['Items'] : array();
						if(isset($pxml["ItemSearchResponse"]["Errors"])){
							$pxmlerrors[] = $pxml["ItemSearchResponse"]["Errors"];
						}elseif(isset($pxml["ItemSearchResponse"]['Items']['Request']["Errors"]["Error"])){
							$pxmlerrors[] = $pxml["ItemSearchResponse"]['Items']['Request']["Errors"]['Error'];
						}
						if( isset($pxml['ItemSearchResponse']['Items']) ) 
							unset( $pxml['ItemSearchResponse']['Items'] );
						if( isset($items['Item']['ASIN']) ){ // only one and not an array;
							$asin = $items['Item']['ASIN'];
							$pxml['ItemSearchResponse']['Items']['Item'][$asin] = $items['Item'];
						}else{
							if( isset( $items['Item'] ) && !empty( $items['Item'] ) ){
								foreach( $items['Item'] as $aitem ){
									$asin = $aitem['ASIN'];
									$pxml['ItemSearchResponse']['Items']['Item'][$asin] = $aitem;
								}
							}
						}
						$newpxml[$cCkey] = $pxml;
					}
				}
				return $newpxml;
			}
			return false;
		}else{
			$checks 		= array();
			$newpxml		= array();
			if( !$skip_cache ){
				if( !empty( $allASINs['requested'] ) ){
					foreach( $allASINs['requested'] as $casin ){
						$checks[] = " (`URL` LIKE '{$keystr}%{$casin}%' ) ";
					}
				}
				$newSQL 	= implode(' OR ', $checks );
				$checksql 	= "SELECT `body`, ( NOW() - `updated` ) as Age, `URL` FROM {$wpdb->prefix}amazoncache WHERE ({$newSQL}) AND NOT( `body` LIKE '%AccountLimitExceeded%') AND `body` != '' AND NOT( `body` LIKE '%SignatureDoesNotMatch%') AND (NOW() - `updated`) <= '{$cachetime}';";
				$result 	= $wpdb->get_results($checksql);
				if( is_array( $result ) && !empty( $result )){
					foreach($result as $resultkey => $resultvalue){
						$completeCache[$resultvalue->URL] = array('body' => $resultvalue->body, 'Age' => $resultvalue->Age );
						$tempASINs 	= explode( ',', str_replace($cacheString, '', $resultvalue->URL ) );
						if( !is_array( $tempASINs ) ){
							$allASINs['cached'][] 	= $tempASINs;
							if(isset($allASINs['needed'][$tempASINs]))
								unset($allASINs['needed'][$tempASINs]);
						}else{
							$allASINs['cached'] = array_merge( $allASINs['cached'], $tempASINs );
							if( is_array( $tempASINs ) && !empty( $tempASINs) ){
								foreach( $tempASINs as $ky => $vl ){
									if(isset($allASINs['needed'][$vl]))
										unset($allASINs['needed'][$vl]);
								}
							}
						}
					}
				}
			}
			if( empty( $allASINs['needed'] ) ){
				//nothing needed - all cached!
				if( $cacheOnly )
					return true;
			}else{
				//need to cache something
				$allASINs['needed'] = array_flip($allASINs['needed']);
				$allASINs['toget'] 	= array_chunk($allASINs['needed'], 10);
				$getArr = array(
					'region'	 => $region,
					'method' 	 => $method,
					'host' 		 => $host,
					'uri'		 => $uri,
					'asins' 	 => $allASINs['toget'],
					'params' 	 => $params,
					'privatekey' => $privatekey,
				);
				

				$tempRequest 	= get_appip_signature_requests($getArr);
				$request		= $tempRequest['requests'];	
				$requestedASINs = $tempRequest['asins'];

				/** DO THE REQUEST **/
				if( is_array( $request ) && !empty( $request )){
					$newREQ = amazon_product_do_API_request( $request, $keystr, $usefgc, $usecurl, $requestedASINs);
					$completeCache = array_merge( $completeCache, $newREQ );
				}
				/** END DO THE REQUEST **/
			}
		}
		/**************
		* END Check if ASINs are in CACHE already
		**************/

		/**************
		* Return needed products
		**************/
		if( is_array( $completeCache ) && !empty( $completeCache ) ){
			$pxmlerrors 	= array();
			$addlResponse 	= array();
			$newre 	= array();
			// this would be from a Request Error, not an item error.
			if( isset($completeCache['Error']['body']) ){
				$newre["Errors"] = $completeCache['Error']['body'];
				return $newre;
			}
			foreach( $completeCache as $cCkey => $cCvalue){
				$pxml 	= appip_get_XML_structure_new( $cCvalue['body'], $cCvalue['Age'] );
				$temp 	= $pxml;
				/* Errors */
				if( isset($temp["ItemLookupErrorResponse"]["Errors"]) ){
					// this would be from a Request Error, not an item error.
					if( isset( $temp["ItemLookupErrorResponse"]["Errors"]["Error"][0]) && !empty($temp["ItemLookupErrorResponse"]["Errors"]["Error"][0]) ){
						foreach($temp["ItemLookupErrorResponse"]["Errors"]["Error"] as $er ){
							$newre[] = $er;
						}
					}else{
						$newre[] = $temp["ItemLookupErrorResponse"]["Errors"]["Error"];
					}
				}
				if( isset($temp["ItemLookupResponse"]['Items']['Request']["Errors"]['Error']) ){
					// this would be from an Item Error (i.e., bad product ASIN, not in locale or not available in API.
					if( isset( $temp["ItemLookupResponse"]['Items']['Request']["Errors"]['Error'][0]) && !empty($temp["ItemLookupResponse"]['Items']['Request']["Errors"]['Error'][0]) ){
						foreach($temp["ItemLookupResponse"]['Items']['Request']["Errors"]['Error'] as $er ){
							$newre[] = $er;
						}
					}else{
						$newre[] = $temp["ItemLookupResponse"]['Items']['Request']["Errors"]['Error'];
					}
				}
				/* END Errors */
				
				if($temp['ItemLookupResponse']['Items']['Item'])
					unset($temp['ItemLookupResponse']['Items']['Item']);
				$addlResponse[] = isset($temp['ItemLookupResponse']['OperationRequest']) ? $temp['ItemLookupResponse']['OperationRequest'] : '';
				$items	= isset($pxml['ItemLookupResponse']['Items']) && !empty($pxml['ItemLookupResponse']['Items']) ? $pxml['ItemLookupResponse']['Items'] : array();
				if( !empty( $newre ) )
					$pxmlerrors = $newre ;
				if( isset($pxml['ItemLookupResponse']['Items']) ) 
					unset( $pxml['ItemLookupResponse']['Items'] );
				if( isset($items['Item']['ASIN']) ){ // only one and not an array;
					$asin = $items['Item']['ASIN'];
					if( in_array( $asin, $allASINs['requested'] ) )
						$pxml['ItemLookupResponse']['Items']['Item'][$asin] = $items['Item'];
				}else{
					if( isset( $items['Item'] ) && !empty( $items['Item'] ) ){
						foreach( $items['Item'] as $aitem ){
							$asin = $aitem['ASIN'];
							if( in_array( $asin, $allASINs['requested'] ) && $asin != '' ){
								$pxml['ItemLookupResponse']['Items']['Item'][$asin] = $aitem;
							}
						}
					}
				}
				$newpxml[$cCkey] = $pxml;
			}
		}
		$newPXTemp 		= array();
		$newPX 			= array();
		$newPXReturn	= array();
		$copy_allASINs 	= $allASINs['requested'];
		if( is_array($newpxml) && !empty($newpxml) ){
			foreach( $newpxml as $key => $val ){
				$item = isset($val['ItemLookupResponse']['Items']['Item']) && !empty($val['ItemLookupResponse']['Items']['Item']) ? $val['ItemLookupResponse']['Items']['Item'] : array();
				foreach( $item as $ikey => $ival ){
					$newPXTemp[$ikey] = $ival;
				}
			}
		}
		if( is_array($copy_allASINs) && !empty($copy_allASINs) ){
			foreach( $copy_allASINs as $caVAL ){
				$newPX['ItemLookupResponse']['Items']['Item'][] = $newPXTemp[$caVAL];
			}
		}
		ksort($newPX['ItemLookupResponse']['Items']['Item']);
		if(!empty($pxmlerrors)){
			$newPX["Errors"] = $pxmlerrors;
		}
		if(is_array($addlResponse) && !empty($addlResponse)){
			$newPX["ItemLookupResponse"]["AdditionalResponse"] = $addlResponse;
		}
		unset($newpxml);
		$newPXReturn[] = $newPX;

		return $newPXReturn;
		/**************
		* END Return needed products
		**************/
	}
}

function get_appip_signature_requests($getArr){
	if(!is_array($getArr) || empty($getArr))
		return array('requests','asins');
	$request 	= array();
	$defaults 	= array(
		'method' 	 => 'GET',
		'host' 		 => '',
		'uri'		 => '',
		'asins' 	 => array(),
		'params' 	 => array(),
		'privatekey' => '',
		'reqtype'	 => 'asin',
	);
	extract( shortcode_atts( $defaults, $getArr ) );
	if( $reqtype == 'asin'){
		$requestedASINs = array();
		if( is_array( $asins ) && !empty( $asins ) ){
			foreach($asins as $akey => $aval){
				$canquery = array();
				foreach ( $params as $param => $value ){
					$fValue = str_replace("%7E", "~", rawurlencode( ( $param == 'ItemId'  ? implode( ',', $aval ) : $value ) ) );
					$fParam = str_replace("%7E", "~", rawurlencode( $param ) );
					$canquery[] = $fParam . "=" . $fValue;
				}
				$string_to_sign 	= $method."\n".$host."\n".$uri."\n".implode("&", $canquery );
				$signature 			= str_replace("%7E", "~", rawurlencode( base64_encode( appip_plugin_aws_hash_hmac( "sha256", $string_to_sign, $privatekey, true ) ) ) );
				$request[] 			= "https://".$host.$uri."?".implode("&", $canquery)."&Signature=".$signature; //changed to https 3.6.1
				$requestedASINs[] 	= implode(',',$aval);
			}
		}
		return array( 'requests' => $request, 'asins' => $requestedASINs );
	}else{
		//search
		$canquery = array();
		foreach ( $params as $param => $value ){
			$fValue = str_replace("%7E", "~", rawurlencode( $value ) );
			$fParam = str_replace("%7E", "~", rawurlencode( $param ) );
			$canquery[] = $fParam . "=" . $fValue;
		}
		$string_to_sign 	= $method."\n".$host."\n".$uri."\n".implode("&", $canquery );
		$signature 			= str_replace("%7E", "~", rawurlencode( base64_encode( appip_plugin_aws_hash_hmac( "sha256", $string_to_sign, $privatekey, true ) ) ) );
		$request[] 			= "https://".$host.$uri."?".implode("&", $canquery)."&Signature=".$signature; //changed to https 3.6.1
		return array( 'requests' => $request, 'asins' => array() );
	}
}

function amazon_product_do_API_request( $request = array(), $keystr = '', $usefgc = 0, $usecurl = 0, $requestedASINs = array() ){
	$newpxml = array();
	if( !empty( $request ) ){
		global $wpdb;
		$APIGet = appip_check_CURL_FOPEN();
		foreach( $request as $rkey => $sReqA ){
			if( $APIGet['fopen'] === true ){
				$allowCurl = $APIGet['curl'] === true ? true : false;
				$response = appip_API_file_get_contents( $sReqA,$allowCurl );
			}elseif($APIGet['curl'] === true ){
				$response = appip_do_API_CURL( $sReqA );
			}else{
				return array( 'Error' => array('Age'=> 0, 'body'=>'Error: Cannot make request - no transport'));
			}
			$xbody 	= trim(addslashes($response));
			if( strpos($xbody, 'Error:') === false ){
				if($xbody ==''){
					return array( 'Error' => array('Age'=> 0, 'body'=>'Error: Empty Result.<br/>Something when wrong with the request. If you continue to have this problem, check your API keys for accuracy. If you still have the issue, send your Debug key and site URL to plugins@fischercreativemedia.com for help.'));
				}else{
					if( isset($requestedASINs[$rkey]) ){
						$keystrTemp = $keystr.$requestedASINs[$rkey];
					}else{
						$keystrTemp = $keystr;
					}
					$newpxml[$keystrTemp] 	= array('body' => stripslashes($xbody), 'Age'=> 0);
					$updatesql 		  		= "INSERT IGNORE INTO {$wpdb->prefix}amazoncache (`URL`, `body`, `updated`) VALUES ('{$keystrTemp}', '{$xbody}', NOW()) ON DUPLICATE KEY UPDATE `body`='$xbody', `updated`=NOW();";
					$wpdb->query( $updatesql );
				}
			}elseif( strpos($xbody, 'Error:') !== false ){
					return array( 'Error' => array('Age'=> 0, 'body'=> $xbody ));
			}
		}
	}
	return $newpxml;
}
	
function appip_do_API_CURL( $request = '' ){
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $request);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	if(curl_exec($ch) === false){
		$data = curl_error($ch);
	}else{
		$data = curl_exec($ch);
	}
	curl_close($ch);
	return $data;
}

function appip_check_CURL_FOPEN(){
	$hasfgc 	= ini_get('allow_url_fopen') ? true : false;
	$hasfin 	= ini_get('allow_url_include') ? true : false;
	$hascurl 	= function_exists('curl_version') ? true : false;
	$setis		= array('fopen' => $hasfgc, 'include'=> $hasfin, 'curl' => $hascurl);
	return $setis;
}

function appip_API_file_get_contents( $request = '', $allowCurl = false ){
	$error = array();
	$response = @file_get_contents($request);
	if( $response === false ){
		$error[] = 'Error: Invalid Request (file_get_contents) &mdash; Please check your Access Key ID and Secret Access Key for errors.';
		if( $allowCurl === true){
			$response = appip_do_API_CURL( $request );
			//try CURL 
			if( $response === false ){
				$error[] = 'Error: Invalid Request (CURL & file_get_contents) &mdash; Please check your Access Key ID and Secret Access Key for errors.';
			}else{
				$xml = simplexml_load_string($response);
				if( isset($xml->Error) ){
					$error[] = 'Error: (CURL) '.$xml->Error->Code.' &mdash; '.$xml->Error->Message;
				}else{
					$error = array();
				}
			}
		}
	}
	if(!empty($error))
		return implode("<br/>",$error);
	return $response;
}

if(!function_exists('appip_get_XML_structure_new')){
	function appip_get_XML_structure_new ( $xmldata, $cached=0 ){
		if( $xmldata == '' )
			return false;
		$xmldata = stripslashes($xmldata);
		ini_set ('track_errors', '1');
		$xmlreaderror 	= false;
		$charset 		= get_bloginfo( 'charset' ) =='' ? 'UTF-8' : get_bloginfo( 'charset' );
		$parser 		= xml_parser_create ($charset);
		xml_parser_set_option ($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parser_set_option ($parser, XML_OPTION_CASE_FOLDING, 0);
		if ( !xml_parse_into_struct ( $parser, $xmldata, $vals, $index ) )
			$xmlreaderror = true;
		xml_parser_free ($parser);
		if ( !$xmlreaderror ) {
			$result = array ();
			$i 		= 0;
			if( isset( $vals[$i]['attributes'] ) ){
				foreach (array_keys ($vals [$i]['attributes']) as $attkey){
					$attributes [$attkey] = $vals [$i]['attributes'][$attkey];
				}
			}
			$result [$vals [$i]['tag']] = array_merge ($attributes, appip_plugin_GetChildren ($vals, $i, 'open'));
		}
		ini_set ('track_errors', '0');
		$result['CachedAPPIP'] = $cached;
		return $result;
	}
}