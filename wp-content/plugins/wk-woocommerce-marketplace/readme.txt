=== Marketplace ===
Contributors: Webkul
Tags: form, database, db, data, value, shortcode, submit
WordPress :
  * Requires at least: 4.4
  * Tested up to: 5.1.2
WooCommerce: 3.6.x
License: GNU/GPL for more info see license.txt included with plugin
License URI: https://store.webkul.com/license.html

WordPress WooCommerce Marketplace is a e-commerce based plugin which converts your CMS site to a complete frequent marketplace with the help of WooCommerce. WordPress WooCommerce Marketplace allows you to manage sellers, buyers, and their products.
In this Plugin seller can register and publish their own products, add, delete and manage products.

== Description ==

		1. Marketplace Seller Panel
			* Features-
				* Sellers -options
					1. View Profile
					2. Product can be add, update, view and delete.
					3. View Order History.
					4. Manage Shipping
					5. Shop Follower
					6. View dashboard
						* It will contain every information related to orders.
						* i.e. dashboard, last 7 days sales amount, sales order summary, recent orders, top billing countries.
					7. Transaction.
					8. Ask any query to admin.
					9. Notification.
				* Buyers -options
					1. Profile View
		2. Seller List widget
			* Users will be able to view all the sellers available in the marketplace
			* Controls for admin
			* name of list. (Default:Seller List)
			* Display current logged in seller (Default: include seller)
			* Show seller's nick-name or full-name (Default : full name)
			* No. of users (Default: 10 users)


== Installation ==

1. Upload the `marketplace` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin using the 'marketplace' menu

== Frequently Asked Questions ==

= No questions asked yet =

Feel free to do so.

For any Query please generate a ticket at https://webkul.com/ticket/
== 4.9.1
Enhancement - Allow user to change endpoints and title of user Dashboard.
Fix - Removed the dependency on datatable.

== 4.9.0
Enhancement - Added compatibility for Multisite.
Enhancement - Added Refund management at seller end.
Enhancement - Added email templates for seller for following order status: on-hold, processing, completed, refunded, failed and cancelled.
Enhancement - Added some translations.
Enhancement - Added compatibility with WC Marketplace Split Order plugin.
Fix - fixed transaction query.
Fix - fixed transaction query.

== 4.8.4
Fix - Fixed virtual product issue on seller side.
Fix - Fixed seller mail for new order.
Fix - Fixed shipping page issue.
Fix - Fixed seller approve issue on seller-list page.
Tweak - Added css for feedback popup.
Enhancement - Updated the commission part.
Enhancement - Added email templates
Enhancement - Added pagination in products list page in backend to load products faster.
Enhancement - Added compatibility with Webkul Marketplace Event Manager plugin.
Enhancement - Added compatibility with Webkul Marketplace Multicurrency plugin.

== 4.8.3
Enhancement - Fixed error on seller creation on admin side.
Enhancement - Updated transaction function.
Enhancement - Fixed seller register mail issue.
Enhancement - Fixed the product type issue admin side.
Enhancement - Fixed the product meta data issue on seller end.
Tweak - Added additional information on seller order mail.
Tweak - Added compatibility with Webkul Marketplace Reward plugin.
Tweak - Added compatibility with Webkul Marketplace USPS shipping.
Tweak - Added hook for adding order meta data on seller side.

== 4.8.2
Fixed the translation issue.
Fixed product listing issue.
Updated seller page css.
Updated commission management.
Fixed notification issue.
Fixed issues regarding seller slug.

== 4.8.1
Update seller stat for showing only seller data.
Fixed shipping issue.

== 4.8.0
Added feature of seperate seller dashboard for seller.
Added translation support where missing.
Added additional fields provided for seller country and state in profile page.
Added seller query management.
Added additional hooks and filters.
Added mail template for admin answer regarding query.
Updated the commission management.
Fixed responsive issues.
Fixed shipping class issue.
Fixed the shipping and discount issue.
Fixed the countries SVG layout issue.
Fixed mail preview issue.

== 4.7.4
Added marketplace extension tab at admin end for showing addons and other Webkul WordPress and WooCommerce plugins.
Fixed the Emogrifier issue while sending mail to seller while ordering.
Fixed error in adding shipping zone at seller end due to updated version of WooCommerce 3.4.
Seller can now delete the particular shipping method while editing shipping zone.
Fixed jquery error while seller creating product.
Added check for the WC_Admin_Report at dashboard.
Fixed warnings and added link to login page in favourite seller product page.

== 4.7.3
Updated grouped product addition flow as per WooCommerce 3.x.x.
Updated icons in the pages, used Webkul Rango fonts in place of Font Awesome.
Updated price display in product list at seller end.
Added nonce to prevent CSRF vulnerability in product delete at seller end.

== 4.7.2
Added filter to search by seller in marketplace product page at admin end.
Added product page link in products in order details page seller end.
Added variation details in order view for variable product.

== 4.7.1
Introduced category tree at seller end.
Updated new order mail action for seller.
Updated seller delete page layout.
Fixed generated password for new user in email notification.
Restrict seller from accessing admin end.

== 4.7.0
Introduced transactions.
Mass assign products to sellers added.
Upsells/Crosssells feature added for seller.
Seller reviews approval from admin end integrated.
Backend set seller category and product type by admin.

== 4.6.0
Front-end design (layout) updated [Major Update].
Fixed all XSS vulnerabilities.
Email template feature updated, and preview added.
A buyer can give review to seller in a more interactive way.
Marketplace Seller Dashboard updated with new and interactive charts.
Marketplace Seller Menu added with the WooCommerce default menu to make it more accessible.
Log added in "Ask To Admin" feature at seller-end.
Visibility of seller rating at product page.
Seller Profile asset visibility configuration added at admin-end.
Multiple selection of product gallery images added at seller-end.
Manual commission pay feature added.
Inbuilt Marketplace Flat Rate Shipping.
Login with Facebook while reviewing seller fixed.

== 4.5.0
Rewrite rules updated.
Seller preview and collection page restrictions.
Shipping access restrictions for seller.
Variable product issue fixed.
Default Dashboard view at seller end fixed.
Fixed XSS vulnerability at seller's profile edit page.
Seller shop follower page design issue fixed.
Add product update notice at seller end.
Provide multiple file upload for downloadable product at seller end.

== 4.4.0
Introduced multi-language feature, added .pot file.
Code standardization.
Introduced e-mail templates for various actions like seller registration, ask to admin, etc.
Introduced notification center at admin as well as seller end.

== 4.3.2
Introduced Seperate Seller Registration feature which can be managed by admin.

== 4.3.1
Add feature to convert customer into seller by changing role and vice-versa.
Update module as per new version of WooCommerce i.e., 3.0

== 4.3.0
Introduced new Invoice management feature in plugin for seller and admin end.
Introduced New shop follow feature / Favourite Seller in marketplace plugin.
Fixed my account pages calling bugs

== 4.2.3
Updated hook in Seller Panel list files for adding more tabs by plugins.

== 4.2.2
Introduced seller shipping management.
Bug fixed in deletion of zone to seller meta table and page reloaded once zone cost is defined.

== 4.2.1
Fixed bugs related to visibility of seller product.

== 2.3.0=
Product by feature on product page is introduced
Rewrite rule updated for shop address
Phone number validation updated
Edit product page vulnerability issue fixed
Setting page admin hidden value for seller page title is updated

=2.2.0=
Bugs fixed related with admin end.
facebook login issue fixed

=2.1.0=
Bugs Fixed with DEBUG MODE = true

=2.0.0=
Updated version with many bugs fixed and new controls to seller.

= 1.0 =
Initial release
