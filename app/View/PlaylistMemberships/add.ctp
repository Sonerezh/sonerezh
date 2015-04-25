<?php $this->start('script');?>
<script type="text/javascript">
$(function(){
    var newOptions = <?php echo $playlists;?>;
    $('#add-to-playlist-selecter').empty();
    $('#PlaylistTitle').val('');
    $.each(newOptions, function(key, value){
        $('#add-to-playlist-selecter').append($('<option></option>').attr('value', key).text(value));
    });
    $('#add-to-playlist-selecter').selecter('destroy');
    $('#add-to-playlist-selecter').selecter({
        label: "<?php echo __('Select a playlist'); ?>"
    });
});
</script>
<?php $this->end();?>