
  (function() {
      /* Register the buttons */
      tinymce.create('tinymce.plugins.ays_gpg_button_mce', {
          init : function(ed, url) {
  		   /**
  		   * Adds HTML tag to selected content
  		   */
  			ed.addButton( 'ays_gpg_button_mce', {
  				title : 'Add Gallery',
  				image :  url + '/admin/images/gall_icon.png',
  				cmd: 'ays_gpg_button_cmd'
  			});

  			ed.addCommand( 'ays_gpg_button_cmd', function() {
  				ed.windowManager.open(
  				{
  					title : 'Gallery Photo Gallery',
  					file : ajaxurl + '?action=gen_ays_gpg_shortcode',
  					width : 500,
  					height : 300,
  					inline : 1
  				},
  				{
  					plugin_url : url
  				});

  		   });
  		},
  		createControl : function(n, cm) {
  		   return null;
  		},
  	});
      /* Start the buttons */
      tinymce.PluginManager.add( 'ays_gpg_button_mce', tinymce.plugins.ays_gpg_button_mce );
  })();
