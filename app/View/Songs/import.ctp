<?php echo $this->start('script'); ?>
<script type="text/javascript">
    var newSongsTotal = <?php echo $newSongsTotal; ?>;
    var newSongSaved = 0;
    var lastResponse = "";
    var noOutput = false;
    function ajaxImport() {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange  = function() {
            if (xhr.readyState == 4) {

                var sync_token = 0;
                var last_import = '';
                newSongSaved += 100;

                try {
                    var json_res = JSON.parse(xhr.response);
                    sync_token = json_res['sync_token'];
                    last_import = json_res['last_import'];
                } catch (error) {
                    console.log('Unable to parse response: ' + error);
                }

                if (newSongSaved >= newSongsTotal) {

                    $('#import-panel').removeClass('panel-primary').addClass('panel-success');
                    $('#import-panel-header').text("<?php echo __('Import successfully done'); ?>");
                    $('#import-progress-bar').toggleClass('progress-bar-striped progress-bar-success').css('width', '100%').text('100%');
                    $('#import-panel-footer').remove();

                    songsManager.sync(json_res['sync_token']);

                } else {

                    var percentage = Math.round(newSongSaved * 100 / newSongsTotal);
                    $('#import-progress-bar').css('width', percentage + '%').text(percentage + '%');
                    $('#import-last-label').removeClass('hidden');
                    $('#import-last').text(last_import);
                    ajaxImport();
                }
            }
        };
        xhr.open("POST", "<?php echo $this->Html->url(array('controller' => 'songs', 'action' => 'import')); ?>", true);
        xhr.send();
    }

    $('#start-import-btn').click(function(e) {
        e.preventDefault();
        $('#import-panel-header').html('<strong>' + "<?php echo __('Import currently running. Please do not leave the page.'); ?>" + '</strong>');
        $('#start-import-btn').addClass('disabled').text("<?php echo __('Running...'); ?>");
        ajaxImport();
    });
</script>
<?php echo $this->end(); ?>

<div class="col-lg-12">
    <h3><?php echo __('Update the music collection'); ?></h3>
    <hr />
    <?php if ($newSongsTotal > 0): ?>
    <div class="panel panel-primary" id="import-panel">
        <div class="panel-heading" id="import-panel-header">
            <?php echo __n(" %s song ", " %s songs ", $newSongsTotal, $newSongsTotal) . __('are ready to be imported'); ?>
        </div>
        <div class="panel-body">
            <div class="progress" style="margin-bottom: 0px;">
                <div id="import-progress-bar" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        </div>
        <div class="panel-footer" id="import-panel-footer">
            <div class="col-xs-6">
                <p class="help-block hidden" id="import-last-label">
                    <?php echo __('Last import:') . ' '; ?><span id="import-last"></span>
                </p>
            </div>
            <div class="col-xs-6 text-right">
                <button class="btn btn-info" id="start-import-btn">
                    <?php echo __('Start Import'); ?>
                </button>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        <?php echo __('All the songs have already been imported'); ?>
    </div>
    <?php endif; ?>
</div>