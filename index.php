<?php

require 'vendor/autoload.php';
require 'kernel/require.php';
require 'modules/require.php';
require 'controller/require.php';

if (empty($_GET['url']))
	$_GET['url'] = '/';

//*********************** init test ************************//

$isCLI = ( php_sapi_name() == 'cli' );

if ($isCLI) {
	
	echo "Tests base : \n\n";

	$test = new Test();
	$test->setBackTest("rooms_route_without_connexion_0", "POST", "update_params", json_encode(["status" => 404, "response" => "not rights", "text" => ""]), [], "");
	$test->setBackTest("rooms_route_without_connexion_1", "POST", "update_loc", json_encode(["status" => 404, "response" => "not rights", "text" => ""]), [], "");
	$test->setBackTest("rooms_route_without_connexion_2", "POST", "leave_user", json_encode(["status" => 404, "response" => "not rights", "text" => ""]), [], "");
	$test->setBackTest("rooms_route_without_connexion_3", "POST", "user_can_stay", json_encode(["status" => 404, "response" => "not rights", "text" => ""]), [], "");
	$test->setBackTest("rooms_route_without_connexion_4", "POST", "user_delete", json_encode(["status" => 404, "response" => "not rights", "text" => ""]), [], "");
	$test->setBackTest("rooms_route_without_connexion_5", "POST", "user_add", json_encode(["status" => 404, "response" => "not rights", "text" => ""]), [], "");
	$test->setBackTest("rooms_route_without_connexion_6", "POST", "user_update", json_encode(["status" => 404, "response" => "not rights", "text" => ""]), [], "");

	$test->setBackTest("rigths_route_without_connexion_7", "POST", "modify_rights", json_encode(["status" => 404, "response" => "not rights"]), [], "");
	$test->setBackTest("rigths_route_without_connexion_8", "POST", "get_sound_rights", json_encode(["status" => 404, "response" => "not rights"]), [], "");
	$test->setBackTest("rigths_route_without_connexion_9", "POST", "get_playlist_edit_rights", json_encode(["status" => 404, "response" => "not rights"]), [], "");

	$test->setBackTest("chat_route_without_connexion_0", "POST", "chat", json_encode(["status" => 404, "response" => "not rights", "name_song" => ""]), [], "");
	$test->setBackTest("chat_route_without_connexion_1", "POST", "get_chat", json_encode(["status" => 404, "response" => "not rights", "name_song" => ""]), [], "");
	$test->setBackTest("chat_route_without_connexion_2", "POST", "refresh_vote", json_encode(["status" => 404, "response" => "not rights", "name_song" => ""]), [], "");

	$test->setBackTest("playlist_route_without_connexion_0", "POST", "playlistadd", json_encode(["status" => 404, "response" => "not rights"]), [], "");
	$test->setBackTest("playlist_route_without_connexion_1", "POST", "playlistiscreated", json_encode(["status" => 404, "response" => "not rights"]), [], "");
	$test->setBackTest("playlist_route_without_connexion_2", "POST", "playlistchange", json_encode(["status" => 404, "response" => "not rights"]), [], "");

	$test->setBackTest("mcdo_route_without_connexion_0", "POST", "mcdo", json_encode(["status" => 404, "response" => "not rights", "text" => ""]), [], "");
	$test->setBackTest("mcdo_route_without_connexion_1", "POST", "mcdo_comm_add", json_encode(["status" => 404, "response" => "not rights", "text" => ""]), [], "");
	$test->setBackTest("mcdo_route_without_connexion_2", "POST", "mcdo_comm_send", json_encode(["status" => 404, "response" => "not rights", "text" => ""]), [], "");

	$test->setBackTest("lyrics_route_without_connexion_0", "POST", "music_lyrics", json_encode(["status" => 404, "response" => "not rights"]), [], "");

	$test->setBackTest("user_route_without_connexion_0", "POST", "/user/music_add", json_encode(["status" => 404, "response" => "not rights", "id" => ""]), [], "");
	$test->setBackTest("user_route_without_connexion_0", "POST", "/user/user_show", json_encode(["status" => 404, "response" => "not rights", "id" => ""]), [], "");
	$test->setBackTest("user_route_without_connexion_0", "POST", "/user/add_friend", json_encode(["status" => 404, "response" => "not rights", "id" => ""]), [], "");
	$test->setBackTest("user_route_without_connexion_0", "POST", "/user/update_image", json_encode(["status" => 404, "response" => "not rights", "id" => ""]), [], "");

	$test->run();
	return;
}

//*********************** init dir *************************//

$dir = new Directorys([
	"var" => [
		"api_cache",
		"fail2ban",
		"logs",
		"session"
	]
]);
$dir->run();

//********************* secu *******************************//

$fail2ban = new Fail2ban([

	"brut_force" => [
		"jail_time" => 2, // minutes
		"lookup_logs" => "entretrypoint.log",
		"max_try" => 100 // secondes
	],
]);
$banned = $fail2ban->checkLogs("brut_force");
if ($banned["status"] === "true") {
	echo json_encode([
		"status" => 401,
		"response" => "banned"
	]);
	return;
}
//******************** init *********************************//

$routeur = new Router($_GET['url']);

//********************** web *******************************//

$routeur->get('/', 'home#show', "home");
$routeur->get('/prime', 'prime#show', 'prime');
$routeur->get('/prime_payed', 'prime#payed', 'prime_payed');
$routeur->get('/room/:id', 'rooms#show', 'rooms');
$routeur->get('/user/:id', 'UsersShow#show', 'users');
$routeur->get('/deco', 'home#deco', "deconnexion");
$routeur->get('/valid_user/:token', 'login_out#valid', 'validation');
$routeur->get('/google_connexion', 'Google#connexion', 'google_connexion');
$routeur->get('/google_attach', 'Google#attach', 'google_attach');
$routeur->get('/facebook_connexion', 'Facebook#connexion', 'facebook_connexion');
$routeur->get('/facebook_attach', 'Facebook#attach', 'facebook_attach');
$routeur->get('/reset_mdp/:token', 'Reset#mdpForgot', 'reset_mdp');

$routeur->post('/deezer_connexion', 'Deezer#connexion', 'deezer_connexion');
$routeur->post('/deezer_attach', 'Deezer#attach', 'deezer_attach');

$routeur->post('/connect', 'login_out#connect', 'connect');

$routeur->post('/inscription_form', 'login_out#inscription_form', 'inscription_form');
$routeur->post('/inscription', 'login_out#inscription', 'inscription');

$routeur->post('/go_paye', 'prime#go_paye', 'go_paye');

$routeur->post('/check_rooms', 'checkRooms#dispatch', 'check_rooms');
$routeur->post('/rooms_create', 'checkRooms#create', 'add_rooms');

$routeur->post('/update_params', 'Rooms#room_change', 'update_params');
$routeur->post('/update_loc', 'Rooms#getLoc', 'update_loc');
$routeur->post('/leave_user', 'Rooms#leaveUser', 'leave_user');
$routeur->post('/user_can_stay', 'Rooms#IsInRoom', 'user_can_stay');
$routeur->post('/user_delete', 'Rooms#RemoveUser', 'user_delete');
$routeur->post('/user_add', 'Rooms#Invite', 'user_add');
$routeur->post('/user_update', 'Rooms#updateUsers', 'user_update');

$routeur->post('/modify_rights', 'rights#ModifyRights', 'modify_rights');
$routeur->post('/get_sound_rights', 'rights#SoundRights', 'get_sound_rights');
$routeur->post('/get_playlist_edit_rights', 'rights#PlaylistRights', 'get_playlist_edit_rights');

$routeur->post('/chat', 'chat#message', 'chat_message');
$routeur->post('/get_chat', 'chat#getChat', 'chat_getChat');
$routeur->post('/refresh_vote', 'chat#refreshVote', 'refresh_vote');

$routeur->post('/api_cache', 'api_cache#save', 'api_cache_save');
$routeur->post('/api_cache_get', 'api_cache#get', 'api_cache_get');

$routeur->post('/playlistadd', 'playlist#add', 'playlist_add');
$routeur->post('/playlistiscreated', 'playlist#iscreated', 'playlist_iscreated');
$routeur->post('/playlistchange', 'playlist#modify', 'playlist_change');

$routeur->post('/music_lyrics', 'lyrics#search', 'music_lyrics_search');
$routeur->post('/mcdo', 'mcdo#getCateg', "mcdo_categ");
$routeur->post('/mcdo_comm_add', 'mcdo#add', "mcdo_comm_add");
$routeur->post('/mcdo_comm_send', 'mcdo#sendCommande', "mcdo_comm_send");

$routeur->post('/user/music_add', 'UsersShow#addMusic', "add_music_user");
$routeur->post('/user/user_show', 'UsersShow#ModifyPerms', "user_show");
$routeur->post('/user/add_friend', 'UsersShow#AddFriend', "user_friend");
$routeur->post('/user/update_image', 'UsersShow#UpdateImg', "user_img");

$routeur->post('/update_mdp', 'Reset#mdpChange', "user_mdp");

//**************************** api ******************************//

$routeur->get('/api', 'api#home', 'home_api');
$routeur->post('/api_token', 'api#getToken', 'home_api_token');
$routeur->post('/api_token_get', 'api#getMyToken', 'home_api_token_get');

$routeur->get('/api/lyrics/all', 'lyricsApi#getAll', 'get_all_lyrics');
$routeur->get('/api/lyrics/get_by_name/:name', 'lyricsApi#getByName', 'get_lyrics_by_name');

$routeur->get('/api/mcdo/all', 'mcdoApi#getAll', 'get_all_mcdo');
$routeur->get('/api/mcdo/categ/:categorie', 'mcdoApi#getByCateg', 'get_mcdo_by_categ');

$routeur->get('/api/music/all', 'Music#getAll', 'get_all_music');
$routeur->get('/api/music/artist/:name', 'Music#getByArtist', 'get_music_by_artist');
$routeur->get('/api/music/id/:id', 'Music#getById', 'get_music_by_id');
$routeur->get('/api/music/title/:title', 'Music#getByTitle', 'get_music_by_title');
$routeur->get('/api/music/statistic', 'Music#getStatistic', 'get_statistic');

//******************************** sessions ****************************//
$sessions = new SessionSecu();
if ($sessions->isStoll()) {
	header("Location:/music_room/deco");
}
$routeur->run();
?>