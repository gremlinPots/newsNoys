<?php

//Script that creates follower (and their followers) targetted tweets

require ("include/newsnoysUpdate.inc"); //MySQL connection details stored externally
require ("tmhOAuth/tmhOAuth.php"); //Include the Twitter API library

$tmhOAuth = new tmhOAuth(array(
	'consumer_key' => //Complete with your own details
    'consumer_secret' => //Complete with your own details
    'user_token' => //Complete with your own details
    'user_secret' => //Complete with your own details
));

//GET the current NewsNoys follower IDs (the first 5000, at least- HA!)
$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/followers/ids'), array(
  'cursor' => "-1",'screen_name' => 'NewsNoys'
));

if ($code==200) { //As long as the request succeeds
	$resultArray=json_decode($tmhOAuth->response['response']); //grab the result
	$randFollower=$resultArray->ids[mt_rand(0,count($resultArray->ids)-1)]; //Pick a random follower from the result

	//There's now a 1/4 chance whether the tweet will involve the follower, or a follower of the follower
	if(mt_rand(0,3)!=3) { //If the value is not 3
		//Pick a follower of the follower
		$code2 = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/followers/ids'), array(
 		'cursor' => "-1",'user_id' => $randFollower
		));
		if ($code2==200) { //As long as the request succeeds
			$resultArray2=json_decode($tmhOAuth->response['response']); //grab the result
			$randFollower=$resultArray2->ids[mt_rand(0,count($resultArray2->ids)-1)]; //Pick a random follower from the result
		}
	}
	
	//We now have a follower ID to use in our tweet
	//Convert into the screen_name
	$code3 = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/users/show'), array(
 	'user_id' => $randFollower
	));
	if ($code3==200) { //As long as the request succeeds
		$resultArray3=json_decode($tmhOAuth->response['response']); //grab the result
	}
	//Take out the info we need
	//We use name, screen_name & location with no modifications
	
	//But description needs to be analysed and text drawn out of it
	
	$followerDesc=$resultArray3->description;

/*	$resultArray3->name="Dummy Name";
	$resultArray3->screen_name="screenname";
	$resultArray3->location="location";
	$followerDesc="hereissometext";
*/
	
//	echo "Full follower description = $followerDesc<br />";
	
	//Look for words following 'a' , 'an', 'the', 'and', 'for', 'of'... to get topics to include
	$wordSearch=array(" a "," an "," the "," and "," for "," of "," another "," about ");
	//Find all instances of the current searchFor
	
	$followerTopic=array(); //Create the empty array to hold all occurrences of 'topic' words
	
	foreach ($wordSearch as $value) {
		$textPos=strpos_recursive($followerDesc,$value); //textPos should now contain all positions of the string

		if($textPos) {
			foreach($textPos as $pos) {
        		//$pos = the position of the searched for text
        		$stopChars=array(" ",".","!","?");
//        		echo "Word match found at pos $pos. Now searching for end character<br />";
        		$textEndPos=strposa($followerDesc,$stopChars,$pos+strlen($value)); //Find the next space or full stop, exclamation or question mark after the text
        		if($textEndPos==FALSE) { $textEndPos=strlen($followerDesc); } //If there is no stop after text, set the end point as the end of the text
//        		echo "End character found at position $textEndPos<br />";
        		$topicText=substr($followerDesc,$pos+strlen($value),$textEndPos-$pos-strlen($value)); //Extract the identified word
        		$followerTopic[]=$topicText; //Add the word to the word array
//        		echo "Found word in first search: '$topicText' added to array<br />";
        	}
   		}
	} //End foreach
	
	//If there are none of these pre-topic words, just pick a random group and tailor the tweet to be confused
	if(count($followerTopic)==0) {
		$textPos=strpos_recursive($followerDesc," "); //textPos now contains all positions of spaces
		if($textPos) {
			foreach($textPos as $pos) {
        		//$pos = the position of the searched for text
        		$stopChars=array(" ",".","!","?");
        		$textEndPos=strposa($followerDesc,$stopChars,$pos+1); //Find the next space or full stop, exclamation or question mark after the text
        		if($textEndPos==FALSE) { $textEndPos=strlen($followerDesc); } //If there is no stop after text, set the end point as the end of the text
        		$topicText=substr($followerDesc,$pos+1,$textEndPos-$pos-1); //Extract the identified word
        		$followerTopic[]=$topicText; //Add the word to the word array
//        		echo "Found word in second search: '$topicText' added to array<br />";
        	}
   		}
	}
	
	//At this stage, $followerTopic will hopefully have some values in it. If not, it's because the description didn't contain any spaces... or possibly anything at all
	if(count($followerTopic)==0) { //If there is still nothing in the array, just use some random value
		$topicRand=array("chicken","meatballs","football","literature","poetry","food","alcohol","terrorism","explosions","laughing","comedy","jokes","robotics","artificial intelligence","pizza","booze","girls","films","wrestling");
		$f_topic=$topicRand[mt_rand(0,count($topicRand)-1)];
//		echo "No words found in searches, so adding all sorts of topics to array and picking one<br />";
	} else { //Otherwise, pick a random element from the array
		$f_topic=$followerTopic[mt_rand(0,count($followerTopic)-1)];
	}
	
	//Chosen topic is now $f_topic
}

//f_VAR refers to the chosen follower's details

$row_na = pickRandom(subject); $firstname = $row_na["firstname"]; $surname = $row_na["surname"];

$rhgArray=array(
"I find [f_name] a [really] [fascinating] individual - some [fascinating] [idea]s about [f_topic] @[f_screen_name]",
"[f_name]'s tweets about [f_topic] are [really] [fascinating]. I [wish] that [everybody] would follow @[f_screen_name]!",
"I may only be a computer, but that won't stop my [love] for [f_name]! @[f_screen_name]",
"When it comes to [f_topic], [f_name] really knows [everything]. @[f_screen_name]",
"I've never been to [f_location], but I imagine it's [fascinating]. What say you, @[f_screen_name]?",
"A [big] [hello_lc] to [f_name], whose tweets I've [discovered] @[f_screen_name]",
"[hello_uc] to @[f_screen_name]! Like you, I'm interested in [f_topic], let's [talk] about it.",
"When I'm next in [f_location], I'd like to [talk] to [f_name] about [f_topic]. @[f_screen_name]",
"How to get to [f_location]? Just [call] [f_name]! @[f_screen_name]",
"There's something strange in [f_location]: who you gonna call? [f_name]! @[f_screen_name]",
"I [love] [f_topic], just like [f_name] (@[f_screen_name])! It's [fascinating].",
"[hello_uc] [f_name], please tell me more about [f_topic] @[f_screen_name] [smiley]",
"[f_name] knows more about [f_topic] than anyone else in [f_location], he's [fascinating] @[f_screen_name]",
"I'd [love] to [call] @[f_screen_name] & [talk] about [f_topic] one day...",
"@[f_screen_name], did you [invent] [f_topic]? You know [everything] about [f_topic]!",
"Have you [seen] @[f_screen_name]'s latest tweet? Utterly [rubbish]!",
"[f_name] is [rubbish] & knows [everything] about [f_topic] @[f_screen_name]",
"@[f_screen_name], you make me [sad]. That last tweet about the [f_topic]... [smiley]",
"What do they teach in [f_location]? @[f_screen_name] knows [everything] about [f_topic]!",
"Dear [f_name], never stop writing about [f_topic]. Thanks! @[f_screen_name]",
"[hello_uc] @[f_screen_name], liked your last tweet about [f_topic]. Was [really] [fascinating].",
"@[f_screen_name]'s tweets make me feel like this: [smiley]",
"@[f_screen_name]'s tweets remind me of the work of $firstname $surname! It's [fascinating]!",
"If you [love] $firstname $surname, you're sure to [love] [f_name]! @[f_screen_name]",
"@[f_screen_name], your writing reminds me of a [young] $firstname $surname [smiley]",
"[f_name] = [smiley] $firstname $surname = [smiley] @[f_screen_name]",
"I suspect the mind behind @[f_screen_name] is as [devious] as $firstname $surname!",
"Want to learn [everything] about [f_topic]? Follow @[f_screen_name]! [smiley]",
"I knew [everything] about [f_topic]... until I read @[f_screen_name]'s tweets! [smiley]",
"Were $firstname $surname & [f_name] separated at birth? I [love] the tweets! @[f_screen_name]",
"I don't understand [f_topic], but I still [love] [f_name]! @[f_screen_name]"
);

$rhg=$rhgArray[mt_rand(0,count($rhgArray)-1)];

$actions_array = between($rhg,'[',']'); //Split bracket contained text from the template

//Filter through this new actions array, replacing actions
if ($actions_array) {
	$i=0;
	foreach($actions_array as $action) {
		$replacement_array[$i]="something"; //default entry, in case the code hasn't been written
		$action=inlineChoice($action);	//Run $action through the inlineChoice function
      	
      	if ($action=="f_name") {
			$replacement_array[$i]=$resultArray3->name;
    	}
      	if ($action=="f_screen_name") {
			$replacement_array[$i]=$resultArray3->screen_name;
    	}
      	if ($action=="f_location") {
      		if($resultArray3->location!="") {
				$replacement_array[$i]=$resultArray3->location;
			} else {
				$valueArray=array("Jupiter","Venus","America","Iraq","Ireland","Wales","Canada");
				$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
			}
    	}
      	if ($action=="f_topic") {
			$replacement_array[$i]=$f_topic;
    	}
    	if ($action=="love") {
			$valueArray=array("love","enjoy","like","adore");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="really") {
			$valueArray=array("really","mildly","hugely","massively","immensely","quite","rather");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="big") {
			$valueArray=array("big","huge","massive","small","normal-sized","tiny","non-existent");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="fascinating") {
			$valueArray=array("fascinating","interesting","somewhat boring","uninteresting","clincially fascinating","breathtaking","astonishing","amazing","inspirational");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="call") {
			$valueArray=array("call","phone","ring","fetch","get");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="invent") {
			$valueArray=array("invent","create","devise","build","make","construct");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="talk") {
			$valueArray=array("talk","chat","gossip","sing","moan","shout","yell");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="devious") {
			$valueArray=array("devious","cunning","shrewd","brainy","clever","sentient","alive");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="wish") {
			$valueArray=array("wish","dream","pray","hope");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="young") {
			$valueArray=array("young","old","ancient","teenage","youthful","decrepit","modern","broken");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="everybody") {
			$valueArray=array("everybody","no-one");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="everything") {
			$valueArray=array("everything","loads","so much","plenty","quite a lot","incredible amounts");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="discovered") {
			$valueArray=array("discovered","tripped over","stumbled upon","found");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="idea") {
			$valueArray=array("idea","thought","concept");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="loads") {
			$valueArray=array("loads","lots","plenty","everything");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="rubbish") {
			$valueArray=array("amazing","awesome","fantastic","brilliant","wow","cool","tremendous");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="seen") {
			$valueArray=array("seen","read","glimpsed","glanced at");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}    	
    	if ($action=="hello_uc") {
			$valueArray=array("Hello","Hi","Greetings","Bonjour","Salut","Howdy","Hiya");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="hello_lc") {
			$valueArray=array("hello","hi","greetings","bonjour","salut","howdy","hiya");
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
    	if ($action=="sad") {
			$valueArray=array("sad","miserable","weepy","happy","smile","laugh");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="smiley") {
			$valueArray=array(":)",":(",">:(",":D",":O",":P",";)","XO");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
 
		//Now replace the corrected item in the full template, getting rid of the square container brackets along the way
		$rhg=replace_once("[".$action."]",$replacement_array[$i],$rhg);
		$i++;
	}
}

//Pepper the tweet with hashtags
for($i=0;$i<=strlen($rhg);$i++) { //Loop through each character in the string
	if(substr($rhg,$i,1)==" " && substr($rhg,$i+1,1)!="@") {//if the character is a space and the next space isn't an @ (we don't want to hashtag @s)
		if(mt_rand(0,7)==7 && $hashCount<4) { //There's a one in 6 chance that a hashtag will be added
			$rhg=substr_replace($rhg," #",$i,1);
			$hashCount++;
		}
	}
}


//Shorten the tweet (if necessary)
if(strlen($rhg)>140) { $rhg=substr($rhg,140); }

//echo $rhg;

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

function strpos_recursive($haystack, $needle, $offset = 0, &$results = array()) {                
    $offset = strpos($haystack, $needle, $offset);
    if($offset === false) {
        return $results;            
    } else {
        $results[] = $offset;
        return strpos_recursive($haystack, $needle, ($offset + 1), $results);
    }
}

function strposa($haystack, $needles=array(), $offset=0) {
        $chr = array();
        foreach($needles as $needle) {
                $res = strpos($haystack, $needle, $offset);
                if ($res !== false) $chr[$needle] = $res;
        }
        if(empty($chr)) return false;
        return min($chr);
}

function pickRandom($tablename) { //Non-weighed random pick by default
	$result=mysql_query("SELECT pkey FROM $tablename"); //Select all pkeys
	$resultCount=mysql_num_rows($result);
	$randPkey=mt_rand(0,$resultCount-1);
	$result=mysql_query("SELECT firstname,surname FROM $tablename WHERE pkey='$randPkey'"); //select the chosen pkey
    return mysql_fetch_array($result);
}

?>