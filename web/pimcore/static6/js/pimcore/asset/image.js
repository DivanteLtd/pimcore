/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

pimcore.registerNS("pimcore.asset.image");
pimcore.asset.image = Class.create(pimcore.asset.asset, {

    initialize: function (id) {

        this.id = intval(id);
        this.setType("image");
        this.addLoadingPanel();

        pimcore.plugin.broker.fireEvent("preOpenAsset", this, "image");

        var user = pimcore.globalmanager.get("user");

        this.properties = new pimcore.element.properties(this, "asset");
        this.versions = new pimcore.asset.versions(this);
        this.scheduler = new pimcore.element.scheduler(this, "asset");
        this.dependencies = new pimcore.element.dependencies(this, "asset");

        if (user.isAllowed("notes_events")) {
            this.notes = new pimcore.element.notes(this, "asset");
        }

        this.tagAssignment = new pimcore.element.tag.assignment(this, "asset");
        this.metadata = new pimcore.asset.metadata(this);

        this.getData();
    },

    getTabPanel: function () {

        var items = [];
        var user = pimcore.globalmanager.get("user");

        items.push(this.getDisplayPanel());

        if (!pimcore.settings.asset_hide_edit && (this.isAllowed("save") || this.isAllowed("publish"))) {
            items.push(this.getEditPanel());
        }

        var exifPanel = this.getExifPanel();
        if(exifPanel) {
            items.push(exifPanel);
        }

        if (this.isAllowed("publish")) {
            items.push(this.metadata.getLayout());
        }
        if (this.isAllowed("properties")) {
            items.push(this.properties.getLayout());
        }

        if (this.isAllowed("versions")) {
            items.push(this.versions.getLayout());
        }
        if (this.isAllowed("settings")) {
            items.push(this.scheduler.getLayout());
        }

        items.push(this.dependencies.getLayout());

        if (user.isAllowed("notes_events")) {
            items.push(this.notes.getLayout());
        }

        if (user.isAllowed("tags_assignment")) {
            items.push(this.tagAssignment.getLayout());
        }

        this.tabbar = new Ext.TabPanel({
            tabPosition: "top",
            region: 'center',
            deferredRender: true,
            enableTabScroll: true,
            border: false,
            items: items,
            activeTab: 0
        });

        return this.tabbar;
    },

    getEditPanel: function () {

        if (!this.editPanel) {
            var url = '/admin/asset/image-editor?id=' + this.id;
            url = pimcore.helpers.addCsrfTokenToUrl(url);
            var frameId = 'asset_image_edit_' + this.id;
            this.editPanel = new Ext.Panel({
                title: t("edit"),
                html: '<iframe src="' + url + '" frameborder="0" ' +
                'style="width: 100%;" id="' + frameId + '"></iframe>',
                iconCls: "pimcore_icon_edit"
            });
            this.editPanel.on("resize", function (el, width, height, rWidth, rHeight) {
                Ext.get(frameId).setStyle({
                    height: (height - 7) + "px"
                });
            }.bind(this));
        }

        return this.editPanel;
    },

    getExifPanel: function () {
        if (!this.exifPanel) {

            if(!this.data["imageInfo"] || (!this.data["imageInfo"]["exif"] && !this.data["imageInfo"]["iptc"])) {
                return false;
            }

            var exifPanel = new Ext.grid.PropertyGrid({
                title: 'EXIF',
                flex: 1,
                border: true,
                source: this.data["imageInfo"]["exif"] || [],
                clicksToEdit: 1000
            });
            exifPanel.plugins[0].disable();

            var iptcPanel = new Ext.grid.PropertyGrid({
                title: 'IPTC',
                flex: 1,
                border: true,
                source: this.data["imageInfo"]["iptc"] || [],
                clicksToEdit: 1000
            });
            iptcPanel.plugins[0].disable();

            var xmpPanel = new Ext.grid.PropertyGrid({
                title: 'XMP',
                flex: 1,
                border: true,
                source: this.data["imageInfo"]["xmp"] || [],
                clicksToEdit: 1000
            });
            xmpPanel.plugins[0].disable();

            this.exifPanel = new Ext.Panel({
                title: "EXIF/XMP/IPTC",
                layout: {
                    type: 'hbox',
                    align: 'stretch'
                },
                iconCls: "pimcore_icon_exif",
                items: [exifPanel, xmpPanel, iptcPanel]
            });
        }

        return this.exifPanel;
    },

    getDisplayPanel: function () {

        if (!this.displayPanel) {
            var details = [{
                title: t("tools"),
                bodyStyle: "padding: 10px;",
                items: [{
                    xtype: "button",
                    text: t("set_focal_point"),
                    iconCls: "pimcore_icon_focal_point",
                    width: "100%",
                    textAlign: "left",
                    handler: function () {
                        this.addFocalPoint();
                    }.bind(this)
                }, {
                    xtype: "button",
                    text: t("toggle_image_features"),
                    iconCls: "pimcore_icon_image_features",
                    width: "100%",
                    textAlign: "left",
                    hidden: (this.data['customSettings'] && this.data['customSettings']['faceCoordinates']) ? false : true,
                    style: "margin-top: 5px",
                    handler: function () {
                        var features = this.displayPanel.getEl().down('.pimcore_asset_image_preview').query('.image_feature');
                        features.forEach(function (feature) {
                           Ext.get(feature).toggle();
                        });
                    }.bind(this)
                }, {
                    xtype: "container",
                    html: "<hr>"
                }, {
                    xtype: "button",
                    text: t("standard_preview"),
                    iconCls: "pimcore_icon_image",
                    width: "100%",
                    textAlign: "left",
                    style: "margin-top: 5px",
                    handler: function () {
                        if(this.previewMode != 'image') {
                            this.initPreviewImage();
                        }
                    }.bind(this)
                }, {
                    xtype: "button",
                    text: t("vr_viewer"),
                    iconCls: "pimcore_icon_vr",
                    width: "100%",
                    textAlign: "left",
                    style: "margin-top: 5px",
                    handler: function () {
                        if(this.previewMode != 'vr') {
                            this.initPreviewVr();
                        }
                    }.bind(this)
                }]
            }];

            if (this.data.imageInfo.dimensions) {

                var dimensionPanel = new Ext.create('Ext.grid.property.Grid', {
                    title: t("dimensions"),
                    source: this.data.imageInfo.dimensions,
                    autoHeight: true,

                    clicksToEdit: 1000,
                    viewConfig: {
                        forceFit: true,
                        scrollOffset: 2
                    }
                });
                dimensionPanel.plugins[0].disable();
                dimensionPanel.getStore().sort("name", "DESC");

                details.push(dimensionPanel);
            }

            var downloadDefaultWidth = 800;

            if (this.data.imageInfo) {
                if (this.data.imageInfo.dimensions && this.data.imageInfo.dimensions.width) {
                    downloadDefaultWidth = intval(this.data.imageInfo.dimensions.width);
                }
            }

            var downloadShortcutsHandler = function (type) {
                pimcore.helpers.download("/admin/asset/download-image-thumbnail?id=" + this.id   + "&type=" + type);
            };

            this.downloadBox = new Ext.Panel({
                title: t("download"),
                bodyStyle: "padding: 10px;",
                style: "margin: 10px 0 10px 0",
                items: [{
                    xtype: "button",
                    iconCls: "pimcore_icon_image",
                    width: "100%",
                    textAlign: "left",
                    style: "margin-bottom: 5px",
                    text: t("original_file"),
                    handler: function () {
                        pimcore.helpers.download("/admin/asset/download?id=" + this.data.id);
                    }.bind(this)
                },{
                    xtype: "button",
                    iconCls: "pimcore_icon_world",
                    width: "100%",
                    textAlign: "left",
                    style: "margin-bottom: 5px",
                    text: t("web_format"),
                    handler: downloadShortcutsHandler.bind(this, "web")
                }, {
                    xtype: "button",
                    iconCls: "pimcore_icon_print",
                    width: "100%",
                    textAlign: "left",
                    style: "margin-bottom: 5px",
                    text: t("print_format"),
                    handler: downloadShortcutsHandler.bind(this, "print")
                },{
                    xtype: "button",
                    iconCls: "pimcore_icon_docx",
                    width: "100%",
                    textAlign: "left",
                    style: "margin-bottom: 5px",
                    text: t("office_format"),
                    handler: downloadShortcutsHandler.bind(this, "office")
                }]
            });
            details.push(this.downloadBox);


            this.customDownloadBox = new Ext.form.FormPanel({
                title: t("custom_download"),
                bodyStyle: "padding: 10px;",
                style: "margin: 10px 0 10px 0",
                items: [{
                    xtype: "combo",
                    triggerAction: "all",
                    name: "format",
                    fieldLabel: t("format"),
                    store: [["JPEG", "JPEG"], ["PNG", "PNG"]],
                    mode: "local",
                    value: "JPEG",
                    editable: false,
                    listeners: {
                        select: function (el) {
                            if (this.data.imageInfo["exiftoolAvailable"]) {
                                var dpiField = this.customDownloadBox.getComponent("dpi");
                                if (el.getValue() == "JPEG") {
                                    dpiField.enable();
                                } else {
                                    dpiField.disable();
                                }
                            }
                        }.bind(this)
                    }
                }, {
                    xtype: "combo",
                    triggerAction: "all",
                    name: "resize_mode",
                    itemId: "resize_mode",
                    fieldLabel: t("resize_mode"),
                    forceSelection: true,
                    store: [["scaleByWidth", t("scalebywidth")], ["scaleByHeight", t("scalebyheight")], ["resize", t("resize")]],
                    mode: "local",
                    value: "scaleByWidth",
                    editable: false,
                    listeners: {
                        select: function (el) {
                            var widthField = this.customDownloadBox.getComponent("width");
                            var heightField = this.customDownloadBox.getComponent("height");

                            if(el.getValue() == "scalebywidth") {
                                widthField.enable();
                                heightField.disable();
                            } else if(el.getValue() == "scalebyheight") {
                                widthField.disable();
                                heightField.enable();
                            } else {
                                widthField.enable();
                                heightField.enable();
                            }
                        }.bind(this)
                    }
                }, {
                    xtype: "numberfield",
                    name: "width",
                    itemId: "width",
                    fieldLabel: t("width"),
                    value: downloadDefaultWidth
                }, {
                    xtype: "numberfield",
                    name: "height",
                    itemId: "height",
                    fieldLabel: t("height"),
                    disabled: true
                }, {
                    xtype: "numberfield",
                    name: "quality",
                    fieldLabel: t("quality"),
                    emptyText: t("original")
                }, {
                    xtype: "numberfield",
                    name: "dpi",
                    itemId: "dpi",
                    fieldLabel: "DPI",
                    emptyText: t("original"),
                    disabled: !this.data.imageInfo["exiftoolAvailable"]
                }],
                buttons: [{
                    text: t("download"),
                    iconCls: "pimcore_icon_download",
                    handler: function () {
                        var config = this.customDownloadBox.getForm().getFieldValues();
                        pimcore.helpers.download("/admin/asset/download-image-thumbnail?id=" + this.id
                            + "&config=" + Ext.encode(config));
                    }.bind(this)
                }]
            });
            details.push(this.customDownloadBox);

            var thumbnailsStore = new Ext.data.JsonStore({
                autoDestroy: true,
                proxy: {
                    type: 'ajax',
                    url: '/admin/settings/thumbnail-tree'
                },
                fields: ['id']
            });
            thumbnailsStore.load();

            this.thumbnailDownloadBox = new Ext.form.FormPanel({
                title: t("download_thumbnail"),
                bodyStyle: "padding: 10px;",
                style: "margin: 10px 0",
                items: [{
                    xtype: "combo",
                    name: "thumbnail",
                    fieldLabel: t("thumbnail"),
                    store: thumbnailsStore,
                    editable: false
                }],
                buttons: [{
                    text: t("download"),
                    iconCls: "pimcore_icon_download",
                    handler: function () {
                        var config = this.thumbnailDownloadBox.getForm().getFieldValues();
                        if (!config.thumbnail) {
                            pimcore.helpers.showNotification(t("error"), t("no_thumbnail_selected"), "error");
                        } else {
                            pimcore.helpers.download("/admin/asset/download-image-thumbnail?id=" + this.id
                                + "&thumbnail=" + config.thumbnail);
                        }
                    }.bind(this)
                }]
            });
            details.push(this.thumbnailDownloadBox);

            this.previewContainerId = 'pimcore_asset_image_preview_' + this.id;
            this.previewMode = 'image';
            if(this.data.imageInfo["isVrImage"]) {
                this.previewMode = 'vr';
            }

            this.displayPanel = new Ext.Panel({
                title: t("view"),
                layout: "border",
                iconCls: "pimcore_icon_view",
                items: [{
                    region: "center",
                    html: '<div id="' + this.previewContainerId + '" class="pimcore_asset_image_preview"></div>',
                }, {
                    region: "east",
                    width: 300,
                    items: details,
                    scrollable: "y"
                }]
            });

            this.displayPanel.on('afterrender', function (ev) {
                if(this.data['customSettings']) {
                    if (this.data['customSettings']['focalPointX']) {
                        this.addFocalPoint(this.data['customSettings']['focalPointX'], this.data['customSettings']['focalPointY']);
                    }

                    if (this.data['customSettings']['faceCoordinates']) {
                        this.data['customSettings']['faceCoordinates'].forEach(function (coord) {
                            this.addImageFeature(coord);
                        }.bind(this));

                    }
                }

            }.bind(this));

            this.displayPanel.on('resize', function (el, width, height, rWidth, rHeight) {
                if(this.previewMode == 'vr') {
                    this.initPreviewVr();
                } else {
                    this.initPreviewImage();
                    var area = this.displayPanel.getEl().down('img');
                    if(area) {
                        area.setStyle('max-width', (width - 340) + "px");
                        area.setStyle('max-height', (height - 40) + "px");
                    }
                }
            }.bind(this));
        }

        return this.displayPanel;
    },

    initPreviewVr: function () {
        Ext.get(this.previewContainerId).setHtml('');
        var vrView = new VRView.Player('#' + this.previewContainerId, {
            image: '/admin/asset/get-image-thumbnail%3Fid=' + this.id + '%26width=2000',
            is_stereo: (this.data.imageInfo.dimensions.width === this.data.imageInfo.dimensions.height),
            width: (this.displayPanel.getWidth()-340),
            height: (this.displayPanel.getHeight()-40),
            hide_fullscreen_button: true
        });

        this.previewMode = 'vr';
    },

    initPreviewImage: function () {
        var date = new Date();
        var dc = date.getTime();
        var html = '<img src="/admin/asset/get-image-thumbnail?id=' + this.id + '&treepreview=true&hdpi=true&_dc=' + dc + '">';
        Ext.get(this.previewContainerId).setHtml(html);

        this.previewMode = 'image';
    },

    addImageFeature: function (coords) {
        var area = this.displayPanel.getEl().down('.pimcore_asset_image_preview');
        var imageFeature = area.insertHtml('afterBegin', '<div class="image_feature"></div>', true);
        imageFeature.setTop(coords['y'] + "%");
        imageFeature.setLeft(coords['x'] + "%");
        imageFeature.setWidth(coords['width'] + "%");
        imageFeature.setHeight(coords['height'] + "%");
    },

    addFocalPoint: function (positionX, positionY) {

        if(this["marker"]) {
            return;
        }

        var area = this.displayPanel.getEl().down('.pimcore_asset_image_preview');
        var marker = area.insertHtml('afterBegin', '<div class="marker"></div>');
        marker = Ext.get(marker);

        marker.on('contextmenu', function (ev) {
            var menu = new Ext.menu.Menu();

            menu.add(new Ext.menu.Item({
                text: t("delete"),
                iconCls: "pimcore_icon_delete",
                handler: function (el) {
                    marker.remove();
                    delete this.marker;
                }.bind(this)
            }));

            menu.showAt(ev.getXY());
            ev.stopEvent();
        }.bind(this));

        if(positionX && positionY) {
            marker.setTop(positionY + "%");
            marker.setLeft(positionX + "%");
        }

        var markerDD = new Ext.dd.DD(marker);

        this.marker = marker;
    },

    getSaveData : function ($super, only) {
        var parameters = $super(only);

        if(this["marker"]) {

            var top = intval(this.marker.getStyle('top'));
            var left = intval(this.marker.getStyle('left'));

            var boundingBox = this.marker.up().getSize();

            var x = round(left * 100 / boundingBox.width, 8);
            var y = round(top  * 100 / boundingBox.height, 8);

            parameters["image"] = Ext.encode({
                "focalPoint": {
                    "x": x,
                    "y": y
                }
            });
        }

        return parameters;
    }
});
