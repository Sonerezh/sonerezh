window.paceOptions = {
    restartOnPushState: false,
    startOnPageLoad: false
};
$(function(){
    var referrer = document.location.pathname;

    function loadPage(url, pushState, method, data) {
        Pace.restart();
        if(url.indexOf("ajax=true") == -1){
            if(url.indexOf("?") == -1){
                url = url + "?ajax=true";
            }else{
                url = url + "&ajax=true";
            }
        }
        if(pushState === undefined)pushState = true;
        if(method === undefined)method = "GET";
        if(data === undefined)data = null;
        $.ajax({
            url: url,
            data: data,
            method: method,
            processData: (typeof data == "object") ? false : true,
            contentType: (typeof data == "object") ? false : 'application/x-www-form-urlencoded; charset=UTF-8',
            success: function(response){
                $('.modal').modal('hide');
                $('body').removeClass('modal-open');

                if(pushState){
                    history.pushState(null, null, response[1].url);
                }

                if(player.paused() && response[2].html.length){
                    $('title').text(response[1].title);
                }
                $('head').append(response[0].css);
                if(response[2].flash.length){
                    $('#flash').html(response[2].flash);
                    animateFlash();
                }
                if(response[2].html.length){
                    $(document).scrollTop(0);
                    $('#content').html(response[2].html);
                }else{
                    history.replaceState(null, null, referrer);
                    response[1].url = referrer;
                }
                referrer = response[1].url;
                $('body script').remove();
                $('body').append(response[0].js);

                selectMenuItem(response[1].url);
                updateSelectedSong();

            },
            error: function(){
                document.location.reload();
            }
        });
    }

    function animateFlash(){
        $('#flash').animate({right: "15px"});
        setTimeout(function(){
            $('#flash').fadeOut("normal", function(){
                $('#flash').css('right', '-300px').show();
            });
        }, 5000);
    }

    function selectMenuItem(url){
        url = url.substr(baseurl.length+1);
        if(url.lastIndexOf('/') == -1){
            url = baseurl+"/"+url;
        }else{
            url = baseurl+"/"+url.substring(0, url.indexOf('/'));
        }
        $('#main-nav-bar.navbar li.active').removeClass("active");
        $('#main-nav-bar.navbar li a[href="'+url+'"]').parent().addClass('active');
    }

    selectMenuItem(referrer);
    $('body').infinitescroll({callback: updateSelectedSong, loadBefore: '600'});

    $(document).on('click', 'a:not(.no-ajax)', function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        if($(this).data('confirm') && !confirm($(this).data('confirm'))){
            return;
        }
        if(url != "#"){
            loadPage(url);
        }
    });

    $(document).on('submit', 'form:not(.no-ajax)', function(e){
        e.preventDefault();
        var url = $(this).attr('action');
        var data = (this.enctype == "multipart/form-data") ? new FormData(this) : $(this).serialize();
        var method = $(this).attr('method');
        loadPage(url, true, method, data);
    });

    window.onpopstate = function(){
        loadPage(window.location.href, false);
    };


    $("#content").on('show.bs.modal', '#add-to', function(event){
        var button = $(event.relatedTarget);
        var type = button.attr('data-type');
        if(type == "song"){
            var song = button.parents('[data-id]').attr('data-id');
            $(this).find('.hidden-fields').append('<input type="hidden" name="song" value="'+song+'"/>');
        }
        else if(type == "album" || type == "artist"){
            var band = button.parents('[data-band]').attr('data-band');
            $(this).find('.hidden-fields').append('<input type="hidden" name="band" value="'+band+'"/>');
        }
        if(type == "album"){
            var album = button.parents('[data-album]').attr('data-album');
            $(this).find('.hidden-fields').append('<input type="hidden" name="album" value="'+album+'"/>');
        }
    });
    $("#content").on('hide.bs.modal', '#add-to', function(){
        $(this).find('.hidden-fields').empty();
    });

});