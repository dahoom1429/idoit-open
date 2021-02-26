[{isys_group name="tom.popup.add_values"}]
    <div id="add_values-popup">
        <h3 class="popup-header">
            <span>[{isys type="lang" ident="LC__MODULE__MULTIEDIT__ADD_VALUES"}]</span>
            <div id="add_values-popup-header-loading" class="fr hide">
                <img src="[{$dir_images}]ajax-loading.gif" class="vam mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</span>
            </div>
        </h3>

        <div id="add_values-popup-content" class="popup-content">
            <p>
                <div class="fl m10">[{isys type="f_label" ident="Ausgewählte Objekte"}]</div>
                <div class="fl m10">
                    [{isys type="f_dialog" p_strClass="input-small" name="add_values-object-selection" p_bDbFieldNN="1"}]
                    <input type="hidden" name="add_values-object-all" id="add_values-object-all" value="[{$allObjectIds}]">
                    <input type="hidden" name="add_values-category" id="add_values-category" value="[{$categoryClass}]">
                    <input type="hidden" name="add_values-category-info" id="add_values-category-info" value="[{$categoryInfo}]">
                </div>
            </p>
        </div>

        <div id="add_values-popup-footer" class="popup-footer">
            <button type="button" class="btn" id="add_values-popup-apply">
                <img src="[{$dir_images}]icons/apply.png" class="mr5" />
                <span>[{isys type="lang" ident="LC__MODULE__MULTIEDIT__ADD_VALUES__ADD"}]</span>
            </button>

            <button type="button" class="btn ml5 popup-closer" id="add_values-popup-cancel">
                <img src="[{$dir_images}]icons/silk/cross.png" class="mr5" />
                <span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL_CLOSE"}]</span>
            </button>
        </div>
    </div>

    <script type="text/javascript">
        (function () {
            'use strict';
            
            var $popup        = $('add_values-popup'),
                $cancelButton = $('add_values-popup-cancel'),
                $ajaxLoader   = $('add_values-popup-header-loading'),
                $applyButton  = $('add_values-popup-apply');
            
            if ($cancelButton) {
                $cancelButton.on('click', function () { popup_close(); });
            }
            
            if ($applyButton) {
                $applyButton.on('click', function (){
                    
                    if (this.hasClassName('disabled')) {
                        return;
                    }

                    this.addClassName('disabled');
                    $cancelButton.addClassName('disabled');
                    $ajaxLoader.removeClassName('hide');
                    var category = $('add_values-category').value,
                        categoryInfo = $('add_values-category-info').value,
                        randomHash = function () {
                            return Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                        };

                    if ($('add_values-object-selection').value == '-1') {
                        var objectIds = JSON.parse($('add_values-object-all').value);
                        var keys = Object.keys(objectIds);

                        for (var i = 0; i < keys.length; i++) {
                            window.multiEdit.addNewEntry(category, objectIds[i], 'new-' + randomHash(), categoryInfo);
                        }
                    } else {
                        window.multiEdit.addNewEntry(category, $('add_values-object-selection').value, 'new-' + randomHash(), categoryInfo);
                    }

                    popup_close();
                });
            }
            
        })();
    </script>


[{/isys_group}]