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
//			Stack::add( 'admin_header_javascript', Site::get_url( 'vendor' ) . "/multicomplete.js", 'multicomplete', 'jquery.ui' );
			Stack::add( 'admin_header_javascript', $this->get_url() . "/multicomplete.js", 'multicomplete', 'jquery.ui' );
			$url = URL::get( 'ajax', array( 'context' => 'auto_tags' ) );
			$url = '"' . $url . '"';
			$script = <<< HEADER_JS
$(document).ready(function(){
	$("#tags").multicomplete({source: $url,
		minLength: 0
	});
});
HEADER_JS;
			Stack::add( 'admin_header_javascript',  $script, 'tags_auto', array('jquery', 'multicomplete') );
		}
	}

	/**
	 * Respond to Javascript callbacks
	 * The name of this method is action_ajax_ followed by what you passed to the context parameter above.
	 */
	public function action_ajax_auto_tags( $handler )
	{
		// Get the data that was sent
//		$response = $handler->handler_vars[ 'q' ];
		// Wipe anything else that's in the buffer
		$selected = array();
		if( isset( $handler->handler_vars['selected'] ) ) {
			$selected = $handler->handler_vars['selected'];
		}
		if( isset($handler->handler_vars['term'] ) ) {
			$tags = Tags::vocabulary()->get_search( $handler->handler_vars['term'], 'term_display ASC' );
		}
		else {
			$tags = Tags::vocabulary()->get_tree();
		}

		$resp = array();
		foreach ( $tags as $tag ) {

//			$final_response[] = array(
//				'id' => $tag->id,
//				'name' => $tag->term_display,
//			);
			$resp[] = $tag->term_display;
		}
		$resp = array_diff($resp, $selected );
		// Send the response
		echo json_encode( $resp );
	}

}
?>
