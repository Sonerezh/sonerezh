<?= $this->start('script');?>
<script type="text/javascript">
    var songs = <?= $songs ?>;
    var selectedSong = 0;
    function ajaxImport(){
        if(selectedSong<songs.length){
            $.ajax({
                url:"<?= $this->Html->url(array('controller' => 'songs', 'action' => 'ajax_import')); ?>",
                data: "path="+encodeURIComponent(songs[selectedSong]),
                dataType: 'JSON',
                success: function(json){
                    var percentage = Math.round(selectedSong*100/songs.length);
                    $('#progress').css('width', percentage+"%");
                    $('#song-name').text(json.title);
                    selectedSong++;
                    ajaxImport();
                }
            });
        }else{
            $('#progress').addClass('progress-bar-success').css('width', '100%');
            $('#infos').remove();
            $('#label').remove();
        }
    }
    ajaxImport();
</script>
<?= $this->end();?>

<div class="col-lg-12">
    <h3><?= __('Database update'); ?></h3>
    <hr />
    <p id="label">
        <?= __('Sonerezh is currently updating the database. Please be patient, it may take a few minutes...'); ?>
    </p>

    <div class="progress">
        <div id="progress" class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
        </div>
    </div>
    <div id="infos">
        <small><span class="glyphicon glyphicon-music"></span> <span id="song-name"></span></small>
    </div>
</div>