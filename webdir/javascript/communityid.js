/**
 * Classes enforce private and public members through the Module Pattern
 * (the vars outside are private, and what goes inside the return is public)
 * We use classes inside the "SCIRET" namespace
 * @see http://yuiblog.com/blog/2007/06/12/module-pattern/
 */

/**
 * Aliases definitions (functions, namespaces)
 */
YAHOO.namespace("commid");
COMMID = YAHOO.commid;

COMMID.utils = function() {
    return {
        evalScripts: function (el) {
            el = (typeof el =="string")? $(el) : el;
            var scripts = el.getElementsByTagName("script");
            for(var i=0; i < scripts.length;i++) {
                eval(scripts[i].innerHTML);
            }
        },

        replaceContent: function(responseObj, elId) {
            $(elId).innerHTML = responseObj.responseText;
            COMMID.utils.evalScripts(elId);
        },

        removeElement: function(element) {
            element.parentNode.removeChild(element);
        },

        hideElement: function(elName) {
            $(elName).style.visibility = "hidden";
        },

        unHideElement: function(elName) {
            $(elName).style.visibility = "visible";
        },

        asyncFailed: function() {
            alert(COMMID.lang['operation failed']);
        },

        addDatatableTranslations: function(datatableConfig) {
            datatableConfig.MSG_EMPTY = COMMID.lang["No records found."];
            datatableConfig.MSG_LOADING = COMMID.lang["Loading..."];
            datatableConfig.MSG_ERROR = COMMID.lang["Data error."];
            datatableConfig.MSG_SORTASC = COMMID.lang["Click to sort ascending"];
            datatableConfig.MSG_SORTDESC = COMMID.lang["Click to sort descending"];
        }
    }
}();

/**
* This is only to load YUI libs that don't need to be used immediately after
* the page is loaded
*/
COMMID.loader = function() {
    var loader;

    return {
        combine: true,
        base: null,

        insert: function(arrComponents, onSuccess, scope) {
            loader = new YAHOO.util.YUILoader({
                require: arrComponents,
                onSuccess: onSuccess,
                scope: scope,
                base: this.base,

                // uncomment to download debugging libs
                //filter: "DEBUG",

                combine: this.combine
            });
            loader.insert();
        }
    };
}();

/**
 * Rich-text editor
 */
COMMID.editor = function() {
    var myEditor;
    var state = 'off';
    var resize = null;

    return {

        init: function(width, height, element) {
            YAHOO.log('Create the Editor..', 'info', 'example');
            myEditor = new YAHOO.widget.Editor(element, {
                    width: width,
                    height: height,
                    dompath: true, //Turns on the bar at the bottom
                    animate: true, //Animates the opening, closing and moving of Editor windows
                    handleSubmit: true
            });

            myEditor.on('toolbarLoaded', function() {
                this.toolbar.addButtonGroup({
                    group: 'editcodeGroup',
                    label: '&nbsp;',
                    buttons: [
                        {
                            type: 'separator'
                        },
                        {
                            type: 'push',
                            label: 'Edit HTML Code',
                            value: 'editcode'
                        }]
                });

                this.toolbar.on('editcodeClick', function() {
                    var ta = this.get('element');
                    var iframe = this.get('iframe').get('element');

                    if (state == 'on') {
                        state = 'off';
                        this.toolbar.set('disabled', false);
                        YAHOO.log('Inject the HTML from the textarea into the editor', 'info', 'example');
                        this.setEditorHTML(ta.value);
                        if (!this.browser.ie) {
                            this._setDesignMode('on');
                        }

                        YAHOO.util.Dom.removeClass(iframe, 'editor-hidden');
                        YAHOO.util.Dom.addClass(ta, 'editor-hidden');
                        this.show();
                        this._focusWindow();
                    } else {
                        state = 'on';
                        YAHOO.log('Show the Code Editor', 'info', 'example');
                        this.cleanHTML();
                        YAHOO.log('Save the Editors HTML', 'info', 'example'); 
                        YAHOO.util.Dom.addClass(iframe, 'editor-hidden');
                        YAHOO.util.Dom.removeClass(ta, 'editor-hidden');
                        this.toolbar.set('disabled', true);
                        this.toolbar.getButtonByValue('editcode').set('disabled', false);
                        this.toolbar.selectButton('editcode');
                        this.dompath.innerHTML = 'Editing HTML Code';
                        this.hide();
                    }
                    return false;
                }, this, true);

                this.on('cleanHTML', function(ev) {
                    YAHOO.log('cleanHTML callback fired..', 'info', 'example'); 
                    this.get('element').value = ev.html;
                }, this, true);

                this.on('afterRender', function() {
                    var wrapper = this.get('editor_wrapper');
                    wrapper.appendChild(this.get('element'));
                    this.setStyle('width', '100%');
                    this.setStyle('height', '100%');
                    this.setStyle('visibility', '');
                    this.setStyle('top', '');
                    this.setStyle('left', '');
                    this.setStyle('position', '');

                    this.addClass('editor-hidden');
                }, this, true);

            }, myEditor, true);

            myEditor.on('editorContentLoaded', function() {
                resize = new YAHOO.util.Resize(myEditor.get('element_cont').get('element'), {
                    handles: ['br'],
                    autoRatio: true,
                    status: true,
                    proxy: true,
                    setSize: false
                });

                resize.on('startResize', function() {
                    this.hide();
                    this.set('disabled', true);
                }, myEditor, true);

                resize.on('resize', function(args) {
                    var h = args.height;
                    var th = (this.toolbar.get('element').clientHeight + 2); // it has a 1px border
                    var dh = (this.dompath.clientHeight + 1); // it has a 1px top border
                    var newH = (h - th - dh);
                    this.set('width', args.width + 'px');
                    this.set('height', newH + 'px');
                    this.set('disabled', false);
                    this.show();
                }, myEditor, true);
            });

            myEditor._defaultToolbar.titlebar = false;
            myEditor.render();
        }
    }
}();

/**
* MessageUsers
*/
COMMID.messageUsers = function() {
    return {
        send: function() {
            if (!confirm(COMMID.lang["Are you sure you wish to send this message to ALL users?"])) {
                return false;
            }

            document.messageUsersForm.messageType.value = $('bodyPlainDt').style.display == "block"? "plain" : "rich";

            return true;
        },

        /* gotta hide/show dt and dd's independently, to overcome an IE bug */
        switchToPlainText: function() {
            $('linkSwitchToPlain').style.display = "none";
            $('linkSwitchToRich').style.display = "block";

            $('bodyPlainDt').style.display = "block";
            $('bodyPlainDd').style.display = "block";
            $('bodyHTMLDt').style.display = "none";
            $('bodyHTMLDd').style.display = "none";
        },

        switchToRichText: function() {
            $('linkSwitchToPlain').style.display = "block";
            $('linkSwitchToRich').style.display = "none";

            $('bodyPlainDt').style.display = "none";
            $('bodyPlainDd').style.display = "none";
            $('bodyHTMLDt').style.display = "block";
            $('bodyHTMLDd').style.display = "block";
        }
    };
}();

COMMID.general = function() {

    return {
        editAccountInfo: function() {
            COMMID.utils.unHideElement("loadingAccountInfo");
            var transaction = YAHOO.util.Connect.asyncRequest(
                'GET',
                'profilegeneral/editaccountinfo?userid=' + COMMID.targetUserId,
                {
                    success: function (responseObj) {
                        COMMID.utils.replaceContent(responseObj, "accountInfo")
                        COMMID.utils.hideElement("loadingAccountInfo");
                    },
                    failure: COMMID.utils.asyncFailed
                });
        },

        changePassword: function() {
            COMMID.utils.unHideElement("loadingAccountInfo");
            var transaction = YAHOO.util.Connect.asyncRequest(
                'GET',
                'profilegeneral/changepassword?userid=' + COMMID.targetUserId,
                {
                    success: function (responseObj) {
                        COMMID.utils.replaceContent(responseObj, "accountInfo")
                        COMMID.utils.hideElement("loadingAccountInfo");
                    },
                    failure: COMMID.utils.asyncFailed
                });
        }
    };
}();


COMMID.personalInfo = function() {
    return {
        edit: function() {
            COMMID.utils.unHideElement("loadingEditPersonalInfo");
            var transaction = YAHOO.util.Connect.asyncRequest(
                'GET',
                'personalinfo/edit',
                {
                    success: function (responseObj) {
                        COMMID.utils.replaceContent(responseObj, "personalInfo")
                        COMMID.utils.hideElement("loadingEditPersonalInfo");
                    },
                    failure: COMMID.utils.asyncFailed
                }
            );
        }
    };
}();


COMMID.sitesList = function() {
    var myDataSource;
    var myDataSourceURL;
    var myDataTable;
    var myPaginator;
    var myTableConfig;
    var fieldsDialog;

    var buildQueryString = function (state,dt) { 
        return "startIndex=" + state.pagination.recordOffset + 
               "&results=" + state.pagination.rowsPerPage;
    }; 

    var formatOperationsColumn = function(elCell, oRecord, oColumn, oData) {
        var links = new Array();
        var recordId = oRecord.getId();

        if (oRecord.getData("trusted")) {
            links.push("<a href=\"#\" onclick=\"COMMID.sitesList.deny('" + recordId + "')\">" + COMMID.lang["deny"] + "</a>");
        } else {
            links.push("<a href=\"#\" onclick=\"COMMID.sitesList.allow('" + recordId + "')\" >" + COMMID.lang["allow"] + "</a>");
        }

        if (oRecord.getData("infoExchanged")) {
            links.push("<a href=\"#\" onclick=\"COMMID.sitesList.showInfo('" + recordId + "')\" >" + COMMID.lang["view info exchanged"] + "</a>");
        }

        links.push("<a href=\"#\" onclick=\"COMMID.sitesList.deleteSite('" + recordId + "')\">" + COMMID.lang["delete"] + "</a>");

        elCell.innerHTML = links.join("&nbsp;|&nbsp;");
    };

    var myColumnDefs = [
        {key: "site", label: COMMID.lang["Site"]},
        {key: "operations", label: "", formatter: formatOperationsColumn}
    ];

    return {
        init: function() {
            myDataSourceURL = COMMID.baseDir + "/sites/list?";

            fieldsDialog = new YAHOO.widget.Dialog(
                "fieldsDialog",
                {
                    width       : "30em",
                    effect      : {
                                    effect      : YAHOO.widget.ContainerEffect.FADE,
                                    duration    : 0.25
                                  },
                    fixedcenter : false,
                    modal       : true,
                    visible     : false,
                    draggable   : true
                }
            );
            fieldsDialog.render();
        },

        initTable: function() {
            myDataSource = new YAHOO.util.DataSource(myDataSourceURL);
            myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
            myDataSource.responseSchema = {
                resultsList: "records",
                fields: ["id", "site", "trusted", "infoExchanged"],
                metaFields : {
                    totalRecords: 'totalRecords'
                }
            };

            myPaginator = new YAHOO.widget.Paginator({
                alwaysVisible      : false,
                containers         : ['paging'],
                pageLinks          : 5,
                rowsPerPage        : 15,
                rowsPerPageOptions : [15,30,60],
                template           : "<strong>{CurrentPageReport}</strong> {PreviousPageLink} {PageLinks} {NextPageLink} {RowsPerPageDropdown}",
                pageReportTemplate      : "({currentPage} " + COMMID.lang["of"] + " {totalPages})",
                nextPageLinkLabel       : COMMID.lang["next"] + "&nbsp;&gt;",
                previousPageLinkLabel   : "&lt;&nbsp;" + COMMID.lang["prev"]
            });

            myTableConfig = {
                initialRequest         : 'startIndex=0&results=15',
                generateRequest        : buildQueryString,
                paginator              : myPaginator
            };
            COMMID.utils.addDatatableTranslations(myTableConfig);

            myDataTable = new YAHOO.widget.DataTable("dt", myColumnDefs, myDataSource, myTableConfig);
        },

        showInfo: function(recordId) {
            var oRecord = myDataTable.getRecord(recordId);
            var infoExchanged = oRecord.getData("infoExchanged");

            $("fieldsDialogSite").innerHTML = oRecord.getData("site");

            var fields = new Array();
            for (var fieldName in infoExchanged) {
                fields.push("<div class=\"yui-gf\"><div class=\"yui-u first\">" + fieldName + ":</div>\n"
                            +"<div class=\"yui-u\">" + infoExchanged[fieldName] + "</div></div>");
            }
            $("fieldsDialogDl").innerHTML = fields.join("\n");
            $("fieldsDialog").style.display = "block";
            fieldsDialog.show();
        },

        closeDialog: function() {
            fieldsDialog.hide();
        },

        deny: function(recordId) {
            var oRecord = myDataTable.getRecord(recordId);
            var site = oRecord.getData("site");
            if (!confirm(COMMID.lang["Are you sure you wish to deny trust to this site?"] + "\n\n" + site)) {
                return;
            }

            YAHOO.util.Connect.asyncRequest(
                "POST",
                COMMID.baseDir + "/sites/deny",
                {
                    success : function (responseObj) {
                        try {
                            var r = YAHOO.lang.JSON.parse(responseObj.responseText);
                            if (r.code == 200) {
                                alert(COMMID.lang["Trust the following site has been denied:"] + "\n\n" + site);
                                this.initTable();
                            }
                        } catch (e) {
                            alert(COMMID.lang["ERROR. The server returned:"] + "\n\n" + responseObj.responseText);
                        }
                    },
                    failure : COMMID.utils.asyncFailed,
                    scope   : this
                },
                "id=" + oRecord.getData("id")
            );
        },

        allow: function(recordId) {
            var oRecord = myDataTable.getRecord(recordId);
            var site = oRecord.getData("site");
            if (!confirm(COMMID.lang["Are you sure you wish to allow access to this site?"] + "\n\n" + site)) {
                return;
            }

            YAHOO.util.Connect.asyncRequest(
                "POST",
                COMMID.baseDir + "/sites/allow",
                {
                    success : function (responseObj) {
                        try {
                            var r = YAHOO.lang.JSON.parse(responseObj.responseText);
                            if (r.code == 200) {
                                alert(COMMID.lang["Trust to the following site has been granted:"] + "\n\n" + site);
                                this.initTable();
                            }
                        } catch (e) {
                            alert(COMMID.lang["ERROR. The server returned:"] + "\n\n" + responseObj.responseText);
                        }
                    },
                    failure : COMMID.utils.asyncFailed,
                    scope   : this
                },
                "id=" + oRecord.getData("id")
            );
        },

        deleteSite: function(recordId) {
            var oRecord = myDataTable.getRecord(recordId);
            var site = oRecord.getData("site");
            if (!confirm(COMMID.lang["Are you sure you wish to delete your relationship with this site?"] + "\n\n" + site)) {
                return;
            }

            YAHOO.util.Connect.asyncRequest(
                "POST",
                COMMID.baseDir + "/sites/delete",
                {
                    success : function (responseObj) {
                        try {
                            var r = YAHOO.lang.JSON.parse(responseObj.responseText);
                            if (r.code == 200) {
                                alert(COMMID.lang["Your relationship with the following site has been deleted:"] + "\n\n" + site);
                                this.initTable();
                            }
                        } catch (e) {
                            alert(COMMID.lang["ERROR. The server returned:"] + "\n\n" + responseObj.responseText);
                        }
                    },
                    failure : COMMID.utils.asyncFailed,
                    scope   : this
                },
                "id=" + oRecord.getData("id")
            );
        }
    };
}();

COMMID.historyList = function() {
    var myDataSource;
    var myDataSourceURL;
    var myDataTable;
    var myPaginator;
    var myTableConfig;

    var buildQueryString = function (state,dt) { 
        return "startIndex=" + state.pagination.recordOffset + 
               "&results=" + state.pagination.rowsPerPage;
    }; 

    var formatResultsColumn = function(elCell, oRecord, oColumn, oData) {
        switch(oRecord.getData("result")) {
            case 0:
                elCell.innerHTML = COMMID.lang["Denied"];
                break;
            case 1:
                elCell.innerHTML = COMMID.lang["Authorized"];
                break;
        }
    };

    var myColumnDefs = [
        {key: "date", label: COMMID.lang["Date"]},
        {key: "site", label: COMMID.lang["Site"]},
        {key: "ip", label: COMMID.lang["IP"]},
        {key: "result", label: COMMID.lang["Result"], formatter: formatResultsColumn}
    ];

    return {
        init: function() {
            myDataSourceURL = COMMID.baseDir + "/history/list?";
            myDataSource = new YAHOO.util.DataSource(myDataSourceURL);
            myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
            myDataSource.responseSchema = {
                resultsList: "records",
                fields: ["id", "date", "site", "ip", "result"],
                metaFields : {
                    totalRecords: 'totalRecords'
                }
            };

            myPaginator = new YAHOO.widget.Paginator({
                alwaysVisible      : false,
                containers         : ['paging'],
                pageLinks          : 5,
                rowsPerPage        : 15,
                rowsPerPageOptions : [15,30,60],
                template           : "<strong>{CurrentPageReport}</strong> {PreviousPageLink} {PageLinks} {NextPageLink} {RowsPerPageDropdown}",
                pageReportTemplate      : "({currentPage} " + COMMID.lang["of"] + " {totalPages})",
                nextPageLinkLabel       : COMMID.lang["next"] + "&nbsp;&gt;",
                previousPageLinkLabel   : "&lt;&nbsp;" + COMMID.lang["prev"]
            });

            myTableConfig = {
                initialRequest         : 'startIndex=0&results=15',
                generateRequest        : buildQueryString,
                paginator              : myPaginator
            };
            COMMID.utils.addDatatableTranslations(myTableConfig);

            myDataTable = new YAHOO.widget.DataTable("dt", myColumnDefs, myDataSource, myTableConfig);
            myDataTable.subscribe('renderEvent', this.showClearHistoryBtn, this, true);
        },

        showClearHistoryBtn: function() {
            if (myDataTable.getRecordSet().getLength() > 0) {
                $("clearHistory").style.display = "block";
            } else {
                $("clearHistory").style.display = "none";
            }
        },

        clearEntries: function() {
            if (!confirm(COMMID.lang["Are you sure you wish to delete all the History Log?"])) {
                return;
            }

            YAHOO.util.Connect.asyncRequest(
                "POST",
                "history/clear",
                {
                    success : function(responseObj) {
                        try {
                            var r = YAHOO.lang.JSON.parse(responseObj.responseText);
                            if (r.code == 200) {
                                alert(COMMID.lang["The history log has been cleared"]);
                                this.init();
                            }
                        } catch (e) {
                            alert(COMMID.lang["ERROR. The server returned:"] + "\n\n" + responseObj.responseText);
                        }
                    },
                    failure : COMMID.utils.asyncFailed,
                    scope   : this
                }
            );
        }
    };
}();


COMMID.usersList = function() {
    var myDataSource;
    var myDataSourceURL;
    var myDataTable;
    var myPaginator;
    var myTableConfig;

    var buildQueryString = function (state,dt) { 
        var request = "";
        if (state.sortedBy) {
            request += "sort=" + state.sortedBy.key + "&dir="
              + (state.sortedBy.dir === YAHOO.widget.DataTable.CLASS_ASC? 0 : 1) + "&";
        }

        request += "startIndex=" + state.pagination.recordOffset
                + "&results=" + state.pagination.rowsPerPage;

        return request;
    }; 

    var formatOperationsColumn = function(elCell, oRecord, oColumn, oData) {
        var links = new Array();

        links.push("<a href=\"" + COMMID.baseDir + "/users/profile?userid=" + oRecord.getData("id") + "\">"
                  + COMMID.lang["profile"] +    "</a>");

        if (COMMID.userRole == "admin" && COMMID.userId != oRecord.getData("id")) {
            links.push("<a href=\"javascript:void(0)\" onclick=\"COMMID.usersList.deleteUser('"+oRecord.getId()+"')\">" + COMMID.lang["delete"] + "</a>");
        }

        if (links.length > 0) {
            elCell.innerHTML = links.join("&nbsp;|&nbsp;");
        } else {
            elCell.innerHTML = "";
        }
    };

    var formatNameColumn = function(elCell, oRecord, oColumn, oData) {
        if (oRecord.getData("role") == "admin") {
            elCell.innerHTML = "<b>" + oRecord.getData("name") + "</b>";
        } else {
            elCell.innerHTML = oRecord.getData("name");
        }
    };

    var formatStatusColumn = function(elCell, oRecord, oColumn, oData) {
        if (oRecord.getData("role") == "admin") {
            elCell.innerHTML = "<b>" + oRecord.getData("status") + "</b>";
        } else {
            elCell.innerHTML = oRecord.getData("status");
        }
    };

    var handleDataReturnPayload = function(oRequest, oResponse, oPayload) { 
        oPayload.totalRecords = oResponse.meta.totalRecords; 
        $("totalUsers").innerHTML = oResponse.meta.totalRecords;
        $("totalUnconfirmedUsers").innerHTML = oResponse.meta.totalUnconfirmedUsers;
        $("totalConfirmedUsers").innerHTML = oResponse.meta.totalRecords - oResponse.meta.totalUnconfirmedUsers;
        return oPayload; 
    };

    var deleteUserCompleted = function(oRecord, responseObj) {
        alert(responseObj.responseText);
        myDataTable.deleteRow(oRecord);
    };
    
    var deleteUnconfirmedCompleted = function(responseObj) {
        this.init("all");
    };

    return {
        init: function(filter) {
            myDataSourceURL = COMMID.baseDir + "/users/userslist?filter=" + filter + "&";
            myDataSource = new YAHOO.util.DataSource(myDataSourceURL);
            myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
            myDataSource.responseSchema = {
                resultsList: "records",
                fields: ["id", "name", "registration", "status", "role"],
                metaFields : {
                    totalRecords            : 'totalRecords',
                    totalUnconfirmedUsers   : 'totalUnconfirmedUsers'
                }
            };

            myPaginator = new YAHOO.widget.Paginator({
                alwaysVisible      : false,
                containers         : ['paging'],
                pageLinks          : 5,
                rowsPerPage        : 15,
                rowsPerPageOptions : [15,30,60],
                template           : "<strong>{CurrentPageReport}</strong> {PreviousPageLink} {PageLinks} {NextPageLink} {RowsPerPageDropdown}",
                pageReportTemplate      : "({currentPage} " + COMMID.lang["of"] + " {totalPages})",
                nextPageLinkLabel       : COMMID.lang["next"] + "&nbsp;&gt;",
                previousPageLinkLabel   : "&lt;&nbsp;" + COMMID.lang["prev"]
            });

            myTableConfig = {
                initialRequest         : 'startIndex=0&results=15',
                generateRequest        : buildQueryString,
                dynamicData            : true,
                paginator              : myPaginator
            };
            COMMID.utils.addDatatableTranslations(myTableConfig);

            var myColumnDefs = [
                {key: "name", label: COMMID.lang["Name"], sortable: true, formatter: formatNameColumn},
                {key: "registration", label: COMMID.lang["Registration"], formatter: 'date', sortable: true},
                {key: "status", label: COMMID.lang["Status"], sortable: true, hidden: (filter != 'all'), formatter: formatStatusColumn},
                {key: "operations", label: "", formatter: formatOperationsColumn}
            ];

            myDataTable = new YAHOO.widget.DataTable("dt", myColumnDefs, myDataSource, myTableConfig);
            myDataTable.handleDataReturnPayload = handleDataReturnPayload;

            switch (filter) {
                case 'all': 
                    $("links_topright_all").className = "disabledLink";
                    $("links_topright_confirmed").className = "enabledLink";
                    $("links_topright_unconfirmed").className = "enabledLink";
                    $("deleteUnconfirmedSpan").style.display = "none";
                    break;
                case 'confirmed':
                    $("links_topright_all").className = "enabledLink";
                    $("links_topright_confirmed").className = "disabledLink";
                    $("links_topright_unconfirmed").className = "enabledLink";
                    $("deleteUnconfirmedSpan").style.display = "none";
                    break;
                case 'unconfirmed':
                    $("links_topright_all").className = "enabledLink";
                    $("links_topright_confirmed").className = "enabledLink";
                    $("links_topright_unconfirmed").className = "disabledLink";
                    $("deleteUnconfirmedSpan").style.display = "inline";
                    break;
            }
        },

        deleteUser: function(recordId) {
            var oRecord = myDataTable.getRecord(recordId);
            if (confirm(COMMID.lang["Are you sure you wish to delete the user"] + " " + oRecord.getData("name") + "?")) {
                var transaction = YAHOO.util.Connect.asyncRequest(
                    "POST",
                    COMMID.baseDir + "/users/manageusers/delete",
                    {
                        success: function (responseObj) {deleteUserCompleted(oRecord, responseObj);},
                        failure: function() {alert(COMMID.lang['operation failed'])}
                    },
                    "userid=" + oRecord.getData("id"));
            }
        },

        deleteUnconfirmed: function() {
            if (!confirm(COMMID.lang["Are you sure you wish to delete all the unconfirmed accounts?"])) {
                return;
            }

            YAHOO.util.Connect.asyncRequest(
                "POST",
                COMMID.baseDir + "/users/manageusers/deleteunconfirmed",
                {
                    success : deleteUnconfirmedCompleted,
                    failure : function() {alert(COMMID.lang['operation failed'])},
                    scope   : this
                },
                null);
        }
    };
}();


COMMID.editAccountInfo = function() {

    return {
        save: function() {
            YAHOO.util.Connect.setForm("accountInfoForm", true);
            YAHOO.util.Connect.asyncRequest(
                'POST',
                'profilegeneral/saveaccountinfo?userid=' + COMMID.targetUserId,
                {
                    upload: function (responseObj) {COMMID.utils.replaceContent(responseObj, "accountInfo")}
                },
                null
            );
        },

        cancel: function() {
            var transaction = YAHOO.util.Connect.asyncRequest(
                'GET',
                'profilegeneral/accountinfo?userid=' + COMMID.targetUserId,
                {
                    success: function (responseObj) {COMMID.utils.replaceContent(responseObj, "accountInfo")},
                    failure: COMMID.utils.asyncFailed
                }
            );
        }
    };
}();

COMMID.stats = function() {
    return {
        loadReport: function(report, div, params) {
            if (params) {
                params = "?" + params;
            } else {
                params = "";
            }

            YAHOO.util.Connect.asyncRequest(
                "GET",
                "stats/" + report + params,
                {
                    success: function (responseObj) {
                        COMMID.utils.replaceContent(responseObj, div)
                    },
                    failure: COMMID.utils.asyncFailed
                });
        }
    }
}();
