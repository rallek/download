( function ($) {
    $.extend($.summernote.plugins, {
        /**
         * @param {Object} context - context object has status of editor.
         */
        'rkdownloadmodule': function (context) {
            var self = this;

            // ui provides methods to build ui elements.
            var ui = $.summernote.ui;

            // add button
            context.memo('button.rkdownloadmodule', function () {
                // create button
                var button = ui.button({
                    contents: '<img src="' + Zikula.Config.baseURL + Zikula.Config.baseURI + '/web/modules/rkdownload/images/admin.png' + '" alt="Down load" width="16" height="16" />',
                    tooltip: 'Down load',
                    click: function () {
                        RKDownLoadModuleFinderOpenPopup(context, 'summernote');
                    }
                });

                // create jQuery object from button instance.
                var $button = button.render();

                return $button;
            });
        }
    });
})(jQuery);
