<?php echo $this->start('script'); ?>
<script type="text/javascript">
    var files_count = <?php echo $to_import_count; ?>;
    var files_imported = 0;
    var lastResponse = "";
    var noOutput = false;
    function ajaxImport() {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange  = function() {
            if (xhr.readyState == 4) {

                var sync_token = 0;
                files_imported += 100;

                try {
                    var res = JSON.parse(xhr.response);
                    sync_token = res['sync_token'];
                } catch (error) {
                    console.log('Unable to parse response: ' + error);
                }

                if (files_imported >= files_count) {

                    $('#import-panel').removeClass('panel-primary').addClass('panel-success');
                    $('#import-panel-header').text("<?php echo __('Import successfully done'); ?>");
                    $('#import-progress-bar').toggleClass('progress-bar-striped progress-bar-success').css('width', '100%').text('100%');
                    $('#import-panel-footer').remove();

                    songsManager.sync(res['sync_token']);

                } else {

                    var percentage = Math.round(files_imported * 100 / files_count);
                    $('#import-progress-bar').css('width', percentage + '%').text(percentage + '%');
                    $('#import-last-label').removeClass('hidden');

                    var fullpath_last_import = res['import_result'][res['import_result'].length - 1]['file']
                    var splitted_lat_import = fullpath_last_import.split('/');
                    $('#import-last').text(splitted_lat_import[splitted_lat_import.length - 1]);
                    ajaxImport();
                }

                for (var i = 0, len = res['import_result'].length; i < len; i++) {
                    if (res['import_result'][i]['status'] == 'WARN') {
                        $('#accordion-warn').removeClass('hidden');
                        $('#warn-logs').append('[' + res['import_result'][i]['file'] + '] ' + res['import_result'][i]['message'] + '<br />');
                    }
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
    <?php if ($to_import_count > 0): ?>
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
                <?php echo __n("%s song detected ", "%s songs detected ", $to_import_count, $to_import_count) . '(' . $diff_count . __(' already imported)'); ?>
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
    <?php else: ?>
        <div class="alert alert-info">
            <?php echo __('All the songs have already been imported'); ?>
        </div>
    <?php endif; ?>
</div>