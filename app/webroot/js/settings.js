$(function(){
    var mp3Quality = {min: 56, max: 320, steps:[128, 192, 256]};
    var oggQuality = {min: 0, max: 10, steps:[1, 2, 3, 4, 5, 6, 7, 8, 9]};
    var mp3Val = 256;
    var oggVal = 8;
    var init = true;

    $('#quality-slider').slider({change: function(val) {
        $('#SettingQuality').val(val);
        updateSlider();
    }});

    $('[name="data[Setting][convert_to]"]').change(function(){
        var $checked = $('[name="data[Setting][convert_to]"]:checked');
        if($checked.val() == "mp3") {
            $('#quality-slider').slider(mp3Quality);
            if(!init) {
                $('#SettingQuality').val(mp3Val);
            }
        }else {
            $('#quality-slider').slider(oggQuality);
            if(!init) {
                $('#SettingQuality').val(oggVal);
            }
        }
        init = false;
        $('#quality-slider').slider('value', $('#SettingQuality').val());
        updateSlider();
    }).change();

    function updateSlider() {
        var val = $('#SettingQuality').val();
        if($('[name="data[Setting][convert_to]"]:checked').val() == "mp3") {
            mp3Val = val;
            val += 'kb/s';
        }else {
            oggVal = val;
        }
        $('.quality div:last').text(val);
    }

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