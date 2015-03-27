var player = new Player(baseurl+"/songs/ajax_");
var mute = false;
var shortcut = true;
var volume = localStorage.getItem('volume');
var repeat = localStorage.getItem('repeat');
var shuffle = localStorage.getItem('shuffle');

function updateSelectedSong(){
    var track = player.getCurrentTrack();
    if(track){
        $('tr.on-air').removeClass('on-air');
        $('#content tr[data-id="'+track.id+'"]').addClass('on-air');
        $('#queue tr:nth-child('+(player.getCurrentIndex()+1)+')').addClass('on-air');
        if(player.paused()){
            $('#content tr[data-id="'+track.id+'"]').addClass('paused');
            $('#queue tr:nth-child('+(player.getCurrentIndex()+1)+')').addClass('paused');
        }
    }
}
function toggleQueueList() {
    if($('#queue').hasClass('queue-open')){
        $('#queue').removeClass('queue-open');
    }else{
        $('#queue').addClass('queue-open');
    }
}

function updateControls(){
    if(player.canPlay()){
        $('#play').parent().removeClass('disable');
    }else{
        $('#play').parent().addClass('disable');
    }
    if(player.hasNext()){
        $('#forward').parent().removeClass('disable');
    }else{
        $('#forward').parent().addClass('disable');
    }
    if(player.hasPrev()){
        $('#backward').parent().removeClass('disable');
    }else{
        $('#backward').parent().addClass('disable');
    }
    if(player.isShuffle()){
        $('#queue-shuffle').parent().addClass('active');
    }else{
        $('#queue-shuffle').parent().removeClass('active');
    }
    if(player.repeatState()) {
        $("#queue-repeat").parent().addClass('active');
        if(player.repeatState() == "single") {
            $("#queue-repeat").parent().addClass('single');
        }
    }else{
        $("#queue-repeat").parent().removeClass('active single');
    }
}
function getFormatedTime(s){
    s = Math.round(s);
    var minutes = Math.floor(s/60);
    var secondes = Math.round(s%60);
    minutes = minutes < 10 ? "0"+minutes : minutes;
    secondes = secondes < 10 ? "0"+secondes : secondes;
    if(isNaN(minutes) || isNaN(secondes)){
        minutes = secondes = "--";
    }
    return minutes+":"+secondes;
}


updateControls();
$('.currentTime').text(getFormatedTime(0));
$('.totalTime').text(getFormatedTime());


$(function(){

    //ACTIONS
    //NAVBAR
    $('#play').click(function(e){
        e.preventDefault();
        player.paused() ? player.play() : player.pause();
    });
    $('#forward').click(function(e){
        e.preventDefault();
        player.next();
        updateControls();
    });
    $('#backward').click(function(e){
        e.preventDefault();
        player.prev();
        updateControls();
    });
    $('#mute').click(function(e){
        e.preventDefault();
        if(mute){
            player.setVolume(mute);
            mute = false;
        }else{
            mute = player.getVolume();
            player.setVolume(0);
        }
    });
    $('#volume').slider({change: function(val){
        mute = false;
        player.setVolume(val);
    }});
    $('#volume').bind('DOMMouseScroll mousewheel', function(e){
        e.preventDefault();
        e = e.originalEvent;
        var delta = e.wheelDelta>0||e.detail<0?1:-1;
        if(delta > 0){
            player.setVolume(player.getVolume()+2);
        }else if(delta < 0){
            player.setVolume(player.getVolume()-2);
        }
        mute = false;
    });
    $('#timebar').slider({max:0,change: function(val){
        player.seek(val);
    }});
    $("#queue-repeat").on('click', function(){
        player.repeat();
    });
    $("#queue-shuffle").on('click', function(){
        player.shuffle();
    });
    $('#queue-button').on('click', function() {
        toggleQueueList();
    });
    //QUEUE
    $('#queue').on('click', '.action-play', function(){
        var index = $(this).parents('tr').index();
        var currentIndex = player.getCurrentIndex();
        if(index == currentIndex) {
            player.play();
        }else{
            player.playIndex(index);
        }
    });
    $('#queue').on('click', '.action-remove-from-queue', function() {
        player.remove($(this).parents('tr').index());
    });
    //CONTENT
    $('#content').on('click', '.action-play', function(){
        var id = $(this).parents('tr').attr('data-id');
        var song = player.getCurrentTrack();
        if(song != null && song.id == id){
            player.play();
        }else{
            player.clearPlaylist();
            player.add(id, function(song) {
                player.play(song.id);
            });
        }
    });
    $('#content').on('dblclick', 'tr[data-id]', function(){
        player.clearPlaylist();
        player.add($(this).attr('data-id'), function(song) {
            player.play(song.id);
        });
    });
    $('#content').on('click', '.action-play-artist', function(){
        player.clearPlaylist();
        var artist = $(this).parents('[data-band]').attr('data-band');
        player.addArtist(artist, function(songs) {
            player.play(songs[0].id);
        });
    });
    $('#content').on('click', '.action-play-album', function(){
        player.clearPlaylist();
        var artist = $(this).parents('[data-band]').attr('data-band');
        var album = $(this).parents('[data-album]').attr('data-album');
        player.addAlbum(artist, album, function(songs) {
            player.play(songs[0].id);
        });
    });
    $('#content').on('click', '.action-play-playlist', function(){
        player.clearPlaylist();
        var id = $(this).parents('[data-playlist]').attr('data-playlist');
        player.addPlaylist(id, function(songs) {
            player.play(songs[0].id);
        });
    });
    $('#content').on('click', '.action-play-next', function(){
        player.playNext($(this).parents('tr').attr('data-id'), function() {
            updateControls();
        });
    });
    $('#content').on('click', '.action-artist-play-next', function(){
        var artist = $(this).parents('[data-band]').attr('data-band');
        player.playArtistNext(artist, function() {
            updateControls();
        });
    });
    $('#content').on('click', '.action-album-play-next', function(){
        var artist = $(this).parents('[data-band]').attr('data-band');
        var album = $(this).parents('[data-album]').attr('data-album');
        player.playAlbumNext(artist, album, function() {
            updateControls();
        });
    });
    $('#content').on('click', '.action-playlist-play-next', function(){
        var id = $(this).parents('[data-playlist]').attr('data-playlist');
        player.playPlaylistNext(id, function() {
            updateControls();
        });
    });
    $('#content').on('click', '.action-add-to-up-next', function(){
        player.add($(this).parents('tr').attr('data-id'), function() {
            updateControls();
        });
    });
    $('#content').on('click', '.action-add-album-to-up-next', function(){
        var artist = $(this).parents('[data-band]').attr('data-band');
        var album = $(this).parents('[data-album]').attr('data-album');
        player.addAlbum(artist, album);
    });
    $('#content').on('click', '.action-add-artist-to-up-next', function(){
        var artist = $(this).parents('[data-band]').attr('data-band');
        player.addArtist(artist);
    });
    $('#content').on('click', '.action-add-playlist-to-up-next', function(){
        var id = $(this).parents('[data-playlist]').attr('data-playlist');
        player.addPlaylist(id);
    });
    $('#content').on('click', '.action-shuffle-artist', function(){
        player.clearPlaylist();
        var artist = $(this).parents('[data-band]').attr('data-band');
        player.shuffleArtist(artist, function(songs){
            player.play(songs[0].id);
        });
    });
    $('#content').on('click', '.action-shuffle-album', function(){
        player.clearPlaylist();
        var artist = $(this).parents('[data-band]').attr('data-band');
        var album = $(this).parents('[data-album]').attr('data-album');
        player.shuffleAlbum(artist, album, function(songs){
            player.play(songs[0].id);
        });
    });
    $('#content').on('click', '.action-shuffle-playlist', function(){
        player.clearPlaylist();
        var id = $(this).parents('[data-playlist]').attr('data-playlist');
        player.shufflePlaylist(id, function(songs){
            player.play(songs[0].id);
        });
    });


    $('#content, #queue').on('mousedown', 'tr[data-id]', function(e){
        e.preventDefault();
    });
    $('.navbar-player').on('mousedown', function(e){
        e.preventDefault();
    });
    $('#content, #queue').on('click', '.action-pause', function(){
        player.pause();
    });

    //MISE A JOUR DE L'UI
    player.addEventListener('play', function(){
        var track = player.getCurrentTrack();
        $('title').text(track.title+" - "+track.artist);
        $('#content tr, #queue tr').removeClass('paused');
        $('#play').removeClass('glyphicon-play').addClass('glyphicon-pause');
    });
    player.addEventListener('pause', function(){
        var track = player.getCurrentTrack();
        $('#content tr[data-id="'+track.id+'"], #queue tr[data-id="'+track.id+'"]').addClass('paused');
        $('#play').removeClass('glyphicon-pause').addClass('glyphicon-play');
    });
    player.addEventListener('volumechange', function(){
        $('#volume').slider('value', player.getVolume());
        if(player.getVolume() == 0){
            $('#mute').removeClass('glyphicon-volume-up').addClass('glyphicon-volume-off');
        }else{
            $('#mute').removeClass('glyphicon-volume-off').addClass('glyphicon-volume-up');
        }
        localStorage.setItem('volume', player.getVolume());
    });
    player.addEventListener('loadstart', function(){
        var track = player.getCurrentTrack();
        $('.song-name').text(track.title);
        $('.song-artist').text(track.artist);
        $('.song-cover').attr('src', track.cover.replace(/\\/g, '/').replace(/(\.[a-z0-9]+)/i, "_64x64$1"));
        updateSelectedSong();
        updateControls();
    });
    player.addEventListener('durationchange', function(){
        $('#timebar').slider({max: player.getDuration()});
        $('.totalTime').text(getFormatedTime(player.getDuration()));
        $('#timebar').slider('buffered', player.getBuffered());
    });
    player.addEventListener('timeupdate', function(){
        $('#timebar').slider('value', player.getCurrentTime());
        $('.currentTime').text(getFormatedTime(player.getCurrentTime()));
    });
    player.addEventListener('progress', function(){
        $('#timebar').slider('buffered', player.getBuffered());
    });
    player.addEventListener('error', function(){
        window.location.reload();
    });
    player.addEventListener('repeatChange', function(){
        updateControls();
        localStorage.setItem('repeat', player.repeatState());
    });
    player.addEventListener('shuffleChange', function(){
        updateControls();
        localStorage.setItem('shuffle', player.isShuffle());
    });
    player.addEventListener('playlistChange', function(){
        $('#queue table tbody').empty();
        var songs = player.getPlaylist();
        if(songs.length) {
            $('#alert-empty-queue').hide();
            $('#queue-list').show().find('.queue-size').text(songs.length);
        }else{
            $('#alert-empty-queue').show();
            $('#queue-list').hide();
        }
        for(var i = 0; i < songs.length; i++){
            var song = songs[i];
            var $template = $($('#queue-tr').html());
            $template.attr('data-id', song.id);
            $template.find('td.title').text(song.title);
            $template.find('td.artist').text(song.artist);
            $template.find('td.album').text(song.album);
            $template.find('.song-playtime').text(song.playtime);
            $('#queue table tbody').append($template);
        }
        updateSelectedSong();
        updateControls();
    });


    $(document).on('focus', 'input', function(){
        shortcut=false;
    });
    $(document).on('blur', 'input', function(){
        shortcut=true;
    });
    var konami = [38,38,40,40,37,39,37,39,66,65];
    var keys = [0,0,0,0,0,0,0,0,0,0];
    window.onkeydown = function(e){
        var key = e.keyCode ? e.keyCode : e.which;
        keys.shift();
        keys.push(key);
        if(konami.join('') == keys.join('')){
            player.konami();
        }
        if(shortcut){
            if(key == 32){
                e.preventDefault();
                player.paused() ? player.play() : player.pause();
            }else if(key == 39){
                player.next();
            }else if(key == 37){
                player.prev();
            }
        }
    };
    $('#queue').on('DOMMouseScroll mousewheel', function(e){
        e = e.originalEvent;
        var delta = e.wheelDelta>0||e.detail<0?1:-1;
        var bottom = $('.current-queue-inner')[0].scrollHeight - $('.current-queue-inner').height();
        if($('.current-queue-inner').scrollTop() == 0 && delta > 0
            || $('.current-queue-inner').scrollTop() == bottom && delta < 0 ){
            e.stopPropagation();
            e.preventDefault();
        }
    });
    $(document).mousedown(function(e){
        var queueInner = $('#queue');
        // If the target isn't the container nor a descendant
        if(!queueInner.is(e.target) && queueInner.has(e.target).length === 0 && queueInner.hasClass('queue-open') && e.target.id != 'queue-button' && e.target.id != 'queue-shuffle') {
            toggleQueueList();
        }
    });

    if(volume === null){
        volume = 50;
    }else if(volume == 100){
        player.setVolume(0);
    }
    player.setVolume(volume);

    if(repeat === null || repeat == "false"){
        repeat = false;
    }
    player.repeat(repeat);

    if(shuffle !== null && shuffle == "true"){
        player.shuffle(true);
    }

});