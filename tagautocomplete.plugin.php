<?php
class TagAutoComplete extends Plugin
{
	/**
	 * Add the required javascript to the publish page
	 * @param Theme $theme The admin theme instance
	 **/
	public function action_admin_header($theme)
	{
		if( $theme->page == 'publish' ) {
			Stack::add( 'admin_header_javascript', $this->get_url() . "/multicomplete.js", 'multicomplete', array( 'jquery.ui' ) );
			$url = URL::get( 'ajax', array( 'context' => 'auto_tags' ) );
			$url = '"' . $url . '"';
			$script = <<< HEADER_JS
$(document).ready(function(){
	$("#tags").multicomplete({source: $url,
		minLength: 1
	});
});
HEADER_JS;
			Stack::add( 'admin_header_javascript',  $script, 'tags_auto', array('jquery', 'multicomplete') );
//			Stack::add( 'admin_stylesheet', array( $this->get_url() . "/jquery.ui.autocomplete.css", 'screen' ), 'autocomplete', array( 'jqueryui' ) );
		}
	}

	/**
	 * Respond to Javascript callbacks
	 * The name of this method is action_ajax_ followed by what you passed to the context parameter above.
	 */
	public function action_ajax_auto_tags( $handler )
	{
		$selected = array();
		if( isset( $handler->handler_vars['selected'] ) ) {
			$selected = $handler->handler_vars['selected'];
		}
		if( isset( $handler->handler_vars['term'] ) && MultiByte::strlen( $handler->handler_vars['term'] ) ) {
			$tags = Tags::vocabulary()->get_search( $handler->handler_vars['term'], 'term_display ASC' );
		}
		else {
			$tags = Tags::vocabulary()->get_tree();
		}

		$resp = array();
		foreach ( $tags as $tag ) {
			$resp[] = $tag->term_display;
		}
		$resp = array_diff($resp, $selected );
		// Send the response
		echo json_encode( $resp );
	}

}
?>
