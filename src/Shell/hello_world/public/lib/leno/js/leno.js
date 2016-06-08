
var leno = leno || {};
(function(leno) {
    /*
     * calculate the length of object,array,string
     * 计算一个对象，数组，字符串的长度
     */
    leno.length = function(obj) {
        if(typeof obj === 'string') {
            return obj.strlen();
        }
        if(typeof obj === 'object') {
            var ret = 0;
            for(var i in obj) {
                ret++;
            }
            return ret;
        }
        if(typeof obj === 'array') {
            return obj.length;
        }
        return 0;
    }

    /*
     * return the height of document
     * 返回文档的高度
     */
    leno.clientHeight = function() {
        if(document.documentElement.clientHeight == 0) {
            return document.body.clientHeight;
        }
        return document.documentElement.clientHeight;
    }

    /*
     * return the width of document
     * 返回文档的宽度
     */
    leno.clientWidth = function() {
        if(document.documentElement.clientWidth == 0) {
            return document.body.clientWidth;
        }
        return document.documentElement.clientWidth;
    }

    /*
     * return the width of element, on element display none
     * this method may return 0
     * 返回元素的宽度,如果元素没有显示，可能会返回0
     */
    leno.width = function(obj) {
        if(typeof obj.get === 'function') {
            obj = obj.get(0);
        }
        return obj.offsetWidth;
    }

    /*
     * return the height of element, on element display none
     * this method may return 0
     * 返回元素的高度，如果元素没有显示，可能返回0
     */
    leno.height = function(obj) {
        if(typeof obj.get === 'function') {
            obj = obj.get(0);
        }
        return obj.offsetHeight;
    }


    /*
     * return the position of the element
     * 返回元素的位置
     */
    leno.position = function(obj) {
        if(obj.length !== null && obj.length > 0) {
            obj = obj.get(0);
        }
        for(var y = obj.offsetTop, x=obj.offsetLeft; 
                                            obj=obj.offsetParent;) {
            y += obj.offsetTop;
            x += obj.offsetLeft;
        }
        return {x: x, y: y};
    }

    /*
     * is the object,array,string empty
     * 判断对象，数组，字符串是否为空
     */
    leno.empty = function(obj) {
        if(obj === null || obj === undefined) {
            return true;
        }
        if(typeof obj === 'object') {
            var empty = true;
            for(var i in obj) {
                empty = false;
                break;
            }
            return empty;
        }
        if(typeof obj === 'string') {
            return obj === '';
        }
        if(typeof obj === 'number') {
            return obj === NaN;
        }
        return true;
    }

    leno.query_list = {};
    leno.query = function(opts) {
        this.exec = function() {
            var _this = this;
            var dft = {
                type: 'post',
                async: true,
                cache: true,
                timeout: opts.timeout || 20*1000,
                error: function(req) {
                    _this.querying = false;
                    if(req.statusText == 'timeout') {
                        leno.alert('请求超时');
                    }
                },
                complete: function(req) {
                    _this.querying = false;
                    if(typeof opts.done === 'function') {
                        var complete = opts.done;
                        complete(req);
                    }
                }
            };
            if(this.querying) {
                return;
            }
            this.querying = true;
            $.ajax($.extend(dft, opts));
        };
        if(leno.query_list[opts.id] == null) {
            lenoo.query_list[opts.id] = this;
        }
        leno.query_list[opts.id].exec();
    }

    /*
     * make the object draganddrop ability
     * 让一个元素支持拖放
     */
    leno.dragAndDrop = function(opts) {
        var target = {
            toggle: opts.toggle,
            mover: opts.mover,
            callback: {
                onDrag: function(target) {
                    // if the callback return false, drag will not trigger
                    return true;
                },
                onDown: function(elem, target) {
                    // if the callback return false, drop will not hold
                    return false;
                },
                onMove: function(elem, target) {
                    // if the callback return false, move will interapt
                    return true;
                }
            }
        };
        target.callback = $.extend(target.callback, opts.callback);
        target.down = false;
        target.css_position = target.mover.css('position');
        target.css_top = target.mover.css('top');
        target.css_left = target.mover.css('left');
        target.css_z_index = target.mover.css('z-index');
        target.toggle.mousedown(function(e) {
            if(e.buttons == 2 && !target.callback.onDrag(target)) {
                return;
            }
            $(this).css('cursor', 'move');
            target.begin = leno.position(target.mover);
            target.pointer = {
                x: e.screenX,
                y: e.screenY
            }
            if(target.mover.css('position') != 'fixed') {
                target.mover.css('position', 'absolute');
            }
            target.mover.css('z-index', 100000);
            target.down = true;
            e.stopPropagation();
        }).mouseup(function(e) {
            $(this).css('cursor', 'default');
            target.down = false;
            if(!target.callback.onDown($(this))) {
                target.mover.css('position', target.css_position);
                target.mover.css('top', target.css_top);
                target.mover.css('left', target.css_left);
            }
            target.mover.css('z-index', target.css_z_index);
        });
        $(window).mousemove(function(e) {
            if(!target.down) {
                return;
            }
            var content = target.mover;
            var position = {
                x: target.begin.x + e.screenX - target.pointer.x,
                y: target.begin.y + e.screenY - target.pointer.y
            }
            target.mover.css('left', position.x);
            target.mover.css('top', position.y);
        }).click(function() {
            target.down = false;
        });
        return target;
    };

    /*
     * return the scrolltop of the window
     * 返回页面的向下滚动的高度
     */
    leno.scrollTop = function() {
        return parseFloat($(window).scrollTop());
    }

    /*
     * return the scrollleft of the window
     * 返回页面向左滚动的高度
     */
    leno.scrollLeft = function() {
        return parseFloat($(window).scrollLeft());
    }

    leno.profile = function(opts) {
        var getPos = function(opts, showTop, showLeft) {
            var orientation = opts.orientation || 'vertical';
            var toggle = opts.toggle;
            var tp = leno.position(toggle);
            var arrow = (function(size) {
                switch(size) {
                    case 1:
                        return { width: 5, cls: 'arrow-1' };
                    case 3:
                        return { width: 15, cls: 'arrow-3' };
                    case 4:
                        return { width: 20, cls: 'arrow-4' };
                    case 5:
                        return { width: 25, cls: 'arrow-5' };
                    default:
                        return { width: 10, cls: 'arrow-2' };
                }
            })(opts.arrow_size);
            if(opts.width == null || opts.width == 0) {
                opts.width = leno.width(opts.node);
            }
            if(opts.height == null || opts.height == 0) {
                opts.height = leno.height(opts.node);
            }
            // 箭头在上下的情况
            if(orientation == 'vertical') {
                if(tp.y < leno.scrollTop() + opts.height - 40) {
                    showTop = true;
                }
                var movex = -arrow.width+leno.width(toggle)/2;
                var movey = arrow.width;
                if(showTop) {
                    opts.node.find('.arrow').removeClass('arrow-bottom')
                                            .addClass('arrow-top');
                    var position = {
                        y: tp.y + leno.height(toggle) + movey,
                        x: tp.x + movex
                    };
                    opts.node.find('.arrow').after(opts.node.find('.ccc'));
                    opts.node.find('.arrow').css(
                        'marginTop', -2*arrow.width + 1
                    );
                    opts.node.find('.arrow-border').css({
                        top: (-arrow.width+1)+'px',
                        left: (-arrow.width) + 'px'
                    });
                } else {
                    opts.node.find('.arrow').removeClass('arrow-top')
                                            .addClass('arrow-bottom');
                    var position = {
                        y: tp.y - opts.height - movey,
                        x: tp.x + movex
                    };
                    opts.node.find('.arrow').before(opts.node.find('.ccc'));
                    opts.node.find('.arrow').css(
                        'margin-top', '1px'
                    );
                    opts.node.find('.arrow-border').css({
                        left: (-arrow.width) + 'px',
                        top: (-arrow.width-1)+'px'
                    });
                }
                if(opts.width < leno.clientWidth() && 
                        opts.width/2 + tp.x < leno.clientWidth() &&
                                                tp.x - opts.width/2 > 0) {
                    var    movl = opts.width/2 - arrow.width;
                } else {
                    var movl = tp.x + opts.width - leno.clientWidth();
                }
                if(movl < 4) {
                    movl = 4;
                }
                if(position.x+opts.width+10-leno.clientWidth() > movl) {
                    movl = position.x+opts.width+10-leno.clientWidth();
                }
                opts.node.find('.arrow').css('margin-left', movl);
                position.x -= movl;
            } else {
            // 箭头在左右的情况
                if(tp.x < leno.scrollLeft() + opts.width - 2*arrow.width) {
                    showLeft = true;
                }
                var movy = 0;
                var movx = 0;
                if(showLeft) {
                    opts.node.find('.arrow').removeClass('arrow-right')
                                    .addClass('arrow-left')
                                    .css('margin-left', -2*arrow.width);
                    var position = {
                        y: tp.y + leno.height(toggle)/2,
                        x: tp.x + leno.width(toggle) + arrow.width
                    };
                    opts.node.find('.arrow-border').css({
                        marginTop: arrow.width+'px',
                        marginLeft: arrow.width+1+'px'
                    });
                } else {
                    opts.node.find('.arrow').removeClass('arrow-left')
                                            .addClass('arrow-right')
                                        .css('margin-left', opts.width);
                    var position = {
                        y: tp.y + leno.height(toggle)/2,
                        x: tp.x - opts.width - arrow.width
                    };
                    opts.node.find('.arrow-border').css({
                        marginTop: arrow.width+'px',
                        marginLeft: arrow.width-1+'px'
                    });
                }
                var halfTop = tp.y + opts.height - leno.clientHeight();
                if(opts.height < leno.clientHeight() && halfTop < 10) {
                    var    movl = opts.height/2;
                } else {
                    var movl = halfTop + 30;
                }
                if(movl < arrow.width * 1.5) {
                    movl = arrow.width * 1.5;
                }
                position.y -= movl;
                opts.node.find('.arrow').css({
                    marginTop: movl - arrow.width + 'px',
                    marginRight: '2px'
                });
            }
            opts.node.find('.arrow, .arrow .arrow-border')
                .addClass(arrow.cls);
            return position;
        }

        var profile = {
            init: function(opts) {
                var showTop = opts.showTop || false;
                if(leno.empty(opts.toggle)) {
                    throw 'Node Not Found';
                }
                var node = false;
                if(opts.node) {
                    node = opts.node;//.clone();
                }
                opts.node = $('<div class="leno-profile" style="min-width: 80px"><div class="arrow"><div class="arrow-border"></div></div></div>');
                var arrow = opts.node.find('.arrow');
                if(typeof opts.callback !== 'object') {
                    opts.callback = {};
                }
                var content = $('<div class="ccc">正在加载。。。</div>');
                if(showTop) {
                    opts.node.append(content);
                } else {
                    opts.node.prepend(content);
                }
                if(opts.url) {
                    $.get(opts.url, opts.params, function(data) {
                        content.html(data);
                    });
                } else {
                    content.empty();
                    content.append(node.css('display', 'block'));
                }
                opts.position = {x: 0, y: 0};
                opts.css = opts.css || '____hhh____';
                if(opts.style == null) {
                    opts.style = {};
                }
                opts.style = {
                    position: opts.style.position || 'absolute',
                    overflow: opts.style.overflow || 'visible'
                };
                delete opts.url;
                if(opts.callback == null) {
                    opts.callback = {};
                }
                var beforeShow = opts.callback.beforeShow;
                opts.callback.beforeShow = function(l, after) {
                    var tafter = function(layer) {
                        var position = 'absolute';
                        var node = opts.toggle.get(0);
                        while(node != null) {
                            if($(node).css('position') == 'fixed') {
                                position = 'fixed';
                                break;
                            }
                            node = node.parentElement;
                        }
                        layer.content.css('position', position);
                        layer.setPosition(getPos(layer.opts, showTop));
                        layer.opts.toggle.css('z-index', '10000');
                        after(layer);
                    }
                    if(typeof beforeShow == 'function') {
                        beforeShow(l, function(layer) {
                            tafter(layer);
                        });
                    } else {
                        tafter(l);
                    }
                }
                var afterShow = opts.callback.afterShow;
                opts.callback.afterShow = function(l) {
                    l.setPosition(getPos(l.opts, showTop));
                    if(typeof afterShow == 'function') {
                        afterShow(l);
                    }
                }
                var beforeHide = opts.callback.beforeHide;
                opts.callback.beforeHide = function(l, after) {
                    if(typeof beforeHide == 'function') {
                        beforeHide(l);
                    }
                    l.opts.toggle.css('z-index', 'auto');
                    return true;
                }
                opts.type = layer.TYPE_DROPDOWN;

                this.layer = new layer(opts);
            }
        }

        profile.init(opts);
        return profile;
    }

    leno.colorSelector = function(opts) {
        var colors = ['#000000', '#FFFFFF', '#FF0000', '#FF7F00',
            '#FFFF00', '#00FFFF', '#0000FF', '#8B00FF'];
        var node = $('<div class="leno-color-selector"></div>');    
        var his = $('<div data-id="cs-f"></div>').css({
            borderBottom: '1px solid #999',
            margin: '0px 0px 5px 0px'
        }).appendTo(node);
        for(var i = 0; i < colors.length; ++i) {
            $('<span class="selector-item" data-id="'+colors[i]+'" style="background-color: '+colors[i]+'"></span>').appendTo(his);
        }
        var content = $('<div class="selector-item-container"></div>');
        content.appendTo(node);
        var red = 0;
        var green = 0;
        var blue = 0;
        var container = content;
        for(var x = 0; x < 4; x++) {
            red = x*64;
            if(red.toString(16).length < 2) {
                red = '0'+red.toString(16);
            } else {
                red = red.toString(16);
            }
            for(var y = 0; y < 4; y++) {
                green = y*64;
                if(green.toString(16).length < 2) {
                    green = '0'+green.toString(16);
                } else {
                    green = green.toString(16);
                }
                for(var z = 0; z < 4; z++) {
                    blue = z*64;
                    if(blue.toString(16).length < 2) {
                        blue = '0'+blue.toString(16);
                    } else {
                        blue = blue.toString(16);
                    }
                    var rgb = red+green+blue;
                    $('<span data-id="#'+rgb+'"></span>').css({
                        backgroundColor: '#'+rgb
                    }).addClass('selector-item').appendTo(container);
                }
            }
        }
        opts.node.append(node);
        var colorSelector = {
            init: function(node) {
                node.find('.selector-item').click(function() {
                    var color = $(this).attr('data-id');
                    opts.callback.onSelect(color);
                });
            }
        };
        return colorSelector;
    };
    leno.shelter = { 
        show: function(id, isloading) {
            var cover = $('<div></div>');
            if(isloading) {
                $('<div class="leno-loading"></div>')
                .css('margin-top', leno.clientHeight()/2 - 30+'px')
                .appendTo(cover);
            }
            new layer({
                id: '-shelter-node-'+id,
                node: cover,
                type: layer.TYPE_SHELTER,
                position: layer.left_top,
                css: 'leno-shelter',
                style: { overflow: 'hidden' },
                showAnimation: function(layer, after) {
                    layer.content.show();
                    after(layer);
                },
                hideAnimation: function(layer, after) {
                    layer.content.hide();
                    after(layer);
                },
                callback: {
                    beforeShow: function(l, after) {
                        l.overflow = $('body').css('overflow');
                        $('body').css('overflow', 'hidden');
                        after(l);
                    },
                    afterHide: function(l) {
                        $('body').css('overflow', l.overflow);
                    }
                },
                width: '100%',
                height: '100%',
                maxWidth: '100%',
                maxHeight: '100%'
            });
        },
        hide: function(id) {
            Layer.get('-shelter-node-'+id).hide();
        }
    }
    
    leno.clone = function(elem) {
        var obj = {};
        for(var i in elem) {
            if(typeof elem[i] === 'object') {
                obj[i] = leno.clone(elem[i]);
            } else {
                obj[i] = elem[i];
            }
        }
        return obj;
    }

    leno.randomNum = function(min, max) {
        var range = max - min;
        return min + Math.round(range*Math.random());
    }

    leno.imgFullShow = function(img, src) {
        var src = src || img.attr('src');
        var new_img = new Image();
        new_img.src = src;
        if(new_img.complete) {
            afterImgDone($(new_img));
        } else {
            new_img.onload = function() {
                afterImgDone($(new_img));
            }
        }
        function afterImgDone(new_img) {
            var width = parseInt(new_img.css('width'));
            new_img.css({
                maxWidth: '100%',
                maxHeight: leno.clientHeight() - 60
            });
            $(window).resize(function() {
                new_img.css({
                    axWidth: '100%',
                    maxHeight: leno.clientHeight() - 60
                });
            });
            var node = $('<div></div>').append(new_img);
            var big = new layer({
                id: 'big',
                type: layer.TYPE_BIGIMG,
                node: node,
                shelter: true,
                css: 'empty',
                position: layer.center,
                close: true
            });
        }
    }

    leno.hiddenBox = function(opts) {
        var box = function(opts) {
            var profile;
            var timeout = null;
            var id = opts.id;
            var triggerType;
            var popts = {
                id: opts.id,
                toggle: opts.trigger,
                width: opts.width,
                height: opts.height,
                url: opts.url,
                node: opts.node,
                orientation: opts.orientation,
                params: opts.params,
                hide: true,
                css: opts.css,
                arrow_size: opts.arrow_size,
                showTop: opts.showTop,
                showLeft: opts.showLeft,
                style: opts.style,
                callback: {}
            };
            popts.callback = $.extend(opts.callback, popts.callback);
            var onCreate = popts.callback.onCreate;
            if(opts.showType == null) {
                opts.showType = 'hover';
            }
            opts.trigger.css('position', 'relative');
            if(opts.showType == 'hover') {
                popts.callback.onCreate = function(layer) {
                    layer.content.mouseenter(function() {
                        clearTimeout(timeout);
                    }).mouseleave(function() {
                        clearTimeout(timeout);
                        timeout = setTimeout(function() {
                            layer.hide();
                        }, 500);
                    });
                    if(typeof onCreate === 'function') {
                        onCreate(layer);
                    }
                }
                opts.trigger.mouseenter(function() {
                    if(!$(this).attr('disabled')) {
                        Layer.get(id).show();
                    }
                }).mouseleave(function() {
                    if(!$(this).attr('disabled')) {
                        clearTimeout(timeout);
                        var _this = this;
                        timeout = setTimeout(function() {
                            Layer.get(id).hide();
                        }, 500);
                    }
                });
            } else {
                popts.callback.onCreate = function(l) {
                    l.content.find('li').click(function() {
                        l.content.find('li').removeClass('active');
                        $(this).addClass('active');
                        l.hide();
                    });
                    l.content.click(function(e) {
                        e.stopPropagation();
                    });
                    if(typeof onCreate == 'function') {
                        onCreate(l);
                    }
                }
                opts.trigger.click(function(e) {
                    if(!$(this).attr('disabled')) {
                        var l = Layer.get(opts.id);
                        if(l != null) {
                            if(l.isShow) {
                                l.hide();
                            } else {
                                l.show();
                            }
                        }
                    }
                    e.stopPropagation();
                });
            }
            profile = leno.profile(popts);
        };
        return box(opts);
    }

    leno.dropdown = function(node) {
        var trigger = node.find('[data-toggle=dropdown]');
        var selectedholder = node.find('[data-holder=dropdown]');
        var down = node.find('.leno-dropdown-menu');
        var replace = node.attr('data-replace');
        var type = node.attr('data-type');
        if(type == '' || type == null) {
            type = 'click';
        }
        var css = node.attr('data-css');
        var showTop = node.attr('data-top');
        if(showTop == 'false') {
            showTop = false;
        } else {
            showTop = true;
        }
        if(selectedholder.length === 0) {
            selectedholder = trigger;
        }
        var opts = {
            trigger: trigger,
            id: node.attr('id'),
            node: down,
            showType: type,
            showTop: showTop,
            width: down.width() + 2,
            height: down.height(),
            orientation: node.attr('data-orientation'),
            css: css,
            callback: {
                onCreate: function(l) {
                    if(replace !== 'false') {
                        l.content.find('li').click(function() {
                            selectedholder.html($(this).html());
                        });
                    }
                },
                afterShow: function(l) {
                    l.opts.width = leno.width(
                        l.content.find('.leno-dropdown-menu')
                    ) + 2;
                    l.opts.height = 
                        leno.height(l.content.find('.leno-dropdown-menu'));
                }
            }
        };
        leno.hiddenBox(opts);
    }
    leno.disableDropdown = function(id) {
        $('#'+id).find('[data-toggle=dropdown]').attr('disabled', true);
    }
    leno.enableDropdown = function(id) {
        $('#'+id).find('[data-toggle=dropdown]').removeAttr('disabled');
    }
    leno.scrollTo = function(node, relate, timeout, on_done) {
        var relate = relate || 70;
        if(timeout == null) {
            timeout = 30;
        }
        if(typeof node == 'number') {
            var y = node;
        } else {
            var pos = leno.position(node);
            var y = pos.y;
        }
        var screenHeight = parseInt($(window).height());
        var begin = parseInt($(window).scrollTop());
        var    end = y - relate;
        var path = end-begin;
        var max = path*2/timeout - 1;
        var step = (max-1)/(timeout - 1);
        var i = 0;
        var speed = max;
        var interval = setInterval(function() {
            if(i == timeout || begin < 0) {
                clearInterval(interval);
                if(typeof on_done === 'function') {
                    on_done();
                }
                return;
            }
            begin = begin + speed;
            speed -= step;
            $(window).scrollTop(begin);
            i++;
        }, 1);
    }
})(leno);
var Layer = layer = (function() {
    var dfunc = function(layer) { return true; }
    $(window).resize(function() {
        layer.resize();
    });
    var layer = function(opts) {
        this.init = function(opts) {
            var callback = $.extend(
                leno.clone(layer.default.callback), 
                opts.callback
            );
            delete opts.callback;
            this.opts = $.extend(leno.clone(layer.default.options), opts);
            this.opts.callback = callback;
            if(this.opts.node == null) {
                throw 'Node Not Found!';
                return;
            }
            if(this.opts.id == null) {
                throw 'ID Not Found!';
                return;
            }
            this.opts.type = this.opts.type || 0;
            this.opts.position = opts.position || layer.center;
            opts.style = opts.style || {};
            opts.style.position = opts.style.position || 'fixed';
            opts.style.overflow = opts.style.overflow || 'auto';
            this.content = $('<div></div>').css({
                display: 'none',
                overflow: opts.style.overflow,
                position: opts.style.position
            }).addClass(this.opts.css)
            .attr('id', '__layer_'+this.opts.id)
            .appendTo('body');
            if(this.opts.url) {
                this.opts.node = $('<iframe></iframe>').attr({
                    frameborder: 'none'
                });
            }
            this.content.append(this.opts.node);
            if(layer.get(opts.id)) {
                layer.get(opts.id).remove();
            }
            var _this = this;
            if(this.opts.close) {
                var close = $('<div class="l-win-close"></div>')
                .click(function() {
                    _this.hide();
                }).append('<span class="zmdi zmdi-close"></span>')
                .prependTo(this.content);
                this.content.css('overflow', 'visible');
            }
            this.opts.callback.onCreate(this);
            this.isShow = false;
            if(this.opts.hide == false) {
                this.show();
            }
            layer.instance[this.opts.id] = this;
            return this;
        }

        this.getType = function() {
            return this.opts.type;
        }

        this.show = function() {
            var before = this.opts.callback.beforeShow;
            var _this = this;
            before(this, function() {
                if(_this.isShow) {
                    return;
                }
                _this.isShow = true;
                if(_this.opts.shelter) {
                    leno.shelter.show(_this.opts.id);
                }
                if(Layer.showInstance == null) {
                    Layer.showInstance = {};
                }
                var length = leno.length(Layer.showInstance) + 1000;
                Layer.showInstance[_this.opts.id] = length;
                _this.content.css('z-index', length);
                var aniShow = _this.opts.showAnimation;
                aniShow(_this, function() {
                    var after = _this.opts.callback.afterShow;
                    after(_this);
                    _this.resize();
                });
                _this.resize();
            });
            return this;
        }

        this.hide = function(func) {
            if(!this.isShow) {
                return this;
            }
            var before = this.opts.callback.beforeHide;
            if(!before(this)) {
                return this;
            }
            var _this = this;
            var aniHide = this.opts.hideAnimation;
            aniHide(_this, function() {
                var after = _this.opts.callback.afterHide;
                after(_this);
                if(typeof func == 'function') {
                    func(_this);
                }
            });
            if(this.opts.shelter) {
                leno.shelter.hide(this.opts.id);
            }
            if(typeof Layer.showInstance == 'object') {
                delete Layer.showInstance[this.opts.id];
            }
            this.isShow = false;
            return this;
        }

        this.remove = function() {
            var before = this.opts.callback.beforeRemove;
            var _this = this;
            if(before(this)) {
                _this.content.remove();
                delete layer.instance[_this.opts.id];
                if(typeof Layer.showInstance == 'object') {
                    delete Layer.showInstance[this.opts.id];
                }
                var after = _this.opts.callback.afterRemove;
                after();
            }
            if(this.opts.shelter) {
                leno.shelter.hide(this.opts.id);
            }
        }

        this.getPos = function() {
            var position = this.opts.position;
            var node = this.content;
            if(this.opts.width) {
                var width = this.opts.width;
                if(typeof width == 'string' && width.match(/\%/)) {
                    var width = leno.clientWidth()*parseInt(width)/100;
                }
                this.opts.node.css('width', width);
            }
            if(this.opts.height) {
                var height = this.opts.height;
                if(typeof height == 'string' && height.match(/\%/)) {
                    var height = leno.clientHeight()*parseInt(height)/100;
                }
                this.opts.node.css('height', height);
            }
            var _this = this;
            var sheight = leno.clientHeight();
            var swidth = leno.clientWidth();
            var thisheight = node.height();
            var thiswidth = node.width();
            var hmid = (sheight - thisheight)/2;
            hmid = hmid < 0 ? 0 : hmid;
            var wmid = (swidth - thiswidth)/2;
            wmid = wmid < 0 ? 0 : wmid;
            var ret = {
                left: 'auto',
                top: 'auto',
                bottom: 'auto',
                right: 'auto'
            };
            switch(position) {
                case layer.left_top:
                    ret['left'] = '0px';
                    ret['top'] = '0px';
                    break;
                case layer.top:
                    ret['left'] = wmid + 'px';
                    ret['top'] = '0px';
                    break;
                case layer.right_top:
                    ret['right'] = '0px';
                    ret['top'] = '0px';
                    break;
                case layer.left:
                    ret['left'] = '0px';
                    ret['top'] = hmid + 'px';
                    break;
                case layer.center:
                    ret['left'] = wmid + 'px';
                    ret['top'] = hmid + 'px';
                    break;
                case layer.right:
                    ret['right'] = '0px';
                    ret['top'] = hmid + 'px';
                    break;
                case layer.bottom:
                    ret['bottom'] = '0px' ;
                    ret['left'] = wmid + 'px';
                    break;
                case layer.left_bottom:
                    ret['bottom'] = '0px' ;
                    ret['left'] = '0px';
                    break;
                case layer.right_bottom:
                    ret['right'] = '0px' ;
                    ret['bottom'] = '0px';
                    break;
                default:
                    ret['top'] = position.y + 'px';
                    ret['left'] = position.x + 'px';
            }
            return ret;
        }

        this.resize = function(timeout) {
            var pos = this.getPos();
            var swidth = this.opts.maxWidth;
            var sheight = this.opts.maxHeight;
            if(!sheight) {
                sheight = '95%';
            }
            if(!swidth) {
                swidth = '95%';
            }
            this.content.css({
                top: pos['top'],
                left: pos['left'],
                maxWidth: swidth,
                maxHeight: sheight
            });
            return this;
        }

        this.setPosition = function(position) {
            this.opts.position = position;
            this.resize();
        }
        this.init(opts);
    }

    layer.default = {
        options: {
            drag: false,
            position: layer.center,
            node: null,
            hide: false,
            id: null,
            css: 'leno-layer',
            hideAnimation: function(layer, afterHide) {
                var pos = layer.getPos();
                if(pos.bottom == 'auto') {
                    var _top = parseInt(pos['top']);
                    var new_top = _top - 20;
                    layer.content.animate({
                        top: new_top,
                        opacity: 0,
                    }, 150, 'linear', function() {
                        $(this).css('display', 'none');
                        afterHide(layer);
                    });
                } else {
                    var _bottom = parseInt(pos['bottom']);
                    var new_bottom = _bottom - 20;
                    layer.content.animate({
                        bottom: new_bottom,
                        opacity: 0
                    }, 150, 'linear', function() {
                        $(this).css('display', 'none');
                        afterHide(layer);
                    });
                }
            },
            showAnimation: function(l, afterShow) {
                var pos = l.getPos();
                if(pos.bottom == 'auto') {
                    l.content.css('opacity', 0);
                    var _top = parseInt(pos['top']);
                    var new_top = _top - 20;
                    var height = leno.height(l.content);
                    l.content.css('top', new_top +'px');
                    l.content.show();
                    l.content.animate({
                        opacity: 1, 
                        top: _top
                    }, 150, 'linear', function() {
                        afterShow(l);
                    });
                } else {
                    l.content.css('opacity', 0);
                    var _bottom = parseInt(pos['bottom']);
                    var new_bottom = _bottom - 20;
                    l.content.css('bottom', new_bottom +'px');
                    l.content.show();
                    l.content.animate({
                        opacity: 1, 
                        bottom: _bottom
                    }, 150, 'linear', function() {
                        afterShow(l);
                    });
                }
            }
        },
        callback: {
            beforeShow: function(layer, func) {func(layer);},
            afterShow: dfunc,
            beforeHide: dfunc,
            afterHide: dfunc,
            beforeRemove: dfunc,
            afterRemove: dfunc,
            onCreate: dfunc
        }
    };
    layer.left = 1;
    layer.left_top = 2;
    layer.top = 3;
    layer.right_top = 4;
    layer.right = 5;
    layer.right_bottom = 6;
    layer.bottom = 7;
    layer.left_bottom = 8;
    layer.center = 9;

    layer.TYPE_MODAL = 'modal';
    layer.TYPE_DROPDOWN = 'dropdown';
    layer.TYPE_SHELTER = 'shelter';
    layer.TYPE_EDITOR = 'editor';
    layer.TYPE_CONTENT_MENU = 'content_menu';
    layer.TYPE_BIGIMG = 'big_img';
    layer.TYPE_NOTIFIER = 'notifier';

    layer.instance = {};

    layer.showInstance = {};

    layer.resize = function(timeout) {
        for(var i in layer.instance) {
            var l = layer.instance[i];
            l.resize(timeout);
        }
    }
    layer.get = function(id) {
        return layer.instance[id];
    }

    layer.modal = layer.win = function(opts) {
        if(opts.position == null) {
            opts.position = layer.center;
        }
        var $node = $('<div></div>');
        layer.win.down = false;
        var $header = $('<div class="leno-window-header"></div>')
        .appendTo($node);
        var $title = $('<div>'+opts.title+'</div>')
                    .appendTo($header);
        if(opts.callback == null) {
            opts.callback = {};
        }
        if(opts.callback.onMinimize) {
            var minimize = opts.callback.onMinimize;
            var $mini = $('<div class="lw-h-oper zmdi zmdi-window-minimize"></div>')
            .mousedown(function() {
                return false;
            }).hover(function() {
                $(this).css('cursor', 'pointer');
            }).click(function() {
                minimize(Layer.get(opts.id));
            }).appendTo($header);
        }
        var $close = $('<div class="lw-h-oper zmdi zmdi-close"></div>')
        .mousedown(function() {
            return false;
        }).click(function(e) {
            Layer.get(opts.id).hide();
        }).hover(function() {
            $(this).css('cursor', 'pointer');
        }).appendTo($header);
        if(opts.url) {
            var f = $('<iframe></iframe>').appendTo($node);
            f.attr('src', opts.url)
             .attr('frameborder', 0);
        } else if(opts.node.length > 0){
            var f = $('<div class="lw-section"></div>');
                f.css('overflow', 'auto');
                f.appendTo($node).append(opts.node);
        } else {
            throw 'Not a url and Not a node';
        }
        delete opts.title;
        opts.node = $node;
        if(opts.css == null) {
            opts.css = 'leno-window';
        }
        var beforeShow = opts.callback.beforeShow;
        var setContentSize = function(layer) {
            var opts = layer.opts;
            var width = opts.width;
            var height = opts.height;
            var maxWidth = leno.clientWidth() * 0.9;
            var maxHeight = leno.clientHeight() * 0.9;
            if(width == null) {
                width = layer.content.width() + 2;
            }
            if(height == null) {
                height = layer.content.height() + 2;
            }
            if(width > maxWidth) {
                width = maxWidth;
                opts.width = width;
            }
            if(height > maxHeight) {
                height = maxHeight;
                opts.height = height;
            }
            layer.content.css({width: width, height: height});
            var iframe = layer.content.find('iframe');
            if(iframe.length > 0) {
                iframe.attr({width: width - 2, height: height - 52});
            }
            if(opts.node.find('.lw-toolbox').length > 0) {
                var node = opts.node.find('.lw-toolbox').next();
                node.css({maxHeight: (height - 90)+'px', overflow: 'auto'});
            } else {
                opts.node.css('max-height', (height - 50)+'px');
            }
        };
        opts.callback.beforeShow = function(layer, after) {
            setContentSize(layer);
            if(typeof beforeShow == 'function') {
                beforeShow(layer, after);
            } else {
                after(layer);
            }
        };
        opts.callback.onCreate = function(layer) {
            $(window).resize(function() {
                setContentSize(layer);
            });
            leno.dragAndDrop({
                toggle: $header,
                mover: layer.content,
                callback: {
                    onDown: function() {
                        return true;
                    }
                }
            });
        };
        opts.style = { overflow: 'hidden' };
        opts.maxWidth = '95%';
        opts.maxHeight = '95%';
        opts.shelter = opts.shelter || false;
        opts.type = layer.TYPE_MODAL;
        return new layer(opts);
    }

    layer.length = function() {
        var length = 0;
        for(var i in layer.instance) {
            ++length;
        }
        return length;
    }
    return layer;
})();
//leno.layer = Layer;
(function(L) {
    var t = 20;
    L.alert = function(msg, timeout, func, data) {
        var timeoutobj;
        if(layer) {
            if(L.empty(timeout)) {
                timeout = 4000;
            }
            if(typeof msg == 'string') {
                var node = $('<div class="zmdi zmdi-alert-circle">'+msg+'</div>');
            } else {
                var node = msg;
            }
            var notilayer = new layer({
                id: 'notifier',
                node: node,
                css: 'leno-notifier leno-alert',
                position: layer.bottom,
                type: layer.TYPE_NOTIFIER,
                callback: {
                    afterHide: function() {
                        if(typeof func == 'function') {
                            func(data);
                        }
                    }
                }
            });
            clearTimeout(timeoutobj);
            timeoutobj = setTimeout(function() {
                notilayer.hide();
            }, timeout);
        }
    };

    L.confirm = function(msg, opts, callback) {
        var content = $('<div></div>');
        if(typeof msg === 'string') {
            $('<div class="zmdi zmdi-help">'+msg+'</div>').css({
                color: 'red',
                minHeight: '60px',
                padding: '10px 0px',
                textAlign: 'center'
            }).appendTo(content);
        } else {
            content.append(msg);
        }
        var toolbar = $('<div></div>').css('text-align', 'center')
                    .appendTo(content);
        $('<button class="leno-btn leno-btn-success">'+opts.ok+'</button>')
        .css({marginRight: '20px'}).click(function() {
            callback(true);
            Layer.get('confirm').hide();
        }).appendTo(toolbar);
        $('<button class="leno-btn leno-btn-red">'+opts.cancel+'</button>')
        .click(function(){
            callback(false);
            Layer.get('confirm').hide();
        }).appendTo(toolbar);
        return new layer({
            id: 'confirm',
            node: content,
            css: 'leno-notifier leno-confirm',
            position: layer.top,
            type: layer.TYPE_NOTIFIER,
            shelter: true
        });
    }
})(leno);
var Form = (function(L) {
    var form = function(opts) {
        var dft_opts = {
            node: null,
            method: 'put',
            callback: {
                beforeSubmit: function() {
                    return true;
                },
                afterReturn: function() {
                    return true;
                }
            },
        }
        this.form_opts = $.extend(dft_opts, opts);
        this.form_opts.callback = $.extend(
                dft_opts.callback,
                opts.callback
        );
        this.form_id = opts.id;
        opts.node.find('input, textarea, select').bind('input propertychange', function() {
            var regexp = $(this).attr('data-regexp');
            var val = $.trim($(this).val());
            var node = $(this).parent();
            if(!node.hasClass('leno-input-group')) {
                node = $(this);
            }
            if(regexp != null && regexp != '' && !(new RegExp(regexp).test(val))) {
                node.addClass('leno-error').removeClass('leno-success');
            } else {
                node.addClass('leno-success').removeClass('leno-error');
            }
        });
        (function(f) {
            var submit = f.form_opts.node.find('[data-id=submit]');
            var url = f.form_opts.url;
            if(submit.length == 0) {
                throw 'not submit button in form';
            }
            submit.click(function() {
                var data = form.validate(f);
                data._method = f.form_opts.method;
                if(!data) {
                    return false;
                }
                var beforeSubmit = f.form_opts.callback.beforeSubmit;
                var a = beforeSubmit(data);
                if(a === false) {
                    return;
                }
                if(leno.empty(f.form_opts.url)) {
                    throw 'submit url is empty';
                    return false;
                }
                submit.attr('disabled', true);
                $.ajax({
                    url: f.form_opts.url,
                    type: 'post',
                    data: data,
                    complete: function(response) {
                        submit.removeAttr('disabled');
                        if(response.status != 200) {
                            return;
                        }
                        var after = f.form_opts.callback.afterReturn;
                        after(response);
                    }
                });
            });
            if(f.form_opts.enter !== false) {
                f.form_opts.node.children().keydown(function(e) {
                    if(e.keyCode == 13) {
                        submit.click();
                    }
                    return true;
                });
            }
        })(this);
        leno.form_instance[opts.id] = this;
    };

    form.prototype.beforeSubmit = function(callback) {
        this.form_opts.callback.beforeSubmit = callback;
    }

    form.prototype.afterReturn = function(callback) {
        this.form_opts.callback.afterReturn = callback;
    }

    form.validate = function(f) {
        var node = f.form_opts.node;
        var error = false;
        var data = {};
        node.find('input, textarea, select').each(function() {
            var regstr = $(this).attr('data-regexp');
            var val = $.trim($(this).val());
            var name = $.trim($(this).attr('name'));
            var reg = new RegExp(regstr);
            if(!reg.test(val)) {
                var node = $(this).parent();
                if(!node.hasClass('leno-input-group')) {
                    node = $(this);
                }
                node.addClass('leno-error').removeClass('leno-success');
                var msg = $.trim($(this).attr('data-msg'));
                leno.alert(msg);
                error = true;
                return false;
            }
            data[name] = val;
            return true;
        });
        if(error) {
            return false;
        }
        return data;
    }
    return form;
})();
leno.form = Form;
leno.form_instance = {};
leno.getForm = function(id) {
    return leno.form_instance['leno-form-'+id];
}
var ImageUploader = (function() {
    var upload = function(opts) {
        this.init = function(opts) {
            var dft_opts = leno.clone(upload.dft_opts);
            this.position = opts.position || layer.top;
            var callback = leno.clone(dft_opts.callback);
            delete dft_opts.callback;
            this.callback = $.extend(callback, opts.callback);
            var opts = $.extend(dft_opts, opts);
            this.id = opts.id;
            this.on_off = opts.on_off || upload.dft;
            this.preview = opts.preview;
            this.upload = opts.upload;
            this.empty = opts.empty;
            this.url = opts.url;
            this.view_url = opts.view_url;
            this.size = opts.size || 2*1024*1024;
            this.length = opts.length;
            this.agent = opts.agent;

            // 如果on_of为upload.default,构建界面
            var _this = this;
            if(this.on_off == upload.dft) {
                var node = imageUploadView(this);
                new layer.modal({
                    id: this.id,
                    position: _this.position,
                    node: node,
                    title: '上传图片...',
                    callback: {
                        afterShow: function(layer) {
                            initEvent(_this);
                        },
                        afterHide: function() {
                            _this.files = {};
                            if( typeof _this.callback.onClose == 'function') {
                                _this.callback.onClose(upload);
                            }
                        }
                    }
                });
            } else {
                initEvent(this);
            }
        }
        this.getFiles = function() {
            return this.files;
        }
        this.init(opts);
    }
    var tips = '<div class="tips">请点击添加按钮添加图片</div>';
    function imageUploadView(upload) {
        var node = $(""+
        '<div class="image-uploader">'+
            '<div class="lw-toolbox">'+
                '<input type="file" name="image_selector" multiple />'+
                '<button class="leno-btn leno-btn-success" style="margin-right: 10px"  name="image_selector_fack">'+
                    '添加'+
                '</span>'+
                '<button class="leno-btn" style="margin-right: 10px" name="image-empty">清空</button>'+
                '<button class="leno-btn leno-btn-error" style="margin-right: 10px" name="image-upload">上传</button>'+
            '</div>'+
            '<div class="image-preview">'+
                tips+
            '</div>'+
        '</div>');
        if(upload.agent != null) {
            upload.agent = $('<iframe src="'+upload.agent+'"></iframe>');
            upload.agent.attr({
                width: 0,
                height: 0,
                frameborder: 0
            }).appendTo(node);
        }
        node.find('.lw-toolbox').css('text-align', 'right');
        upload.preview = node.find('.image-preview');
        upload.on_off = node.find('[name=image_selector_fack]');
        upload.empty = node.find('[name=image-empty]');
        upload.upload = node.find('[name=image-upload]');
        upload.preview.css({
            height: 'calc(100% - 60px)',
            overflow: 'auto',
            padding: '5px'
        });
        node.find('[name=image_selector]').css({
            display: 'inline-block',
            width: '0px',
            position: 'relative',
            padding: '0px',
            margin: '0px',
            opacity: '0'
        }).hover(function() {
            $(this).css('cursor', 'pointer');        
        });
        return node;
    }

    function addToPreview(upload, file) {
        var file = file || {};
        if(!/^image\/\w+/.test(file.type)) {
            leno.alert('你上传的不是图片');
            return;
        }
        if(file.size > upload.size) {
            leno.alert('请上传小于'+upload.size/1024/1024+'MB的图片');
            return;
        }
        if(upload.length != null && 
                        leno.length(upload.files) >= upload.length) {
            leno.alert('你只能添加'+upload.length+'张图片');
            return;
        }
        var preview = upload.preview;
        var reader = new FileReader();
        if(typeof upload.files !== 'object') {
            upload.files = {};
        }
        if(!leno.empty(upload.files[file.size])) {
            leno.alert('你已经添加过该图片，已掠过');
            return;
        }
        upload.files[file.size] = file;
        reader.onload = function(e) {
            var url = e.target.result;
            var pic = $('<div class="pic-show" data-id="'+file.size+'">'+
                        '<img src="'+url+'" title="单击查看大图" />'+
                        '<div title="点击删除该图片"><span class="zmdi zmdi-close"></span></div>'+
                        '</div>').appendTo(preview);
            pic.find('img').click(function() {
                leno.imgFullShow($(this));
            });
            pic.find('div').click(function() {
                var idx = $(this).attr('name');
                delete upload.files[file.size];
                pic.remove();
                Layer.get(upload.id).resize();
                if($('.pic-show').length == 0) {
                    $('.image-preview').append(tips);
                }
            }).hover(function() {
                $(this).css('cursor', 'pointer');
            });
            $('.image-preview .tips').remove();
            layer.get(upload.id).resize();
        }
        reader.readAsDataURL(file);
    }
    function getXhr() {
        var xhr = new window.XMLHttpRequest();
        xhr.addEventListener("progress", function(evt){
            if (evt.lengthComputable) {
                var percentComplete = evt.loaded / evt.total;
                $('#up_pro_view').html(percentComplete*100);
                if(percentComplete == 1) {
                    leno.alert('上传完成');
                }
            }
        }, false);
        return xhr;
    }
    function initEvent(upload) {
        $('[name=image_selector]').change(function() {
            var files = this.files;
            if(upload.callback.beforeSelect(upload)) {
                for(var i in files) {(function(file) {
                    if(typeof file === 'object') {
                        addToPreview(upload, file);
                        if(this.files) {
                            delete this.files[i];
                        }
                    }
                })(files[i])}
            }
            upload.callback.afterSelect(upload);
        });
        upload.on_off.click(function() {
            $('[name=image_selector]').click();
        });
        var deleteNode = function(node) {
            node.fadeOut('fast', function() {
                delete upload.files[$(this).attr('data-id')];
                var next = $(this).next();
                $(this).remove();
                layer.get(upload.id).resize();
                if(next.hasClass('pic-show')) {
                    deleteNode(next);
                }
            });
        }
        upload.empty.click(function() {
            if(leno.empty(upload.files)) {
                leno.alert('当前没有选择照片');
                return;
            }
            leno.confirm('点清空你将丢失还未上传到服务器上的照片?',
            {ok: '清空', cancel: '不清空'}, function(e) {
                if(e) {
                    deleteNode(upload.preview.find('.pic-show').first());
                }
            });
        });
        upload.upload.click(function() {
            var files = upload.files;
            if(leno.empty(files)) {
                leno.alert('请先选择图片');
                upload.on_off.click();
                return;
            }
            function uploading(upload, num) {
                if(num == null) {
                    num = 0;
                }
                var file = null;
                for(var i in upload.files) {
                    file = upload.files[i];
                    break;
                }
                // 没有取出图片，图片已全部上传完
                if(file == null) {
                    if(typeof upload.callback.onDone == 'function') {
                        upload.callback.onDone(upload);
                    }
                    upload.image_url = [];
                    return;
                }
                var noti = '<div><div>正在上传第'+(num+1)+'张图片</div>'+
                                '<br />'+
                //        '<div>已上传<span id="up_pro_view">0</span>%</div>'+
                    '</div>';
                leno.alert(noti, 500000);
                var formdata = new FormData();
                formdata.append(file.size, file);
                if(typeof upload.callback.each == 'function') {
                    var data = upload.callback.each(formdata);
                } else {
                    var data = formdata;
                }
                $.ajax({
                    xhr: function() {
                        return getXhr();
                    },
                    url: upload.url,
                    type: 'POST',
                    data: data,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if(typeof upload.image_url != 'object') {
                            upload.image_url = [];
                        }
                        upload.image_url.push(response.responseText);
                        if(upload.callback.onSuccess(upload, response)) {
                            $('[data-id='+file.size+']').remove();
                            delete upload.files[file.size];
                            uploading(upload, ++num);
                        }
                    },
                    error: function(response) {
                        upload.callback.onError(response);
                    }
                });
            }
            uploading(upload, 0);
        });
    }
    upload.dft_opts = {
        id: '',
        on_of: upload.dft,
        preview: upload.dft,
        multiple: true,
        callback: {
            beforeSelect: function() { return true; },
            afterSelect: function() { return true; },
            onSuccess: function() { return true; },
            onError: function() { return true; }
        }
    };
    upload.dft = 'default';
    return upload;
})();
leno.editor = (function() {
    var ViewConstructor = function(editor) {
        var opts = editor.config;
        var root = document.getElementById(opts.id);
        var _returnHTML = root.innerHTML;
        $(root).empty();
        $(root).addClass('leno-editor');
        var toolbar = document.createElement('div');
        toolbar.setAttribute('class', 'editor-toolbar');
        root.appendChild(toolbar);
        var nexttoolbar = document.createElement('div');
        root.appendChild(nexttoolbar);
        var content = document.createElement('iframe');
        content.setAttribute('src', '');
        content.setAttribute('frameborder', '0');
        content.setAttribute('width', opts.width);
        root.appendChild(content);

        var statusbar = document.createElement('div');
        statusbar.setAttribute('class', 'editor-statusbar');
        root.appendChild(statusbar);
        
        var state = document.createElement('span');
        state.innerHTML = '当前已输入<span data-id="current" style="color: green">0</span>/'+editor.config.limit;
        state.setAttribute('data-id', 'status');
        statusbar.appendChild(state);

        var oper = document.createElement('div');
        oper.setAttribute('class', 'editor-oper');
        statusbar.appendChild(oper);

        if(typeof editor.config.operation === 'object') {
            for(var i = 0; i < editor.config.operation.length; ++i) {
                (function(operation) {
                    if(operation.css == null) {
                        operation.css = 'leno-btn';
                    } $('<button class="'+operation.css+'">'+operation['label']+'</button>').click(function() {
                        if(typeof operation['click'] === 'function') {
                            operation['click'](editor);
                        }
                    }).appendTo(oper);
                })(editor.config.operation[i])
            }
        }
        editor.toolbarfixed = false;

        var editorContent = {
            getDocument: function() {
                return content.contentWindow.document;
            },
            getWindow: function() {
                return content.contentWindow;
            },
            getRanges: function() {
                var selection = content.contentWindow.getSelection();
                var len = selection.rangeCount;
                var ranges = [];
                for(var i = 0; i < len; ++i) {
                    ranges.push(selection.getRangeAt(i));
                }
                return ranges; 
            },
            exec: function(command, UI, params) {
                editorContent.getDocument()
                        .execCommand(command, UI, params);
                editor.contentResize();
            },
            getFrame: function() {
                return content;
            },
            getToolbar: function() {
                return toolbar;
            },
            getStatusBar: function() {
                return statusbar;
            },
            getFocusNode: function() {
                var selection = content.contentWindow.getSelection();
                return selection.anchorNode;
            },
            getContent: function() {
                return root;
            }
        };
        editor.editorContent = editorContent;
        editor.opts = opts;
        return _returnHTML;
    }

    var config = {
        toolbar: [
            'fullscreen', 'html', 'select_all', 'copy', 'cut', 'paste',
            's', 'font_bold', 'font_italic',
            'font_underline', 'font_size','font_fore_color', 
            'font_back_color', 's', 'align_left','align_center', 
            'align_right', 'align_full', 's','heading','ordered_list',
            'unordered_list', 'text_height', 'text_width', 'code', 's',
            's', 'image', 'link', 'insert_table',
            's', 'indent','outdent', 's', 'undo', 'redo'
        ],
        callback: {
            ready: function() { return true; },
            input: function() { return true; },
            onImageButtonClick: function(editor) {
                var url = editor.config.imageUploadUrl;
                editor.imageUploader = new ImageUploader({
                    id: editor.config.id,
                    url: url,
                    title: '编辑器图片上传',
                    callback: {
                        onDone: function(imager) {
                            var done = editor.config.callback.onImageUploadDone;
                            done(imager, editor);
                        }
                    }
                });
            },
            onImageUploadDone: function(imager, editor) {
                var baseurl = editor.config.imageGetUrl;
                var images = imager.image_url;
                for(var i = 0; i < images.length; ++i) {
                    var url = baseurl + '?md5=' + images[i];
                    editor.editorContent.exec('InsertImage', false, url);
                }
            },
            afterFullscreen: function(editor) {
            
            },
            afterUnfullscreen: function(editor) {
            
            }
        },
        width: '100%',
        height: 400,
        toolbarFixedHeight: 0, // number || null
        imageUploadUrl: '',
        autoIncrement: true,
        mode: 'all',
        limit: 100000,
        fullscreen: {
            top: 1,
            bottom: 1,
            left: 1,
            right: 1
        }
        //, operation: [
        //    {
        //        label: 'hello',
        //        click: function() {
        //        
        //        }
        //    }   
        //]
    };

    var lenoEditor = function(opts) {
        if(opts.toolbar != null) {
            config.toolbar = opts.toolbar;
        }
        if(opts.callback != null) {
            config.callback = $.extend(config.callback, opts.callback);
        }
        if(opts.fullscreen != null) {
            config.fullscreen = $.extend(config.fullscreen, opts.fullscreen);
        }
        config = $.extend(config, opts);
        if(config.toolbarFixedHeight != null && config.autoIncrement) {
            config.toolbarFix = true;
        } else {
            config.toolbarFix = false;
        }
        config.operation = opts.operation;
        this.config = config;
        var html = ViewConstructor(this);
        var editor = this;
        editor.editorContent.getFrame().focus(function() {
            editor.focus = true;
        });
        window.onload = function () {
            lenoEditor.toolbar.init(editor, lenoEditor.toolbar.items);
            editor.setContent(html);
            editor.focus();
            editor.resize();
            var doc = editor.editorContent.getDocument();
            var body = doc.getElementsByTagName('body');
            var head = doc.getElementsByTagName('head');
            $(head).append('<style>td {border: 1px solid #999;}</style>');
            $(body[0]).bind('input propertychange', function(e) {
                var state = editor.editorContent.getStatusBar();
                var c = $.trim(editor.getTxt().length);
                if(c > editor.config.limit) {
                    leno.alert('你只能输入'+editor.config.limit+'的文本内容');
                    return false;
                }
                editor.contentResize();
                $(state).find('[data-id=current]').html(c);
                config.callback.input(editor);
            }).click(function() {
                lenoEditor.toolbar.changeState(editor);
                var l = layer.get('contextmenu');
                if(l && l.hide) {
                    l.hide();
                }
            }).keyup(function() {
                lenoEditor.toolbar.changeState(editor);
            });
            config.callback.ready(editor);
            editor.editorContent.getDocument().designMode = 'On';
            lenoEditor.contextmenu.init(editor);

            // 开启工具栏置于顶部功能
            $(window).scroll(function() {
                var root = editor.editorContent.getContent();
                var pos = leno.position(root);
                var toolbar = editor.editorContent.getToolbar();
                var opts = editor.opts;
                var content = body[0];
                var oldtoolbarfixed = editor.toolbarfixed;
                if(opts.toolbarFix) {
                    var sheight = leno.scrollTop();
                    $(toolbar).css('top', Math.max(pos.y, sheight + opts.toolbarFixedHeight));
                    if(sheight + opts.toolbarFixedHeight > pos.y +
                            $(root).height() - $(toolbar).height()) {
                        $(toolbar).hide();
                    } else {
                        $(toolbar).show();
                    }
                } else if(editor.toolbarfixed) {
                    $(toolbar).removeClass('stickTop');
                    $(toolbar).css('top', 'auto');
                    $(toolbar).css('left', 'auto');
                    editor.toolbarfixed = false;
                }
            });
        };

        $(window).resize(function() {
            editor.contentResize();
        });

        if(leno.editorInstance == null) {
            leno.editorInstance = {};
        }
        leno.editorInstance[opts.id] = this;
    }

    lenoEditor.prototype.getContent = function() {
        var doc = this.editorContent.getDocument();
        var body = doc.getElementsByTagName('body');
        return body[0].innerHTML;
    }

    lenoEditor.prototype.resize = function(width, height) {
        if(width == null) {
            width = this.config.width;
        }
        if(height == null) {
            height = this.config.height;
        }
        if(typeof width == 'number') {
            var w = width + 'px';
        } else {
            var w = width;
        }
        if(typeof height == 'number') {
            var h = height + 'px';
        } else {
            var h = height;
        }
        var root = this.editorContent.getContent();
        var toolbar = this.editorContent.getToolbar();
        var statusbar = this.editorContent.getStatusBar();
        var frame = this.editorContent.getFrame();

        root.style.width = w;
        root.style.height = h;
        frame.setAttribute('width', w);
        $(toolbar).css({
            width: this.getSize().width - 2,
            left: leno.position(root).x + 1
        });
        $(toolbar).next().css({
            width: this.getSize().width - 2,
            height: $(toolbar).height() + 10,
            left: leno.position(root).x + 1
        });
        if(this.toolbarfixed) {
            var frameheight = height - statusbar.offsetHeight - 8;
        } else {
            var frameheight = height - toolbar.offsetHeight - statusbar.offsetHeight - 8;
        }

        frame.setAttribute('height', frameheight+'px');
        return this;
    }

    lenoEditor.prototype.fullscreen = function() {
        if(this.fullscreened) {
            return;
        }
        $('body').css('overflow', 'hidden');
        var height = leno.clientHeight() - this.config.fullscreen.top
                                        - this.config.fullscreen.bottom;
        var width = leno.clientWidth() - this.config.fullscreen.left
                                        - this.config.fullscreen.right;
        var content = this.editorContent.getContent();
        this.unfullscreenedcss = {
            position: $(content).css('position'),
            top: $(content).css('top'),
            left: $(content).css('left'),
            zIndex: $(content).css('z-index')
        };

        // 关闭toolbar浮动
        this.unfullscreenedtoolbarfix = this.config.toolbarFix;
        this.config.toolbarFix = false;

        // 关闭文本自增长
        this.unfullscreenautoIncrement = this.config.autoIncrement;
        this.config.autoIncrement = false;

        // 全屏展示
        this.resize(width, height);
        
        // 调整工具栏位置
        var toolbar = this.editorContent.getToolbar();
        this.toolbar_style = toolbar.style;
        $(toolbar).css('top', 0);
        $(toolbar).css('left', 0);

        $(content).css({
            position: 'fixed',
            top: this.config.fullscreen.top,
            left: this.config.fullscreen.left,
            zIndex: '1000'
        });
        var doc = this.editorContent.getDocument();
        var body = doc.getElementsByTagName('body');
        $(body[0]).css('overflow-y', 'auto');
        lenoEditor.toolbar.activeItem(this, 'fullscreen');
        this.fullscreened = true;
        this.config.callback.afterFullscreen(this);
    }

    lenoEditor.prototype.unfullscreen = function() {
        if(this.fullscreened != true) {
            return;
        }
        $('body').css('overflow', 'auto');
        var content = this.editorContent.getContent();
        $(content).css(this.unfullscreenedcss);

        var doc = this.editorContent.getDocument();
        var body = doc.getElementsByTagName('body');
        $(body[0]).css('overflow-y', 'hidden');

        this.config.toolbarFix = this.unfullscreenedtoolbarfix;
        this.config.autoIncrement = this.unfullscreenautoIncrement; 

        this.resize(this.config.width, this.config.height);
        this.editorContent.getToolbar().style = this.toolbar_style;
        lenoEditor.toolbar.unactiveItem(this, 'fullscreen');
        this.fullscreened = false;
        this.contentResize();
        this.config.callback.afterUnfullscreen(this);
    }

    lenoEditor.prototype.setToolbarFixedHeight = function(height) {
        this.config.toolbarFixedHeight = height;
    }

    lenoEditor.prototype.getSize = function() {
        return {
            width: this.editorContent.getContent().offsetWidth,
            height: this.editorContent.getContent().offsetHeight
        }
    }

    lenoEditor.prototype.getTxt = function(root) {
        var doc = this.editorContent.getDocument();
        var body = doc.getElementsByTagName('body');
        return $(body[0]).text();
    }

    lenoEditor.prototype.setContent = function(html) {
        var doc = this.editorContent.getDocument();
        var body = doc.getElementsByTagName('body');
        body[0].focus();
        body[0].innerHTML = html;
        this.contentResize();
        return this;
    }

    lenoEditor.prototype.focus = function() {

        var frame = this.editorContent.getFrame();
        frame.focus();
        return this;
    }

    lenoEditor.prototype.contentResize = function() {
        if(this.fullscreened) {
            var height = leno.clientHeight() - this.config.fullscreen.top
                                        - this.config.fullscreen.bottom;
            var width = leno.clientWidth() - this.config.fullscreen.left
                                        - this.config.fullscreen.right;
        } else {
            if(!this.config.autoIncrement) {
                return false;
            }
            var size = this.getSize();
            var doc = this.editorContent.getDocument();

            var body = doc.getElementsByTagName('body');
            if(this.toolbarfixed) {
                var newHeight = body[0].offsetHeight +
                    this.editorContent.getStatusBar().offsetHeight + 8;
            } else {
                var newHeight = body[0].offsetHeight +
                    this.editorContent.getToolbar().offsetHeight + 
                    this.editorContent.getStatusBar().offsetHeight + 8;
            }
            if(newHeight < this.opts.height) {
                newHeight = this.opts.height;
            }
            $(body[0]).css({
                overflowY: 'hidden'
            });
            var height = newHeight;
            var width = this.opts.width;
        }
        this.resize(width, height);
        return this;
    }

    lenoEditor.prototype.setToolbarFix = function(toolbarFix) {
        this.config.toolbarFix = toolbarFix;
    }

    lenoEditor.contextmenu = {

        init: function(editor) {
            var doc = editor.editorContent.getDocument();
            $(doc).bind('contextmenu', function(e) {
                var items = [];
                var params = lenoEditor.tableEdit.getCurrent(editor);
                if(!params) {
                    return;
                } else {
                    items = lenoEditor.tableEdit.menu;
                }
                var node = $('<ul class="leno-editor-menu"></ul>');
                for(var i = 0; i < items.length; ++i) {
                (function(item) {
                    if(item.label == 's') {
                        $('<li><div class="hr"></div></li>').appendTo(node);
                    } else {
                        $('<li><span class="'+item.icon+'"></span>'+item.label+'</li>').click(function() {
                            item.click(editor, params);
                            var l = layer.get('contextmenu');
                            if(l && l.hide) {
                                l.hide();
                            }
                        }).appendTo(node);
                    }
                })(items[i])}
                var extra = leno.position(
                    editor.editorContent.getContent()
                );
                new layer({
                    id: 'contextmenu',
                    type: layer.TYPE_MENU,
                    node: node,
                    position: {x:e.clientX+extra.x,y:e.clientY+extra.y},
                    css: 'leno-editor-menu-container',
                    style: {
                        position: 'absolute'
                    }
                });
                return false;
            });
        }
    };

    function toolbardropdown(editor, item, opts) {
        if(opts.callback == null) {
            opts.callback = {};
        }
        if(opts.callback.beforeShow) {
            var beforeShow = opts.callback.beforeShow;
        }
        opts.callback.beforeShow = function(l, after) {
            if($(item).hasClass('disabled')) {
                return;
            }
            if(editor.toolbarfixed) {
                l.content.css({
                    position: 'fixed'
                });
            } else {
                l.content.css({
                    position: 'absolute'
                });
            }
            if(typeof beforeShow == 'function') {
                beforeShow(l, after);
            } else {
                after(l);
            }
        }
        opts.showType = 'click';
        //opts.css = opts.css || 'black-profile';
        leno.hiddenBox(opts);
        $(item).attr('data-drapdown', opts.id);
    }

    lenoEditor.toolbar = {
        getItem: function(editor, id) {
            var toolbar = editor.editorContent.getToolbar();
            return $(toolbar).find('[data-id='+id+']');
        },
        activeItem: function(editor, id) {
            var item = lenoEditor.toolbar.getItem(editor, id);
            item.addClass('active');
        },
        unactiveItem: function(editor, id) {
            var item = lenoEditor.toolbar.getItem(editor, id);
            item.removeClass('active');
            var lid = $(item).attr('data-dropdown');
            var l = layer.get(lid);
            if(l && l.isShow && l.hide) {
                l.hide();
            }
        },
        disableItem: function(editor, id) {
            var item = lenoEditor.toolbar.getItem(editor, id);
            item.addClass('disabled');
            lenoEditor.toolbar.unactiveItem(editor, id);
        },
        undisableItem: function(editor, id) {
            var item = lenoEditor.toolbar.getItem(editor, id);
            item.removeClass('disabled');
        },
        switchToggle: function(editor, name, id) {
            var toolbar = editor.editorContent.getToolbar();
            $(toolbar).find('[radio-name='+name+']').each(function() {
                $(this).removeClass('active');
            });
            lenoEditor.toolbar.activeItem(editor, id);
        },
        changeState: function(editor) {
            doc = editor.editorContent.getDocument();

            // 如果当前状态为输入粗体字，激活font_bold
            if(doc.queryCommandState('bold')) {
                lenoEditor.toolbar.activeItem(
                    editor, 'font_bold'
                );
            } else {
                lenoEditor.toolbar.unactiveItem(
                    editor, 'font_bold'
                );
            }
            // 如果当前状态为输入斜体，激活font_italic
            if(doc.queryCommandState('italic')) {
                lenoEditor.toolbar.activeItem(
                    editor, 'font_italic'
                );
            } else {
                lenoEditor.toolbar.unactiveItem(
                    editor, 'font_italic'
                );
            }
            // 如果当前状态为输入下划线，激活font_underline
            if(doc.queryCommandState('underline')) {
                lenoEditor.toolbar.activeItem(
                    editor, 'font_underline'
                );
            } else {
                lenoEditor.toolbar.unactiveItem(
                    editor, 'font_underline'
                );
            }

            // 字体颜色
            var item = lenoEditor.toolbar.getItem(
                editor, 'font_fore_color'
            );
            $(item).css('color', doc.queryCommandValue('forecolor'));

            // 字体背景色 BUGGY
           // var item = lenoEditor.toolbar.getItem(
           //     editor, 'font_back_color'
           // );
           // console.log(doc.queryCommandValue('backcolor'));
           // $(item).find('.selected-color').css(
           //     'color', doc.queryCommandValue('backcolor')
           // );
            // 字体大小
            var item = lenoEditor.toolbar.getItem(editor, 'font_size');
            var sizearray = [10,12,16,18,24,32,48];
            var sizeindex = doc.queryCommandValue('fontsize');
            if(sizeindex == '') {
                var size = 16;
            } else {
                var size = sizearray[sizeindex - 1];
            }
            $(item).html(size);

            // 段落和头
            var formatblock = doc.queryCommandValue('formatblock');
            if(formatblock == '') {
                formatblock = 'p';
            }
            var item = lenoEditor.toolbar.getItem(editor, 'heading');
            $(item).html(formatblock);

            // 对齐方式
            if(doc.queryCommandState('justifyleft')) {
                lenoEditor.toolbar.switchToggle(
                    editor, 'align', 'align_left'
                );
            } else if(doc.queryCommandState('justifyright')) {
                lenoEditor.toolbar.switchToggle(
                    editor, 'align', 'align_right'
                );
            } else if(doc.queryCommandState('justifycenter')) {
                lenoEditor.toolbar.switchToggle(
                    editor, 'align', 'align_center'
                );
            } else if(doc.queryCommandState('justifyfull')) {
                lenoEditor.toolbar.switchToggle(
                    editor, 'align', 'align_full'
                );
            } else {
                lenoEditor.toolbar.switchToggle(
                    editor, 'align', 'align_left'
                );
            }
        },
        init: function(editor, items) {
            var addToView = function (itemwrapper, configitems) {
                for(var i = 0; i < configitems.length; ++i) {
                    var j = configitems[i];
                    if(j == 'seperator' || j == 's') {
                        var last = $(itemwrapper).find('li').last()
                                                            .find('span');
                        if(last.hasClass('editor-toolbar-seperator')) {
                            continue;
                        }
                        var itemli = document.createElement('li');
                        itemwrapper.appendChild(itemli);
                        var item = document.createElement('span');
                        var c = 'toolbar-item editor-toolbar-seperator';
                        item.setAttribute('class', c);
                        itemli.appendChild(item);
                        continue;
                    } else {
                        var itemContent = items[j];
                        if(itemContent == null) {
                            continue;
                        }
                        var itemli = document.createElement('li');
                        itemwrapper.appendChild(itemli);
                        var item = document.createElement('span');
                        item.setAttribute('data-id', j);
                        if(itemContent['radio_name'] != null) {
                            item.setAttribute(
                                'radio-name', itemContent['radio_name']
                            );
                        }
                        var c = 'toolbar-item '+ itemContent.css;
                        if(itemContent.style != null) {
                            for(var z in itemContent.style) {
                                item.style[z] = itemContent.style[z];
                            }
                        }
                    }
                    item.setAttribute('class', c);
                    item.setAttribute('title', itemContent.title);
                    itemli.appendChild(item);
                    (function(it) {
                        if(typeof it.callback.onConstruct == 'function') {
                            it.callback.onConstruct(item, editor);
                        }
                        item.addEventListener('click', function() {
                            editor.focus();
                            if($(this).hasClass('disabled')) {
                                return;
                            }
                            switch(it['type']) {
                                case 'radio':
                                    $('[radio-name='+it['radio_name']+']')
                                    .removeClass('active');
                                    $(this).addClass('active');
                                    break;
                                case 'check':
                                    if($(this).hasClass('active')) {
                                        $(this).removeClass('active');
                                    } else {
                                        $(this).addClass('active');
                                    }
                                    break;
                            }
                            if(typeof it.callback.onClick == 'function') {
                                it.callback.onClick(editor);
                            }
                        });
                    })(itemContent);
                }
            }

            var toolbar = editor.editorContent.getToolbar();
            var config = editor.config;
            var itemwrapper = document.createElement('ul');
            toolbar.appendChild(itemwrapper);
            addToView(itemwrapper, config.toolbar);
            $(editor.editorContent.getDocument()).click(function() {
                editor.focus();
                for(var i in layer.instance) {
                    var l = layer.get(i);
                    if(l.getType() == layer.TYPE_DROPDOWN) {
                        l.hide();
                    }
                }
            });
        },
        items: {
            paste: {
                css: 'zmdi zmdi-paste',
                title: '粘贴',
                callback: {
                    onClick: function(editor) {
                        editor.editorContent.exec('paste');
                    }
                }
            },
            cut: {
                css: 'zmdi zmdi-crop',
                title: '剪切',
                callback: {
                    onClick: function(editor) {
                        editor.editorContent.exec('cut');
                    }
                }
            },
            copy: {
                css: 'zmdi zmdi-copy',
                title: '复制',
                callback: {
                    onClick: function(editor) {
                        editor.editorContent.exec('copy');
                    }
                }
            },
            select_all: {
                css: 'zmdi zmdi-select-all',
                title: '全选',
                callback: {
                    onClick: function(editor) {
                        editor.editorContent.exec('selectall');
                    }
                }
            },
            font_bold: {
                css: 'zmdi zmdi-format-bold',
                title: '粗体字',
                type: 'check',
                callback: {
                    onClick: function(editor) {
                        editor.editorContent.exec('Bold');
                    }
                }
            }, 
            font_italic: {
                css: 'zmdi zmdi-format-italic',
                title: '斜体字',
                type: 'check',
                callback: {
                    onClick: function(editor) {
                        editor.editorContent.exec('italic');
                    }
                }
            }, 
            font_underline: {
                css: 'zmdi zmdi-format-underlined',
                title: '加下划线',
                type: 'check',
                callback: {
                    onClick: function(editor) {
                        editor.editorContent.exec('underline');
                    }
                }
            }, 
            ordered_list: {
                css: 'zmdi zmdi-format-list-numbered',
                title: '有序列表',
                callback: {
                    onClick: function(editor) {
                        editor.editorContent.exec('InsertOrderedList');
                    }
                }
            }, 
            unordered_list: {
                css: 'zmdi zmdi-format-list-bulleted zmdi-hc-2x',
                title: '无序列表',
                callback: {
                    onClick: function(editor) {
                        editor.editorContent.exec('InsertUnorderedList');
                    }
                }
            }, 
            font_family: {
                css: 'toolbar-item-btn',
                title: '设置字体',
                callback: {
                    onConstruct: function(item) {
                        var fontFamily = $(
                            '<span id="fontFamilySetting">宋体</span>'+
                            '<ul>'+
                            '</ul>').appendTo($(item));
                    }
                }
            },
            font_size: {
                css: 'toolbar-item-btn',
                title: '设置字体大小',
                callback: {
                    onConstruct: function(item, editor) {
                        var fontSize = $(item).html('16');
                        var fontDown = $('<ul class="leno-dropdown-menu"></ul>');
                        var sizes = [10, 12, 16, 18, 24, 32, 48];
                        for(var i = 1; i <= 7; ++i) {
                            fontDown.append(
                                $('<li style="font-size: '+sizes[i-1]+'px; display: block;" data-id="'+i+'"><a>'+sizes[i-1]+'</a></li>')
                            );
                        }
                        toolbardropdown(editor, item, {
                            id: 'font_size',
                            trigger: fontSize,
                            node: fontDown,
                //            height: 313,
                            width: 80,
                            showTop: true,
                            callback: {
                                onCreate: function(layer) {
                                    layer.content.find('li').click(function() {
                                        var fs = $.trim(
                                            $(this).attr('data-id')
                                        );
                                        fontSize.html(
                                            $(this).find('a').html()
                                        );
                                        editor.editorContent.exec(
                                            'FontSize', false, fs
                                        );
                                        Layer.get('font_size').hide();
                                        editor.focus();
                                    });
                                }
                            }
                        });
                    }
                }
            },
            heading: {
                css: 'toolbar-item-btn',
                title: '添加标题',
                callback: {
                    onConstruct: function(item, editor) {
                        var heading = $(item).html('p');
                        var fontDown = $('<ul class="leno-dropdown-menu"></ul>');
                        var size = [30, 26, 22, 18, 16, 12];
                        for(var i = 1; i <= 6; ++i) {
                            fontDown.append(
                                $('<li data-id="H'+i+'"><a style="font-size: '+size[i-1]+'px">H'+i+'</a></li>')
                            );
                        }
                        $('<li data-id="p"><a>p</a></li>')
                        .appendTo(fontDown);

                        toolbardropdown(editor, item, {
                            id: 'heading',
                            trigger: heading,
                            node: fontDown,
                            width: 80,
                            showTop: true,
                            callback: {
                                onCreate: function(layer) {
                                    layer.content.find('li')
                                    .click(function() {
                                        var fs = $.trim(
                                            $(this).attr('data-id')
                                        );
                                        heading.html(
                                            $(this).find('a').html()
                                        );
                                        editor.editorContent.exec(
                                            'formatblock', false, fs
                                        );
                                        Layer.get('heading').hide();
                                        editor.focus();
                                    });
                                }
                            }
                        });
                    }
                }
            },
            link: {
                css: 'zmdi zmdi-link zmdi-hc-2x',
                title: '插入链接',
                callback: {
                    onConstruct: function(item, editor) {
                        var node = $('<div style="padding: 10px 20px;">'+
                                '<input name="href" class="leno-input" type="text" placeholder="http://www.example.com" />'+
                                '<button class="leno-btn" style="margin-left: 5px;" data-id="href-submit" >添加</button>'+
                        '</div>');
                        toolbardropdown(editor, item, {
                            id: 'link',
                            trigger: $(item),
                            node: node,
                            showTop:true,
                            css: '___hhh___',
                            callback: {
                                onCreate: function(layer) {
                                    layer.content.keydown(function(e) {
                                        if(e.keyCode == 13) {
                                            layer.content.find('[data-id=href-submit]').click();
                                        }
                                        return true;
                                    });
                                    layer.content.find('[data-id=href-submit]').click(function() {
                                        var href = layer.content.find('[name=href]').val();
                                        var label = layer.content.find('[name=label]').val();
                                        if(!/^\https{0,1}\:\/\/.*/.test(href)) {
                                            leno.alert('链接不合法');
                                            return;
                                        }
                                        editor.editorContent.exec(
                                            'createLink', false, href
                                        );
                                        layer.hide();
                                        editor.focus();
                                    });
                                }
                            }
                        });
                    }
                }
            },
            font_fore_color: {
                css: 'zmdi zmdi-format-color-text zmdi-hc-2x',
                title: '设置字体颜色',
                callback: {
                    onConstruct: function(item, editor) {
                        var node = $('<div style="padding: 5px;"></div>');
                        var colorselector = leno.colorSelector({
                            node: node,
                            callback: {
                                onSelect: function(color) {
                                    $(item).css('color', color);
                                    editor.editorContent.exec(
                                        'ForeColor', false, color
                                    );
                                    Layer.get('font_fore_color').hide();
                                    editor.focus();
                                }
                            }
                        });
                        toolbardropdown(editor, item, {
                            id: 'font_fore_color',
                            trigger: $(item),
                            node: node,
                            showTop: true,
                            css: '___hhh___',
                            width: 172,
                //            height: 237,
                            callback: {
                                onCreate: function(layer) {
                                    colorselector.init(layer.content);
                                }
                            }
                        });
                    }
                }
            },
            font_back_color: {
                css: 'toolbar-item-select-back-color',
                title: '设置字体背景颜色',
                callback: {
                    onConstruct: function(item, editor) {
                        var trigger = $('<span class="selected-color"></span>').appendTo($(item));
                        var node = $('<div style="padding: 5px;"></div>');
                        var colorselector = leno.colorSelector({
                            node: node,
                            callback: {
                                onSelect: function(color) {
                                    trigger.css('background-color', color);
                                    editor.editorContent.exec(
                                        'backColor', false, color
                                    );
                                    Layer.get('font_back_color').hide();
                                    editor.focus();
                                }
                            }
                        });
                        toolbardropdown(editor, item, {
                            id: 'font_back_color',
                            trigger: trigger,
                            node: node,
                            css: '___hhh___',
                            showTop: true,
                            width: 172,
                //            height: 237,
                            callback: {
                                onCreate: function(layer) {
                                    colorselector.init(layer.content);
                                }
                            }
                        });
                    }
                }
            },
            align_left: {
                css: 'zmdi zmdi-format-align-left zmdi-hc-2x',
                title: '左对齐',
                radio_name: 'align',
                type: 'radio',
                callback: {
                    onClick: function(editor) {
                        editor.editorContent.exec('justifyLeft');
                    }
                }
            }, 
            align_center: {
                id: 'align_center',
                css: 'zmdi zmdi-format-align-center zmdi-hc-2x',
                title: '居中对齐',
                radio_name: 'align',
                type: 'radio',
                callback: {
                    onClick: function(editor) {
                        editor.editorContent.exec('justifyCenter');
                    }
                }
            }, 
            align_right: {
                css: 'zmdi zmdi-format-align-right zmdi-hc-2x',
                title: '右对齐',
                radio_name: 'align',
                type: 'radio',
                callback: {
                    onClick: function(editor) {
                        editor.editorContent.exec('justifyRight');
                    }
                }
            }, 
            align_full: {
                css: 'zmdi zmdi-format-align-justify zmdi-hc-2x',
                title: '两端对齐',
                radio_name: 'align',
                type: 'radio',
                callback: {
                    onClick: function(editor) {
                        editor.editorContent.exec('justifyFull');
                    }
                }
            },
            indent: {
                css: 'zmdi zmdi-format-indent-increase zmdi-hc-2x',
                title: '增加缩进',
                callback: {
                    onClick: function(editor) {
                        editor.editorContent.exec('indent');
                    }
                }
            },
            outdent: {
                css: 'zmdi zmdi-format-indent-decrease zmdi-hc-2x',
                title: '减少缩进',
                callback: {
                    onClick: function(editor) {
                        editor.editorContent.exec('outdent');
                    }
                }
            },
            undo: {
                css: 'zmdi zmdi-undo zmdi-hc-2x',
                title: '撤销',
                callback: {
                    onClick: function(editor) {
                        editor.editorContent.exec('undo');
                    }
                }
            },
            redo: {
                css: 'zmdi zmdi-redo zmdi-hc-2x',
                title: '恢复',
                callback: {
                    onClick: function(editor) {
                        editor.editorContent.exec('redo');
                    }
                }
            },
            image: {
                css: 'zmdi zmdi-collection-image zmdi-hc-2x',
                title: '上传图片...',
                callback: {
                    onClick: function(editor) {
                        config.callback.onImageButtonClick(editor);
                    }
                }
            },
            fullscreen: {
                css: 'zmdi zmdi-fullscreen zmdi-hc-2x',
                title: '全屏',
                style: {float: 'right'},
                callback: {
                    onClick: function(editor) {
                        if(editor.fullscreened) {
                            editor.unfullscreen();
                        } else {
                            editor.fullscreen();
                        }
                    }
                }
            },
            insert_table: {
                css: 'zmdi zmdi-grid zmdi-hc-2x',
                title: '插入表格...',
                callback: {
                    onConstruct: function(item, editor) {
                        var node = $('<div style="padding: 10px 20px">'+
                            '<input class="leno-input" type="text" placeholder="行" name="row" style="width: 50px; margin-right: 10px" />'+        
                            '<input class="leno-input" type="text" placeholder="列" name="col" style="width: 50px; margin-right: 10px" />'+        
                            '<button class="leno-btn" data-id="createTable">创建</button>'+        
                        '</div>');
                        node.find('[data-id=createTable]').click(function() {
                            var row = node.find('[name=row]').val();
                            var col = node.find('[name=col]').val();
                            if(row == '' || row < 0) {
                                leno.alert('行数不合法');
                                return;
                            }
                            if(col == '' || col < 0 || col > 50) {
                                leno.alert('列数不合法');
                                return;
                            }
                            lenoEditor.tableEdit.createTable(
                                editor, row, col
                            );
                            Layer.get('insert_table').hide();
                            editor.focus();
                        });

                        toolbardropdown(editor, item, {
                            id: 'insert_table',
                            trigger: $(item),
                            node: node,
                            css: '___hhh___',
                            showTop: true
                        });
                    }
                }
            }
        }
    };

    lenoEditor.tableEdit = {
        menu: [{
            label: '当前行之前添加',
            icon: 'zmdi zmdi-border-top',
            click: function(editor, current) {
                lenoEditor.tableEdit.insertRowBefore(editor, current, 1);
            }
        }, {
            label: '当前行之后添加',
            icon: 'zmdi zmdi-border-bottom',
            click: function(editor, current) {
                lenoEditor.tableEdit.insertRowAfter(editor, current, 1);
            }
        }, {
            label: '当前列之前添加',
            icon: 'zmdi zmdi-border-left',
            click: function(editor, current) {
                lenoEditor.tableEdit.insertColBefore(editor, current, 1);
            }
        }, {
            label: '当前列之后添加',
            icon: 'zmdi zmdi-border-right',
            click: function(editor, current) {
                lenoEditor.tableEdit.insertColBefore(editor, current, 1);
            }
        }, {
            label: 's'
        }, {
            label: '删除当前行',
            icon: 'zmdi zmdi-border-horizontal',
            click: function(editor, current) {
                lenoEditor.tableEdit.deleteCurrentRow(editor, current);
            }
        }, {
            label: '删除当前列',
            icon:'zmdi zmdi-border-vertical',
            click: function(editor, current) {
                lenoEditor.tableEdit.deleteCurrentCol(editor, current);
            }
        }, {
            label: '删除表格',
            icon:'zmdi zmdi-border-outer',
            click: function(editor, current) {
                lenoEditor.tableEdit.deleteTable(editor, current);
            }
        }],
        insertRowBefore: function(editor, current, num) {
            num = num || 1;
            var table = current.body.parentElement;
            var newtable = $(table).clone();
            var node = $(newtable).find('tr').get(current.row);
            var newnode = $(node).clone();
            newnode.find('td').each(function() {
                $(this).html('&nbsp;');
            });
            for(var i = 0; i < num; ++i) {
                $(node).before(newnode.clone());
            }
            var html = '<table style="width: 100%; border: 1px solid #444; border-collapse: collapse">'+newtable.html()+'</table>';
            var win = editor.editorContent.getWindow();
            var selection = win.getSelection();
            var range = win.document.createRange();
            range.selectNode(table);
            selection.addRange(range);
            editor.editorContent.exec(
                'insertHTML', false, html
            );
        },
        insertRowAfter: function(editor, current, num) {
            num = num || 1;
            var table = current.body.parentElement;
            var newtable = $(table).clone();
            var node = $(newtable).find('tr').get(current.row);
            var newnode = $(node).clone();
            newnode.find('td').each(function() {
                $(this).html('&nbsp;');
            });
            for(var i = 0; i < num; ++i) {
                $(node).after(newnode.clone());
            }
            var html = '<table style="width: 100%; border: 1px solid #444; border-collapse: collapse">'+newtable.html()+'</table>';
            var win = editor.editorContent.getWindow();
            var selection = win.getSelection();
            var range = win.document.createRange();
            range.selectNode(table);
            selection.addRange(range);
            editor.editorContent.exec(
                'insertHTML', false, html
            );
        },
        insertColBefore: function(editor, current, num) {
            var table = current.body.parentElement;
            var newtable = $(table).clone();
            $(newtable).find('tr').each(function() {
                var node = $(this).find('td').get(current.col);
                var newnode = $(node).clone();
                newnode.html('&nbsp;');
                for(var i = 0; i < num; ++i) {
                    $(node).before(newnode.clone());
                }
            });
            var html = '<table style="width: 100%; border: 1px solid #444; border-collapse: collapse">'+newtable.html()+'</table>';
            var win = editor.editorContent.getWindow();
            var selection = win.getSelection();
            var range = win.document.createRange();
            range.selectNode(table);
            selection.addRange(range);
            editor.editorContent.exec(
                'insertHTML', false, html
            );
        },
        insertColAfter: function(editor, current, num) {

            var table = current.body.parentElement;
            var newtable = $(table).clone();
            $(newtable).find('tr').each(function() {
                var node = $(this).find('td').get(current.col);
                var newnode = $(node).clone();
                newnode.html('&nbsp;');
                for(var i = 0; i < num; ++i) {
                    $(node).after(newnode.clone());
                }
            });
            var html = '<table style="width: 100%; border: 1px solid #444; border-collapse: collapse">'+newtable.html()+'</table>';
            var win = editor.editorContent.getWindow();
            var selection = win.getSelection();
            var range = win.document.createRange();
            range.selectNode(table);
            selection.addRange(range);
            editor.editorContent.exec(
                'insertHTML', false, html
            );
        },
        deleteCurrentRow: function(editor, current) {
            var table = current.body.parentElement;
            var newtable = $(table).clone();
            var node = $(newtable).find('tr').get(current.row);
            node.remove();
            if(newtable.find('tr').length == 0) {
                lenoEditor.tableEdit.deleteTable(editor, current);
                return;
            }
            var html = '<table style="width: 100%; border: 1px solid #444; border-collapse: collapse">'+newtable.html()+'</table>';
            var win = editor.editorContent.getWindow();
            var selection = win.getSelection();
            var range = win.document.createRange();
            range.selectNode(table);
            selection.addRange(range);
            editor.editorContent.exec('insertHTML', false, html);
        },
        deleteCurrentCol: function(editor, current) {
            var table = current.body.parentElement;
            var newtable = $(table).clone();

            $(newtable).find('tr').each(function() {
                var node = $(this).find('td').get(current.col);
                $(node).remove();
            });
            if(newtable.find('td').length == 0) {
                lenoEditor.tableEdit.deleteTable(editor, current);
                return;
            }
            var html = '<table style="width: 100%; border: 1px solid #444; border-collapse: collapse">'+newtable.html()+'</table>';
            var win = editor.editorContent.getWindow();
            var selection = win.getSelection();

            var range = win.document.createRange();
            range.selectNode(table);
            selection.addRange(range);
            editor.editorContent.exec(
                'insertHTML', false, html
            );
        },
        deleteTable: function(editor, current) {
            var table = current.body.parentElement;
            var win = editor.editorContent.getWindow();
            var selection = win.getSelection();
            var range = win.document.createRange();
            range.selectNode(table);
            selection.addRange(range);
            editor.editorContent.exec('delete', false);
        },
        createTable: function(editor, row, col) {
            var str = '<table style="width: 100%; border: 1px solid #444; border-collapse: collapse"><tbody>';
            for(var i = 0; i < row; ++i) {
                str += '<tr>';
                for(var j = 0; j < col; ++j) {
                    str += '<td>&nbsp;</td>';
                }
                str += '</tr>';
            }
            str += '</tbody></table>';
            editor.editorContent.exec('insertHtml', false, str);
            editor.editorContent.exec(
                'insertHTML', false, '<p></p>'        
            );
        },
        getCurrent: function(editor) {
            var focusnode = editor.editorContent.getFocusNode();
            var extra = leno.position(editor.editorContent.getFrame());
            if(focusnode == null ||
                focusnode.tagName && (
                focusnode.tagName.toLowerCase() == 'html' || 
                focusnode.tagName.toLowerCase() == 'body')) {
                return false;
            }
            var intable = false;
            for(i = focusnode; i != null; i = i.parentElement) {
                if(i.tagName && i.tagName.toLowerCase() == 'td') {
                    intable = true;
                    var currentTd = i;
                    break;
                }
            }
            if(intable) {
                $(currentTd).attr('data-id', '__current__');
                var currentTr = currentTd.parentElement;
                var i = 0;
                $(currentTr).find('td').each(function() {
                    if($(this).attr('data-id') == '__current__') {
                        $(this).attr('data-id', '');
                        return false;
                    } else {
                        ++i;
                    }
                });
                $(currentTr).attr('data-id', '__current__');
                var j = 0;
                var tableBody = currentTr.parentElement;
                $(tableBody).find('tr').each(function() {
                    if($(this).attr('data-id') == '__current__') {
                        $(this).attr('data-id', '');
                        return false;
                    } else {
                        ++j;
                    }
                });
                var current = {
                    row: j,
                    col: i,
                    body: tableBody
                };
                return current;
            } else {
                return false;
            }
        }
    };
    return lenoEditor;
})();
leno.getEditor = function(id) {
    if(typeof leno.editorInstance === 'object') {
        return leno.editorInstance[id];
    }
}
$(document).ready(function() {
    $('.leno-input-group').click(function() {
        $(this).find('.leno-input,input,textarea').first().focus();
    });
    $('.leno-dropdown').each(function() {
        leno.dropdown($(this));
    });
    $('.leno-form').each(function() {
        var url = $(this).attr('href');
        var id = $(this).attr('id');
        var go = $(this).attr('go');
        new leno.form({
            url: url,
            node: $(this),
            method: $(this).attr('method'),
            id: 'leno-form-'+id,
            callback: {
                beforeSubmit: function() {
                    return true;
                },
                afterReturn: function(response) {
                    if(response.status == 200) {
                        window.location.href = go;
                    }
                }
            }
        });
    });
    $('.leno-input-group input').focus(function() {
        $(this).parent().addClass('leno-success');
    }).blur(function() {
        $(this).parent().removeClass('leno-success');
    });
    $(document).click(function() {
        var instance = layer.instance;
        for(var i in instance) {
            if(instance[i].getType() == layer.TYPE_DROPDOWN) {
                instance[i].hide();
            }
        }
    });
});
