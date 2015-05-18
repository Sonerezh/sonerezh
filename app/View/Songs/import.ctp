<?= $this->start('script');?>
<script type="text/javascript">
    $(function(){
        var newSongsTotal = <?php echo $newSongsTotal;?>;
        var newSongSaved = 0;
        var lastResponse = "";
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange  = function() {
            var song = xhr.responseText.substring(lastResponse.length);
            lastResponse = xhr.responseText;
            if (xhr.readyState != 4) {
                $('#song-name').text(song.substring(0, song.length-4));
                newSongSaved++;
                var percentage = Math.round(newSongSaved * 100 / newSongsTotal);
                $('#progress').css('width', percentage + "%");
            } else {
                $('#progress').addClass('progress-bar-success').css('width', '100%');
                $('#infos').remove();
                $('#label').remove();
            }
        };
        xhr.open("POST", "<?= $this->Html->url(array('controller' => 'songs', 'action' => 'import')); ?>", true);
        xhr.send();
    });

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