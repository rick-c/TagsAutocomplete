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
			Stack::add( 'admin_header_javascript', Site::get_url( 'vendor' ) . "/multicomplete.js", 'multicomplete', array( 'jquery.ui' ) );
			$url = '"' . URL::get( 'ajax', array( 'context' => 'auto_tags' ) ) . '"';
			$script = <<< HEADER_JS
$(document).ready(function(){
	$("#tags").multicomplete({source: $url,
		minLength: 1,
		autoFocus: true,
	});
});
HEADER_JS;
			Stack::add( 'admin_header_javascript',  $script, 'tags_auto', array( 'multicomplete' ) );
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
			$search = $handler->handler_vars['term'] . '%';
			$tags = new Terms( DB::get_results( "SELECT * FROM {terms} WHERE vocabulary_id = :vid and LOWER(term_display) LIKE LOWER(:crit) ORDER BY term_display ASC", array( 'vid' => Tags::vocabulary()->id, 'crit' => $search ), 'Term' ) );
		}
		else {
			$tags = Tags::vocabulary()->get_tree( 'term_display ASC' );
		}

		$resp = array();
		foreach ( $tags as $tag ) {
			$resp[] = array(
			    'label' => $tag->term_display,
			    'value' => MultiByte::strpos( $tag->term_display, ',' ) === false ? $tag->term_display : $tag->tag_text_searchable
			);
		}
		$resp = array_diff($resp, $selected );
		// Send the response
		echo json_encode( $resp );
	}

}
?>
