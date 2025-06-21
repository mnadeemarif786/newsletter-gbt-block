## 🧭 Usage

## Gutenberg Block

	1- Open Posts or Pages, click Add Block, search "SAM Newsletter", and insert.

	2- In the editor you'll see a visual preview; on the frontend, users can submit their details.

## Admin Dashboard
	1- Go to Newsletter in the admin menu.
	2- View subscribers, use search, trash/restore/delete, and "Add New" or Edit per user.

## 🛠 Technical Details
	1- Database table: wp_sam_newsletter with columns id, name, email, status, created_at

	2- Built with WP_List_Table, AJAX hooks, nonces, REST-safe handlers

	3- Includes uninstall.php to remove table when plugin is deleted

	4- Built with @wordpress/scripts, no unnecessary dependencies