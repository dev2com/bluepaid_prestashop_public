{*
* 2015 Dev2Com
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Dev2Com EURL
*  @copyright 2015 Dev2Com EURL
*  @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Dev2Com EURL
*}
{literal}
<script>
function popuprnp1xrnp(){
var win2 = window.open("http://www.bluepaid.com",'popup','height=705,width=610,status=no,scrollbars=no,menubar=no,resizable=no');
}
</script>
{/literal}

{if $comptant}
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <p class="payment_module">
                <a class="payment" href="{$modules_dir|escape:'html':'UTF-8'}bluepaid/controllers/payment/sendtoBPI.php?payment=1" title="{l s='Credit card payment by Bluepaid' mod='bluepaidmulti'}">
                    <img title="" alt="cadenas_mini" width="50" src="{$modules_dir}bluepaid/views/img/cadenas_mini.png" /> 
					{l s='Bluepaid, pay safely by credit card.' mod='bluepaid'}
					<a style="display: block;margin-left: 125px;margin-top: 5px;" onClick="popuprnp1xrnp();return false;"><u>{l s='What is Bluepaid ?' mod='bluepaid'}</u></a>
                </a>
            </p>
        </div>
    </div>
{/if}
{if $bluepaid_multipayment}
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <p class="payment_module">
                <a class="payment" href="{$modules_dir|escape:'html':'UTF-8'}bluepaid/controllers/payment/sendabotoBPI.php?payment=1" title="{l s='Credit card payment by Bluepaid' mod='bluepaidmulti'}">
                    <img title="" alt="cadenas_mini" width="50" src="{$modules_dir}bluepaid/views/img/cadenas_mini.png" /> 
           	 		{l s='Bluepaid, pay safely in' mod='bluepaid'} {$bluepaid_multipayment_nbmax} {l s='times by credit card.' mod='bluepaid'}
					<a style="display: block;margin-left: 125px;margin-top: 5px;" onClick="popuprnp1xrnp();return false;"><u>{l s='What is Bluepaid ?' mod='bluepaid'}</u></a>
                </a>
                {if $init_percent_amount}
                    <br />
                    <span style="font-size:0.8em; text-transform:lowercase">{l s='With a down payment of ' mod='bluepaid'} {$init_percent_amount} % {l s=' of the total performed today' mod='bluepaid'}</span>
                {/if}
            </p>
        </div>
    </div>
{/if}
