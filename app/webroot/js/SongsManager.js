function SongsManager(baseurl, version) {
    const DB_NAME = 'songs-db';
    const DB_VERSION = 1;
    const DB_STORE_NAME = 'songs';

    let self = this;
    let db;
    let onDBReady = [];
    let songs = [];
    let playlist = [];

    openDB(function(){
        if (+version > +localStorage.getItem('sync_token')) {
            self.sync(+version);
        }
    });

    function openDB(callback) {
        let req = indexedDB.open(DB_NAME, DB_VERSION);
        req.onsuccess = function () {
            db = this.result;
            for (let i = 0; i < onDBReady.length; i++) {
                onDBReady[i]();
            }
            let store = getObjectStore(DB_STORE_NAME, 'readonly');
            store.openCursor().onsuccess = function (e) {
                if (e.target.result) {
                    songs = e.target.result.value;
                }
            };
            callback();
        };
        req.onerror = function (e) {
            console.log('indexedDB error : ' + e.target.errorCode);
        };
        req.onupgradeneeded = function (e) {
            e.target.result.createObjectStore(DB_STORE_NAME, { autoIncrement: true });
        };
    }

    function albumSort(a, b) {
        if (a.album > b.album) {
            return 1;
        } else if (a.album < b.album) {
            return -1;
        }

        var aDics = a.disc === null ? 0 : +a.disc.split('/')[0];
        var bDics = b.disc === null ? 0 : +b.disc.split('/')[0];

        if (aDics > bDics) {
            return 1;
        } else if (aDics < bDics) {
            return -1;
        }

        if (+a.track_number > +b.track_number) {
            return 1;
        }

        if (+a.track_number < +b.track_number) {
            return -1;
        }

        return 0;
    }

    function bandSort(a, b) {
        if (a.band > b.band) {
            return 1;
        } else if (a.band < b.band) {
            return -1;
        }

        return albumSort(a, b);
    }

    this.addOnDBReadyListener = function(callback) {
        onDBReady.push(callback);
    };

    this.isOpen = function() {
        return db !== null;
    };

    var getObjectStore = function(storeName, mode) {
        let tx = db.transaction(storeName, mode);
        return tx.objectStore(storeName);
    };

    var clearObjectStore = function(storeName) {
        let store = getObjectStore(storeName, 'readwrite');
        store.clear();
    };

    var addSongs = function(songs) {
        let store = getObjectStore(DB_STORE_NAME, 'readwrite');
        store.add(songs);
    };

    this.getAllSongs = function() {
        return songs.sort(bandSort);
    };

    this.getAllAlbumSongs = function() {
        return songs.sort(albumSort);
    };

    this.getSong = function(id) {
        for (let i = 0; i < songs.length; i++) {
            if(songs[i].id == id){
                return songs[i];
            }
        }
    };

    this.getPlaylistAllSongs = function() {
        return playlist;
    };

    this.getBandSongs = function(band) {
        let bands = [];
        for (let i = 0; i < songs.length; i++) {
            if(songs[i].band == band) {
                bands.push(songs[i]);
            }
        }
        return bands;
    };

    this.getFirstbandSong = function(band) {
        for (let i = 0; i < songs.length; i++) {
            if(songs[i].band == band) {
                return songs[i];
            }
        }
    };

    this.getAlbumSongs = function(band, album) {
        let albums = [];
        for (let i = 0; i < songs.length; i++) {
            if(songs[i].band == band && songs[i].album == album) {
                albums.push(songs[i]);
            }
        }
        return albums;
    };

    this.getFirstAlbumSong = function(band, album) {
        for (let i = 0; i < songs.length; i++) {
            if(songs[i].band == band && songs[i].album == album) {
                return songs[i];
            }
        }
    };

    this.sync = function(syncToken) {
        console.time('sync');
        clearObjectStore(DB_STORE_NAME);
        let xhr = new XMLHttpRequest();
        xhr.open('GET', baseurl + '/tracks/sync');
        xhr.onload = function() {
            let json = JSON.parse(xhr.response);
            addSongs(json);
            songs = json;
            localStorage.setItem('sync_token', syncToken);
            console.timeEnd('sync');
        };
        xhr.send();
    };
    this.setPlaylist = function(songs) {
        playlist = songs;
    };
}