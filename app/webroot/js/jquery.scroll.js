(function ($){
    $.fn.infinitescroll = function(options){

        var settings = $.extend({
            nextSelector : '.next',
            nav : '.pagination',
            loadBefore: '100',
            callback: function(){}
        }, options );

        var loading = false;

        $(window).scroll(function(){
            $(settings.nav).hide();
            if($('#scroll-loader').length == 0){
                $('[data-scroll-container="true"]').after('<div id="scroll-loader" class="loader" style="display:none;text-align: center"><i></i><i></i><i></i><i></i></div>');
            }
            if(loading)
                return;
            if($(document).height()-settings.loadBefore <= ($(document).scrollTop()+$(window).height())){
                var link = $(settings.nextSelector).attr('href');
                if(link === undefined){
                    return;
                }
                loading = true;
                $('#scroll-loader').show();
                $.ajax({
                    url: link + (link.indexOf("?ajax=true") == -1 ? (link.indexOf("?") == -1) ? "?ajax=true" : "&ajax=true" : ""),
                    type: 'json',
                    success: function(data){
                        var html = data[2].html;
                        var nextLink = $(html).find(settings.nextSelector).attr('href');
                        $(settings.nextSelector).attr('href', nextLink ? nextLink : null);
                        $('[data-scroll-container="true"]').append($(html).find('[data-scroll-content="true"]'));
                        loading = false;
                        $('#scroll-loader').hide();
                        settings.callback();
                    }
                });
            }
        });

        return this;
    }
}(jQuery));