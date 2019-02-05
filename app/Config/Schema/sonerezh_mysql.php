<?php 
class SonerezhMysqlSchema extends CakeSchema {

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $albums = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'cover' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 37, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'band_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'year' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 4, 'unsigned' => false),
		'created' => array('type' => 'timestamp', 'null' => false, 'default' => null),
		'updated' => array('type' => 'timestamp', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'albums_bands_id_fk' => array('column' => 'band_id', 'unique' => 0),
			'albums_created_index' => array('unique' => false, 'column' => 'created'),
			'albums_name_index' => array('column' => 'name', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci', 'engine' => 'InnoDB')
	);

	public $bands = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'created' => array('type' => 'timestamp', 'null' => false, 'default' => null),
		'updated' => array('type' => 'timestamp', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'bands_name_index' => array('column' => 'name', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci', 'engine' => 'InnoDB')
	);

	public $playlist_memberships = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 8, 'unsigned' => true, 'key' => 'primary'),
		'playlist_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 8, 'unsigned' => true),
		'song_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 8, 'unsigned' => true),
		'sort' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 8, 'unsigned' => true),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci', 'engine' => 'InnoDB')
	);

	public $playlists = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 8, 'unsigned' => true, 'key' => 'primary'),
		'title' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 5, 'unsigned' => true),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci', 'engine' => 'InnoDB')
	);

	public $rootpaths = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'setting_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'rootpath' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1024, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci', 'engine' => 'InnoDB')
	);

	public $settings = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 3, 'unsigned' => true, 'key' => 'primary'),
		'enable_auto_conv' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'convert_from' => array('type' => 'string', 'null' => false, 'default' => 'aac,flac', 'length' => 25, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'convert_to' => array('type' => 'string', 'null' => false, 'default' => 'mp3', 'length' => 5, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'quality' => array('type' => 'integer', 'null' => false, 'default' => '256', 'length' => 3, 'unsigned' => true),
		'enable_mail_notification' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'sync_token' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci', 'engine' => 'InnoDB')
	);

	public $songs = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 8, 'unsigned' => true, 'key' => 'primary'),
		'title' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'album' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'artist' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'source_path' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 1024, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'path' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 1024, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'cover' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'playtime' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 9, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'track_number' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 5, 'unsigned' => true),
		'year' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 4, 'unsigned' => true),
		'disc' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 7, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'band' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'genre' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'ix_songs_album' => array('column' => 'album', 'unique' => 0),
			'ix_songs_band' => array('column' => 'band', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci', 'engine' => 'InnoDB')
	);

	public $tracks = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'title' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'source_path' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 4096, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'playtime' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 9, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'track_number' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 5, 'unsigned' => false),
		'max_track_number' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 5, 'unsigned' => false),
		'disc_number' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 7, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'max_disc_number' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 5, 'unsigned' => false),
		'year' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 4, 'unsigned' => false),
		'genre' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'artist' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'created' => array('type' => 'timestamp', 'null' => false, 'default' => null),
		'album_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'updated' => array('type' => 'timestamp', 'null' => true, 'default' => null),
		'imported' => array('type' => 'boolean', 'null' => true, 'default' => '1', 'key' => 'index'),
        'path' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 4096),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'tracks_albums_id_fk' => array('column' => 'album_id', 'unique' => 0),
			'tracks_title_index' => array('column' => 'title', 'unique' => 0),
			'tracks_artist_index' => array('column' => 'artist', 'unique' => 0),
			'tracks_genre_index' => array('column' => 'genre', 'unique' => 0),
			'tracks_imported_index' => array('column' => 'imported', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci', 'engine' => 'InnoDB')
	);

	public $users = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 5, 'unsigned' => true, 'key' => 'primary'),
		'email' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'password' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'role' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 15, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'avatar' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'preferences' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8mb4', 'collate' => 'utf8mb4_unicode_ci', 'engine' => 'InnoDB')
	);

}
