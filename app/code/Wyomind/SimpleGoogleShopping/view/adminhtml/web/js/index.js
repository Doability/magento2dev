/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

SimpleGoogleShopping = {
    updater: {
        init: function () {
            data = new Array();
            jQuery('.updater').each(function () {
                var feed = [jQuery(this).attr('id').replace("feed_", ""), jQuery(this).attr('cron')];
                data.push(feed);
            });

            jQuery.ajax({
                url: updater_url,
                data: {
                    data: JSON.stringify(data)
                },
                type: 'GET',
                showLoader: false,
                success: function (data) {
                    data.each(function (r) {
                        jQuery("#feed_" + r.id).parent().html(r.content)
                    });
                    setTimeout(SimpleGoogleShopping.updater.init, 1000)
                }
            });

        }
    }
};

require(["jquery", "mage/mage"], function ($) {
    $(function () {
        if (typeof updater_url === 'undefined') {
            updater_url = "";
        }
        SimpleGoogleShopping.updater.init();
    });
});