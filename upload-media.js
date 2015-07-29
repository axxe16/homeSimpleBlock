jQuery(document).ready(function($) {

    $(document).on("click", ".upload_image_button", function() {

        jQuery.data(document.body, 'prevElement', $(this).prev());

        window.send_to_editor = function(html) {
            
            var attachment_id = $('img',html).attr('class');
			attachment_id = attachment_id.replace(/[^0-9]+/ig,"");
            
            var inputText = jQuery.data(document.body, 'prevElement');

            if(inputText != undefined && inputText != '')
            {
                inputText.val(attachment_id);
            }

            tb_remove();
        };
        tb_show('', 'media-upload.php?type=image&TB_iframe=true');
        return false;
    });
    
    //inserisco url nel campo per pagine link   
	$(document).on("change", "#selectContent", function() {
		var elem = jQuery('option:selected', this).val();
		jQuery(this).next().attr('value',elem);
	});
});