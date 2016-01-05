(function(window, define, requirejs, undefined) {
    
    requirejs.config({
        paths: {
            'ace': '../vendor/ace-editor'
        }
    });
    
    define([
        'ace/ace'
    ], function(ace) {
        var editor = ace.edit('wiki-editor');
        editor.setTheme('ace/theme/monokai');
        editor.getSession().setMode('ace/mode/markdown');
        editor.on('change', function(e) {
            var contents = editor.getSession().getDocument().getValue();
            var inputField = document.getElementById('wiki-content');
            
            inputField.value = contents;
        });
    });
    
})(this, this.define, this.requirejs);