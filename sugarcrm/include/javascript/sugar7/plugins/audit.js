(function (app) {
    app.events.on("app:init", function () {
        app.plugins.register('audit', ['view'], {
            /**
             * @param component
             * @param plugin
             */
            onAttach: function (component, plugin) {
                this.options.layout.on("audit:close_changelog", this.closeChangelog, this);
                this.options.context.on('button:audit_button:click', this.auditClicked, this);

                this.on('render', this.renderChangelog, this);
            },

            /**
             * an audit button has been clicked
             */
            auditClicked: function () {
                this.showChangelog();
            },

            /**
             * call this on render event
             */
            renderChangelog: function () {
                var auditView = this.layout.getComponent('audit');

                if (!auditView) {
                    return;
                }
                auditView.setElement(this.$('[data-plugin=audit][cid=' + this.cid + ']'));
                auditView.loadData();
                auditView.render();
            },

            /**
             * Show changelog panel
             */
            showChangelog: function () {
                var context,
                    auditView;

                // we need to place this component in the layout, but not use the default
                // element append that addComponent gives us
                auditView = this.layout.getComponent('audit');

                if (!auditView) {
                    context = this.context.getChildContext({
                        module: 'Audit'
                    });
                    context.prepare();
                    auditView = app.view.createView({
                        context: context,
                        name: 'audit',
                        module: 'Audit',
                        layout: this.layout
                    });
                    this.layout._components.push(auditView);
                }

                this.renderChangelog();
            },

            /**
             * Close the changelog panel
             */
            closeChangelog: function () {
                var component = this.layout.getComponent('audit');
                if (component) {
                    this.layout.removeComponent(component);
                    component.dispose();
                }
            },

            /**
             * we're going away, clean up our event handlers.
             */
            onDetach: function (component, plugin) {
                this.options.layout.off("audit:close_changelog", this.closeChangelog, this);
                this.options.context.off('button:audit_button:click', this.auditClicked, this);
            }
        });
    });
})(SUGAR.App);
