<style>
    ul.pager {
        margin: 0px;
        text-align: center;
    }
    ul.pager>li {
        list-style: none;
        display: inline-block;
    }
    ul.pager>li>a {
        box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        padding: 5px 10px;
        background-color: white;
        border-radius: .2em;
    }
    ul.pager>li>a.current {
        background-color: green;
        color: white;
    }
</style>
<?php
    /**
     * @param base_url  选填
     * @param total     必填
     * @param page      选填
     * @param page_size 选填
     */
    $base_url = $base_url ?? '';
    $page = $page ?? $_GET['page'] ?? 1;
    $page_size = $page_size ?? $_GET['page_size'] ?? 10;
    $total_page = ceil($total/$page_size);
    $shows = [];
    $show_begin = ($page - 2) > 0 ? ($page - 2) : 1;
    $show_end = ($total_page + 2) < $total_page ? ($page + 2) : $total_page;
    for($i = $show_begin; $i <= $show_end; ++$i) {
        $the = ['page' => $i];
        if($i == $page) {
            $the['current'] = 1;
        }
        $shows[] = $the;
    }
    $handle_begin = [1, 2];
    $handle_end = [$total_page, $total_page - 1];

    function getUrlOfPage($page, $page_size, $url)
    {
        if(!preg_match('/\?/', $url)) {
            $url .= '?';
        }
        return $url .= implode('&', [
            'page='.$page,
            'page_size='.$page_size,
        ]);
    }
?>
<neq name="{$total_page}" value="1">
    <ul class="pager">
        <neq name="{$show_begin}" value="1">
            <li><a href="{:getUrlOfPage(1, $page_size, $base_url)}">1</a></li>
        </neq>
        <nin name="{$show_begin}" value="{$handle_begin}">
            <li>...</li>
        </nin>
        <llist name="{$shows}" id="show">
            <empty name="{$show.current}">
                <li><a href="{:getUrlOfPage($show['page'], $page_size, $base_url)}">{$show.page}</a></li>
            </empty>
            <notempty name="{$show.current}">
                <li><a class="current" href="{:getUrlOfPage($show['page'], $page_size, $base_url)}">{$show.page}</a></li>
            </notempty>
        </llist>
        <nin name="{$show_end}" value="{$handle_end}">
            <li>...</li>
        </nin>
        <neq name="{$show_end}" value="{$total_page}">
            <li><a href="{:getUrlOfPage($total, $page_size, $base_url)}">{$total_page}</a></li>
        </neq>
    </ul>
</neq>
