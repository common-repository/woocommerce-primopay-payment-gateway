=== Plugin Name ===
Contributors: primopay
Donate link: 
Tags: woocommerce, primopay, payment gateway, gateway, payments, credit card, Australia, ctel
Requires at least: 3.8.0
Tested up to: 4.2.2
Stable tag: 1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WooCommerce PrimoPay

== Description ==

Allows your WooCommerce shop to accept credit cards payments using the PrimoPay payment gateway.

The PrimoPay website has more information regarding this product: http://www.primopay.com.au



== Installation ==

1. Download the plugin, unzip then upload all files to `/wp-content/plugins/woocommerce-primopay` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the gateway via Admin -> WooCommerce -> Settings -> Payment Gateways -> PrimoPay


== Frequently Asked Questions ==

= When processing a payment I get SOAP errors =

You need to ensure that the SOAP extension is enabled on your website server. Contact your hosting company to get them to enable SOAP.


= In test mode, I always get error 99 =

In test mode, the number of cents of the order becomes the returned error code. 

To place a successful order you need to have 00 cents or 08 cents as your total order amount.

(08 is success for ANZ, 00 is success for everyone else).

When you go to live mode, this restriction is removed and orders can be successful at any cents value.


= Can you handle non Australian Merchants? =

PrimoPay only works with Australian online enabled merchant accounts. If you have an Australian merchant bank account then you can use PrimoPay.


= Do you process via tokens? =

The token facility will be coded in a future version. If you require tokens then please contact me.



== Screenshots ==
* Configuration screen.


== Changelog ==

= 1.2 =
* Tested up to 4.2.2
* Adjusted error messages to remove deprecated API calls
* Used WP built in URL finder for call to wsdl file

= 1.1 =
* Tested up to 4.1.0

= 1.0 =
* Initial release.


== Upgrade Notice ==

= 1.1 =
* Tested up to 4.1.0

= 1.0 =
Initial release.
