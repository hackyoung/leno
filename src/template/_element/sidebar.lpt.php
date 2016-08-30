<?php
/**
 * @lpt_name _element.sidebar
 * @param content [
 *      [
 *          'url' => '',
 *          'icon' => '',
 *          'title' => '',
 *          'subtitle' => '',
 *          'items' => [
 *
 *          ]
 *      ]
 * ]
 */
?>
<empty name="{$level}">
    <fragment name="top">hello world</fragment>
</empty>
<ul id="leno-navbar-{$id}" class="leno-navbar">
    <llist name="{$content}" id="item">
        <li class="leno-navbar-item" data-level="{$level}-{$__number__}">
        <a href="{$item.url}" title="{$item.subtitle}">
            <img src="{$item.icon}" />
            <span>{$item.title}</span>
        </a>
        <notempty name="{$item.items}">
            <?php $item_content = ['level' => ($level ?? '').'-'.$__number__, 'content' =>$item['items']]; ?>
            <view name="leno._element.sidebar" data="{$item_content}" />
        </notempty>
        </li>
    </llist>
</ul>
<script>
var navbar = (function(open) {
    var open = (open || '-0').split('-');
    var $node;
    var init = function(opts) {
        if(opts.id == null) {
            throw 'need id to init';
        }
        $node = $('#leno-navbar-'+opts.id);
        $node.find('li a').click(function() {
            var $p = $(this).parent();
            if($(this).next().hasClass('leno-navbar')) {
                $p.parent().find('>li').removeClass('open');
                $p.addClass('open');
                return false;
            }
            var url = $(this).attr('href');
            if(/\?/.test(url)) {
                url += '&navbarid='+$p.attr('data-level');
            } else {
                url += '?navbarid='+$p.attr('data-level');
            }
            window.location.href = url;
            return false;
        });
        var the_open = [];
        for(var i = 0; i < open.length; ++i) {
            the_open.push(open[i]);
            var selector = the_open.join('-');
            if(selector.length > 0) {
                $('[data-level='+the_open.join('-')+']').addClass('open');
            }
        }
        var selector = the_open.join('-');
        if(selector.length > 0) {
            $('[data-level='+the_open.join('-')+']').addClass('selected');
        }
    }
    return {init: init};
})('{$_GET.navbarid}');
</script>
<style>
.leno-navbar {
    background-color: #444;
    overflow: hidden;
    color: white;
    margin: 0px;
    padding: 0px;
    transition: all 0.2s ease-in;
    -moz-transition: all 0.2s ease-in;
    -webkit-transition: all 0.2s ease-in;
}

.leno-navbar a {
    color: #999;
    text-decoration: none;
    padding-left: 20px;
    width: 100%;
    height: 100%;
    display: inline-block;
}

.leno-navbar a:hover {
    background-color: #222;
}

.leno-navbar .leno-navbar-item.open>a {
    color: white;
}

.leno-navbar .leno-navbar-item {
    list-style: none;
    width: 100%;
    line-height: 40px;
}

.leno-navbar .leno-navbar-item .leno-navbar {
    margin-left: 20px;
}

.leno-navbar .leno-navbar-item img {
    vertical-align: middle;
}

.leno-navbar .leno-navbar-item .leno-navbar {
    display: none;
}

.leno-navbar .leno-navbar-item.open .leno-navbar {
    display: block;
}
</style>
