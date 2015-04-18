$(function(){
    $('#quality-slider').slider({min: 56, max: 320, steps:[128, 192, 256], change: function(val){
        $('#SettingQuality').val(val);
        $('.quality div:last').text(val+'kb/s');
    }});
    $('#quality-slider').slider("value", $('#SettingQuality').val());

    $('#SettingIndexForm').on('click', '.remove-dir', function(){
        $(this).parents('.rootpath').remove();
        updateRootpathIndex();
    });

    $('#add-root-path-field').click(function(){
        var $field = $(this).parents('.rootpath').clone();
        $field.find('input[type=hidden]').remove();
        $field.find('input').val("");
        $field.removeAttr('id').find('button').removeAttr('id').toggleClass('btn-primary btn-danger remove-dir').find('i').toggleClass('glyphicon-plus glyphicon-minus');
        $(this).parents('.rootpath').after($field);
        updateRootpathIndex();
    });

    function updateRootpathIndex(){
        $('.rootpath').each(function(i, e){
            $(e).find('input').each(function(index, element){
                var name = $(element).attr('name').replace(/\]\[([0-9]+)\]\[/, "]["+i+"][");
                $(element).attr('name', name);
            });
        });
    }

});