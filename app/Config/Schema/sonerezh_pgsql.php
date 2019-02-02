<?php 
class SonerezhPgsqlSchema extends CakeSchema {

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $albums = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null),
		'cover' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 37),
		'band_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'year' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'updated' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'albums_bands_id_fk' => array('unique' => false, 'column' => 'band_id'),
			'albums_created_index' => array('unique' => false, 'column' => 'created'),
			'albums_name_index' => array('unique' => false, 'column' => 'name')
		),
		'tableParameters' => array()
	);

	public $bands = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'updated' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'bands_name_index' => array('unique' => false, 'column' => 'name')
		),
		'tableParameters' => array()
	);

	public $playlist_memberships = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'playlist_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'song_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'sort' => array('type' => 'integer', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $playlists = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'title' => array('type' => 'string', 'null' => false, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $rootpaths = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'setting_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'rootpath' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1024),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $settings = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'enable_auto_conv' => array('type' => 'boolean', 'null' => false, 'default' => false),
		'convert_from' => array('type' => 'string', 'null' => false, 'default' => 'aac,flac', 'length' => 25),
		'convert_to' => array('type' => 'string', 'null' => false, 'default' => 'mp3', 'length' => 5),
		'quality' => array('type' => 'integer', 'null' => false, 'default' => '256'),
		'enable_mail_notification' => array('type' => 'boolean', 'null' => false, 'default' => false),
		'sync_token' => array('type' => 'integer', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

	public $songs = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'title' => array('type' => 'string', 'null' => false, 'default' => null),
		'album' => array('type' => 'string', 'null' => false, 'default' => null),
		'artist' => array('type' => 'string', 'null' => false, 'default' => null),
		'source_path' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1024),
		'path' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 1024),
		'cover' => array('type' => 'string', 'null' => true, 'default' => null),
		'playtime' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 9),
		'track_number' => array('type' => 'integer', 'null' => true, 'default' => null),
		'year' => array('type' => 'integer', 'null' => true, 'default' => null),
		'disc' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 7),
		'band' => array('type' => 'string', 'null' => true, 'default' => null),
		'genre' => array('type' => 'string', 'null' => true, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'ix_songs_album' => array('unique' => false, 'column' => 'album'),
			'ix_songs_band' => array('unique' => false, 'column' => 'band')
		),
		'tableParameters' => array()
	);

	public $tracks = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'title' => array('type' => 'string', 'null' => true, 'default' => null),
		'source_path' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 4096),
		'playtime' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 9),
		'track_number' => array('type' => 'integer', 'null' => true, 'default' => null),
		'max_track_number' => array('type' => 'integer', 'null' => true, 'default' => null),
		'disc_number' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 7),
		'max_disc_number' => array('type' => 'integer', 'null' => true, 'default' => null),
		'year' => array('type' => 'integer', 'null' => true, 'default' => null),
		'genre' => array('type' => 'string', 'null' => true, 'default' => null),
		'artist' => array('type' => 'string', 'null' => true, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => 'CURRENT_TIMESTAMP'),
		'album_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'updated' => array('type' => 'datetime', 'null' => true, 'default' => 'CURRENT_TIMESTAMP'),
		'imported' => array('type' => 'boolean', 'null' => true, 'default' => true),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id'),
			'tracks_albums_id_fk' => array('unique' => false, 'column' => 'album_id'),
			'tracks_artist_index' => array('unique' => false, 'column' => 'artist'),
			'tracks_genre_index' => array('unique' => false, 'column' => 'genre'),
			'tracks_imported_index' => array('unique' => false, 'column' => 'imported'),
			'tracks_title_index' => array('unique' => false, 'column' => 'title')
		),
		'tableParameters' => array()
	);

	public $users = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
		'email' => array('type' => 'string', 'null' => false, 'default' => null),
		'password' => array('type' => 'string', 'null' => false, 'default' => null),
		'role' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 15),
		'avatar' => array('type' => 'string', 'null' => true, 'default' => null),
		'preferences' => array('type' => 'text', 'null' => true, 'default' => null, 'length' => 1073741824),
		'indexes' => array(
			'PRIMARY' => array('unique' => true, 'column' => 'id')
		),
		'tableParameters' => array()
	);

}
