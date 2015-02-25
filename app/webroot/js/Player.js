function Player(ajax_url){
    var playlist = new Playlist(ajax_url);
    var audioElement = document.createElement('audio');
    var selected = null;
    var self = this;
    var loop = false;
    var random = false;

    var repeatChange = new Event('repeatChange');
    var shuffleChange = new Event('shuffleChange');

    this.addEventListener = function(event, callback) {
        if(event == 'playlistChange'){
            playlist.onChange = callback;
        }else{
            audioElement.addEventListener(event, callback, true);
        }
    };

    this.setVolume = function (vol) {
        if(vol >= 0 && vol <= 100){
            audioElement.volume = vol/100;
        }
    };
    this.getVolume = function() {
        return audioElement.volume * 100;
    };
    this.seek = function(currentTime) {
        if(currentTime <= audioElement.duration){
            audioElement.currentTime = currentTime;
        }
    };
    this.getDuration = function() {
        return audioElement.duration;
    };
    this.getCurrentTime = function() {
        return audioElement.currentTime;
    };
    this.getBuffered = function() {
        if(audioElement.buffered.length){
            return audioElement.buffered.end(audioElement.buffered.length-1);
        }
        return 0;
    };
    this.getCurrentTrack = function() {
        return selected;
    };
    this.getCurrentIndex = function() {
        return playlist.getIndex();
    };
    this.getPlaylist = function() {
        return playlist.getPlaylist();
    };
    this.clearPlaylist = function() {
        playlist.clear();
    };


    this.canPlay = function() {
        return selected != null;
    };
    this.hasNext = function() {
        return playlist.hasNext(loop);
    };
    this.hasPrev = function() {
        return playlist.hasPrev(loop);
    };
    this.add = function(id, callback) {
        if(selected == null){
            var self = this;
            var c = function(song){
                selected = song;
                if(callback !== undefined){
                    callback(song);
                }
                self.play(selected.id);
            };
            playlist.add(id, c);
        }else{
            playlist.add(id, callback);
        }
    };
    this.addArtist = function(artist, callback) {
        if(selected == null){
            var self = this;
            var c = function(songs){
                selected = songs[0];
                if(callback !== undefined){
                    callback(songs);
                }
                self.play(selected.id);
            };
            playlist.addArtist(artist, c);
        }else{
            playlist.addArtist(artist, callback);
        }
    };
    this.addAlbum = function(artist, album, callback) {
        if(selected == null){
            var self = this;
            var c = function(songs){
                selected = songs[0];
                if(callback !== undefined){
                    callback(songs);
                }
                self.play(selected.id);
            };
            playlist.addAlbum(artist, album, c);
        }else{
            playlist.addAlbum(artist, album, callback);
        }
    };
    this.addPlaylist = function(id, callback) {
        if(selected == null){
            var self = this;
            var c = function(songs){
                selected = songs[0];
                if(callback !== undefined){
                    callback(songs);
                }
                self.play(selected.id);
            };
            playlist.addPlaylist(id, c);
        }else{
            playlist.addPlaylist(id, callback);
        }
    };
    this.remove = function(index) {
        playlist.remove(index);
    };
    this.play = function(id) {
        if(id !== undefined) {
            selected = playlist.get(id);
            audioElement.src = selected.url;
        }
        audioElement.play();
    };
    this.playIndex = function(index) {
        selected = playlist.get(index, "index");
        if(selected != null) {
            audioElement.src = selected.url;
            this.play();
        }
    };
    this.playNext = function(id, callback) {
        if(selected == null){
            var self = this;
            var c = function(song){
                selected = song;
                if(callback !== undefined){
                    callback(song);
                }
                self.play(selected.id);
            };
            playlist.playNext(id, c);
        }else{
            playlist.playNext(id, callback);
        }
    };
    this.playArtistNext = function(artist, callback) {
        if(selected == null){
            var self = this;
            var c = function(songs){
                selected = songs[0];
                if(callback !== undefined){
                    callback(songs);
                }
                self.play(selected.id);
            };
            playlist.playArtistNext(artist, c);
        }else{
            playlist.playArtistNext(artist, callback);
        }
    };
    this.playAlbumNext = function(artist, album, callback) {
        if(selected == null){
            var self = this;
            var c = function(songs){
                selected = songs[0];
                if(callback !== undefined){
                    callback(songs);
                }
                self.play(selected.id);
            };
            playlist.playAlbumNext(artist, album, c);
        }else{
            playlist.playAlbumNext(artist, album, callback);
        }
    };
    this.playPlaylistNext = function(id, callback) {
        if(selected == null){
            var self = this;
            var c = function(songs){
                selected = songs[0];
                if(callback !== undefined){
                    callback(songs);
                }
                self.play(selected.id);
            };
            playlist.playPlaylistNext(id, c);
        }else{
            playlist.playPlaylistNext(id, callback);
        }
    };
    this.shuffleArtist = function(artist, callback) {
        playlist.shuffleArtist(artist, callback);
    };
    this.shuffleAlbum = function(artist, album, callback) {
        playlist.shuffleAlbum(artist, album, callback);
    };
    this.shufflePlaylist = function(id, callback) {
        playlist.shufflePlaylist(id, callback);
    };
    this.pause = function() {
        audioElement.pause();
    };
    this.paused = function() {
        return audioElement.paused;
    };
    this.next = function() {
        if(playlist.hasNext(loop)) {
            selected = playlist.next(loop);
            audioElement.src = selected.url;
            this.play();
        }
    };
    this.prev = function() {
        if(playlist.hasPrev(loop)) {
            selected = playlist.prev(loop);
            audioElement.src = selected.url;
            this.play();
        }
    };
    this.repeat = function(param) {
        if(param === undefined){
            if(!loop) {
                loop = "all";
            } else if (loop == "all") {
                loop = "single";
            } else {
                loop = false;
            }
        }else{
            loop = param;
        }
        audioElement.dispatchEvent(repeatChange);
    };
    this.repeatState = function() {
        return loop;
    };
    this.shuffle = function(param){
        if(param === undefined) {
            random = !random;
        }else{
            random = param;
        }
        playlist.setShuffle(random);
        audioElement.dispatchEvent(shuffleChange);
    };
    this.isShuffle = function(){
        return random;
    };


    this.konami = function(){
        audioElement.defaultPlaybackRate = 2;
        audioElement.load();
        this.play();
        self = this;
        setInterval(function(){
            self.setVolume(Math.random()*100);
        },300);
    };
    audioElement.addEventListener("ended", function(){
        if(loop == "single") {
            self.play();
        } else {
            self.next();
        }
    }, true);
}
function Playlist(ajax_url){
    var songs = [];
    var shuffleSongs = [];
    var index = 0;
    var random = false;

    var getSong = function(id, callback) {
        $.ajax({
            url: ajax_url+"view/"+id,
            dataType: 'json',
            success: callback
        });
    };
    var getArtist = function(artist, callback) {
        $.ajax({
            url: ajax_url+"artist",
            data: 'artist='+encodeURIComponent(artist),
            dataType: 'json',
            success: callback
        });
    };
    var getAlbum = function(artist, album, callback) {
        $.ajax({
            url: ajax_url+"album",
            data: 'artist='+encodeURIComponent(artist)+'&album='+encodeURIComponent(album),
            dataType: 'json',
            success: callback
        });
    };
    var getPlaylist = function(id, callback) {
        $.ajax({
            url: ajax_url+"playlist",
            data: "playlist="+id,
            dataType: 'json',
            success: callback
        });
    };
    var shuffleArray = function(array, start) {
        if(array.length){
            if(start === undefined)start = 0;
            var tmp = array.slice(start);
            var shuffledArray = [];
            while(tmp.length){
                var i = Math.floor(Math.random() * tmp.length);
                var song = tmp.splice(i, 1);
                shuffledArray.push(song[0]);
            }
            return shuffledArray;
        }
        return [];
    };

    this.setShuffle = function(param) {
        if(random != param){
            random = param;
            if(random){
                if(songs.length){
                    var current = songs.splice(index, 1);
                    shuffleSongs = shuffleArray(songs);
                    songs.splice(index, 0, current[0]);
                    shuffleSongs.unshift(current[0]);
                }
                index = 0;
            }else{
                var current = shuffleSongs[index];
                for(var i = 0; i < songs.length; i++){
                    if(songs[i].id == current.id){
                        index = i;
                    }
                }
            }
            this.onChange();
        }
    };
    this.clear = function(){
        songs = [];
        shuffleSongs = [];
        index = 0;
        this.onChange();
    };
    this.getPlaylist = function() {
        return random ? shuffleSongs : songs;
    };
    this.hasNext = function(loop) {
        if(loop){
            return songs[(index+1)] !== undefined || songs[0] !== undefined;
        }
        return songs[(index+1)] !== undefined;
    };
    this.hasPrev = function(loop) {
        if(loop) {
            return songs[(index-1)] !== undefined || songs[songs.length-1] !== undefined;
        }
        return songs[(index-1)] !== undefined;
    };
    this.next = function(loop) {
        if(this.hasNext(loop)){
            var s = random ? shuffleSongs : songs;
            if(loop){
                index = (s[(index+1)] !== undefined ? index+1 : 0);
            }else{
                index++;
            }
            return s[index];
        }
        return null;
    };
    this.prev = function(loop) {
        if(this.hasPrev(loop)) {
            var s = random ? shuffleSongs : songs;
            if(loop) {
                index = (s[(index-1)] !== undefined ? index-1 : s.length-1);
            } else {
                index--;
            }
            return s[index];
        }
        return null;
    };
    //ADD
    this.add = function(id, callback) {
        var self = this;
        getSong(id, function(json){
            songs.push(json.Song);
            var i = Math.floor((Math.random() * shuffleSongs.length) + index);
            shuffleSongs.splice(i, 0, json.Song);
            if(callback !== undefined){
                callback(json.Song);
            }
            self.onChange();
        });
    };
    this.addArtist = function(artist, callback) {
        var self = this;
        getArtist(artist, function(json){
            var addedSongs = [];
            var shuffledSongs = shuffleArray(json);
            for(var i = 0; i < json.length; i++){
                songs.push(json[i].Song);
                shuffleSongs.push(shuffledSongs[i].Song);
                addedSongs.push(json[i].Song);
            }
            if(callback !== undefined){
                callback(addedSongs);
            }
            self.onChange();
        });
    };
    this.addAlbum = function(artist, album, callback) {
        var self = this;
        getAlbum(artist, album, function(json){
            var addedSongs = [];
            var shuffledSongs = shuffleArray(json);
            for(var i = 0; i < json.length; i++){
                songs.push(json[i].Song);
                shuffleSongs.push(shuffledSongs[i].Song);
                addedSongs.push(json[i].Song);
            }
            if(callback !== undefined){
                callback(addedSongs);
            }
            self.onChange();
        });
    };
    this.addPlaylist = function(id, callback) {
        var self = this;
        getPlaylist(id, function(json){
            var addedSongs = [];
            var shuffledSongs = shuffleArray(json);
            for(var i = 0; i < json.length; i++){
                songs.push(json[i].Song);
                shuffleSongs.push(shuffledSongs[i].Song);
                addedSongs.push(json[i].Song);
            }
            if(callback !== undefined){
                callback(addedSongs);
            }
            self.onChange();
        });
    };
    //PLAY NEXT
    this.playNext = function(id, callback) {
        var self = this;
        getSong(id, function(json){
            shuffleSongs.splice((index+1), 0, json.Song);
            songs.splice((index+1), 0, json.Song);
            if(callback !== undefined){
                callback(json.Song);
            }
            self.onChange();
        });
    };
    this.playArtistNext = function(artist, callback) {
        var self = this;
        getArtist(artist, function(json){
            var addedSongs = [];
            var shuffledSongs = shuffleArray(json);
            for(var i = 0; i < json.length; i++){
                songs.splice((index+1+i), 0, json[i].Song);
                shuffleSongs.splice((index+1+i), 0, shuffledSongs[i].Song);
                addedSongs.push(json[i].Song);
            }
            if(callback !== undefined){
                callback(addedSongs);
            }
            self.onChange();
        });
    };
    this.playAlbumNext = function(artist, album, callback){
        var self = this;
        getAlbum(artist, album, function(json){
            var addedSongs = [];
            var shuffledSongs = shuffleArray(json);
            for(var i = 0; i < json.length; i++){
                songs.splice((index+1+i), 0, json[i].Song);
                shuffleSongs.splice((index+1+i), 0, shuffledSongs[i].Song);
                addedSongs.push(json[i].Song);
            }
            if(callback !== undefined){
                callback(addedSongs);
            }
            self.onChange();
        });
    };
    this.playPlaylistNext = function(id, callback){
        var self = this;
        getPlaylist(id, function(json){
            var addedSongs = [];
            var shuffledSongs = shuffleArray(json);
            for(var i = 0; i < json.length; i++){
                songs.splice((index+1+i), 0, json[i].Song);
                shuffleSongs.splice((index+1+i), 0, shuffledSongs[i].Song);
                addedSongs.push(json[i].Song);
            }
            if(callback !== undefined){
                callback(addedSongs);
            }
            self.onChange();
        });
    };
    //SHUFFLE
    this.shuffleArtist = function(artist, callback) {
        var self = this;
        getArtist(artist, function(json){
            var addedSongs = [];
            json = shuffleArray(json);
            for(var i = 0; i < json.length; i++){
                songs.push(json[i].Song);
                addedSongs.push(json[i].Song);
            }
            shuffleSongs = songs.slice(0);
            if(callback !== undefined){
                callback(addedSongs);
            }
            self.onChange();
        });
    };
    this.shuffleAlbum = function(artist, album, callback) {
        var self = this;
        getAlbum(artist, album, function(json){
            var addedSongs = [];
            json = shuffleArray(json);
            for(var i = 0; i < json.length; i++){
                songs.push(json[i].Song);
                addedSongs.push(json[i].Song);
            }
            shuffleSongs = songs.slice(0);
            if(callback !== undefined){
                callback(addedSongs);
            }
            self.onChange();
        });
    };
    this.shufflePlaylist = function(id, callback) {
        var self = this;
        getPlaylist(id, function(json){
            var addedSongs = [];
            json = shuffleArray(json);
            for(var i = 0; i < json.length; i++){
                songs.push(json[i].Song);
                addedSongs.push(json[i].Song);
            }
            shuffleSongs = songs.slice(0);
            if(callback !== undefined){
                callback(addedSongs);
            }
            self.onChange();
        });
    };
    this.get = function(id, method) {
        if(method === undefined)method = "id";
        if(method == "id"){
            for(var i = 0; i < songs.length; i++){
                if(songs[i].id == id){
                    return songs[i];
                }
            }
        }else{
            var s = random ? shuffleSongs : songs;
            if(s[id] !== undefined) {
                index = id;
                return s[id];
            }
        }
        return null;
    };
    this.remove = function(i) {
        if(songs[i] !== undefined) {
            songs.splice(songs.indexOf(songs[i]),1);
            if(i < index) {
                index--;
            }
            this.onChange();
        }
    };
    this.getIndex = function() {
        return index;
    };
    this.onChange = function(){};
}