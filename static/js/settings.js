pimcore.registerNS("pimcore.plugin.MicrosoftTranslator");

pimcore.plugin.MicrosoftTranslator = Class.create(pimcore.plugin.admin, {

    getClassName: function () {
        return "pimcore.plugin.MicrosoftTranslator";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },
    
    pimcoreReady: function (params, broker){
        // add a sub-menu item under "Extras" in the main menu
        var extrasBar = pimcore.globalmanager.get('layout_toolbar').extrasMenu;

        var action = new Ext.Action({
            id: "microsofttranslator_menu_item",
            text: "Microsoft Translator",
            iconCls: "pimcore_icon_translations",
            handler: this.getSettings.bind(this)
        });

        extrasBar.add(action);
    },
    
    getSettings : function() {

        Ext.Ajax.request({
            url: "/plugin/MicrosoftTranslator/settings/get-settings",
            method: 'get',
            success: this.onGetSettingsSuccess.bind(this)
        });

    },

    onGetSettingsSuccess: function(response) {
        this.data = Ext.decode(response.responseText);
        this.getTabPanel();
    },
    
    getTabPanel: function() {

         this.layout = new Ext.FormPanel({
             bodyStyle:'padding:20px 5px 20px 5px;',
             layout: "vbox",
             buttons: [{
                 text: "Save",
                 handler: this.setSettings.bind(this),
                 iconCls: "pimcore_icon_apply"
             }],
             items: [{
                 xtype: 'fieldset',
                 forceLayout: true,
                 title: t('general'),
                 collapsible: false,
                 collapsed: false,
                 autoheight: true,
                 labelWidth: 250,
                 defaultType: 'textfield',
                 defaults: {
                     width:200,
                     editable: true
                 },
                 items: [{
                     fieldLabel: t('Client Id'),
                     name: 'clientId',
                     value: this.data.clientId
                 },{
                     fieldLabel: t('Client Secret'),
                     name: 'clientSecret',
                     value: this.data.clientSecret
                 },{
                     fieldLabel: t('Default Content Language'),
                     name: 'defaultContentLanguage',
                     value: this.data.defaultContentLanguage
                 }]
             },{
                 html:['<p><a target="_blank" href="http://msdn.microsoft.com/en-us/library/hh454950.aspx">Where to get the details above?</a></p>',
                       '<p>Set the default language to the language of the default site, this stops your default language from being translated too.</p>'].join('')
             }]
         });

         this.panel = new Ext.Panel({
            id:         "microsoft_translator_settings_panel",
            title:      "Microsoft Translator Settings",
            iconCls:    "pimcore_icon_translations",
            border:     false,
            layout:     "fit",
            closable:   true,
            items: [this.layout]
        });

        var tabPanel = Ext.getCmp("pimcore_panel_tabs");
        tabPanel.add(this.panel);
        pimcore.layout.refresh();

        tabPanel.setActiveItem("microsoft_translator_settings_panel");

    },
    
    setSettings : function() {
        var values = this.layout.getForm().getFieldValues();
        Ext.Ajax.request({
            url: "/plugin/MicrosoftTranslator/settings/set-settings",
            method: 'post',
            params: {
                data: Ext.encode(values)
            },
            success: this.onSetSettingsSuccess.bind(this)
        })
    },

    onSetSettingsSuccess: function(response) {
        try {
            var res = Ext.decode(response.responseText);
            if (res.success) {
                pimcore.helpers.showNotification(t("success"), t("microsoft_translator_settings_success"), "success");

                Ext.MessageBox.confirm(t("info"), t("reload_pimcore_changes"), function (buttonValue) {
                    if (buttonValue == "yes") {
                        window.location.reload();
                    }
                }.bind(this));
            } else {
                pimcore.helpers.showNotification(t("error"), t("microsoft_translator_save_error"),
                    "error", t(res.message));
            }
        } catch(e) {
            pimcore.helpers.showNotification(t("error"), t("microsoft_translator_save_error"), "error");
        }
    }

});

var MicrosoftTranslator = new pimcore.plugin.MicrosoftTranslator();