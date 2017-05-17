/*! @license Copyright (c) 161 SARL, https://161.io */

+function($) {
    'use strict';

    var Translate;

    /**
     * @param {Boolean} show
     */
    function ajaxRunning(show) {
        $('#ajax_running').css('display', (show ? '' : 'none'));
    }

    /**
     * Importation par lot
     * @type {Boolean}
     */
    var importGroup = false;

    /**
     * Importation par lot en cours
     * @returns {Boolean}
     */
    function isImportGroup() {
        return importGroup;
    }

    /**
     * Importer une category
     * @param {jQuery} $btn
     * @param {Function} onComplete
     * @returns {Boolean}
     */
    function importCategory($btn, onComplete) {
        var xhr = $btn.data('xhr');
        if (xhr) { return false; }

        ajaxRunning(true);
        xhr = $.ajax({
            url     : $btn.data('action'),
            type    : 'POST',
            cache   : false,
            dataType: 'json'
        });
        xhr.done(function(data) {
            switch(data.growlType) {
                case 'notice' :
                    $btn.prop('disabled', true).attr('disabled', 'disabled');
                    $.growl.notice({
                        title  : '',
                        size   : 'large',
                        message: data.growlMessage
                    });
                    if (onComplete) {
                        onComplete();
                    }
                    break;
                default :
                    importGroup = false;
                    $.growl.error({
                        title  : '',
                        size   : 'large',
                        message: (data.growlMessage || 'Message not found')
                    });
                    break;
            }
        });
        xhr.fail(function() {
            importGroup = false;
            $.growl.error({
                title  : Translate.errorTitle,
                size   : 'large',
                message: Translate.errorMessage
            });
        });
        xhr.always(function() {
            xhr = null;
            $btn.data('xhr', xhr);
            ajaxRunning(false);
        });
        $btn.data('xhr', xhr);

        return false;
    }

    /**
     * Importer en loop
     */
    function importLoop() {
        var $btn = $('.btn[data-action]').filter(':not(:disabled)').first();
        if ($btn.length) {
            importGroup = true;
            ajaxRunning(true);
            importCategory($btn, importLoop);
        } else {
            // Fin
            importGroup = false;
        }
    }

    $(function() {
        Translate = window.LaboDataTranslate;

        $('.table.configuration').find('td.pointer')
            .removeAttr('onclick')
            .removeClass('pointer');

        // Ajouter tout
        $('#btn-add-all').on('click', function() {
            if (isImportGroup()) {
                return false;
            }
            if (!confirm($(this).data('confirm'))) {
                return;
            }
            importLoop();
        });

        // Ajouter une categorie
        $('.btn[data-action]').on('click', function() {
            if (isImportGroup()) {
                return false;
            }
            importCategory($(this));
            return false;
        });


    });

}(jQuery);
