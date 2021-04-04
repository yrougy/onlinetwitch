<?php
date_default_timezone_set('UTC');
$client_id = 'xyz123';
$client_secret = 'qwerty678';
$users = array(
	"Yorzian",
	"codemiko",
	"lara6683"
);

function GetAuthToken($client_id,$client_secret)
{
	//On a besoin du token oAuth2 pour interroger l'API
	$oauthurl = "https://id.twitch.tv/oauth2/token";
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, "https://id.twitch.tv/oauth2/token?client_id=$client_id&client_secret=$client_secret&grant_type=client_credentials");
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_POST, true);
	$reponse = curl_exec($c);
	$retour = json_decode($reponse, true);
	curl_close($c);
	$oauthtoken = $retour['access_token'];
	return $oauthtoken;
}

function GetUserInfo($users,$oauthtoken,$client_id)
{
	//Le token est dans $oauthtoken
	//Il faut l'utiliser dans les headers des requêtes
	$nombreUsers = count($users);
	$i = 1;
	$url="https://api.twitch.tv/helix/streams?user_login=$users[0]";
	//echo $url;
	while ($i < $nombreUsers)
	{
		$url=$url."&user_login=".$users[$i];
		$i++;
	}
	//echo "url: ".$url;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Client-ID: $client_id",
		"Authorization: Bearer $oauthtoken"
	));
	//print (curl_exec($ch));
	$retour = curl_exec($ch);
	$profile_data = json_decode($retour, true);
	//echo $retour."<br/>";
	curl_close($ch);
	$nombreOnline = count($profile_data['data']);
	$i = 0;
	while ( $i < $nombreOnline )
	{
		$user = $profile_data['data'][$i]['user_name'];
		$title = $profile_data['data'][$i]['title'];
		$viewer_count = $profile_data['data'][$i]['viewer_count'];
		$went_live_at = DateTime::createFromFormat("Y-m-d H:i:s", date("Y-m-d H:i:s", strtotime($profile_data['data'][$i]['started_at'])))->format('Y-m-d H:i:s');
		$started = date_create($went_live_at);
		$now = date_create(date('Y-m-d H:i:s'));
		$diff = date_diff($started, $now);
		$hours = $diff->h;
		$minutes = $diff->i;
	
		echo "<h2>$user</h2><a href=https://twitch.tv/$user>En ligne</a> depuis $hours h $minutes min avec $viewer_count viewers<br/> $title";
		$i++;
	}
}

//Déjà on prend le token pour faire les requetes
$token=GetAuthToken($client_id,$client_secret);
$nombreUsers = count($users);
$i = 0;
$enligne = 0;
echo GetUserInfo($users,$token,$client_id);
