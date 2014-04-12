<?php

//Script that creates tweets (also used to gradually reduce all popularity values)
echo "Go!";

$popDecrease=5; //The amount by which to decrease a subject's popularity
$popMinimum=10;


require ("include/newsnoysUpdate.inc"); //MySQL connection details stored externally
require ("tmhOAuth/tmhOAuth.php"); //Include the Twitter API library

mysql_query("UPDATE subject SET popularity=popularity-$popDecrease"); //Decrease popularity of all subjects

//Next determine whether or not a tweet is required
if(mt_rand(0,1)==1) { //There is a 1 in 2 chance that a tweet gets created

$hashCount=0;

$row_ex = pickRandom(beginning); $exclamation = $row_ex["beginning"];
$row_na = pickRandom(subject,1); $firstname = $row_na["firstname"]; $surname = $row_na["surname"];
$row_na2 = pickRandom(subject,1); $firstname2 = $row_na2["firstname"]; $surname2 = $row_na2["surname"]; //Might need a second
$row_ac = pickRandom(action); $action = $row_ac["action"];
$row_ob = pickRandom(objects); $object = $row_ob["objects"];

if ($row_na["alive"]=="no") { //If the person is marked as dead
	$has=($row_na["sex"]=="plural"?$has="'s [ghost]s have":$has="'s [ghost] has"); //Has or have
} else { //Else, the person is alive
	$has=($row_na["sex"]=="plural"?$has=" have":$has=" has"); //Has or have
}

if(mt_rand(0,14)==14) { //There is a one in 15 chance of the subject being 'I'
	if(strpos($action,"[add_object]")) { //if the action contains the object call
		$action=str_replace("[add_object]",$object,$action); //And insert the object text into the action
		$rhg=$exclamation." I have".rtrim($action).(mt_rand(0,4)==4?"!":"."); //Use the template without object at the end
	} else { //Otherwise use the standard template
		$rhg=$exclamation." I have".$action." ".$object.(mt_rand(0,4)==4?"!":".");
	}
	$row_na["sex"]="me";
} else {
	if(strpos($action,"[add_object]")) { //if the action contains the object call
		$action=str_replace("[add_object]",$object,$action); //And insert the object text into the action
		$rhg=$exclamation." ".$firstname." ".$surname."".$has."".rtrim($action).(mt_rand(0,4)==4?"!":"."); //Use the template without object at the end
	} else { //Otherwise use the standard template
		$rhg=$exclamation." ".$firstname." ".$surname."".$has."".$action." ".$object.(mt_rand(0,4)==4?"!":".");
	}
}



$actions_array = between($rhg,'[',']'); //Split bracket contained text from the template

//Filter through this new actions array, replacing actions
if ($actions_array) {
	$i=0;
	foreach($actions_array as $action) {
		$replacement_array[$i]="something"; //default entry, in case the code hasn't been written
		$action=inlineChoice($action);	//Run $action through the inlineChoice function
		
		if ($action=="his") {
        	if ($row_na["sex"]=="male") {$replacement_array[$i]="his";} else if ($row_na["sex"]=="female") {$replacement_array[$i]="her";} else if ($row_na["sex"]=="plural") {$replacement_array[$i]="their";} else if ($row_na["sex"]=="me") {$replacement_array[$i]="my";}
		}
		if ($action=="him") {
			if ($row_na["sex"]=="male") {$replacement_array[$i]="him";} else if ($row_na["sex"]=="female") {$replacement_array[$i]="her";} else if ($row_na["sex"]=="plural") {$replacement_array[$i]="them";} else if ($row_na["sex"]=="me") {$replacement_array[$i]="me";}
		}
		if ($action=="himself") {
    	    if ($row_na["sex"]=="male") {$replacement_array[$i]="himself";} else if ($row_na["sex"]=="female") {$replacement_array[$i]="herself";} else if ($row_na["sex"]=="plural") {$replacement_array[$i]="themselves";} else if ($row_na["sex"]=="me") {$replacement_array[$i]="myself";}
      	}
      	if ($action=="he") {
 	       if ($row_na["sex"]=="male") {$replacement_array[$i]="he";} else if ($row_na["sex"]=="female") {$replacement_array[$i]="she";} else if ($row_na["sex"]=="plural") {$replacement_array[$i]="they";} else if ($row_na["sex"]=="me") {$replacement_array[$i]="I";}
      	}
      	if ($action=="spouse") {
 	       if ($row_na["sex"]=="male") {$replacement_array[$i]="wife";} else if ($row_na["sex"]=="female") {$replacement_array[$i]="husband";} else if ($row_na["sex"]=="plural") {$replacement_array[$i]="partners";} else if ($row_na["sex"]=="me") {$replacement_array[$i]="brother";}
      	}
      	if ($action=="man_woman") {
 	       if ($row_na["sex"]=="male") {$replacement_array[$i]="man";} else if ($row_na["sex"]=="female") {$replacement_array[$i]="woman";} else if ($row_na["sex"]=="plural") {$replacement_array[$i]="team";} else if ($row_na["sex"]=="me") {$replacement_array[$i]="person";}
      	}
      	
    	if ($action=="one") {
			$valueArray=array("one","two","three","four","five","six","seven","eight","nine");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="first") {
			$valueArray=array("first","second","third","forth","fifth","sixth","seventh","eighth","ninth");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="single") {
			$valueArray=array("single","double","triple","quadruple");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="better") {
			$valueArray=array("better","worse");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
        if ($action=="number") {
			$valueArray=array("hundred","thousand","million","billion","trillion","gazillion");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="day") {
			$valueArray=array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="time") {
			$valueArray=array("second","minute","hour","day","week","year","decade","aeon");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="man") {
			$valueArray=array("man","woman","child","toddler","infant","baby","foetus","teenager","youth");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="any_vehicle") {
			$valueArray=array("car","monster truck","motorbike","unicycle","van","lorry","JCB","combine harvester","biplane","private jet","helicopter","hang glider","Boeing 747","gyrocopter","biplane","speedboat","jet ski","hovercraft","pedalo","kayak","canoe","cruise ship","submarine");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="land_vehicle") {
			$valueArray=array("car","monster truck","motorbike","unicycle","van","lorry","JCB","combine harvester");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="air_vehicle") {
			$valueArray=array("biplane","private jet","helicopter","hang glider","Boeing 747","gyrocopter","biplane");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="body_part") {
			$valueArray=array("nose","mouth","knees","arms","face","arse","genitals","teeth","legs","feet","hands","ears","armpits","bottom","throat","voice box","larynx","elbows","ear lobes","fingertips","tongue","head","knuckles","fingernails","toenails","kneecaps","shoulders","skin","forehead","lips","tongue","eyeballs");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="body_hole") {
			$valueArray=array("nostril","mouth","anus","eye socket","lung","chest cavity","throat");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="animal") {
			$valueArray=array("dog","budgie","tiger","chimpanzee","lemming","gecko","spider","parrot","hamster","rabbit","gerbil","guinea pig","orangutan","cat","lion","platypus","bear","grizzly bear","chameleon","jellyfish","shark","cod","herring","tuna","kipper","horse","sheep","pig","duck","goose","cow","zebra","elephant","giraffe","porcupine","Pokemon");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="fish") {
			$valueArray=array("jellyfish","shark","cod","herring","tuna","kipper");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="insect") {
			$valueArray=array("flea","cockroach","ant","bug","scorpion","gnat","bedbug","cricket","dustmite","earwig","centipede","millipede","caterpillar","red ant","locust");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="seen") {
			$valueArray=array("seen","spotted","witnessed","photographed","caught on camera","identified","spotted on CCTV","glimpsed","noticed","recorded","filmed");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="mighty") {
			$valueArray=array("mighty","wimpy","hefty","feeble","almighty","powerful","weak");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="disease") {
			$valueArray=array("cancer","AIDS","disintegration","scurvy","disease","decay","rot","tuberculosis","pneumonia","arthritis","flu","hepatitis","germs","blood poisoning","inflation","swelling","withering","hair-loss");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="jumping") {
			$valueArray=array("jumping","leaping","bouncing","tripping","tumbling","falling","plummeting","soaring","levitating","launching");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="jumped") {
			$valueArray=array("jumped","leapt","bounced","tripped","tumbled","fallen","plummeted","soared","levitated","launched");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="eat") {
			$valueArray=array("eat","scoff","swallow","munch","inhale","suck","ingest","gobble","bite");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="shape") {
			$valueArray=array("cigar","saucer","balloon","fruit","pencil","box","banana","ring","teardrop","spherical","cube","double-helix","spiral");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="fruit") {
			$valueArray=array("apple","orange","banana","strawberry","lemon","lime","kiwi","raspberry","blackberry","grape");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="liquid") {
			$valueArray=array("water","Ribena","Vimto","vomit","urine","piss","Pepsi","Coca-Cola","pond water","raw sewage","gravy","rum","Red Bull","lemonade","milkshake","Dr Pepper","acid","oil");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="refreshing") {
			$valueArray=array("refreshing","tasty","delicious","sparkling","cool","bubbling","frothy","zesty");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="delicious") {
			$valueArray=array("delicious","mouth-watering","tasty","foul","godawful","putrid","maggot-ridden","appetising","inedible");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="clothing") {
			$valueArray=array("pair of trousers","pair of underpants","pair of shoes","t-shirt","sweater","pair of socks","kilt","set of clothes","jumper","hairpiece","wig","leather jacket","pair of jeans","thong","swimming costume","bra","pair of knickers","bondage outfit");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="country") {
			$valueArray=array("England","America","Mexico","Africa","India","Germany","France","Afghanistan","Iraq","Libya","Syria","Greece","Pakistan","Lebanon","Israel","Wales","Scotland","Ireland","Brazil","Australia","New Zealand","China","Turkey","Russia","South Korea","Nigeria","Vietnam","Bangladesh","Egypt","Singapore","Chile","Saudi Arabia","Morocco","Poland");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="nationality") {
			$valueArray=array("English","American","Mexican","African","Indian","German","French","Iraqi","Libyan","Syrian","Greek","Lebanese","Israeli","Welsh","Scottish","Irish","Brazilian","Australian","Chinese","Turkish","Russian","South Korean","Nigerian","Vietnamese","Egyptian","Saudi Arabian","Moroccan","Polish");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="city") {
			$valueArray=array("London","Manchester","Aberdeen","Edinburgh","Swansea","Liverpool","Dublin","Paris","Shanghai","Istanbul","Mumbai","Karachi","Moscow","Beijing","Delhi","Tokyo","New York","Lima","Hong Kong","Bangkok","Cairo","Rio","Baghdad","Singapore","Santiago","Saint Petersburg","Johannesburg","Los Angeles","Yokohama","Berlin","Madrid","Nairobi","Las Vegas");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="colour") {
			$valueArray=array("red","yellow","green","orange","pink","brown","white","black","blue","purple","gold","silver","scarlet","mauve","violet","crimson","bronze");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="metal") {
			$valueArray=array("gold","silver","bronze","copper","tin","lead","pewter","alloy","zinc");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="attack") {
			$valueArray=array("attack","punch","kick","smash","splinter","destroy","headbutt","yank","fist","skewer","crunch","maul","slash","demolish");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="today") {
			$valueArray=array("today","yesterday","last week","tomorrow");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="appearance") {
			$valueArray=array("beautiful","lovely","pretty","shapely","gorgeous","really ugly","hideous","deformed","vomit-inducing","wonky","handsome");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="possessions") {
			$valueArray=array("possessions","goods","belongings","worldly goods","furniture","household goods","groceries","electrical appliances","antiques","memories","teenage diaries");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="theme_park") {
			$valueArray=array("Disneyland","Disneyworld","Universal Studios","Euro Disney","Thorpe Park","Chessington","Alton Towers");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="computer_system") {
			$valueArray=array("NES","Gameboy","SNES","N64","Gamecube","Wii","Master System","Game Gear","Mega Drive","Genesis","Dreamcast","PC","Mac","Raspberry Pi","Neo-Geo","electric typewriter","C64","Amiga 500","CD32","Atari ST","Spectrum","Atari Jaguar","Amstrad");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="going_to_the_toilet") {
			$valueArray=array("going to the toilet","urinating","having a piss","draining the snake","having a slash","pissing","having a wee","weeing","peeing","defecating","having a shit","shitting","taking a dump","having a crap","crapping");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="being_sick") {
			$valueArray=array("being sick","vomiting","puking","puking guts","rainbow laughing","barfing","chundering","coughing blood","spluttering","sneezing","projectile vomiting");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="drawing") {
			$valueArray=array("drawing","sketching","pencilling","scribbling","painting","scrawling");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="drawn") {
			$valueArray=array("drawn","sketched","pencilled","scribbled","painted","scrawled");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="verdict") {
			$valueArray=array("guilty","not guilty","innocent");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="cartoon_character") {
			$valueArray=array("Charlie Brown","Felix the Cat","Inspector Gadget","Superted","Ben 10","He-Man","Daffy Duck","Micky Mouse","Donald Duck","Goofy","Mr Magoo","Homer Simpson");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="artist") {
			$valueArray=array("Rothko","Van Gogh","Picasso","Dali","Michelangelo","Mondrian","Da Vinci");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="ghost") {
			$valueArray=array("ghost","phantom","spirit","corpse","skeleton","headstone","soul","spook");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="think") {
			$valueArray=array("think","believe","suspect","presume","guess","suppose","feel","assume","conclude","reckon","imagine","expect","deduce","theorise");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="edited") {
			$valueArray=array("edited","changed","re-written","altered","fiddled with","tampered with","messed around with");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="net_media") {
			$valueArray=array("Wikipedia entry","Facebook profile","Twitter profile","MySpace page","blog","webpage","LinkedIn profile","Facebook timeline","tweet history");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="removing") {
			$valueArray=array("removing","adding","inserting","deleting","purging","creating");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="thinking") {
			$valueArray=array("thinking","pondering","contemplating","guessing","imagining","theorising");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="villain") {
			$valueArray=array("Lord Voldemort","Darth Vadar","a Terminator","Prof. Moriarty","Dr. Hannibal Lecter","HAL 9000","Count Dracula","Freddy Krueger","The Joker","Goldfinger","a Dalek","a Cyberman");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="squash") {
			$valueArray=array("squash","flatten","crush","press","stuff");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="song") {
			$valueArray=array("song","sonata","chiptune","tune","ditty","hymn");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="container") {
			$valueArray=array("vat","jar","tin","box","container","barrel","jam jar","phone box","cupboard","wardrobe","shoebox","case","briefcase","jiffy bag","bag","plastic bag","bin bag","bin","garbage pail","skip");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="torrent") {
			$valueArray=array("torrent","jet","squirt","spurt");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="cup") {
			$valueArray=array("cup","mug","tankard","goblet","glass","pint glass","milk bottle","bottle","bucket","cocktail glass");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="emotion") {
			$valueArray=array("angry","furious","enraged","merciless","rabid","annoyed","irritated","smiling","laughing","happy","joyous","welcoming","waving","cheerful","friendly","overwhelmed","overjoyed","appalled");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="written_work") {
			$valueArray=array("novel","epic poem","poem","play","opera","rock-opera","short story","children's story","blog","website");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="invincible") {
			$valueArray=array("invincible","bulletproof","impenetrable","steel","rock-hard","","super strong","inpenetrable");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
        if ($action=="wonderful") {
			$valueArray=array("wonderful","amazing","glorious","fantastic","awesome","terrible","godawful","excruciating","mindblowing","orgasmic");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
        }
     	if ($action=="victorian") {
			$valueArray=array("Victorian","Edwardian","Elizabethan","Roman","Anglo Saxon","futuristic","Stone Age","Neolithic","prehistoric","really old","brand new");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
     	if ($action=="material") {
			$valueArray=array("wooden","metal","silicone","fibreglass","plastic","alloy","carbon fibre","stone","mud","skin");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
     	if ($action=="cutlery") {
			$valueArray=array("spoon","knife","fork","ladle","spatula","pizza wheel","whisk","soup spoon","steak knife");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
     	if ($action=="white_good") {
			$valueArray=array("cooker","washing machine","dishwasher","fridge","freezer","fridge-freezer","bread maker","tumble dryer");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
     	if ($action=="thrown") {
			$valueArray=array("thrown","tossed","chucked","slam-dunked","bowled","batted","knocked");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
     	if ($action=="police") {
			$valueArray=array("police","FBI","CIA","coast guards","cops","fuzz","traffic wardens","neighbourhood watch","Metropolitan police","army","French foreign legion","navy","air-force","Israeli Defence Force");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="criminal") {
			$valueArray=array("criminal","fiend","mastermind","evil-doer","terrorist","bomber","suicide-bomber","crook","bad guy","bank robber","trickster","pickpocket");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="organ") {
			$valueArray=array("heart","brain","kidney","pancreas","liver","spine","appendix","intestines","stomach");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
        if ($action=="meal") {
			$valueArray=array("breakfast","brunch","lunch","elevenses","dinner");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
        if ($action=="meat_food") {
			$valueArray=array("hotdog","meatball","steak","burger","chipolata","sausage","chicken wing","saveloy","beef burger","bratwurst","pork pie","fatburg");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
        if ($action=="mountain") {
			$valueArray=array("Mount Everest","Mount Vesuvius","Mount Etna","Scafell Pike","Ben Nevis","Kilimanjaro","the Himalayas","the Rocky Mountains","the Alps");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
        if ($action=="god") {
			$valueArray=array("God","Jesus","Christ","Satan","Poseidon","Jehova","Buddha","Zeus","Jupiter","Thor","Hera","Aphrodite","Osiris","Mohammed");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
        if ($action=="currency") {
			$valueArray=array("pounds","dollars","yen","euros");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
        if ($action=="accidentally") {
			$valueArray=array("accidentally","unwittingly","mistakenly","wrongly","foolishly","naughtily","foolhardishly","awkwardly");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
        if ($action=="massive") {
			$valueArray=array("massive","huge","giant","kingsize","gigantic","mahoosive","big","large","microscopic","whopping","normal sized","swollen","enlarged","erect");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
        if ($action=="bag") {
			$valueArray=array("bag","suitcase","briefcase","laundry-sack","bin-bag","bumbag","backpack","wallet","purse","plastic bag","paper bag");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
        if ($action=="odour") {
			$valueArray=array("odour","smell","stench","stink","taste");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
        if ($action=="superhero") {
			$valueArray=array("Superman","Spiderman","Batman","Wolverine","Aquaman","Green Lantern","Captain America","Wonder Woman","The Flash","Iron Man","The Incredible Hulk","Mr Fantastic","The Human Torch","Daredevil","Thor");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
        if ($action=="group") {
			$valueArray=array("the Red Army","the Nazi party","the Labour party","the Tories","the Conservatives","UKIP","the BNP","the skinheads","the rockers","the mods","punk rockers","goths","The Avengers","the Taliban");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
        if ($action=="gang") {
			$valueArray=array("gang","group","pack","band","cult","sect","Internet forum");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="ancient_wonder") {
			$valueArray=array("the Sphinx","the Colosseum","the Parthenon","Stone Henge","Machu Pichu","Easter Island heads","the ruins of Troy","the ruins of Mycenae");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="drug") {
			$valueArray=array("heroin","ecstasy","narcotics","cocaine","cannabis","crystal meth","painkillers","amphetamins","morphine","Night Nurse","Calpol");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="occupation") {
			$valueArray=array("teacher","astronaut","professor","librarian","opera singer","singer","golfer","sportsman","painter","decorator","dancer","plumber","electrician","programmer","designer","manager");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="sport") {
			$valueArray=array("football","soccer","rugby","basketball","baseball","golf","athletics","tennis","badminton","weightlifting","ballet","swimming","syncronised swimming","curling","ice hockey","surfing","inline skating","skateboarding","motorcross");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}	
    	if ($action=="stunt_move") {
			$valueArray=array("backflip","handstand","cartwheel","star-jump","salute","flip","forward roll","lunge","push-up","moonwalk","splits");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}	
    	if ($action=="idea") {
			$valueArray=array("idea","notion","theory","thought","inkling","brainwave");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="pub") {
			$valueArray=array("pub","watering hole","Starbucks","Costa","public house","wine bar","juice joint","smoothie bar","juice bar","Shake Shack","natural spring","watering hole");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="celebration") {
			$valueArray=array("Halloween","Christmas","Hanukkah","Chinese New Year","Easter","Mothers' Day","Fathers' Day");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="damage") {
			$valueArray=array("damage","destroy","repair","fix","smash","crush","punch");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="great") {
			$valueArray=array("great","wonderful","brilliant","fantastic","terrible","awful","shameful","despicable","horrible");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="building") {
			$valueArray=array("house","church","hospital","asylum","block of flats","tenement","looney-bin","apartment block","mansion","palace","hideout","factory","warehouse","clock tower","school");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="investigation") {
			$valueArray=array("investigation","probe");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="claimed") {
			$valueArray=array("claimed","alleged","said","yelled","suggested","hinted");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="imaginary") {
			$valueArray=array("imaginary","made-up","non-existant","pretend");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="up") {
			$valueArray=array("up","down","sideways","backwards","forwards");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="sweets") {
			$valueArray=array("sweets","Smarties","Skittles","Opal Fruits","Munchies","Tic-Tacs","Fruit Pastilles","Mentos","After Eight Mints","Worthers Originals");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="chocolate_bar") {
			$valueArray=array("chocolate bar","Snickers bar","Mars bar","Twix","Wispa bar","Twirl bar","fudge");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="increase") {
			$valueArray=array("increase","decrease");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="price") {
			$valueArray=array("price","cost");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="large") {
			$valueArray=array("big","large","massive","mahoosive","gigantic","small","tiny","little","minuscule");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="supermarket") {
			$valueArray=array("ASDA","Morrisons","Waitrose","Sainsburys","Aldi","Netto","Walmart","Costco");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="planet") {
			$valueArray=array("Mars","Mercury","Venus","Pluto","the Moon","the Sun","a distant planet","the Death Star","Titan","Jupiter");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="grade") {
			$valueArray=array("B-","B+","C-","C+","D","E","F","pass","fail");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
		if ($action=="school_subject") {
			$valueArray=array("English Language","English","Maths","Science","PE","General Studies","Religious Education","IT","Biology","Physics","Chemistry");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="exam") {
			$valueArray=array("GCSE","A-Level","BTEC","O-Level","foundation course","degree course");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="holding") {
			$valueArray=array("holding","grasping","grabbing","squeezing","carrying","wringing","extending","pulling","pushing","strangling","choking","stroking","petting");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="running") {
			$valueArray=array("running","dashing","sprinting","walking","strolling","marching","moonwalking","skating","hopping");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="religion") {
			$valueArray=array("Christianity","Sikhism","Buddhism","Islam","Zoroastrianism","Atheism","Judaism","Mormonism","Paganism","Hinduism");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="antique_item") {
			$valueArray=array("toilet","urinal","bathtub","chest of drawers","TV unit","fireplace","vase","engraving","figurine","coffin");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="landmark") {
			$valueArray=array("Big Ben","the Houses of Parliament","the White House","the Taj Mahal","St Pauls Cathedral","Buckingham Palace","Canterbury Cathedral","Venice","The Statue of Liberty");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="swimming_pool") {
			$valueArray=array("swimming pool","bathtub","paddling pool","pond","jacuzzi","whirlpool","artificial lake","surfing simulator");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="violently") {
			$valueArray=array("violently","angrily","furiously","gently","calmly","softly","visciously","cruely");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="brick") {
			$valueArray=array("brick","breeze block","clay","plasterboard","drywall","straw bale","steel","glass","resin");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="in") {
			$valueArray=array("in","on","at");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="into") {
			$valueArray=array("into","onto","over","all over","past");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="opera") {
			$valueArray=array("Cats","Joseph","Les Miserables","Mamma Mia","Jersey Boys","The Woman in Black","Othello","Macbeth","Romeo & Juliet");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="drunk") {
			$valueArray=array("drunk","slurped","chugged","downed");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="tool") {
			$valueArray=array("hammer","sledge hammer","screwdriver","mallet","axe","chisel","spanner","wrench","saw","hacksaw","nail gun");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="wielding") {
			$valueArray=array("wielding","waving","tossing");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="rusty") {
			$valueArray=array("rusty","decrepit","broken","dusty","greasy");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="faulty") {
			$valueArray=array("faulty","broken","malfunctioning","sparking","smoking","error-prone","defective");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="comedy") {
			$valueArray=array("comedy","tragedy","horror","snuff","porno","zombie","light-hearted","romantic comedy");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
	    if ($action=="film") {
			$valueArray=array("film","motion picture","TV series","documentary","cartoon");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
	    if ($action=="film_series") {
			$valueArray=array("James Bond","Star Wars","Rocky","Rambo","Indiana Jones","Carry On","Harry Potter","Twilight","Hunger Games");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
	    if ($action=="weapon") {
			$valueArray=array("weapon","gun","machine-gun","crossbow","taser","pop-gun","spud gun","sword","samurai sword","ninja-star","lead pipe","armoured vehicle","body armour","light-saber");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
	    if ($action=="tickling") {
			$valueArray=array("tickling","fingering","itching","scratching","stroking","playing with","touching");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="greek_letter") {
			$valueArray=array("alpha","beta","gamma","delta","epsilon","zeta","lambda","omicron","sigma","omega");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="bodily_fluid") {
			$valueArray=array("vomit","sick","puke","shit","faeces","poop","urine","wee","piss","blood","semen","drool","saliva","bile");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="disability") {
			$valueArray=array("disabled","blind","deaf","mute","wheelchair-bound","paralysed","deformed");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="war") {
			$valueArray=array("World War 1","World War 2","Vietnam War","Iraq War","Crimean War","American Revolutionary War","Seven Years' War","Boer War","Inca Civil War","Peloponnesian War","First Barbary War","Peninsular War","Neapolitan War","First Opium War","Balkan War");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="tv_show") {
			$valueArray=array("Gavin and Stacey","Red Dwarf","The Brittas Empire","Allo Allo","Dad's Army","Keeping Up Appearances","The Good Life","Rising Damp","Friends","How I Met Your Mother","Two Broke Girls","The Big Bang Theory","Sesame Street","Frasier","Cheers");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="racism") {
			$valueArray=array("racism","sexism","bigotry","homosexuality","hetrosexuality","nastiness","religion","silliness");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="value") {
			$valueArray=array("high-end","valuable","cheap","budget","stolen","state of the art","crappy","brand-new","second hand");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="board_game") {
			$valueArray=array("Monopoly","Pictionary","Twister","Mouse Trap","Draw Something","Snakes and Ladders","Cluedo","the Game of Life","Guess Who","Battleships","Scrabble","chess","drafts","Trivial Pursuit","Apple Picker","Boggle","Operation","Dungeons and Dragons","Hungry Hippos","Pop-Up Pirate");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="kitchen_brand") {
			$valueArray=array("Tefal","Philips","Le Cruset","Morphy Richards","Pampered Chef","Cook's","Russell Hobbs","Jamie Oliver","George Foreman");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="random_kitchen_app") {
    		$valueArray1=array("Acti","Super","Maxi","Multi","Hyper","Combi","Healthi","Tasti","Infini");
			$valueArray2=array("fry","boil","toast","grill","steam");
			$replacement_array[$i]=$valueArray1[mt_rand(0,count($valueArray1)-1)].$valueArray2[mt_rand(0,count($valueArray2)-1)];
    	}
    	if ($action=="soccer_team") {
			$valueArray=array("Man Utd","Man City","Tottenham","Chelsea","Everton","Arsenal","West Brom","Liverpool","Swansea","Stoke","West Ham Utd","Norwich","Fulham","Sunderland","Newcastle","Aston Villa","Southampton","Wigan","Reading","QPR","Cardiff","Hull","Leicester","Watford","Leeds Utd","Brighton","Blackpool","Bolton","Ipswich","Peterborough","Barnsley","Tranmere","Swindon","Crawley","Crewe","Shrewsbury","Carlisle","Oldham","Bury","Leyton Orient","Gillingham","Port Vale","Fleetwood","Burton Albion","York","Accrington Stanley","Wimbledon","Grimsby","Forest Green","Luton","Dartford","Hyde","Woking","Tamworth","Braintree","Ebbsfleet","Barrow","Brackley","Gainsborough","FC Halifax","Stalybridge","Solihull Moors","Corby","Colwyn Bay","Kinckley Utd","Salisbury","Dover","Chelmsford","Staines Town","Sutton Utd","Billericay","Basingstoke","Eastleigh","Truro City");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="newspaper") {
			$valueArray=array("the Sun","the Daily Mail","the TES","the Guardian","the Sunday Times","the National Enquirer","the Sunday Sport","ITN","the BBC","ITV","Channel 4","Fox News");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="shocking") {
			$valueArray=array("shocking","scandalous","jaw-dropping","unbelievable","mind-numbing","incredible","mind-blowing","crazy","made-up","true","possibly true","hilarious");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="crime") {
			$valueArray=array("abduction","murder","sex crimes","perjury","war crimes","extortion","arson","assault","piracy","torture","theft","hooliganism‎","hate crimes","terrorism","fraud");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="monarch") {
    		$valueArray=array("King Henry VIII","King Richard III","Queen Elizabeth I","Henry II","Queen Victoria","Prince Albert","the Queen Mother","Mary Queen of Scots","Diana, Princess of Wales","Hakka-Bukka","Alfred the Great","Edward VII");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
    	if ($action=="singing") {
    		$valueArray=array("Singing","Yelling","Looking","Scouting","Calling","Jumping","Longing","Praying","Tears","Weeping","Searching","Fishing");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
        if ($action=="mad") {
    		$valueArray=array("mad","crazy","looney","nutty","schizophrenic","loopy","silly","foolish");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
        if ($action=="blown") {
    		$valueArray=array("blown","sucked","pulled","washed","swept");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}
        if ($action=="weather_feature") {
    		$valueArray=array("cyclone","hurricane","tornado","whirlpool","cloud");
			$replacement_array[$i]=$valueArray[mt_rand(0,count($valueArray)-1)];
    	}

		if ($action=="has") {
			if ($row_na["sex"]=="plural") {$replacement_array[$i]="have";} else {$replacement_array[$i]="has";}
		}
		if ($action=="is") {
			if ($row_na["sex"]=="plural") {$replacement_array[$i]="are";} else {$replacement_array[$i]="is";}
		}
		if ($action=="does") {
			if ($row_na["sex"]=="plural") {$replacement_array[$i]="do";} else {$replacement_array[$i]="does";}
		}
		if ($action=="was") {
			if ($row_na["sex"]=="plural") {$replacement_array[$i]="were";} else {$replacement_array[$i]="was";}
		}
		
		if ($action=="random_subject") { //In this case, use the second random subject
			$replacement_array[$i]=($surname2!=""?$firstname2." ".$surname2:$firstname2);
    	}

		//Now replace the corrected item in the full template, getting rid of the square container brackets along the way
		$rhg=replace_once("[".$action."]",$replacement_array[$i],$rhg);
		$i++;
	}
}

//Pepper the tweet with hashtags
for($i=0;$i<=strlen($rhg);$i++) { //Loop through each character in the string
	if(substr($rhg,$i,1)==" ") {//if the character is a space
		if(mt_rand(0,7)==7 && $hashCount<4) { //There's a one in 6 chance that a hashtag will be added
			$rhg=substr_replace($rhg," #",$i,1);
			$hashCount++;
		}
	}
}


//Shorten the tweet (if necessary)
if(strlen($rhg)>140) { $rhg=substr($rhg,0,137)."..."; }

//Post the tweet
$tmhOAuth = new tmhOAuth(array(
	'consumer_key' => 'ZDXRb0x6Zm93KBP7FD5Q',
    'consumer_secret' => 'WKJnqAeemsHXOa5PSkgLFzhCouw2zmzwihCMUOYfsQY',
    'user_token' => '859966680-zeJD0N0Wk3aeC97GaRdadmluYeGyM8pTeV5X0ACn',
    'user_secret' => 'fLfy0s36Jpjf1MfwZ9mtBpHobGBRwvkBzEHRl7u9iI',
));

$code = $tmhOAuth->request('POST', $tmhOAuth->url('1.1/statuses/update'), array(
  'status' => $rhg
));

mysql_query("UPDATE subject SET popularity=popularity/2 WHERE firstname=$firstname AND surname=$surname"); //Halve the popularity of the subject so another tweet about them isn't immediately likely

} //End 1 in 2 chance of a tweet being written

mysql_query("UPDATE subject SET popularity=$popMinimum WHERE popularity<$popMinimum"); //Finally, bump popularity back up to minimum value if less

//FUNCTIONS---

function weightedRandom($values,$weights) {
	$count = count($values);
	$i = 0;
    $n = 0;
    $num = mt_rand(0, array_sum($weights));
    while($i < $count){
    	$n += $weights[$i];
        if($n >= $num){
            break;
        }
        $i++;
    }
    if($i==0) { $i=1; } //added in case 0 is picked (which would have become -1, see below)
    return $values[$i-1]; //added the -1 because the random number picker seems to choose the row AFTER the one we want
}
 
function pickRandom($tablename,$weighted=0) { //Non-weighed random pick by default
	if($weighted==0) {
		$result=mysql_query("SELECT pkey FROM $tablename"); //Select all pkeys
		$resultCount=mysql_num_rows($result);
		$randPkey=mt_rand(0,$resultCount-1);
		$result=mysql_query("SELECT $tablename FROM $tablename WHERE pkey='$randPkey'"); //select the chosen pkey
	} else {
		$result=mysql_query("SELECT pkey FROM $tablename"); while($row = mysql_fetch_array($result)) { $pkeyArray[]=$row['pkey']; }
		$result=mysql_query("SELECT popularity FROM $tablename"); while($row = mysql_fetch_array($result)) { $popularityArray[]=$row['popularity']; }
		$randPkey=weightedRandom($pkeyArray,$popularityArray);
		$result=mysql_query("SELECT * FROM $tablename WHERE pkey='$randPkey'"); //select the chosen pkey
	}
    return mysql_fetch_array($result);
}

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