var postinpost = (function($){
	
	var 	ajax_url = '/wp-admin/admin-ajax.php', 
	$ = jQuery;
	
	var 	browse_dialog,
		tabs,
		message,
		entries,
		insert_btn,
		form,
		hidden_post_type;

	var	insertEnabled = false,
		currentEditor = null;


	function loadPostTypes(post_type, callback)
	{
		jQuery.post(	ajax_url, 
					{ action: 'postinpost_load_type', post_type: post_type } , 
					callback, 
					'json');		
	}
	
	function clearEntries()
	{
		entries.html('').removeClass('error');
	}
	
	function enableInsertBtn(enabled)
	{
		enabled ? insert_btn.addClass('disabled')	: insert_btn.removeClass('disabled');
	
		insertEnabled = enabled;
	}
	
	function setMessage(themessage, error)
	{
		if (themessage)
		{
			message.show().html(themessage);	
			error ? message.addClass('error') : message.removeClass('error');
		}
		else
		{
			message.hide();			
		}
		
	}
	
	function init()
	{
		browse_dialog = $('#postinpost-browse');
		tabs = browse_dialog.find('.tabs a');
		message = browse_dialog.find('.postinpost-dialog-message');
		entries = browse_dialog.find('.postinpost-dialog-entries');
		insert_btn  = browse_dialog.find('#postinpost-insert');
		form = browse_dialog.find('form');
		hidden_post_type = browse_dialog.find('#post_type');
		
		// tabs handler
		tabs.click(function(){
			var 	$this = $(this),
				post_type = $this.attr('data-post-type'),
				post_label = $this.attr('data-post-label');
			
			tabs.not(this).removeClass('current');
			$this.addClass('current');
			
			hidden_post_type.val(post_type);
			
			setMessage('Loading '+post_label+'... ');
			
			clearEntries();
			
			enableInsertBtn(false);
			
			loadPostTypes(post_type,function(data){
				
				setMessage(false);
				
				if (!data.success)
				{
					setMessage(data.error, true);
				}
				else
				{
					entries.append(data.output);	
					enableInsertBtn(true);
					
				}
										
				
			});
			
		});
		
		// insert button handler
		insert_btn.click(function(e){
			
			e.preventDefault();
			
			if (!insertEnabled)
				return;
			
			var selection = entries.find('input[type="checkbox"]:checked');
			
			if (selection.length == 0)
			{
				alert("You have not selected any entry to insert.");
				return;				
			}
			
			enableInsertBtn(false);
								
			var ids = [];
			
			selection.each(function(){
				ids.push(this.value);						
			});
			
			var insert_as = $('input[name="insert_as"]:checked').val();
			var insert_length = $('input[name="insert_length"]:checked').val();
			var post_type = hidden_post_type.val();
			
			
			
			jQuery.post(	ajax_url, 
						{ action: 'postinpost_insert', ids: ids.join(','), insert_as:insert_as, insert_length:insert_length, post_type:post_type } , 
						function(data){
							if (!data.success)
							{
								setMessage(data.error, true);
								enableInsertBtn(true);
							}
							else
							{
								insertContent(data.output)
							}
						}, 
						'json');
			
		});

		
	}
	
	function initQuickTag()
	{
		if ( typeof(QTags) == 'undefined' )
			return;
		
		QTags.addButton( 'postinpost', 'Post In Post', function() {
			pub.showDialog(false);
			
		} );
			
	}

	function openDialog()
	{
	
		if (!currentEditor)
		{
		
			if ( ! browse_dialog.data('wpdialog') ) {
				browse_dialog.wpdialog({
					  width : 600,
					  height:500,
					modal: true,
					dialogClass: 'wp-dialog',
					zIndex: 300000
				});
			}
			
			browse_dialog.wpdialog('open');
			
		}	
		else
		{
			currentEditor.windowManager.open({
							   id : 'postinpost-browse',
							   wpDialog : true,
							   width : 600,
							   height:500
							}, {});		
		}	
	}
	
	function insertContent(content)
	{
		if (!currentEditor)
		{
			QTags.insertContent(content);
		}
		else
		{
			currentEditor.execCommand('mceInsertContent', 0, content);	
			currentEditor.windowManager.close();
		}				
	}

	
	$(init);

	$(initQuickTag);
	
	var pub = {

		showDialog:function(mceEditor){
		
				clearEntries();
				
				currentEditor = mceEditor;
				
				openDialog();
				
				// auto load the first item.
				tabs.first().click();
				
				
		}


		
	};
	
return pub;

	
	
})(jQuery);