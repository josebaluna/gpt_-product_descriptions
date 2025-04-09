{*
* 2007-2024 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2024 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel">
    <h3>{$product_name}</h3>
    <textarea id="product_description" name="description" rows="10" cols="50">{$description}</textarea>
    <button type="button" id="generate-description" class="btn btn-default">Generate Description</button>
    <div id="api-response" style="margin-top: 20px; color: green;"></div> <!-- Añadir un div para mostrar la respuesta -->
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('generate-description').addEventListener('click', function () {
            var productName = '{$product_name}';
            var productId = '{$product_id}';
            var token = '{$token}';
            generateDescription(productName, productId, token);
        });
    });

    function generateDescription(productName, productId, token) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '{$link->getAdminLink('AdminModules', true, array('configure' => 'autodescription', 'module_name' => 'autodescription'))}', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.error) {
                            alert(response.error);
                        } else {
                            document.getElementById('product_description').value = response.description;
                            document.getElementById('api-response').innerText = response.description; // Mostrar la descripción en la pantalla
                        }
                    } catch (e) {
                        console.error('Invalid JSON response:', xhr.responseText);
                    }
                } else {
                    console.error('HTTP error:', xhr.status, xhr.statusText);
                }
            }
        };
        xhr.send('ajax=1&action=generateDescription&product_name=' + encodeURIComponent(productName) + '&product_id=' + encodeURIComponent(productId) + '&token=' + encodeURIComponent(token));
    }
</script>