function Player() {

    var audioElement = document.createElement('audio');
    var buffered = false;
    var playlist = new Playlist();
    var self = this;
    var selected = null;
    var loop = false; // all || single || false
    var shuffle = false;

    var repeatChange = new Event('repeatChange');
    var playlistChange = new Event('playlistChange');
    var shuffleChange = new Event('shuffleChange');

    audioElement.addEventListener("ended", function(){
        if(loop == "single") {
            self.play();
        }else {
            self.next();
        }
    }, true);
    this.addEventListener = function(event, callback) {
        audioElement.addEventListener(event, callback, true);
    };
    this.addEventListener('progress', function() {
        setTimeout(function() {
            if(self.getBuffered() == self.getDuration() && self.hasNext() && !buffered) {
                var index = playlist.getCurrentIndex()+1;
                if (index == playlist.size()) {
                    index = 0;
                }
                var audio = new Audio(playlist.getByIndex(index).url);
                audio.preload = 'auto';
                audio.play();
                audio.pause();
                buffered = true;
            }
        }, 500);
    });
    this.getPlaylist = function() {
        return playlist.getSongs();
    };
    this.getCurrentTrack = function() {
        return selected;
    };
    this.getCurrentIndex = function() {
        return playlist.getCurrentIndex();
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
    this.seek = function(currentTime) {
        if(currentTime <= audioElement.duration){
            audioElement.currentTime = currentTime;
            if (this.isPlaying()) {
                audioElement.pause();
                audioElement.play();
            }
        }
    };
    this.canPlay = function() {
        return selected !== null;
    };
    this.hasPrev = function() {
        return (this.canPlay() && playlist.hasPrev());
    };
    this.hasNext = function() {
        return (this.canPlay() && playlist.hasNext());
    };
    this.play = function(id) {
        if(id !== undefined) {
            selected = playlist.get(id);
            audioElement.src = selected.url;
        }else if(audioElement.src == "" && selected != null) {
            audioElement.src = selected.url;
        }
        buffered = false;
        if(audioElement.src != "") {
            audioElement.play();
        }
    };
    this.playIndex = function(index) {
        this.play(playlist.getByIndex(index).id);
    };
    this.pause = function() {
        audioElement.pause();
    };
    this.isPlaying = function() {
        return !audioElement.paused;
    };
    this.prev = function() {
        if(selected != null) {
            var next = playlist.prev();
            if(next !== null) {
                selected = next;
                audioElement.src = selected.url;
                this.play();
            }
        }
    };
    this.next = function() {
        if(selected != null) {
            var next = playlist.next();
            if(next !== null) {
                selected = next;
                audioElement.src = selected.url;
                this.play();
            }
        }
    };
    this.setFirst = function(songId) {
        playlist.setFirst(songId);
        audioElement.dispatchEvent(playlistChange);
    };
    this.add = function(song) {
        playlist.add(song);
        if(selected === null) {
            selected = song;
        }
        audioElement.dispatchEvent(playlistChange);
    };
    this.addAll = function(songs) {
        playlist.addAll(songs);
        if(selected == null) {
            selected = playlist.getByIndex(0);
        }
        audioElement.dispatchEvent(playlistChange);
    };
    this.playNext = function(song) {
        playlist.addNext(song);
        if(selected == null) {
            selected = song;
        }
        audioElement.dispatchEvent(playlistChange);
    };
    this.playNextAll = function(songs) {
        songs.reverse();
        for(var i = 0; i < songs.length; i++) {
            playlist.addNext(songs[i]);
        }
        if(selected == null) {
            selected = songs[songs.length-1];
        }
        audioElement.dispatchEvent(playlistChange);
    };
    this.remove = function(index) {
        playlist.remove(index);
        audioElement.dispatchEvent(playlistChange);
    };
    this.clearPlaylist = function() {
        playlist.clear();
        audioElement.dispatchEvent(playlistChange);
    };
    this.volume = function(vol) {
        if(vol === undefined) {
            return audioElement.volume*100;
        }else if(vol >= 0 && vol <= 100) {
            audioElement.volume = vol/100;
        }
    };
    this.mute = function() {
        audioElement.muted = !audioElement.muted;
    };
    this.isMuted = function() {
        return audioElement.muted;
    };
    this.repeat = function(data) {
        if(data === undefined) {
            if(loop == false) {
                loop = "all";
            }else if(loop == "all") {
                loop = "single";
            }else {
                loop = false;
            }
        }else {
            loop = data;
        }
        playlist.setLoopMode(loop);
        audioElement.dispatchEvent(repeatChange);
    };
    this.repeatMode = function() {
        return loop;
    };
    this.shuffle = function(param) {
        if(param === undefined) {
            shuffle = !shuffle;
            audioElement.dispatchEvent(shuffleChange);
        }else if(shuffle != param){
            shuffle = param;
            audioElement.dispatchEvent(shuffleChange);
        }
        playlist.setShuffle(shuffle);
        audioElement.dispatchEvent(playlistChange);
    };
    this.isShuffle = function() {
        return shuffle;
    };
}

function Playlist() {
    var songs = [];
    var shuffledSongs = [];
    var index = 0;
    var loopMode = false;
    var shuffle = false;

    var getIndex = function(id) {
        for(var i = 0; i < playlist().length; i++) {
            if(playlist()[i].id == id) {
                return i;
            }
        }
        return null;
    };
    var playlist = function() {
        return shuffle ? shuffledSongs : songs;
    };

    var shuffleArray = function(array) {
        if(array.length == 0) return [];
        var tmp = array.slice();
        return tmp.shuffle();
    };

    this.getSongs = function() {
        return playlist();
    };
    this.setShuffle = function(param) {
        shuffle = param;
        if(songs.length) {
            if(shuffle) {
                var current = songs.splice(index, 1);
                shuffledSongs = shuffleArray(songs);
                songs.splice(index, 0, current[0]);
                shuffledSongs.unshift(current[0]);
                index = 0;
            }else {
                var current = shuffledSongs[index];
                for(var i = 0; i < songs.length; i++){
                    if(songs[i].id == current.id){
                        index = i;
                    }
                }
            }
        }
    };
    this.setLoopMode = function(mode) {
        loopMode = mode;
    };
    this.size = function() {
        return playlist().length;
    };
    this.clear = function() {
        songs = [];
        shuffledSongs = [];
        index = 0;
    };
    this.setFirst = function(songId) {
        var song = playlist().splice(getIndex(songId), 1);
        playlist().unshift(song[0]);
    };
    this.add = function(song) {
        songs.push(song);
        var i = Math.floor((Math.random() * shuffledSongs.length) + index);
        shuffledSongs.splice(i, 0, song);
    };
    this.addAll = function(allSongs) {
        var shuffled = shuffleArray(allSongs);
        for(var i = 0; i < allSongs.length; ++i) {
            songs[songs.length] = allSongs[i];
            shuffledSongs[shuffledSongs.length] = shuffled[i];
        }
    };
    this.addNext = function(song) {
        shuffledSongs.splice(index+1, 0, song);
        songs.splice(index+1, 0, song);
    };
    this.remove = function(i) {
        var song = playlist()[i];
        playlist().splice(i, 1);
        shuffle = !shuffle;
        var s = getIndex(song.id);
        playlist().splice(s, 1);
        shuffle = !shuffle;
        if(i < index) {
            index--;
        }
    };
    this.get = function(id) {
        index = +getIndex(id);
        if(index !== null) {
            return playlist()[index];
        }
        return null;
    };
    this.getByIndex = function(i) {
        if(playlist()[i] === undefined) return null;
        return playlist()[i];
    };
    this.getCurrentIndex = function() {
        return index;
    };
    this.hasPrev = function() {
        return (loopMode && this.size()) || (playlist()[(index-1)] !== undefined);
    };
    this.hasNext = function() {
        return (loopMode && this.size()) || (playlist()[(index+1)] !== undefined);
    };
    this.prev = function() {
        if(playlist()[(index-1)] !== undefined) {
            return playlist()[--index];
        }else if(loopMode && this.size()) {
            index = this.size()-1;
            return playlist()[index];
        }
        return null;
    };
    this.next = function() {
        if(playlist()[(index+1)] !== undefined) {
            return playlist()[++index];
        }else if(loopMode && this.size()) {
            index = 0;
            return playlist()[0];
        }
        return null;
    };
}