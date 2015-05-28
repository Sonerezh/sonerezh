(function($) {
    $.fn.list = function(options) {

        var settings = $.extend({
            min: 1,
            table: '',
            cellTemplate: '',
            updateTemplate: function() {},
            callback: function() {},
            cellHeight: 20,
            cellsShowed: 10,
            data: []
        }, options);

        var totalSize = settings.data.length * settings.cellHeight;
        var lastIndex = 0;
        var maxHeight = totalSize-(settings.cellsShowed * settings.cellHeight);
        var maxIndex = settings.data.length - settings.cellsShowed;

        $(settings.table  + " tbody", this).append('<tr class="first-line"><td style="padding:0"></td></tr>');
        for(var i = 0; i < settings.cellsShowed; i++) {
            var $template = settings.updateTemplate($(settings.cellTemplate), settings.data[i], i);
            $(settings.table  + " tbody", this).append($template);
        }
        $(settings.table  + " tbody", this).append('<tr class="last-line" style="height:'+(totalSize-(settings.cellsShowed * settings.cellHeight))+'px"><td style="padding: 0;"></td></tr>');

        this.unbind('scroll');
        this.scroll(function() {
            var index = Math.floor($(this).scrollTop()/settings.cellHeight)-settings.min;
            if(index < settings.min && index < lastIndex) {
                index = 0;
            }
            if(index >= 0 && index != lastIndex) {
                var topSize = index * settings.cellHeight;
                var bottomSize = totalSize - (index + settings.cellsShowed) * settings.cellHeight;

                if(topSize > maxHeight) {
                    topSize = maxHeight;
                }

                $(settings.table, this).find('.first-line').height(topSize);
                $(settings.table, this).find('.last-line').height(bottomSize);

                if(index > maxIndex) {
                    index = maxIndex;
                }

                var diff = index - lastIndex;
                if(diff > 0) {
                    for(var i = 0; i < diff; i++) {
                        var $tr = $(settings.table, this).find('tr:eq(1)');
                        var realIndex = index-diff+i+settings.cellsShowed;
                        $tr = settings.updateTemplate($tr, settings.data[realIndex], realIndex);
                        $(settings.table, this).find('.last-line').before($tr);
                    }
                }else {
                    for(var i = 0; i > diff; i--) {
                        var $tr = $(settings.table, this).find('tr:eq('+settings.cellsShowed+')');
                        var realIndex = index-diff+i-1;
                        $tr = settings.updateTemplate($tr, settings.data[realIndex], realIndex);
                        $(settings.table, this).find('.first-line').after($tr);
                    }
                }
                settings.callback();
                lastIndex = index;
            }

        });
    }
}(jQuery));