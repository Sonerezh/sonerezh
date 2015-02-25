<?php echo $this->start('script'); ?>
<script>
    $(function(){
        $('#add-to-playlist-selecter').selecter({
            label: "<?php echo __('Select a playlist'); ?>"
        });

        $('#btn-show-plstinput').on('click', function(){
            $('#create-playlist-input').slideDown();
            $('#add-to-playlist-selecter').selecter('disable');
        });

        $('#btn-hide-plstinput').on('click', function(){
            $('#create-playlist-input').hide();
            $('#add-to-playlist-selecter').selecter('enable');
        });

        $('#add-to').on('hidden.bs.modal', function(){
            $('#btn-hide-plstinput').click();
        });

        $('#content').on('click', '.action-add-to', function(){
            var songId = $(this).parents('tr').attr('data-id');
            $('#SongId').val(songId);
        });
    });
</script>
<?php echo $this->end(); ?>

<!-- Add to playlist modal -->
<div class="modal fade" id="add-to">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo $this->Form->create('PlaylistMembership', array('action' => 'add')); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title"><?php echo __('Add to...'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="input-group">
                            <?php echo $this->Form->input('Playlist.id', array(
                                'id'        => 'add-to-playlist-selecter',
                                'label'     => false,
                                'options'   => $playlists,
                                'class'     => false,
                                'div'       => false,
                                'required'  => true
                            )); ?>
                            <span class="input-group-btn">
                                <button type="button" id="btn-show-plstinput" class="btn btn-primary btn-selecter"><i class="glyphicon glyphicon-plus"></i></button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div id="create-playlist-input" class="input-group" style="display: none;">
                            <?php echo $this->Form->input('Playlist.title', array(
                                'placeholder'   => __('Playlist Title'),
                                'class'         => 'form-control',
                                'div'           => false,
                                'label'         => false
                            )); ?>
                            <span class="input-group-btn">
                                <button type="button" id="btn-hide-plstinput" class="btn btn-danger"><i class="glyphicon glyphicon-remove"></i></button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="hidden-fields"></div>
            </div>
            <div class="modal-footer">
                <?php echo $this->Form->submit(__('Add'), array('class' => 'btn btn-success')); ?>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>