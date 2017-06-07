<?php echo $this->start('script'); ?>
<script type="text/javascript">
    var files_count = <?php echo $to_import_count + $to_update_count + $to_remove_count; ?>;
    var files_updated = 0;
    var lastResponse = "";
    var noOutput = false;
    function ajaxImport() {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange  = function() {
            if (xhr.readyState == xhr.DONE) {

                var sync_token = 0;
                files_updated += <?php echo SYNC_BATCH_SIZE ?>;

                try {
                    var res = JSON.parse(xhr.response);
                    sync_token = res['sync_token'];
                } catch (error) {
                    console.log('Unable to parse response: ' + error);
                }

                for (var i = 0, len = res['update_result'].length; i < len; i++) {
                    if (res['update_result'][i]['status'] == 'WARN') {

                        $('#accordion-warn').removeClass('hidden');
                        $('#warn-logs').append('[' + res['update_result'][i]['file'] + '] ' + res['update_result'][i]['message'] + '<br />');

                    } else if (res['update_result'][i]['status'] == 'ERR') {

                        $('#import-panel').toggleClass('panel-primary panel-danger');
                        $('#import-panel-header').text("<?php echo __('Something bad happened, update aborted :('); ?>");
                        $('#import-progress-bar').toggleClass('progress-bar-stripped progress-bar-danger');
                        $('#import-panel-footer').remove();
                        $('#accordion-warn').removeClass('hidden');
                        $('#warn-logs').append('[' + res['update_result'][i]['file'] + '] ' + res['update_result'][i]['message'] + '<br />');

                        songsManager.sync(res['sync_token']);
                        this.abort();
                        return;

                    }
                }

                if (files_updated >= files_count) {

                    $('#import-panel').toggleClass('panel-primary panel-success');
                    $('#import-panel-header').text("<?php echo __('Update successfully done'); ?>");
                    $('#import-progress-bar').toggleClass('progress-bar-striped progress-bar-success').css('width', '100%').text('100%');
                    $('#import-panel-footer').remove();

                    songsManager.sync(res['sync_token']);

                } else {

                    var percentage = Math.round(files_updated * 100 / files_count);
                    $('#import-progress-bar').css('width', percentage + '%').text(percentage + '%');
                    $('#import-last-label').removeClass('hidden');

                    var fullpath_last_import = res['update_result'][res['update_result'].length - 1]['file'] ? res['update_result'][res['update_result'].length - 1]['file'] : 'unknown';
                    var splitted_lat_import = fullpath_last_import.split('/');
                    $('#import-last').text(splitted_lat_import[splitted_lat_import.length - 1]);
                    ajaxImport();
                }
            }
        };
        xhr.open("POST", "<?php echo $this->Html->url(array('controller' => 'songs', 'action' => 'import')); ?>", true);
        xhr.send();
    }

    $('#start-import-btn').on('click', function(e) {
        e.preventDefault();
        $('#import-panel-header').html('<strong>' + "<?php echo __('Update currently running. Please do not leave the page.'); ?>" + '</strong>');
        $('#start-import-btn').addClass('disabled').text("<?php echo __('Running...'); ?>");
        ajaxImport();
    });

</script>
<?php echo $this->end(); ?>

<div class="col-lg-12">
    <h3><?php echo __('Update the music collection'); ?></h3>
    <hr />
    <?php if (($to_import_count > 0 || $to_remove_count > 0 || $to_update_count > 0) && !Cache::read('import')): ?>
        <?php if ($to_import_count > 5000): ?>
            <span class="help-block">
                <?php echo __('Have a huge collection? You might be interested in the CLI tool'); ?>
                <?php echo $this->Html->link(
                    '<i class="glyphicon glyphicon-question-sign"></i>',
                    'https://www.sonerezh.bzh/docs/en/command line tool.html',
                    array('escape' => false, 'target' => 'blank', 'class' => 'no-ajax')
                ); ?>
            </span>
        <?php endif; ?>

        <div class="panel panel-primary" id="import-panel">
            <div class="panel-heading" id="import-panel-header">
                <?php if ($to_import_count > 0): ?>
                    <?php echo __n("%s song detected ", "%s songs detected ", $to_import_count, $to_import_count) . '(' . $already_imported_count . __(' already imported)') . '<br/>'; ?>
                <?php endif; ?>
                <?php if ($to_update_count > 0): ?>
                    <?php echo __n("%s song updated on the file system ", "%s songs updated on the file system ", $to_update_count, $to_update_count) . '<br/>'; ?>
                <?php endif; ?>
                <?php if ($to_remove_count > 0): ?>
                    <?php echo '<br/>' . __n("%s song removed from file system ", "%s songs removed from file system ", $to_remove_count, $to_remove_count) . '<br/>'; ?>
                <?php endif; ?>
            </div>
            <div class="panel-body">
                <div class="progress" style="margin-bottom: 0;">
                    <div id="import-progress-bar" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
            </div>
            <div class="panel-footer" id="import-panel-footer">
                <div class="col-xs-6">
                    <p class="help-block hidden" id="import-last-label">
                        <?php echo __('Last update:') . ' '; ?><span id="import-last"></span>
                    </p>
                </div>
                <div class="col-xs-6 text-right">
                    <button class="btn btn-info" id="start-import-btn">
                        <?php echo __('Start Update'); ?>
                    </button>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <div class="panel-group hidden" id="accordion-warn" role="tablist" aria-multiselectable="true">
            <div class="panel panel-warning">
                <div class="panel-heading" role="tab" id="headingOne">
                    <h4 class="panel-title">
                        <a class="no-ajax" role="button" data-toggle="collapse" data-parent="#accordion-warn" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                            <?php echo __('Warning logs'); ?>
                        </a>
                    </h4>
                </div>
                <div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                    <p style="font-family: monospace;" id="warn-logs"></p>
                </div>
            </div>
        </div>
    <?php elseif (Cache::read('import')): ?>
        <div class="alert alert-warning">
            <?php echo __('The update process is already running via another client or the CLI. You can click on "Clear cache" on the settings page to remove the lock, if needed.'); ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <?php echo __('All the songs have already been imported'); ?>
        </div>
    <?php endif; ?>
</div>