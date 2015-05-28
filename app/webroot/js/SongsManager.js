function SongsManager(baseurl, version) {
    const DB_NAME = "songs-db";
    const DB_VERSION = 1;
    const DB_STORE_NAME = "songs";
    const DB_PLAYLIST_STORE_NAME = "playlists";

    var self = this;
    var db;
    var onDBReady = [];

    openDB(function(){
        if(+version > +localStorage.getItem("sync_token")) {
            self.sync(+version);
        }
    });

    function openDB(callback) {
        var req = indexedDB.open(DB_NAME, DB_VERSION);
        req.onsuccess = function () {
            db = this.result;
            for(var i = 0; i < onDBReady.length; i++) {
                onDBReady[i]();
            }
            callback();
        };
        req.onerror = function (e) {
            console.log("indexedDB error : " + e.target.errorCode);
        };
        req.onupgradeneeded = function (e) {
            var store = e.target.result.createObjectStore(DB_STORE_NAME, { autoIncrement: true });
            store.createIndex("id", "id", { unique: true });
            store.createIndex("band", "band", { unique: false });
            store.createIndex("album", ["album", "band"], { unique: false });

            e.target.result.createObjectStore(DB_PLAYLIST_STORE_NAME);
        };
    }

    this.addOnDBReadyListener = function(callback) {
        onDBReady.push(callback);
    };

    this.isOpen = function() {
        return db != null;
    };

    var getObjectStore = function(storeName, mode) {
        var tx = db.transaction(storeName, mode);
        return tx.objectStore(storeName);
    };

    var clearObjectStore = function(storeName) {
        var store = getObjectStore(storeName, "readwrite");
        store.clear();
    };

    var addSongs = function(songs) {
        var store = getObjectStore(DB_STORE_NAME, "readwrite");
        for(var i = 0; i < songs.length; i++) {
            store.add(songs[i]);
        }
    };

    var addPlaylistSong = function(songs) {
        var store = getObjectStore(DB_PLAYLIST_STORE_NAME, "readwrite");
        for(var i = 0; i < songs.length; i++) {
            store.add(songs[i], i);
        }
    };

    this.getAllSongs = function(callback) {
        var store = getObjectStore(DB_STORE_NAME, "readonly");
        var index = store.index("band");
        var songs = [];
        index.openCursor().onsuccess = function (e) {
            var cursor = e.target.result;
            if (cursor) {
                songs.push(cursor.value);
                cursor.continue();
            } else {
                callback(songs);
            }
        }
    };

    this.getAllAlbumSongs = function(callback) {
        var store = getObjectStore(DB_STORE_NAME, "readonly");
        var index = store.index("album");
        var songs = [];
        index.openCursor().onsuccess = function (e) {
            var cursor = e.target.result;
            if (cursor) {
                songs.push(cursor.value);
                cursor.continue();
            } else {
                callback(songs);
            }
        }
    };

    this.getSong = function(id, callback) {
        var store = getObjectStore(DB_STORE_NAME, "readonly");
        var index = store.index("id");
        index.get(id).onsuccess = function (e) {
            callback(e.target.result);
        }
    };

    this.getPlaylistAllSongs = function(callback) {
        var store = getObjectStore(DB_PLAYLIST_STORE_NAME, "readonly");
        var songs = [];
        store.openCursor().onsuccess = function (e) {
            var cursor = e.target.result;
            if (cursor) {
                songs.push(cursor.value);
                cursor.continue();
            } else {
                callback(songs);
            }
        }
    };

    this.getBandSongs = function(band, callback) {
        var store = getObjectStore(DB_STORE_NAME, "readonly");
        var index = store.index("band");
        var songs = [];
        index.openCursor(IDBKeyRange.only(band)).onsuccess = function (e) {
            var cursor = e.target.result;
            if (cursor) {
                songs.push(cursor.value);
                cursor.continue();
            } else {
                callback(songs);
            }
        }
    };

    this.getFirstbandSong = function(band, callback) {
        var store = getObjectStore(DB_STORE_NAME, "readonly");
        var index = store.index("band");
        index.get(band).onsuccess = function (e) {
            callback(e.target.result);
        };
    };

    this.getAlbumSongs = function(band, album, callback) {
        var store = getObjectStore(DB_STORE_NAME, "readonly");
        var index = store.index("album");
        var songs = [];
        index.openCursor(IDBKeyRange.only([album, band])).onsuccess = function (e) {
            var cursor = e.target.result;
            if (cursor) {
                songs.push(cursor.value);
                cursor.continue();
            } else {
                callback(songs);
            }
        }
    };

    this.getFirstAlbumSong = function(band, album, callback) {
        var store = getObjectStore(DB_STORE_NAME, "readonly");
        var index = store.index("album");
        index.get([album, band]).onsuccess = function (e) {
            callback(e.target.result);
        };
    };


    this.sync = function(syncToken) {
        console.time("sync");
        clearObjectStore(DB_STORE_NAME);
        var xhr = new XMLHttpRequest();
        xhr.open("GET", baseurl+"/sync");
        xhr.onload = function() {
            var json = JSON.parse(xhr.response);
            addSongs(json);
            localStorage.setItem("sync_token", syncToken);
            console.timeEnd("sync");
        };
        xhr.send();
    };
    this.setPlaylist = function(songs) {
        clearObjectStore(DB_PLAYLIST_STORE_NAME);
        addPlaylistSong(songs);
    };
}