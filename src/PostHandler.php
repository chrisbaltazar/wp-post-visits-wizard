<?php


namespace PostVisitsWizard;


class PostHandler {

	/**
	 * @param \WP_Post $post
	 *
	 * @return bool
	 */
	private function should_update( \WP_Post $post ): bool {
		$settings = $this->get_settings();

		if ( ! in_array( $post->post_type, $settings['types'] ) || $post->post_status !== 'publish' ) {
			return false;
		}



		$post_tags = wp_get_post_tags( $post->ID, [ 'fields' => 'slugs' ] );
		if ( ! empty( $settings['tags'] ) && is_array( $post_tags ) ) {
			$finder = array_intersect( $settings['tags'], $post_tags );
			if ( empty( $finder ) ) {
				return false;
			}
		}

		return true;
	}

	private function discard_category(int $post_id){
		$post_categories = wp_get_post_categories( $post_id, [ 'fields' => 'slugs' ] );
		if ( ! empty( $settings['categories'] ) && is_array( $post_categories ) ) {
			$finder = array_intersect( $settings['categories'], $post_categories );
			if ( empty( $finder ) ) {
				return true;
			}
		}
	}
}