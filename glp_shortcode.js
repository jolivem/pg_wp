
  (function() {
      /* Register the buttons */
      tinymce.create('tinymce.plugins.glp_button_mce', {
          init : function(ed, url) {
  		   /**
  		   * Adds HTML tag to selected content
  		   */
  			ed.addButton( 'glp_button_mce', {
  				title : 'Add Gallery',
  				image :  url + '/admin/images/gall_icon.png',
  				cmd: 'glp_button_cmd'
  			});

  			ed.addCommand( 'glp_button_cmd', function() {
  				ed.windowManager.open(
  				{
  					title : 'Gallery Photo Gallery',
  					file : ajaxurl + '?action=gen_glp_shortcode',
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
      tinymce.PluginManager.add( 'glp_button_mce', tinymce.plugins.glp_button_mce );
  })();
