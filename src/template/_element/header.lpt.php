<?php
/**
 * @param [
 *      'background' => '',
 *      'visitor' => ['home' => '', 'portrait' => ''],
 *      'menu' => [
 *          [
 *              'id' => '',
 *              'title' => '',
 *              'href' => '',
 *          ]
 *      ]
 * ]
 */
$height = $height ?? 300;
?>
<header class="leno-global-header" style="height: {$height}px;">
    <div class="top" style="height: {$height - 50}px;">
        <fragment name="top"></fragment>
    </div>
    <div class="bottom" style="top: {$height - 65}px;">
        <div class="left">
            <a href="{$visitor.home}">
                <img src="{$visitor.portrait}" />
            </a>
            <ul class="menu">
                <llist name="{$menu}" id="item">
                    <li><a href="{$item.href}">{$item.title}</a></li>
                </llist>
            </ul>
        </div>
        <div class="right">
            <fragment name="right">
            </fragment>
        </div>
    </div>
</header>
<style>
    .leno-global-header div.top {
        display: flex;
        display: -webkit-flex;
        height: 350px;
        justify-content: center;
        align-items: center;
        flex-direction: row;
        flex-wrap: nowrap;
    }
    .leno-global-header {
        width: 100%;
        height: 300px;
        background-image: url({$background});
        background-repeat: no-repeat;
        background-size: 100%;
        background-position: center center;
        background-color: #eee;
        box-shadow: 0px -50px 50px -50px rgba(0, 0, 0, 0.7) inset;
        -moz-box-shadow: 0px -50px 50px -50px rgba(0, 0, 0, 0.7) inset;
        -webkit-box-shadow: 0px -50px 50px -50px rgba(0, 0, 0, 0.7) inset;
        border-bottom: 1px solid #999;
        color: white;
        margin-bottom: 20px;
    }
    .leno-global-header>div.bottom {
        display: flex;
        display: -webkit-flex;
        justify-content: space-between;
        position: absolute;
        top: 235px;
        line-height: 50px;
        text-shadow: 0px 0px 3px rgba(0, 0, 0, 0.6);
        width: 100%;
    }
    .leno-global-header>div.bottom>.left {
        margin-left: 30px;
    }
    .leno-global-header>div.bottom a {
        text-decoration: none;
    }
    .leno-global-header>div.bottom.fixed {
        position: fixed;
        width: 100%;
        box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.5);
        background-image: linear-gradient(top, #fff, #f0f0f0);
        background-image: -moz-linear-gradient(top, #fff, #f0f0f0);
        background-image: -webkit-linear-gradient(top, #fff, #f0f0f0);
        text-shadow: 0px 0px 3px rgba(255, 255, 255, 0.6);
    }
    .leno-global-header>div.bottom.fixed>div.left {
        margin-left: 120px;
    }
    .leno-global-header>div.bottom.fixed>div.right {
        margin-right: 120px;
    }
    .leno-global-header>div.bottom>div.right {
        display: flex;
        display: -webkit-flex;
        align-items: center;
        margin-right: 30px;
    }
    .leno-global-header>div.bottom.fixed img {
        border-radius: 80px;
    }
    .leno-global-header>div.bottom.fixed .menu li a {
        color: #444;
    }
    .leno-global-header>div.bottom>div.left>a>img {
        vertical-align: middle;
        width: 80px;
        height: 80px;
        border: 1px solid #999;
    }
    .leno-global-header ul.menu {
        position: relative;
        top: 3px;
        padding: 0px;
        margin: 0px;
        display: inline-block;
    }
    .leno-global-header ul.menu li {
        height: 50px;
        padding: 0px 20px;
        list-style: none;
        display: inline-block;
    }
    .leno-global-header ul.menu li a {
        color: white;
        text-decoration: none;
    }
</style>
<script>
    var change_top = false;
    var height = {$height};
    $(window).scroll(function() {
        var dest = height - 80;
        var origin = height - 65;
        var $scroll_div = $('.leno-global-header>div.bottom');
        if(window.scrollY > dest) {
            $scroll_div.addClass('fixed').css('top', 0);
            return;
        }
        var rate = Math.min(1, window.scrollY/parseFloat($scroll_div.css('top')));
        var left = Math.ceil(rate*100 + 32);
        $scroll_div.css('top', Math.max((origin - (rate)*15), window.scrollY));
        $scroll_div.find('>div.left').css('margin-left', left);
        $scroll_div.find('>div.right').css('margin-right', left);
        $scroll_div.find('img').css('border-radius', rate*80);
        $scroll_div.removeClass('fixed');
    });
</script>
