<?php

require 'tmhOAuth/tmhOAuth.php'; //Include the library
//require 'tmhOAuth/tmhUtilities.php'; //We're not using the utilities at present

$tmhOAuth = new tmhOAuth(array(
	'consumer_key' => //Complete with your own details
    'consumer_secret' => //Complete with your own details
    'user_token' => //Complete with your own details
    'user_secret' => //Complete with your own details
));

/*$code = $tmhOAuth->request('GET', $tmhOAuth->url('1/trends/23424975')); //Get UK trends*/
$code=$tmhOAuth->request('GET', $tmhOAuth->url('1.1/trends/place'), array('id' => "23424975"));

//$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/trends/available'));


/*
$code = $tmhOAuth->request('POST', $tmhOAuth->url('1/statuses/update'), array(
  'status' => 'My Twitter Message'
));
*/

require ("include/newsnoysUpdate.inc"); //MySQL connection details stored externally

$popIncrease=5; //The amount by which to increase a subject's popularity when a matching trend is found
$popMaximum=60;

if ($code==200) { //As long as the request succeeds

	$resultArray=json_decode($tmhOAuth->response['response']); //grab the result
	
	for($i=0;$i<=9;$i++) { //Loop 10 times (once for each trend)
		$trendName=$resultArray[0]->trends[$i]->name; //Put each trend name into trendName var for easier access
		$trendName=str_replace("#","",$trendName); //Remove any hashtags
		//A name will now be in trendName as 'Justin Bieber' or 'XXX Justin XXXX' or 'XXX Bieber XXX'
		$trendArray=explode(" ",$trendName); //Explode into trendArray
		foreach ($trendArray as $value) { //Loop through the array of values
			mysql_query("UPDATE subject SET popularity=popularity+$popIncrease WHERE firstname='$value' OR surname='$value' OR CONCAT(firstname,surname)='$value'"); //Increment any matching name parts in the database (either firstname or surname)
		}
		
	}

}

//Now we look at our sources:

//Create sources (these are the screen names)
$sourceArray=array("BBCNews","Channel4News","guardiannews","SkyNews","MailOnline");

$popIncrease=10/count($sourceArray); //count the number of sources (using the same variable name as used by the trends)

foreach($sourceArray as $screenName) { //Loop through each source

	$code=$tmhOAuth->request('GET', $tmhOAuth->url('1.1/statuses/user_timeline'), array('screen_name' => $screenName,'count' => '5'));

	if ($code==200) { //As long as the request succeeds

		$resultArray=json_decode($tmhOAuth->response['response']); //grab the result
	
		for($i=0;$i<=9;$i++) { //Loop 10 times (once for each trend)
			$trendName=$resultArray[$i]->text; //Put each trend name into trendName var for easier access
			$trendName=str_replace("#","",$trendName); //Remove any hashtags
			//A name will now be in trendName as 'Justin Bieber' or 'XXX Justin XXXX' or 'XXX Bieber XXX'
			$trendArray=explode(" ",$trendName); //Explode into trendArray
			
			foreach ($trendArray as $value) { //Loop through the array of values
				mysql_query("UPDATE subject SET popularity=popularity+$popIncrease WHERE firstname='$value' OR surname='$value' OR CONCAT(firstname,surname)='$value'"); //Increment any matching name parts in the database (either firstname or surname)
			}

		}
		
	}

} //end foreach

//Finally, cap any values that have risen past the maximum
mysql_query("UPDATE subject SET popularity=$popMaximum WHERE popularity>$popMaximum");

?>