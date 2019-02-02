$(function(){

    $('#content').off('click', '.action-expend');
    $('#content').on('click', '.action-expend', function(){
        var albumId = $(this).attr('data-album-id');
        var $this = $(this);

        $('#album-expended').remove();
        if($this.hasClass('active')){
            $('.action-expend').css('margin-bottom', 0);
            setTimeout(function(){
                $this.removeClass('active');
            }, 200);
            return;
        }
        $('.action-expend').removeClass('active').css('margin-bottom', 0);
        $(this).addClass('loading');
        $(this).prepend('<div class="loader"><i></i><i></i><i></i><i></i></div>');

        $.ajax({
            url: baseurl + '/albums/' + encodeURIComponent(albumId),
            success: function(response){
                var $html = $(response[2].html);
                $html.css('top', $this.offset().top+$this.height()).addClass('animated flipInX');
                $html.find('.close-album').click(function(){
                    $('#album-expended').remove();
                    $('.action-expend').css('margin-bottom', 0);
                    setTimeout(function(){
                        $this.removeClass('active');
                    }, 200);
                });
                $("#content").append($html);
                updateSelectedSong();
                $this.toggleClass('active loading').css('margin-bottom', $html.height()+20);
                $this.find('.loader').remove();
            }
        });
    });
});