Ext.onReady(function() {
    Ext.ComponentMgr.onAvailable("modx-panel-chunk", function(){
        this.addListener('afterrender',function(){
            let formCmp=Ext.getCmp('modx-chunk-form');
            let cols=Ext.getCmp(formCmp.items.keys[1]);
            let col=Ext.getCmp(cols.items.keys[0]);
            let items=[{
                layout: 'column'
                ,defaults: {
                    layout: 'form'
                    ,labelAlign: 'top'
                    ,anchor: '100%'
                    ,border: false
                    ,labelSeparator: ''
                }
                ,items:[{
                    columnWidth: .3,
                    items:[
                        new Ext.form.Checkbox({
                            xtype: 'xcheckbox'
                            ,boxLabel: _('nodejsenv.build_project')
                            ,hideLabel: true
                            ,name: 'nodejsenv_build'
                            ,id: 'modx-chunk-build-project'
                            ,checked: false
                            ,inputValue: 1
                        }),
                    ]
                },{
                    columnWidth: .7,
                    items:[]
                }]
            }];
            if(NodeJSEnv.project.is_git){
                items[0].items[0].items.push(new Ext.form.Checkbox({
                    xtype: 'xcheckbox'
                    ,boxLabel: _('nodejsenv.git_commit')
                    ,hideLabel: true
                    ,name: 'nodejsenv_commit'
                    ,id: 'modx-chunk-git-commit'
                    ,checked: false
                    ,inputValue: 1
                }),new Ext.form.Checkbox({
                    xtype: 'xcheckbox'
                    ,boxLabel: _('nodejsenv.git_push')
                    ,hideLabel: true
                    ,name: 'nodejsenv_push'
                    ,id: 'modx-chunk-git-push'
                    ,checked: false
                    ,inputValue: 1
                }));
                items[0].items[1].items.push({
                    xtype: 'textarea'
                    ,fieldLabel: _('nodejsenv.git_commit_message')
                    ,name: 'nodejsenv_commit_message'
                    ,id: 'modx-chunk-git-commit-message'
                    ,anchor: '100%'
                    ,maxLength: Infinity
                });
            }
            
            
            let fieldset=new Ext.form.FieldSet({
                xtype:'fieldset',
                title: _('nodejsenv.project_control'),
                collapsible: false,
                autoHeight:true,
                items : items
            });
            
            
            col.add(fieldset);
        },this);
        this.addListener('save',function(r){
            this.runshell=false;
            let runshell=false;
            if(r.values['nodejsenv_build']||(NodeJSEnv.project.is_git&&r.values['nodejsenv_commit']&&r.values['nodejsenv_commit_message'].length>0))this.runshell=true;
            //console.log(runshell,r.values,r.values['nodejsenv_build']);
        },this);
        this.addListener('success',function(r){
            if(this.runshell){
                let topic = '/nodejsenv/';
                let register = 'mgr';
                this.console = MODx.load({
                   xtype: 'modx-console'
                   ,register: register
                   ,topic: topic
                   ,show_filename: 0
                   ,listeners: {
                     'shutdown': {fn:function() {
                         /* выполнить код здесь, когда вы закроете консоль */
                     },scope:this}
                   }
                });
                
                this.console.show(Ext.getBody());
                this.console.provider.addListener('data',function(provider,e){
                    this.console.fireEvent('complete');
                },this);
            }
        },this);
    });
});