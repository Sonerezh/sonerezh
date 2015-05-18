<?= $this->start('script');?>
<script type="text/javascript">
        var newSongsTotal = <?php echo $newSongsTotal;?>;
        var newSongSaved = 0;
        var lastResponse = "";
        var noOutput = false;
        function ajaxImport() {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange  = function() {
                if (xhr.readyState == 4) {
                    if(newSongSaved >= newSongsTotal) {
                        $('#progress').addClass('progress-bar-success').css('width', '100%');
                        $('#label').remove();
                    }else {
                        newSongSaved += 100;
                        var percentage = Math.round(newSongSaved * 100 / newSongsTotal);
                        $('#progress').css('width', percentage + "%");
                        ajaxImport();
                    }

                }
            };
            xhr.open("POST", "<?= $this->Html->url(array('controller' => 'songs', 'action' => 'import')); ?>", true);
            xhr.send();
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
</div>