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
                        steps.push(max);
                        var tmpRange = 0;
                        for(var key in steps){
                            if(range >= steps[key]){
                                tmpRange = steps[key];
                            }
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
                if(options.min != undefined){
                    $(this).data('min', options.min);
                }
                if(options.max != undefined){
                    $(this).data('max', options.max);
                }
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
                opts.change = $slider.data('change');
                var position = e.pageX - $slider.offset().left;
                var range = getRangeFromPosition(opts.min, opts.max, $slider.width(), position);
                if(opts.steps !== null){
                    opts.steps.push(opts.max);
                    var tmpRange = opts.min;
                    for(var key in opts.steps){
                        if(range+10 >= opts.steps[key]){
                            tmpRange = opts.steps[key];
                            position = getPositionFromRange(opts.min, opts.max, $slider.width(), opts.steps[key]);
                        }
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
                $(window).mousemove(function(ev){
                    ev.preventDefault();
                    opts.max = $slider.data('max');
                    opts.min = $slider.data('min');
                    opts.change = $slider.data('change');
                    opts.steps = $slider.data('steps');
                    var position = ev.pageX - $slider.offset().left;
                    var range = getRangeFromPosition(opts.min, opts.max, $slider.width(), position);
                    if(opts.steps !== null){
                        opts.steps.push(opts.max);
                        var tmpRange = opts.min;
                        for(var key in opts.steps){
                            if(range >= opts.steps[key]){
                                tmpRange = opts.steps[key];
                                position = getPositionFromRange(opts.min, opts.max, $slider.width(), opts.steps[key]);
                            }
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
            });

            $(this).addClass('slider');
            $(this).append(bufbar);
            $(this).append(slidebar);
            $(this).append(handle);
        });
    };
}(jQuery));