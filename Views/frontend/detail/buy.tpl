{extends file="parent:frontend/detail/buy.tpl"}

{block name="frontend_detail_buy"}
    {$smarty.block.parent}
    {if $smarty.server.HTTP_USER_AGENT|stristr:"safari" and !$smarty.server.HTTP_USER_AGENT|stristr:"chrome"}
        {if {config name=buckaroo_applepay_show_product} eq 'yes' && (!isset($sArticle.active) || $sArticle.active)}
            {if $sArticle.esd }
                <input type="hidden" id="is_downloadable" value="1">
            {else}
                <input type="hidden" id="is_downloadable" value="0">
            {/if}
            {if $sArticle.isAvailable}
                <div class="applepay-button-container">
                    <div></div>
                </div>
                <script type="text/javascript">
                    var el = document.createElement('script');
                    el.type='module';
                    el.src = '{link file="frontend/_resources/js/applepay/index.js"}?v={$smarty.server.REQUEST_TIME}';
                    document.head.appendChild( el );

                    var is_product_detail_page = true;
                    var order_number = '{$sArticle.ordernumber}';
                    var buckarooBaseUrl = '{$Shop->getBaseUrl()}';
                </script>
            {/if}
        {/if}
    {/if}
{/block}
