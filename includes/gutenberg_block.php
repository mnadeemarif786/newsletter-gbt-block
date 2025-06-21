<?php if (!defined('ABSPATH')) exit;
	function sam_register_newsletter_block() {
		$block_js_path = plugin_dir_path(__FILE__) . '../build/index.js';
    	$block_css_path = plugin_dir_path(__FILE__) . '../build/index.css';
		
    	if (file_exists($block_js_path)) {
			wp_register_script(
				'sam-newsletter-block',
				plugins_url('../build/index.js', __FILE__),
				['wp-blocks', 'wp-element', 'wp-editor', 'wp-components'],
				filemtime($block_js_path),
				true
			);
		}
		if (file_exists($block_css_path)) {
			wp_register_style(
		        'sam-newsletter-block-editor',
		        plugins_url('../build/index.css', __FILE__),
		        [],
		        filemtime($block_css_path)
		    );
		}
	    register_block_type('sam/newsletter-block', [
	        'editor_script' => 'sam-newsletter-block',
	        'editor_style'  => 'sam-newsletter-block-editor',
	        'render_callback' => 'sam_render_newsletter_block',
	    ]);
	}

	add_action('init', 'sam_register_newsletter_block');

	function sam_render_newsletter_block() {
	    ob_start();
	    ?>
	    <div class="newsletter-form">
	        <input type="text" name="user_name" placeholder="Your Name" required />
	        <input type="email" name="user_email" placeholder="Your Email" required />
	        <button type="button" class="submit-btn">Subscribe</button>
	        <div class="response-msg"></div>
	    </div>
	    <?php
	    return ob_get_clean();
	}
?>