var player = new Player();
var songsManager = new SongsManager(baseurl, syncToken);

var $playButton = $("#play");
var $prevButton = $("#backward");
var $nextButton = $("#forward");
var $muteButton = $("#mute");

var $coverImg = $('.song-cover');
var $songTitle = $('.song-name');
var $songBand = $('.song-artist');

var $volumeBar = $("#volume");
var $timeBar = $("#timebar");

var $currentTime = $('.currentTime');
var $totalTime = $('.totalTime');

var $queue = $('#queue');

var $queueButton = $("#queue-button");
var $repeatButton = $("#queue-repeat");
var $shuffleButton = $("#queue-shuffle");

var $pageTitle = $('title');

var playTitle = '.action-play';
var pauseTitle = '.action-pause';
var playBand = '.action-play-artist';
var playAlbum = '.action-play-album';
var playTitleNext = '.action-play-next';
var playBandNext = '.action-artist-play-next';
var playAlbumNext = '.action-album-play-next';
var playPlaylistNext = '.action-playlist-play-next';
var playTitleAfter = '.action-add-to-up-next';
var playBandAfter = '.action-add-artist-to-up-next';
var playAlbumAfter = '.action-add-album-to-up-next';
var playPlaylistAfter = '.action-add-playlist-to-up-next';
var playPlaylist = '.action-play-playlist';
var shuffleBand = '.action-shuffle-artist';
var shuffleAlbum = '.action-shuffle-album';
var shufflePlaylist = '.action-shuffle-playlist';

var shortcut=false;
var k = "65663937393740403838";
var ks = "";

var volume = localStorage.getItem('volume');
var mute = localStorage.getItem('muted');
var repeatMode = localStorage.getItem('repeat');
var shuffle = localStorage.getItem('shuffle');

//http://stackoverflow.com/questions/6274339/how-can-i-shuffle-an-array-in-javascript
Array.prototype.shuffle = function() {
    for(var j, x, i = this.length; i; j = Math.floor(Math.random() * i), x = this[--i], this[i] = this[j], this[j] = x);
    return this;
};

function init() {
    updateUI();
    $currentTime.text(getFormatedTime(0));
    $totalTime.text(getFormatedTime());
    $volumeBar.slider({change: function(vol) {
        player.volume(vol);
    }});

    $volumeBar.bind('DOMMouseScroll mousewheel', function(e) {
        e.preventDefault();
        e = e.originalEvent;
        var delta = e.wheelDelta>0||e.detail<0?1:-1;
        if (delta > 0){
            player.volume(player.volume()+2);
        } else if (delta < 0){
            player.volume(player.volume()-2);
        }
    });

    $muteButton.click(function(e) {
        e.preventDefault();
        player.mute();
    });

    $timeBar.slider({max: 0, change: function(val){
        player.seek(val);
    }});

    $playButton.click(function(e) {
        e.preventDefault();
        player.isPlaying() ? player.pause() : player.play();
    });

    $prevButton.click(function(e) {
        e.preventDefault();
        player.prev();
    });

    $nextButton.click(function(e) {
        e.preventDefault();
        player.next();
    });

    $queueButton.click(function(e) {
        e.preventDefault();
        toggleQueueList();
    });

    $repeatButton.click(function(e) {
        e.preventDefault();
        player.repeat();
    });

    $shuffleButton.click(function(e) {
        e.preventDefault();
        player.shuffle();
    });

    $('#content').on('click', playTitle, function(e) {
        e.preventDefault();
        var songId = $(this).parents('[data-id]').attr('data-id');
        var view = $('[data-view]').attr('data-view');

        if (view == 'default') {
            var songs = songsManager.getAllSongs();
        } else {
            var songs = [songsManager.getSong(songId)];
        }

        populatePlaylist(songs);

        if (player.getCurrentTrack().id == songId) {
            player.play();
        } else {
            player.play(songId);
        }
    });

    $('#content').on('dblclick', 'tr[data-id]', function(e) {
        e.preventDefault();
        var songId = $(this).attr('data-id');
        var view = $('[data-view]').attr('data-view');

        if (view == 'default') {
            var songs = songsManager.getAllSongs();
        } else {
            var songs = [songsManager.getSong(songId)];
        }

        populatePlaylist(songs);
        player.play(songId);
    });

    $('#content').on('click', pauseTitle, function(e) {
        e.preventDefault();
        player.pause();
    });

    $('#content').on('click', playTitleNext, function(e) {
        e.preventDefault();
        var songId = $(this).parents('[data-id]').attr('data-id');
        player.playNext(songsManager.getSong(songId));
    });

    $('#content').on('click', playTitleAfter, function(e) {
        e.preventDefault();
        var songId = $(this).parents('[data-id]').attr('data-id');
        player.add(songsManager.getSong(songId));
    });

    $('#content').on('click', playBand, function(e) {
        e.preventDefault();
        var band = $(this).parents('[data-band]').attr('data-band');
        var song = songsManager.getFirstbandSong(band);
        var songs = songsManager.getBandSongs(band);
        populatePlaylist(songs);
        player.play(song.id);
    });

    $('#content').on('click', playBandNext, function(e) {
        e.preventDefault();
        var band = $(this).parents('[data-band]').attr('data-band');
        player.playNextAll(songsManager.getBandSongs(band));
    });

    $('#content').on('click', playBandAfter, function(e) {
        e.preventDefault();
        var band = $(this).parents('[data-band]').attr('data-band');
        player.addAll(songsManager.getBandSongs(band));
    });
    $('#content').on('click', shuffleBand, function(e) {
        e.preventDefault();
        var band = $(this).parents('[data-band]').attr('data-band');
        var songs = songsManager.getBandSongs(band);
        player.clearPlaylist();
        player.addAll(songs.shuffle());
        player.playIndex(0);
    });

    $('#content').on('click', playAlbum, function(e) {
        e.preventDefault();
        var band = $(this).parents('[data-band]').attr('data-band');
        var album = $(this).parents('[data-album]').attr('data-album');
        var song = songsManager.getFirstAlbumSong(band, album);
        var songs = songsManager.getAlbumSongs(band, album);
        populatePlaylist(songs);
        player.play(song.id);
    });

    $('#content').on('click', playAlbumNext, function(e) {
        e.preventDefault();
        var band = $(this).parents('[data-band]').attr('data-band');
        var album = $(this).parents('[data-album]').attr('data-album');
        player.playNextAll(songsManager.getAlbumSongs(band, album));
    });

    $('#content').on('click', playAlbumAfter, function(e) {
        e.preventDefault();
        var band = $(this).parents('[data-band]').attr('data-band');
        var album = $(this).parents('[data-album]').attr('data-album');
        player.addAll(songsManager.getAlbumSongs(band, album));
    });

    $('#content').on('click', shuffleAlbum, function(e) {
        e.preventDefault();
        var band = $(this).parents('[data-band]').attr('data-band');
        var album = $(this).parents('[data-album]').attr('data-album');
        var songs = songsManager.getAlbumSongs(band, album);
        player.clearPlaylist();
        player.addAll(songs.shuffle());
        player.playIndex(0);
    });

    $('#content').on('click', playPlaylist, function(e) {
        e.preventDefault();
        var songs = songsManager.getPlaylistAllSongs();
        player.clearPlaylist();
        player.addAll(songs);
        player.play(songs[0]);
    });

    $('#content').on('click', playPlaylistNext, function(e) {
        e.preventDefault();
        player.playNextAll(songsManager.getPlaylistAllSongs());
    });

    $('#content').on('click', playPlaylistAfter, function(e) {
        e.preventDefault();
        player.addAll(songsManager.getPlaylistAllSongs());
    });

    $('#content').on('click', shufflePlaylist, function(e) {
        e.preventDefault();
        var songs =songsManager.getPlaylistAllSongs();
        player.clearPlaylist();
        player.addAll(songs.shuffle());
        player.play(songs[0]);
    });

    $queue.on('click', playTitle, function() {
        var index = $(this).parents('tr').attr('data-index');
        if (index == player.getCurrentIndex()) {
            player.play();
        } else {
            player.playIndex(index);
        }
    });

    $queue.on('dblclick', 'tr[data-id]', function(e) {
        e.preventDefault();
        var index = $(this).attr('data-index');
        player.playIndex(index);
    });

    $queue.on('click', pauseTitle, function(e) {
        e.preventDefault();
        player.pause();
    });

    $('#queue').on('click', '.action-remove-from-queue', function() {
        player.remove($(this).parents('tr').attr('data-index'));
    });

    $(document).on('focus', 'input', function() {
        shortcut = false;
    });

    $(document).on('blur', 'input', function() {
        shortcut = true;
    });

    window.onkeydown = function(e) {
        var key = e.keyCode ? e.keyCode : e.which;
        ks = (key.toString()+ks).substr(0, 20);
        if (ks == k) {
            $('#main-nav-bar').css('background-image', 'linear-gradient(to right, red, orange, yellow, green,blue, indigo, violet)');
            console.log("%cI %c♥%c BZH", "color:black;font-size:40px;","color:red;font-size:40px;", "color:black;font-size:40px;");
        }
        if (shortcut) {
            if (key == 32) {
                e.preventDefault();
                player.isPlaying() ? player.pause() : player.play();
            } else if (key == 39) {
                player.next();
            } else if (key == 37) {
                player.prev();
            }
        }
    };

    $('#content, #queue').on('mousedown', 'tr[data-id]', function(e) {
        e.preventDefault();
        $('input').blur();
    });

    $('.navbar-player').on('mousedown', function(e) {
        e.preventDefault();
        $('input').blur();
    });

    player.addEventListener('play', function() {
        var track = player.getCurrentTrack();
        $pageTitle.text(track.title+" - "+track.band);
        $playButton.removeClass('glyphicon-play').addClass('glyphicon-pause');
        $('#content tr, #queue tr').removeClass('paused');
        updateSelectedSong();
    });

    player.addEventListener('pause', function() {
        $('#play').removeClass('glyphicon-pause').addClass('glyphicon-play');
        updateSelectedSong();
    });

    player.addEventListener('loadstart', function() {
        var track = player.getCurrentTrack();
        $songTitle.text(track.title);
        $songBand.text(track.band);
        var replace = "_65x65"+(retina ? "@2x" : "")+"$1";
        $coverImg.attr('src', track.cover.replace(/(\.[a-z0-9]+)/i, replace));
        updateUI();
    });

    player.addEventListener('durationchange', function() {
        $timeBar.slider({max: player.getDuration()});
        $timeBar.slider('buffered', player.getBuffered());
        $totalTime.text(getFormatedTime(player.getDuration()));
    });

    player.addEventListener('timeupdate', function() {
        $timeBar.slider('value', player.getCurrentTime());
        $currentTime.text(getFormatedTime(player.getCurrentTime()));
    });

    player.addEventListener('progress', function() {
        $timeBar.slider('buffered', player.getBuffered());
    });

    player.addEventListener('volumechange', function() {
        $volumeBar.slider('value', player.volume());
        if (player.isMuted() || player.volume() == 0) {
            $muteButton.removeClass('glyphicon-volume-up').addClass('glyphicon-volume-off');
        } else {
            $muteButton.removeClass('glyphicon-volume-off').addClass('glyphicon-volume-up');
        }
        localStorage.setItem('volume', player.volume());
        localStorage.setItem('muted', player.isMuted());
    });

    player.addEventListener('repeatChange', function() {
        updateUI();
        localStorage.setItem('repeat', player.repeatMode());
    });

    player.addEventListener('shuffleChange', function() {
        updateUI();
        localStorage.setItem('shuffle', player.isShuffle());
    });

    player.addEventListener('playlistChange', function() {
        $('table tbody', $queue).empty();
        var songs = player.getPlaylist();
        if (songs.length) {
            $('#alert-empty-queue').hide();
            $('#queue-list').show().find('.queue-size').text(songs.length);
        } else {
            $('#alert-empty-queue').show();
            $('#queue-list').hide();
        }
        if (songs.length) {
            $('.current-queue-inner').list({
                cellHeight: 36,
                cellsShowed: 30,
                min: 10,
                cellTemplate: $('#queue-tr').html(),
                table:'.table-album',
                data: songs,
                callback: updateSelectedSong,
                updateTemplate: function($template, song, index) {
                    $template.attr('data-id', song.id).attr('data-index', index);
                    $template.find('td.title').text(song.title);
                    $template.find('td.artist').text(song.artist);
                    $template.find('td.album').text(song.album);
                    $template.find('.song-playtime').text(song.playtime);
                    return $template;
                }
            });
        }
        updateSelectedSong();
        updateUI();
    });

    $queue.on('DOMMouseScroll mousewheel', function(e) {
        e = e.originalEvent;
        var delta = e.wheelDelta>0||e.detail<0?1:-1;
        var bottom = $('.current-queue-inner')[0].scrollHeight - $('.current-queue-inner').height();
        if ($('.current-queue-inner').scrollTop() == 0 && delta > 0
            || $('.current-queue-inner').scrollTop() == bottom && delta < 0 ) {
            e.stopPropagation();
            e.preventDefault();
        }
    });
    $(document).mousedown(function(e) {
        // If the target isn't the container nor a descendant
        if (!$queue.is(e.target) && $queue.has(e.target).length === 0 && $('.navbar-player').has(e.target).length == 0 && $queue.hasClass('queue-open')) {
            toggleQueueList();
        }
    });

    player.volume(0);
    player.volume(volume ? volume : 50);
    if (mute == "true") {
        player.mute();
    }
    player.repeat((repeatMode == "false" || !repeatMode) ? false : repeatMode);
    player.shuffle(shuffle == "true" ? true : false);
}

function updateUI() {
    if (player.canPlay()) {
        $playButton.parent().removeClass("disable");
    } else {
        $playButton.parent().addClass("disable");
    }
    if (player.hasPrev()) {
        $prevButton.parent().removeClass("disable");
    } else {
        $prevButton.parent().addClass("disable");
    }
    if (player.hasNext()) {
        $nextButton.parent().removeClass("disable");
    } else {
        $nextButton.parent().addClass("disable");
    }
    if (player.repeatMode()) {
        $repeatButton.parent().addClass('active');
        if (player.repeatMode() == "single") {
            $repeatButton.parent().addClass('single');
        }
    } else {
        $repeatButton.parent().removeClass('active single');
    }
    if (player.isShuffle()) {
        $shuffleButton.parent().addClass('active');
    } else {
        $shuffleButton.parent().removeClass('active');
    }

}

function updateSelectedSong(){
    var track = player.getCurrentTrack();
    if (track){
        $('tr.on-air').removeClass('on-air');
        $('#content tr[data-id="'+track.id+'"]').addClass('on-air');
        $('#queue').find('[data-index="'+player.getCurrentIndex()+'"]').addClass('on-air');
        if (!player.isPlaying()){
            $('#content tr[data-id="'+track.id+'"]').addClass('paused');
            $('#queue').find('[data-index="'+player.getCurrentIndex()+'"]').addClass('paused');
        }
    }
}

function getFormatedTime(s) {
    s = Math.round(s);
    var minutes = Math.floor(s/60);
    var secondes = Math.round(s%60);
    minutes = minutes < 10 ? "0"+minutes : minutes;
    secondes = secondes < 10 ? "0"+secondes : secondes;
    if (isNaN(minutes) || isNaN(secondes)){
        minutes = secondes = "--";
    }
    return minutes+":"+secondes;
}

function populatePlaylist(songs) {
    player.clearPlaylist();
    player.addAll(songs);
}

function toggleQueueList() {
    if ($queue.hasClass('queue-open')){
        $queue.removeClass('queue-open');
        $queueButton.parent().removeClass('active');
    } else {
        $queue.addClass('queue-open');
        $queueButton.parent().addClass('active');
    }
}

var test = [];
for(var i = 0; i < 100; i++) {
    test.push({title:"element : " + (i+1)});
}
$(function() {
    init();
});