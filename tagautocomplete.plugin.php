<?php
namespace Habari;

class TagAutoComplete extends Plugin
{

	/**
	 *  Register our javascript with Habari
	 */
	public function action_register_stackitems()
	{
		StackItem::register( 'multicomplete', Site::get_url( 'vendor' ) . '/multicomplete.js' )->add_dependency( 'jquery.ui' );
		$url = '"' . URL::get( 'ajax', array( 'context' => 'auto_tags' ) ) . '"';
		$script = <<< HEADER_JS
$(document).ready(function(){
	$("#tags").multicomplete({
		source: $url,
		minLength: 2,
		autoFocus: true,
	});
});
HEADER_JS;
		StackItem::register( 'tags_auto', $script )->add_dependency( 'multicomplete' );
	}

	/**
	 * Actually add the required javascript to the publish page
	 * @param Theme $theme The admin theme instance
	 **/
	public function action_admin_header($theme)
	{
		if( $theme->page == 'publish' ) {
			Stack::add( 'admin_header_javascript', 'multicomplete' );
			Stack::add( 'admin_header_javascript', 'tags_auto' );
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
			$selected = Utils::single_array( $handler->handler_vars['selected'] );
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
			$resp[] = MultiByte::strpos( $tag->term_display, ',' ) === false ? $tag->term_display : $tag->tag_text_searchable;
		}

		if( count( $selected ) ) {
			$resp = array_diff($resp, $selected );
		}
		// Send the response
//		$ar = new AjaxResponse();
//		$ar->data = $resp;
//		$ar->out();
		echo json_encode( $resp );
	}

	public function action_form_publish( $form, $post, $context )
	{
		$old = $form->get_control( 'tags' );
		$tags = $old->value;
		$new = FormControlText::create('tags', null, array( 'style' => 'width:90%;', 'class' => 'check-change', 'id' => 'tags', 'tabindex' => $old->properties['tabindex'] ) );
		$new->set_value( $tags );
		$old->container->append( $new );
		$old->container->replace( $old, $new );
	}

}
?>
