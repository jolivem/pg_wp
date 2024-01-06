(function($) {
    'use strict';   
    $(document).on('click', '[data-slug="geolocated-photo"] .deactivate a', function () {    
        swal({
            html:"<h2>Do you want to upgrade to Pro version or permanently delete the plugin?</h2><ul><li>Upgrade: Your data will be saved for upgrade.</li><li>Deactivate: Your data will be deleted completely.</li></ul>",
            footer: '<a href="javascript:void(0);" class="ays-gallery-photo-gallery-temporary-deactivation">Temporary deactivation</a>',
            type: 'question',
            showCloseButton: true,
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            confirmButtonColor: '#3085D6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Upgrade',
            cancelButtonText: 'Deactivate',
            confirmButtonClass: "ays-gallery-photo-gallery-upgrade-button",
            cancelButtonClass: "ays-gallery-photo-gallery-cancel-button"
        }).then(function(result) {
            if( result.dismiss && result.dismiss == 'close' ){
                return false;
            }
            var upgrade_plugin = false;
            if (result.value) upgrade_plugin = true;
            var data = {action: 'deactivate_plugin_option_pm', upgrade_plugin: upgrade_plugin};
            $.ajax({
                url: glp_admin_ajax.ajax_url,
                method: 'post',
                dataType: 'json',
                data: data,
                success:function () {
                    window.location = $(document).find('[data-slug="geolocated-photo"]').find('.deactivate').find('a').attr('href');
                }
            });
        });
        return false;
    });
    $(document).on('click', '.ays-gallery-photo-gallery-temporary-deactivation', function (e) {
        e.preventDefault();
        $(document).find('.ays-gallery-photo-gallery-upgrade-button').trigger('click');
    });
})(jQuery);