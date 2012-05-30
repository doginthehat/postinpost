// Reference Docs : http://www.tinymce.com/wiki.php/API3:tinymce.api.3.x

(function(postinpost) {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('postinpost');
	
	
	tinymce.create('tinymce.plugins.postinpost', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {

			// Register the command so that it can be invoked from the button
			ed.addCommand('mce_postinpost', function() {
				

				postinpost.showDialog(ed);


			});

			// Register example button
			ed.addButton('postinpost', {
				title : 'postinpost.desc',
				cmd : 'mce_postinpost'
			});
			
			//setTimeout(function(){
			//	
			//	ed.execCommand('mce_postinpost');
			//	
			//}, 200);
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
					longname	: 'Post In Post',
					author	: 'Dog In The Hat',
					authorurl	: 'http://doginthehat.com.au/',
					infourl	: 'http://doginthehat.com.au/',
					version	: '1.0'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('postinpost', tinymce.plugins.postinpost);
})(postinpost);


