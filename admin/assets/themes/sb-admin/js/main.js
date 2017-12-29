$(function() {

    var $body = $('body');
    if( $body.hasClass('dialog')){
        var queries = Fragment.parseQuery(location.hash.length > 1 ? location.hash : location.search);
        window.nativeClose = window.close;
        window.close = function(){
            if(typeof queries._modalIns != 'undefined'){            
                try{
                window.top.hc.ui.openModal.instances[ queries._modalIns ].modal('hide');
                }catch(exp){}
            }
        };

        $('.btn-window-close').on('click', function(evt){
            evt.preventDefault();

            window.close();
        });
    }

	if($.noty)
		$.noty.defaults.theme = 'bootstrapTheme';

    if($.fn.metisMenu)
        $('#side-menu').metisMenu();
    
    
});
