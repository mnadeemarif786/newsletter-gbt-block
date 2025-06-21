import { registerBlockType } from '@wordpress/blocks';
import './editor.css';

registerBlockType('sam/newsletter-block', {
	title: 'SAM Newsletter',
	icon: 'email',
	category: 'widgets',
	edit: () => {
		return (
			<div style={{ padding: '10px', border: '1px solid #ccd0d4' }}>
				<p><strong>Newsletter Subscription Form</strong></p>
                <p style={{ marginBottom: '15px' }}>This below form will appear on the frontend.</p>

				<div className="newsletter-form" style={{ border: '1px solid #ccd0d4', padding: '15px' }}>
			        <input type="text" name="user_name" placeholder="Your Name" disabled style={{ marginBottom: '10px' }} />
			        <input type="email" name="user_email" placeholder="Your Email" disabled style={{ marginBottom: '10px' }} />
			        <button type="button" className="submit-btn" disabled >Subscribe</button>
			        <div className="response-msg"></div>
			    </div>
            </div>
        );
	},
	save: () => {
        return null;
    },
});