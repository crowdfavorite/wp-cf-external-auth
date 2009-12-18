# CF External Auth

This plugin allows for custom cookie checks in directories outside of WordPress' control to be authenticated based on the user's login status in WordPress.

## Modification

The plugin can be modified in a few ways:

- `cfea_handle_auth_request` filter: define a function to be run to perform the authentication of the user. By default this is a function that simply checks wether the user is logged in or not.
- `cfea_non_auth_redirect` action: by default a non authorized user will be redirected to /wp-login.php. Use this action to redirect to a different resource if needed.
- `cfea_login_redirect` filter: by default the user will be redirected back to the requested resource. Use this action to do more checks on the user's condition/permissions if needed and redirect to somewhere other than the outside resource.

## Requirements

- Apache 2.2+
- WordPress 2.8+
- Admin privileges to alter the vhosts declaration

## Install

### WordPress

Install this plugin like you would any other plugin. If you don't know how to do that then the rest of this configuration is beyond your means and you should consult with someone who knows more than you do.

### Apache

Copy the contents of the htaccess-dist.txt file in to the .htaccess file in the base folder (not web root) of the folder structure to be validated.

Enable the RewriteMap in the vhosts file by adding the following code INSIDE the VirtualHost that it applies to. **Note:** YES, you do need to enable RewriteEngine even though its done in the .htaccess by default for WordPress. This loads before that declaration so we need to enable it for the RewriteMap directives to be applied when the configuration loads.
		
	# CFEA_AUTH: Enable the ModRewrite engine and add our Rewrite Maps
	RewriteEngine On
	RewriteMap cfeacookiecheck prg:/_path_/_to_/wp-content/mu-plugins/cf-external-auth/cfea_cookiecheck.pl
	RewriteMap cfeacookieauth prg:/_path_/_to_/oxfordclub/wp-content/mu-plugins/cf-external-auth/cfea_cookieauth.pl
			
Some systems my need a RewriteLock file, depending upon Apache's config. Add one if one is not defined elsewhere in the config. Add this code OUTSIDE of the VirtualHost delcaration.

	RewriteLock /tmp/rewrite_lock
			
Restart Apache.

### File Structure

The plugin requires that the files be in a 3 tier organization structure. THIS IS MANDATORY!

- ie: `/container/group/edition/[...files-here...]`
- `container` is the base level where you'll put the new .htaccess file (as noted below)
- `group` is used to create groupings of files.
- `edition` is for the individual group is stored.
- Examples:
 	- `/downloads/software/software-name/software-version1.zip,software-version2.zip,...`
	- `/publications/publication-name/pubication-year/publication-may.pdf,publication-april.pdf,...`