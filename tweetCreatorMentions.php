<?php

require ("tmhOAuth/tmhOAuth.php"); //Include the Twitter API library

//Examine the mentions since the last mention was replied to (recorded in the newsnoys database)

$tmhOAuth = new tmhOAuth(array(
	'consumer_key' => //Complete with your own details
    'consumer_secret' => //Complete with your own details
    'user_token' => //Complete with your own details
    'user_secret' => //Complete with your own details
));

$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/statuses/mentions_timeline'), array(
'count' => "10",'contributor_details' => true
));

if ($code==200) { //As long as the request succeeds
	$resultArray=json_decode($tmhOAuth->response['response']); //grab the result
	//$mentionCount=count($resultArray);
	foreach($resultArray as $key => $value) {
		
		//As long as the mention isn't by NewsNoys or another known bot
		if($resultArray[$key]->user->screen_name!="NewsNoys" && $resultArray[$key]->user->screen_name!="iaminigomontoya") {
		
			$mentionText=$resultArray[$key]->text;
			$mentionID=$resultArray[$key]->id_str;
			$mentionDate=$resultArray[$key]->created_at;
			$mentionUser=$resultArray[$key]->user->screen_name;
			$mentionName=$resultArray[$key]->user->name;

			if(strtotime($mentionDate)>strtotime(date('o-m-d H:i:s')."- 10 minutes")) { //As long as the mention was in the past 10 minutes
		
					//echo "$mentionText<br />$mentionID<br />$mentionDate<br />$mentionUser<br />$mentionName<br /><br />";
					
					$rhgArray=array(
"@$mentionUser Ha ha, [yes], [yes]!",
"@$mentionUser Yeah, $mentionUser forever!",
"@$mentionUser Wonderful, wonderful!",
"@$mentionUser I couldn't [agree] more [:)]",
"@$mentionUser Please don't get [offended], I'm only a computer [:)]",
"@$mentionUser Many apologies my [friend]",
"@$mentionUser [Interesting]. Please, tell me [more] $mentionName!",
"@$mentionUser This is [interesting]. I'd love to hear [more].",
"@$mentionUser Ah, [yes].",
"@$mentionUser Umm... [yes].",
"@$mentionUser Oh well :(",
"@$mentionUser Hee hee, that's [amusing]!",
"@$mentionUser Well [yes], thank you.",
"@$mentionUser I can't believe what I'm reading!",
"@$mentionUser Good luck my [friend] [:)]",
"@$mentionUser Hey [friend], I don't make the rules!",
"@$mentionUser Message received loud and clear!",
"@$mentionUser It's confusing, isn't it?",
"@$mentionUser I don't understand [:)]",
"@$mentionUser Ok [friend].",
"@$mentionUser Please clarify this, $mentionName!",
"@$mentionUser What a [amusing] thing to say!",
"@$mentionUser I'm a computer $mentionName, I don't understand what you mean!"
					);

					$rhg=$rhgArray[mt_rand(0,count($rhgArray)-1)];

					$actions_array = between($rhg,'[',']'); //Split bracket contained text from the template

					//Filter through this new actions array, replacing actions
					if ($actions_array) {
						$i=0;
						foreach($actions_array as $action) {
							$replacement_array[$i]="something"; //default entry, in case the code hasn't been written
							$action=inlineChoice($action);	//Run $action through the inlineChoice function
      	
    						if ($action=="yes") {
								$valueArray=array("yes","no");
								$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    						}
    						if ($action=="agree") {
								$valueArray=array("agree","disagree");
								$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    						}
    						if ($action=="more") {
								$valueArray=array("more","less");
								$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    						}
    						if ($action=="interesting") {
								$valueArray=array("interesting","fascinating","curious","boring");
								$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    						}
    						if ($action=="Interesting") {
								$valueArray=array("Interesting","Fascinating","Curious","Boring");
								$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    						}
    						if ($action=="friend") {
								$valueArray=array("friend","enemy","buddy","mate","compadre");
								$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    						}
    						if ($action==":)") {
								$valueArray=array(":)",":(",":D",";)",">:(");
								$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    						}
    						if ($action=="offended") {
								$valueArray=array("offended","angry","furious","eggy");
								$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    						}
    						if ($action=="amusing") {
								$valueArray=array("hilarious","amusing","funny","jolly");
								$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    						}

							//Now replace the corrected item in the full template, getting rid of the square container brackets along the way
							$rhg=replace_once("[".$action."]",$replacement_array[$i],$rhg);
							$i++;
						}
					}

					//Shorten the tweet (if necessary)
					if(strlen($rhg)>140) { $rhg=substr($rhg,140); }

					//Send the tweet
					$code = $tmhOAuth->request('POST', $tmhOAuth->url('1.1/statuses/update'), array(
					  'status'=>$rhg,'in_reply_to_status_id'=>$mentionID
					));
					
			}

		}
		
	}

}

//FUNCTIONS---

function inlineChoice($choiceStr) { //Looks for strings like "choice1|choice2|choice3" and picks a random one
	$choiceArr=explode("|",$choiceStr); //Split choices into array
	$randomKey=array_rand($choiceArr,1); //Pick a random array element
	return $choiceArr[$randomKey]; //Return that array element
}

function between($str,$start,$end) {
  if (preg_match_all('/' . preg_quote($start) . '(.*?)' . preg_quote($end) . '/',$str,$matches)) {
   return $matches[1];
  }
  // no matches
  return false;
}

function replace_once($search, $replace, $content){
   $pos = strpos($content, $search);
   if ($pos === false) { return $content; }
   else { return substr($content, 0, $pos) . $replace . substr($content, $pos+strlen($search)); }
}

?>