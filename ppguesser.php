<?php
//DB Vars
$dbhost = 'localhost';
$dbuser = 'dbuser';
$dbpass = 'dbpass';
$dbname = 'dbname';

//Converts Steam32 ID to Steam64 ID
function convert32to64($steam_id)
{
	list( , $m1, $m2) = explode(':', $steam_id, 3);
	list($steam_cid, ) = explode('.', bcadd((((int) $m2 * 2) + $m1), '76561197960265728'), 2);
	return $steam_cid;
}
?>
<!doctype html>
<head>
<title>Private Date Created Guesser</title>
<style type="text/css">
body {font-family: Verdana, Geneva, sans-serif;}
#result {margin-top:10px;}
</style>
</head>
<body>
	<form method="get">
		<input type="text" name="steamid" placeholder="Enter Steam ID" />
		<input type="submit" value="Find" />
	</form>
	<div id="result">
<?php
if(isset($_GET['steamid'])) {
	$steam_id = $_GET['steamid'];
	if(preg_match('/^STEAM_0:[01]:[0-9]+/', $steam_id))
	{
		$steam_id = convert32to64($steam_id);
	}
	elseif(ctype_digit($steam_id) && strlen($steam_id) == 17) {
		$steam_id = $steam_id;
	} else {
		die("Invalid SteamID Input.");
	}
	echo '<a href="http://steamcommunity.com/profiles/'.$steam_id.'">'.$steam_id.'</a><br/>';
	
	
	$pdo = new PDO('mysql:host='.$dbhost.';dbname='.$dbname.';charset=GBK', $dbuser, $dbpass);
	
	//Get the two steam ids closest around the one entered
	$stmt = $pdo->prepare("(select steamid, datecreated, 'upper' as 'bound' from knownids WHERE steamid > ? ORDER BY steamid ASC LIMIT 0,1)
	UNION
	(select steamid, datecreated, 'lower' as 'bound' from knownids WHERE steamid < ? ORDER BY steamid DESC LIMIT 0,1);");
	$stmt->execute(array($steam_id,$steam_id));
	
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	foreach($results as $row) {
		$prefix = ($row['bound'] == 'upper' ? 'Upper Guess: ' : 'Lower Guess: ');
		$fmtVal = date('M d, Y', $row['datecreated']);
		echo $prefix.$fmtVal.' <!-- '.$row['steamid'].' --><br/>';
	}


	
	
}
?>
</div>
</body>
</html>