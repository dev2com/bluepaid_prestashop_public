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

<!-- ERROR ON UPDATE -->
	<div id="bluepaidmulti_confirmation">{$bluepaid_confirmation}</div>
<!-- ./END ERROR ON UPDATE -->


<!--basic settings-->
<form action="{$bluepaid_form|escape:'htmlall':'UTF-8'}" method="post" style="margin-bottom:25px;">
    <fieldset>
    <legend><img src="../img/admin/contact.gif" />{$legend}</legend>
    	<p>
        	{l s='The Merchant id is furnished by Bluepaid. If you don\'t have credentials, you can create an account by clicking ' mod='bluepaid'}
            <a style='color:blue;text-decoration:underline' href='http://www.bluepaid.com/devis.php' target='_blank'>
            	{l s='here' mod='bluepaid'}
            </a><br /><br />
        </p>
        <table border="0" width="600" cellpadding="0" cellspacing="0" id="form">
            <tr>
            	<td width="130" style="height: 35px;"><label style='text-align:left' for='merchid'>{l s='bluepaid merchant id' mod='bluepaid'}</label></td>
                <td><input size="10" type="text" name="merchid" value="{$merchid}" /> <sup>*</sup></td>
            </tr>
            <tr>
                <td colspan="2" style="height:1.5em"></td>
            </tr>
            <tr>
            	<td width="130" style="height: 35px;"><label style='text-align:left' for='merchid'>{l s='Debug mode' mod='bluepaid'}</label></td>
                <td>
                	<select name="debug_mode" style="width:130px;">
                    	<option value="0"{if !$debugmode} selected="selected"{/if}>{l s='No' mod='bluepaid'}</option>
                    	<option value="1"{if $debugmode} selected="selected"{/if}>{l s='Yes' mod='bluepaid'}</option>
                    </select><br />
                    <i>{l s='If yes, Authorize payments with test cards (furnished by bluepaid)' mod='bluepaid'}</i>
                </td>
            </tr>
        </table>
        <p>
        	<h4 class="highlight" style="padding:5px;"><img src="../img/admin/warning.gif" /> {l s='Important' mod='bluepaid'}</h4>
        	{l s='Update the field "Url de confirmation" with this value, in your back-office Bluepaid : ' mod='bluepaid'}
            <div style="  padding-top: 1em;  padding-right: 0px;  padding-bottom: 1em;  padding-left: 3em;  color: #7f7f7f; ">{$conf_uri|escape:'htmlall':'UTF-8'}</div>
        </p>
    </fieldset>

    <br /><br />
    
    <!--X payments settings-->
    <fieldset>
        <legend><img src="../img/admin/contact.gif" />{l s='Configure multi-payments' mod='bluepaid'}</legend>
        <table border="0" cellpadding="0" cellspacing="0" id="form">
            <caption style="text-align:left"><p>{l s='Please indicate configuration for payments in many times' mod='bluepaid'}</p></caption>
            <tr>
                <td><label for='bpi_xpay_authorize' style='text-align:left'>{l s='Authorize payments in many times' mod='bluepaid'}</label></td>
                <td><input type="checkbox" name="bpi_xpay_authorize" id="bpi_xpay_authorize" value="1" {if $bpi_xpay_authorize}checked {/if}/></td>
            </tr>
            <tr>
                <td colspan="2" style="height:1.5em"></td>
            </tr>
            <tr>
                <td><label style='text-align:left' for='bpi_min_val_xpay'>{l s='Minimum amount for payments in many times' mod='bluepaid'}</label></td>
                <td><input size="10" type="text" name="bpi_min_val_xpay" value="{$bpi_min_val_xpay}" style="top:0px" /><br /><i>{l s='Indicate the minimum amount for payments in many times' mod='bluepaid'}</i></td>
            </tr>
            <tr>
                <td colspan="2" style="height:1.5em"></td>
            </tr>
            <tr>
                <td><label style='text-align:left' for='bpi_xpay_nboccur'>{l s='Total Payments authorized' mod='bluepaid'}</label></td>
                <td>            
                    <select id="bpi_xpay_nboccur" name="bpi_xpay_nboccur" style="width:130px;">
                        <option value="2"{if $totalPayments == 2} selected="selected"{/if}>2</option>
                        <option value="3"{if $totalPayments == 3} selected="selected"{/if}>3</option>
                        <option value="4"{if $totalPayments == 4} selected="selected"{/if}>4</option>
                        <option value="5"{if $totalPayments == 5} selected="selected"{/if}>5</option>
                        <option value="6"{if $totalPayments == 6} selected="selected"{/if}>6</option>
                        <option value="7"{if $totalPayments == 7} selected="selected"{/if}>7</option>
                        <option value="8"{if $totalPayments == 8} selected="selected"{/if}>8</option>
                        <option value="9"{if $totalPayments == 9} selected="selected"{/if}>9</option>
                        <option value="10"{if $totalPayments == 10} selected="selected"{/if}>10</option>
                    </select><br />
                    <i>{l s='Number of authorized deadlines' mod='bluepaid'}</i>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="height:1.5em"></td>
            </tr>
            <tr>
                <td><label style='text-align:left' for='bpi_xpay_initamount'>{l s='Initial amount' mod='bluepaid'}</label></td>
                <td>
                    <input size="10" type="text" name="bpi_xpay_initamount" value="{$bpi_xpay_initamount}" style="top:0px" /> <span>%</span> {l s='of the cart' mod='bluepaid'}<br />
                    <i>{l s='Indicate the amount to take for the first payment (in percent of the cart). Keep empty if the first amount is equal to the the others.' mod='bluepaid'}</i>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="height:1.5em"></td>
            </tr>
            <tr>
                <td><label style='text-align:left' for='bpi_xpay_nbko'>{l s='Count of new payments if ko.' mod='bluepaid'}</label></td>
                <td>
                    <select id="bpi_xpay_nbko" name="bpi_xpay_nbko" style="width:130px;">
                        <option value="1"{if $bpi_xpay_nbko == 0} selected="selected"{/if}>0</option>
                        <option value="0"{if $bpi_xpay_nbko == 1} selected="selected"{/if}>1</option>
                        <option value="2"{if $bpi_xpay_nbko == 2} selected="selected"{/if}>2</option>
                        <option value="3"{if $bpi_xpay_nbko == 3} selected="selected"{/if}>3</option>
                        <option value="4"{if $bpi_xpay_nbko == 4} selected="selected"{/if}>4</option>
                        <option value="5"{if $bpi_xpay_nbko == 5} selected="selected"{/if}>5</option>
                        <option value="6"{if $bpi_xpay_nbko == 6} selected="selected"{/if}>6</option>
                        <option value="7"{if $bpi_xpay_nbko == 7} selected="selected"{/if}>7</option>
                        <option value="8"{if $bpi_xpay_nbko == 8} selected="selected"{/if}>8</option>
                        <option value="9"{if $bpi_xpay_nbko == 9} selected="selected"{/if}>9</option>
                        <option value="10"{if $bpi_xpay_nbko == 10} selected="selected"{/if}>10</option>
                    </select><br />
                 <i>{l s='Indicate how many new requests bluepaid has to do if a payment is refused (from the second debit)' mod='bluepaid'}</i>
                </td>
            </tr>
        </table>
    </fieldset>

    <br /><br />
    
    <!--Security settings-->
    <fieldset>
    	<legend><img src="../img/admin/warning.gif" /> {l s='securtiy configuration' mod='bluepaid'}</legend>
        <h4>{l s='Attention' mod='bluepaid'} !</h4>
        <p>{l s='Don\'t modify these values if you are not sure.' mod='bluepaid'}</p>
        <label>{l s='Authorized IP :' mod='bluepaid'}</label>
        <div class="margin-form">
        	<input type="text" name="bpi_authorized_ip" size="50" value="{$bpi_authorized_ip}" placeholder="XXX.XX.XX.XX" /> 
        </div>
    </fieldset>
    
    
    <center><input class="button" name="submitBluepaid_config" value="{l s='Save' mod='bluepaid'}" type="submit" style="margin-top:15px;" /></center>
    
    <fieldset>
    	<legend>{l s='Informations on this module' mod='bluepaid'}</legend>
        <p>
        	{l s='Your back-office Bluepaid' mod='bluepaid'} : <a href="{$url_back_office}" target="_blank" style="color:blue;text-decoration:underline">{$url_back_office}</a><br /><br />
        	{l s='Your back-office allows you to check payments, reversals, do refund, ....' mod='bluepaid'}
        </p>
    </fieldset>
    
    
</form>
<p style="font-size:0.8em">
    {l s='This module has been developped by ' mod='bluepaid'}<a href="http://addons.dev2com.fr">Dev2Com</a>
</p>