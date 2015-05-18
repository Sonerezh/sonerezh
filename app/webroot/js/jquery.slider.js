(function( $ ){
    $.fn.slider = function(options, range){

        var getRangeFromPosition = function(min, max, width, position){
            var range = position * (max - min) / width + min;
            if(range <= min){
                range = min;
            }else if(range >= max){
                range = max;
            }
            return range;
        };
        var getPositionFromRange = function(min, max, width, range){
            if(range < min){
                range = min;
            }else if(range > max){
                range = max;
            }
            return (range - min) * width / (max - min);
        };

        if(options == "value" || options == "buffered"){
            return this.each(function(){
                $slider = $(this);
                var slidebar = $('.slidebar', this);
                var bufbar = $('.bufbar', this);
                var handle = $('.handle', this);
                var max = $slider.data('max');
                var min = $slider.data('min');
                if(range  == undefined){
                    if(options == "value"){
                        return getRangeFromPosition(min, max, $slider.width(), $slider.data('position'));
                    }else{
                        return getRangeFromPosition(min, max, $slider.width(), bufbar.data('position'));
                    }
                }else{
                    var steps = $slider.data('steps');
                    if(steps !== null){
                        var tmpRange = 0;
                        for(var key in steps){
                            if(range >= steps[key]){
                                tmpRange = steps[key];
                            }
                        }
                        if(range >= max) {
                            tmpRange = max;
                        }
                        range = tmpRange;
                    }
                    var position = getPositionFromRange(min, max, $slider.width(), range);
                    if($slider.data('position') != position){
                        if(options == "value"){
                            handle.css('left', position);
                            slidebar.css('width', position);
                            $slider.data('position', position);
                        }else{
                            bufbar.css('width', position);
                            bufbar.data('position', position);
                        }
                    }
                }
            });
        }

        var defaultOptions = {
            min : 0,
            max : 100,
            steps: null,
            change : function(){}
        };
        var opts = $.extend({}, defaultOptions, options);

        return this.each(function(){
            if($(this).hasClass('slider')){
                $(this).data('min', opts.min);
                $(this).data('max', opts.max);
                $(this).data('steps', opts.steps);

                return;
            }
            var slidebar = $('<div class="slidebar"></div>');
            var bufbar = $('<div class="bufbar"></div>');
            var handle = $('<div class="handle"></div>');
            var $slider = $(this);

            $slider.data('position', 0);
            bufbar.data('position', 0);
            $slider.data('min', opts.min);
            $slider.data('max', opts.max);
            $slider.data('change', opts.change);
            $slider.data('steps', opts.steps);
            $slider.click(function(e){
                e.preventDefault();
                opts.max = $slider.data('max');
                opts.min = $slider.data('min');
                opts.steps = $slider.data('steps');
                opts.change = $slider.data('change');
                var position = e.pageX - $slider.offset().left;
                var range = getRangeFromPosition(opts.min, opts.max, $slider.width(), position);
                if(opts.steps !== null){
                    var tmpRange = opts.min;
                    var tolerance = ((opts.max-opts.min)/opts.steps.length)/2;
                    for(var key in opts.steps){
                        if(range >= opts.steps[key] - tolerance){
                            tmpRange = opts.steps[key];
                            position = getPositionFromRange(opts.min, opts.max, $slider.width(), opts.steps[key]);
                        }
                    }
                    if(range >= opts.max - tolerance){
                        tmpRange = opts.max;
                        position = getPositionFromRange(opts.min, opts.max, $slider.width(), opts.max);
                    }
                    range = tmpRange;
                }
                if(range == opts.min){
                    position = 0;
                }else if(range == opts.max){
                    position = $slider.width();
                }
                if($slider.data('position') != position){
                    $slider.data('position', position);
                    handle.css('left', position);
                    slidebar.css('width', position);
                    opts.change(range);
                }
            });
            handle.mousedown(function(e){
                e.preventDefault();
                e.stopPropagation();
                $(window).mousemove(function(ev){
                    ev.preventDefault();
                    opts.max = $slider.data('max');
                    opts.min = $slider.data('min');
                    opts.change = $slider.data('change');
                    opts.steps = $slider.data('steps');
                    var position = ev.pageX - $slider.offset().left;
                    var range = getRangeFromPosition(opts.min, opts.max, $slider.width(), position);
                    if(opts.steps !== null){
                        var tmpRange = opts.min;
                        var tolerance = ((opts.max-opts.min)/opts.steps.length)/2;
                        for(var key in opts.steps){
                            if(range >= opts.steps[key]-tolerance){
                                tmpRange = opts.steps[key];
                                position = getPositionFromRange(opts.min, opts.max, $slider.width(), opts.steps[key]);
                            }
                        }
                        if(range >= opts.max-tolerance) {
                            tmpRange = opts.max;
                            position = getPositionFromRange(opts.min, opts.max, $slider.width(), opts.max);
                        }
                        range = tmpRange;
                    }
                    if(range == opts.min){
                        position = 0;
                    }else if(range == opts.max){
                        position = $slider.width();
                    }
                    if($slider.data('position') != position){
                        $slider.data('position', position);
                        handle.css('left', position);
                        slidebar.css('width', position);
                        opts.change(range);
                    }
                });
            });
            $(window).mouseup(function(e){
                e.preventDefault();
                $(this).unbind('mousemove');
                handle.data('handled', false);
            });

            $(this).addClass('slider');
            $(this).append(bufbar);
            $(this).append(slidebar);
            $(this).append(handle);
        });
    };
}(jQuery));