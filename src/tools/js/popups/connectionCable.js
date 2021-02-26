'use strict';

/**
 * Cable (sub-) popup for the connection popup.
 *
 * @author     Leonard Fischer <lfischer@i-doit.com>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @version    1.0
 */

window.ConnectionCable = Class.create({
    $root: null,

    initialize: function ($root, options) {
        this.$root = $root;

        this.cables = [];
        this.connectionsA = [];
        this.connectionsB = [];

        this.options = {
            width:         this.$root.getWidth(),
            height:        this.$root.getHeight(),
            url:           '',
            saveCallback:  null,
            abortCallback: null
        };

        Object.extend(this.options, options || {});

        this.$root.setStyle({
            width:      this.options.width + 'px',
            height:     this.options.height + 'px',
            marginLeft: '-' + (this.options.width / 2) + 'px',
            marginTop:  '-' + (this.options.height / 2) + 'px'
        });
    },

    setCables: function (cables) {
        this.cables = cables.uniq();

        return this;
    },

    setConnectors: function (local, destination) {
        this.connectionsA = local;
        this.connectionsB = destination;

        return this;
    },

    setSaveCallback: function (func) {
        this.options.saveCallback = null;

        if (Object.isFunction(func))
        {
            this.options.saveCallback = func;
        }

        return this;
    },

    setAbortCallback: function (func) {
        this.options.abortCallback = null;

        if (Object.isFunction(func))
        {
            this.options.abortCallback = func;
        }

        return this;
    },

    setOptions: function (options) {
        Object.extend(this.options, options || {});

        return this;
    },

    reset: function () {
        return this.setCables([])
                   .setConnectors([], [])
                   .setSaveCallback(null)
                   .setAbortCallback(null)
                   .update();
    },

    update: function () {
        var that = this;

        that.$root
            .update(new Element('div', {className: 'p20 center'})
                .update(new Element('img', {
                    src:       window.dir_images + 'ajax-loading.gif',
                    className: 'mr5'
                }))
                .insert(new Element('span').update(idoit.Translate.get('LC__UNIVERSAL__LOADING'))));

        new Ajax.Request(this.options.url, {
            parameters: {
                cables:      this.cables.join(),
                connectorsA: this.connectionsA.join(),
                connectorsB: this.connectionsB.join(),
                func:        'loadCablePopupTemplate'
            },
            method:     'post',
            onSuccess:  function (xhr) {
                var json = xhr.responseJSON;

                if (is_json_response(xhr, true))
                {
                    if (json.success)
                    {
                        that.$root.update(json.data);
                    }
                }
            }
        });

        return that;
    },

    saveCables: function (callback) {
        var that          = this,
            $hiddenInputs = that.$root.select('input[id$="__HIDDEN"]'),
            changes       = [],
            connectors    = [],
            i, tmp, $tr;

        for (i in $hiddenInputs)
        {
            if ($hiddenInputs.hasOwnProperty(i))
            {
                $tr = $hiddenInputs[i].up('tr');
                tmp = $hiddenInputs[i].id.replace(/\D/g, '');
    
                connectors.push({
                    connA: $tr.readAttribute('data-connector-a'),
                    connB: $tr.readAttribute('data-connector-b'),
                    to:    $hiddenInputs[i].getValue()
                });
                
                if (tmp == $hiddenInputs[i].getValue())
                {
                    continue;
                }

                changes.push({
                    from:       tmp,
                    to:         $hiddenInputs[i].getValue(),
                    connA:      $tr.readAttribute('data-connector-a'),
                    connAObjId: $tr.readAttribute('data-object-a'),
                    connB:      $tr.readAttribute('data-connector-b'),
                    connBObjId: $tr.readAttribute('data-object-b')
                });
            }
        }

        if (Object.isFunction(this.options.saveCallback))
        {
            this.options.saveCallback(connectors);
        }
        else
        {
            if (changes.length)
            {
                new Ajax.Request(this.options.url, {
                    parameters: {
                        changes: Object.toJSON(changes),
                        func:    'saveCablePopupData'
                    },
                    method:     'post',
                    onSuccess:  function (xhr) {
                        var json = xhr.responseJSON;

                        if (is_json_response(xhr, true))
                        {
                            if (json.success && Object.isFunction(callback))
                            {
                                callback();
                            }
                        }
                    }
                });
            }
            else
            {
                if (Object.isFunction(callback))
                {
                    callback();
                }
            }
        }

        return this;
    }
});