<style type="text/css">
    .databaseTree-container {
        display: inline-block;
        text-align: left;
        width:45%;
        heigth: 100%;
        min-height: 330px;
    }

    .databaseTree-container span:hover {
        background: #eee;
    }

    .databaseDetailed-container {
        width:100%;
        max-width: 300px;
        min-height: 330px;
        position: fixed;
    }

    #databaseDetailed table{
        width:100%;
    }

    #databaseDetailed table td{
        width:50%;
    }

    #databaseDetailed div.databaseDetailed-footer {
        position: absolute;
        width: 100%;
        height: 20px;
        bottom: 0;
        border-top: 1px solid #888;
        padding-top: 5px;
        text-align: right;
    }
</style>

<div class="p10">
    <div id="databaseTree" class="ml5 fl box-white text-black  databaseTree-container">
        <h3 class="p5 gradient text-shadow" style="border-top:1px solid #B7B7B7; border-bottom:1px solid #B7B7B7;">[{isys type="lang" ident="LC__CATG__DATABASE_FOLDER__DATABASE_HIERARCHY"}]</h3>
        <div class="m5">

        </div>
    </div>
    <div class="fl">
        <div id="databaseDetailed" class="ml10 box-white text-black databaseDetailed-container hide">
            <h3 class="p5 gradient text-shadow" style="border-top:1px solid #B7B7B7; border-bottom:1px solid #B7B7B7;">[{isys type="lang" ident="LC__CATG__DATABASE_FOLDER__DETAILS"}] <span id="details-type"></span></h3>
            <div class="m5">

            </div>
            <div id="databaseDetailedFooter" class="databaseDetailed-footer mr10">
                <span id="databaseDetailedFooterLink" class="mr10">
                    <a href="?"></a>
                </span>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    (function () {
        "use strict";

        idoit.Require.requireQueue(['treeBase'], function () {
            var translator = idoit.Translate || new Hash;

            translator.set('LC_UNIVERSAL__OBJECT_TYPE', '[{isys type="lang" ident="LC_UNIVERSAL__OBJECT_TYPE" p_bHtmlEncode=false}]');
            translator.set('LC__UNIVERSAL__TITLE_LINK', '[{isys type="lang" ident="LC__UNIVERSAL__TITLE_LINK" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE__MANUFACTURER', '[{isys type="lang" ident="LC__CATG__DATABASE__MANUFACTURER" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE__VERSION', '[{isys type="lang" ident="LC__CATG__DATABASE__VERSION" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE_FOLDER__COUNT_DATABASES', '[{isys type="lang" ident="LC__CATG__DATABASE_FOLDER__COUNT_DATABASES" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE__INSTANCE_NAME', '[{isys type="lang" ident="LC__CATG__DATABASE__INSTANCE_NAME" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE__INSTANCE_TYPE', '[{isys type="lang" ident="LC__CATG__DATABASE__INSTANCE_TYPE" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE__SIZE', '[{isys type="lang" ident="LC__CATG__DATABASE__SIZE" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE__PATH', '[{isys type="lang" ident="LC__CATG__DATABASE__PATH" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE__PORT', '[{isys type="lang" ident="LC__CATG__DATABASE__PORT" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE__PORT_NAME', '[{isys type="lang" ident="LC__CATG__DATABASE__PORT_NAME" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE__COUNT_TABLE', '[{isys type="lang" ident="LC__CATG__DATABASE__COUNT_TABLE" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE', '[{isys type="lang" ident="LC__CATG__DATABASE" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE_SA', '[{isys type="lang" ident="LC__CATG__DATABASE_SA" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE_TABLE__TITLE', '[{isys type="lang" ident="LC__CATG__DATABASE_TABLE__TITLE" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE_TABLE__ROW_COUNT', '[{isys type="lang" ident="LC__CATG__DATABASE_TABLE__ROW_COUNT" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE_TABLE__SIZE', '[{isys type="lang" ident="LC__CATG__DATABASE_TABLE__SIZE" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE_TABLE__SCHEMA_SIZE', '[{isys type="lang" ident="LC__CATG__DATABASE_TABLE__SCHEMA_SIZE" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE_TABLE__MAX_SIZE', '[{isys type="lang" ident="LC__CATG__DATABASE_TABLE__MAX_SIZE" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE_TABLE', '[{isys type="lang" ident="LC__CATG__DATABASE_TABLE" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE__TITLE', '[{isys type="lang" ident="LC__CATG__DATABASE__TITLE" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE__COUNT_SCHEMAS', '[{isys type="lang" ident="LC__CATG__DATABASE__COUNT_SCHEMAS" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE_SA__SIZE', '[{isys type="lang" ident="LC__CATG__DATABASE_SA__SIZE" p_bHtmlEncode=false}]');
            translator.set('LC__CATG__DATABASE_SA__MAX_SIZE', '[{isys type="lang" ident="LC__CATG__DATABASE_SA__MAX_SIZE" p_bHtmlEncode=false}]');

            var databaseTreeClass = Class.create(window.BaseTree, {
                infoCache: [],
                /**
                 * Constructor method.
                 *
                 * @param   $super
                 * @param   $container
                 * @param   options
                 * @returns {Window.LocationTree}
                 */
                initialize: function ($super, $container, options) {
                    this.options = {
                        detailContainer: null,
                        translator: null
                    };

                    options = Object.extend(this.options, options || {});

                    $super($container, options);

                    this.process();
                    return this;
                },

                buildInfos: function (tableElement, arr, infos) {
                    var a;

                    if (!tableElement) {
                        tableElement = new Element('table');
                    }

                    for (a in arr) {

                        tableElement.insert(
                            new Element('tr')
                                .insert(
                                    new Element('td')
                                        .writeAttribute('class', 'key')
                                        .insert(new Element('label').insert(arr[a]))
                                )
                                .insert(
                                    new Element('td')
                                        .writeAttribute('class', 'value')
                                        .insert(infos[a])
                                )
                        );
                    }

                    if (infos['entryLink']) {
                        this.options.detailContainer.down('#databaseDetailedFooterLink a')
                            .writeAttribute('href', window.www_dir + infos['entryLink'])
                            .update(infos['entryLinkTitle']);
                    } else{
                        this.options.detailContainer.down('#databaseDetailedFooterLink a')
                            .writeAttribute('href', 'javascript:void(0);')
                            .update('');
                    }

                    return tableElement;
                },

                detailsApp: function (infos) {

                    var a,
                        $table = new Element('table'),
                        $link = new Element('a')
                            .writeAttribute('href', window.www_dir + infos['link'])
                            .insert(infos['title']),

                        arr = {
                            'type': this.options.translator.get('LC_UNIVERSAL__OBJECT_TYPE'),
                            'manufacturer': this.options.translator.get('LC__CATG__DATABASE__MANUFACTURER'),
                            'version': this.options.translator.get('LC__CATG__DATABASE__VERSION'),
                            'instanceName': this.options.translator.get('LC__CATG__DATABASE__INSTANCE_NAME'),
                            'instanceType': this.options.translator.get('LC__CATG__DATABASE__INSTANCE_TYPE'),
                            'path': this.options.translator.get('LC__CATG__DATABASE__PATH'),
                            'port': this.options.translator.get('LC__CATG__DATABASE__PORT'),
                            'portName': this.options.translator.get('LC__CATG__DATABASE__PORT_NAME'),
                            'countDatabases': this.options.translator.get('LC__CATG__DATABASE_FOLDER__COUNT_DATABASES'),
                        };

                    this.options.detailContainer.down('#details-type').update(infos['type']);

                    $table.insert(
                        new Element('tr')
                            .insert(
                                new Element('td')
                                    .writeAttribute('class', 'key')
                                    .insert(new Element('label').insert(this.options.translator.get('LC__UNIVERSAL__TITLE_LINK')))
                            )
                            .insert(
                                new Element('td')
                                    .writeAttribute('class', 'value')
                                    .insert($link)
                            )
                    );

                    this.options.detailContainer.down('div').update(
                        this.buildInfos($table, arr, infos)
                    );
                },

                detailsDb: function (infos) {
                    var arr = {
                        'title': this.options.translator.get('LC__CATG__DATABASE_TABLE__TITLE'),
                        'instanceName': this.options.translator.get('LC__CATG__DATABASE__INSTANCE_NAME'),
                        'size': this.options.translator.get('LC__CATG__DATABASE_SA__SIZE'),
                        'maxSize': this.options.translator.get('LC__CATG__DATABASE_SA__MAX_SIZE'),
                        'schemaCount': this.options.translator.get('LC__CATG__DATABASE__COUNT_SCHEMAS'),
                        'tableCount': this.options.translator.get('LC__CATG__DATABASE__COUNT_TABLE')
                    };

                    this.options.detailContainer.down('#details-type').update(this.options.translator.get('LC__CATG__DATABASE'));

                    this.options.detailContainer.down('div').update(
                        this.buildInfos(null, arr, infos)
                    );
                },

                detailsDbSchema: function (infos) {
                    var arr = {
                        'database': this.options.translator.get('LC__CATG__DATABASE'),
                        'title': this.options.translator.get('LC__CATG__DATABASE_TABLE__TITLE'),
                        'tableCount': this.options.translator.get('LC__CATG__DATABASE__COUNT_TABLE')
                    };

                    this.options.detailContainer.down('#details-type').update(this.options.translator.get('LC__CATG__DATABASE_SA'));

                    this.options.detailContainer.down('div').update(
                        this.buildInfos(null, arr, infos)
                    );
                },

                detailsDbTable: function (infos) {
                    var arr = {
                        'database': this.options.translator.get('LC__CATG__DATABASE'),
                        'schema': this.options.translator.get('LC__CATG__DATABASE_SA'),
                        'title': this.options.translator.get('LC__CATG__DATABASE_TABLE__TITLE'),
                        'rowCount': this.options.translator.get('LC__CATG__DATABASE_TABLE__ROW_COUNT'),
                        'size': this.options.translator.get('LC__CATG__DATABASE_TABLE__SIZE'),
                        'maxSize': this.options.translator.get('LC__CATG__DATABASE_TABLE__MAX_SIZE'),
                        'schemaSize': this.options.translator.get('LC__CATG__DATABASE_TABLE__SCHEMA_SIZE')
                    };

                    this.options.detailContainer.down('#details-type').update(this.options.translator.get('LC__CATG__DATABASE_TABLE'));

                    this.options.detailContainer.down('div').update(
                        this.buildInfos(null, arr, infos)
                    );
                },

                showDetails: function (nodeId) {
                    this.options.detailContainer.removeClassName('hide');
                    this.options.detailContainer.down('div').update('');

                    var type = nodeId.split('-')[0],
                        infos = this.infoCache['n' + nodeId];

                    switch (type){
                        case 'app':
                            this.detailsApp(infos);
                            break;
                        case 'db':
                            this.detailsDb(infos);
                            break;
                        case 'schema':
                            this.detailsDbSchema(infos);
                            break;
                        case 'db_table':
                            this.detailsDbTable(infos);
                            break;
                        default:
                            this.options.detailContainer.addClassName('hide');
                            break;
                    }
                },

                /**
                 * Method for adding all necessary observers.
                 */
                addObserver: function ($super) {
                    $super();

                    this.$container.on('click', 'a', function (ev) {
                        var $li = ev.findElement('li'),
                            nodeId = $li.readAttribute('data-id');

                        this.showDetails(nodeId);
                    }.bind(this));

                },

                /**
                 * Method for rendering a node.
                 *
                 * @param   data
                 * @returns {*}
                 */
                renderNode: function (data) {
                    var open     = this.isOpenNode(data.nodeId),
                        $tooglerImg = '',
                        clickable = '';


                    if (data.nodeId != this.options.rootNodeId) {
                        var $tooglerImg = new Element('img')
                            .writeAttribute('src', window.dir_images + 'icons/silk/bullet_toggle_' + (open ? 'minus' : 'plus') + '.png')
                            .writeAttribute('class', 'child-toggle ' + (data.hasChildren? '' : 'hide'));
                        clickable = 'mouse-pointer';
                    }

                    return new Element('li')
                        .writeAttribute('data-id', data.nodeId)
                        .update($tooglerImg)
                        .insert(new Element('label')
                            .writeAttribute('class', 'tree-inner ' + clickable)
                            .insert(new Element('img')
                                .writeAttribute('src', data.icon)
                                .writeAttribute('class', 'mr5'))
                            .insert(new Element('a')
                                .update(data.nodeTitle)
                                .writeAttribute('value', data.nodeId)
                                .writeAttribute('href', 'javascript:void(0);')))
                        .insert(new Element('ul')
                            .writeAttribute('class', 'css-tree ' + (open ? '' : 'hide')));
                },

                /**
                 * Method for loading children nodes via ajax.
                 *
                 * @param nodeId
                 * @param callback
                 */
                loadChildrenNodes: function (nodeId, callback) {

                    new Ajax.Request(window.www_dir + '?ajax=1&call=database&func=loadHierarchy', {
                        parameters: {
                            Id: nodeId
                        },
                        onComplete: function (xhr) {
                            var json = xhr.responseJSON, i;

                            if (!json.success) {
                                idoit.Notify.error(json.message || xhr.responseText, {sticky: true});
                            }

                            this.cache['n' + nodeId] = json.data;

                            if (!this.infoCache['n' + nodeId] && json.data.info) {
                                this.infoCache['n' + nodeId] = json.data.info;
                            }

                            for (i in json.data.children) {
                                if (!json.data.children.hasOwnProperty(i)) {
                                    continue;
                                }

                                this.cache['n' + json.data.children[i].nodeId] = json.data.children[i];

                                if (!this.infoCache['n' + json.data.children[i].nodeId]) {
                                    this.infoCache['n' + json.data.children[i].nodeId] = json.data.children[i].info;
                                }
                            }

                            if (Object.isFunction(callback)) {
                                callback(nodeId, this.cache['n' + nodeId]);
                            }
                        }.bind(this)
                    });
                },
            });

            new databaseTreeClass($('databaseTree').down('div'), {
                rootNodeId: 'root-' + parseInt('[{$rootNode}]'),
                detailContainer: $('databaseDetailed'),
                translator: translator
            });
        });
    }());
</script>