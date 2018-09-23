let Encore = require('@symfony/webpack-encore');

Encore
    // the project directory where compiled assets will be stored
    .setOutputPath('public/build/')

    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    .enableSourceMaps(!Encore.isProduction())

    // uncomment to create hashed filenames (e.g. app.abc123.css)
    // .enableVersioning(Encore.isProduction())

    // javascript
    .createSharedEntry('js/vendor', [
        './node_modules/jquery/dist/jquery.min.js',
        './node_modules/jquery-ui-dist/jquery-ui.min.js',
        './node_modules/jquery.cookie/jquery.cookie.js',
        './node_modules/bootstrap3/dist/js/bootstrap.js',
        './node_modules/bootstrap-toggle/js/bootstrap-toggle.min.js',
        './node_modules/bootstrap-datepicker/dist/js/bootstrap-datepicker.js',
        './node_modules/bootstrap-datepicker/dist/locales/bootstrap-datepicker.fi.min.js',
        './node_modules/bootstrap-datepicker/dist/locales/bootstrap-datepicker.sv.min.js',
        './node_modules/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
        './node_modules/bootstrap-contextmenu/bootstrap-contextmenu.js',
    ])
    .addEntry('js/comments', './public/bundles/foscomment/js/comments.js')
    .addEntry('js/app', './assets/js/app.js')
    // .addEntry('js/fcm-service-worker', './assets/js/fcm-service-worker.js')

    //.addEntry('js/bs3-context-menu', './assets/js/bs3-context-menu.js')
    //.addEntry('js/bs3-modal-dialog', './assets/js/bs3-modal-dialog.js')
    //.addEntry('js/bs3-offcanvas-nav', './assets/js/bs3-offcanvas-nav.js')
    //.addEntry('js/bs3-toggle-caret', './assets/js/bs3-toggle-caret.js')
    //.addEntry('js/table-searchable', './assets/js/table-searchable.js')
    //.addEntry('js/table-sortable', './assets/js/table-sortable.js')

    // images
    .addEntry('favicon', './assets/images/favicon.ico')
    .addEntry('juoksurata', './assets/images/juoksurata.jpg')
    .addEntry('gubbar-gs', './assets/images/gubbar-gs.png')
    .addEntry('fma-jurgens-gs', './assets/images/fma-jurgens-gs.png')
    .addEntry('bg-primary', './assets/images/bg-primary4.jpg')
    .addEntry('i<3staf1-nav', './assets/images/i<3staf1-nav.png')
    .addEntry('i<3staf1-nav-2', './assets/images/i<3staf1-nav-2.png')
    .addEntry('confirm', './assets/images/confirm.png')
    .addEntry('icon-192x192', './assets/images/icon-192x192.png')

    // json
    .addEntry('fcm/manifest.sv', './assets/json/manifest.sv.json')
    .addEntry('fcm/manifest.fi', './assets/json/manifest.fi.json')
    .addEntry('fcm/manifest.en', './assets/json/manifest.en.json')

    // stylesheets
    .addStyleEntry('css/app', './assets/css/app.scss')
    .addStyleEntry('css/bs-datepicker', './node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css')
    .addStyleEntry('css/font-awesome', './node_modules/font-awesome/css/font-awesome.min.css')
    .addStyleEntry('css/bootstrap', './node_modules/bootstrap3/dist/css/bootstrap.min.css')
    .addStyleEntry('css/bootstrap-datepicker', './node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css')
    .addStyleEntry('css/bootstrap-datetimepicker', './node_modules/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css')

    .enableSassLoader()
    .enableLessLoader()

    // uncomment for legacy applications that require $/jQuery as a global variable
    .autoProvidejQuery()

    // show OS notifications when builds finish/fail
    .enableBuildNotifications()

    .autoProvideVariables({
        $: 'jquery',
        jQuery: 'jquery',
        'window.jQuery': 'jquery',
    })

;
const config =  Encore.getWebpackConfig();

config.resolve.extensions.push('json');

module.exports = [config];
