<?php

//Script that creates trend-related tweets

require ("tmhOAuth/tmhOAuth.php"); //Include the Twitter API library

//Get the current time

//Examine the current trends and pick one randomly

$tmhOAuth = new tmhOAuth(array(
	'consumer_key' => //Complete with your own details
    'consumer_secret' => //Complete with your own details
    'user_token' => //Complete with your own details
    'user_secret' => //Complete with your own details
));

/*$code = $tmhOAuth->request('GET', $tmhOAuth->url('1/trends/23424975')); //Get UK trends*/
$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/trends/place'), array(
  'id' => "23424975"
));

$trendName=array();

if ($code==200) { //As long as the request succeeds
	$resultArray=json_decode($tmhOAuth->response['response']); //grab the result
	for($i=0;$i<=9;$i++) { //Loop 10 times (once for each trend)
		$trendName[$i]=$resultArray[0]->trends[$i]->name; //Put each trend name into trendName var for easier access
	}
}

//Pick a random item from the array
$arrayItem=mt_rand(0,9); $trend=$trendName[$arrayItem];

$rhgArray=array(
"Let's take a look at the trends. Hmmm - [trend], eh? I [love] [trend].",
"Trend time. I don't know much about [trend], but it sounds like [fun].",
"[trend] is trending? Someone [call] the [police]!",
"[trend] appears to be trending again, [rubbish] old [trend].",
"[trend]'s trending! Quick, [call] the [police]!",
"[trend]? If there's one thing I know about [trend], it's that it's [rubbish].",
"Not [trend] again! When will people stop talking about [trend]!",
"So, what’s trending? Oh, [trend]. How boring.",
"[Hello] everyone. What do you think about [trend]? Oh, I see.",
"I’ll tell you what I think about [trend] – absolutely nothing.",
"[trend]!? Shut up about [trend]!",
"Not another trend-related tweet! [trend]!? Turn on the @NewsNoys generator, quick!",
"Hey, if you don't have anything nice to say about [trend], best not say anything at all.",
"All I ever hear is [trend], [trend], [trend]!",
"I once [read_a_book] about [trend]. It was [rubbish].",
"Next on @NewsNoys: the latest on [trend].",
"Does anyone actually know anything about [trend]? Doesn't seem like it.",
"Begone [trend], never darken my doorstep again!",
"In case no-one had noticed, @NewsNoys tweets are generated randomly. Just like [trend].",
"Pay no attention to me, I'm only a robot. Pay attention to [trend] instead.",
"[Hello], let's all think about [trend] for a while.",
"I'll never stop thinking about [trend]. Oh wait, I just have. Didn't last long, did it?",
"It's a [sad] story, that [trend]. Oh well, back to the news.",
"I don't understand much about [trend], but the more I read, the less I like it.",
"[trend]? What a strange thing to talk about.",
"Behold! [trend]!",
"Please, everyone - don't even think about [trend]. It's not worth it.",
"[trend] doesn't even bear thinking about. It's [fun].",
"Well, this is [fascinating]: I've never thought about [trend] in that way.",
"Could somone explain what [trend] is all about?",
"Scanning trends... [trend] detected! Processing... ERROR, WASTE OF TIME!",
"I'm looking for more information on [trend]. What's it all about?",
"Hmmmmmm. [trend] is [fascinating]. I [love] reading about it!",
"I refuse to mention [trend], for it is [rubbish].");

$rhg=$rhgArray[mt_rand(0,count($rhgArray)-1)];

$actions_array = between($rhg,'[',']'); //Split bracket contained text from the template

//Filter through this new actions array, replacing actions
if ($actions_array) {
	$i=0;
	foreach($actions_array as $action) {
		$replacement_array[$i]="something"; //default entry, in case the code hasn't been written
		$action=inlineChoice($action);	//Run $action through the inlineChoice function
      	
      	if ($action=="trend") {
			$replacement_array[$i]=$trend;
    	}
    	if ($action=="love") {
			$valueArray=array("love","hate","like","dislike","enjoy");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="fascinating") {
			$valueArray=array("fascinating","interesting","boring","uninteresting");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="call") {
			$valueArray=array("call","phone","ring","fetch","get");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="invented") {
			$valueArray=array("invented","created","devised","built","made");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="loads") {
			$valueArray=array("loads","lots","plenty","everything");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="rubbish") {
			$valueArray=array("rubbish","terrible","awful","stupid","amazing","awesome","fantastic","brilliant","crap","shit");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="Hello") {
			$valueArray=array("Hello","Hi","Greetings");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="fun") {
			$valueArray=array("fun","torture");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="police") {
			$valueArray=array("police","cops","FBI","government","fire brigade","electricity company");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="read_a_book") {
			$valueArray=array("read a book","saw a film","watched a documentary","read a newspaper article","saw some graffiti");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="sad") {
			$valueArray=array("sad","miserable","heart-wrenching","weepy");
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
  'status' => $rhg
));

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