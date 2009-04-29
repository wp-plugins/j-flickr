<?php
/*
Plugin Name: J-Flickr
Plugin URI: http://japanographia.com/j-flickr/
Description: Shortcode access to the Flickr API.
Author: John Noel
Version: 1.0
Author URI: http://chaostangent.com/
*/

/*
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @author John "ChaosTangent" Noel <john.noel@chaostangent.com>
 * @link http://chaostangent.com
 * @version 1.0
 * @package j-flickr
 * @license http://opensource.org/licenses/gpl-3.0.html GPL 3.0
 */

define("JFLICKR_HOME", dirname(__FILE__).DIRECTORY_SEPARATOR);
define("JFLICKR_TEMPLATES", JFLICKR_HOME."templates".DIRECTORY_SEPARATOR);

/**
 *
 * @param $params Parma ham
 * @return string|bool The output if successful, false if not
 */
function jflickr_request($params)
{
	$params = array_merge(array(
		"api_key" => get_option("jflickr_apikey"),
		"method" => "photos.getRecent"), $params);

	$params["method"] = (substr($params["method"], 0, 6) != "flickr.") ?
		"flickr.".$params["method"] : $params["method"];

	$getUrl = "http://api.flickr.com/services/rest/?";
	foreach($params AS $param => $value)
	{
		$getUrl .= urlencode($param)."=".urlencode($value)."&";
	}
	$getUrl = rtrim($getUrl, "&");

	$output = "";
	if(function_exists("curl_init"))
	{
		$curl = curl_init($getUrl);
		curl_setopt_array($curl, array(
			CURLOPT_HEADER => false,
			CURLOPT_FAILONERROR => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_MAXREDIRS => 0));

		$res = curl_exec($curl);
		if(curl_errno($curl) == 0)
		{
			$output = $res;
			curl_close($curl);
		}
		else
		{
			echo curl_errno($curl)." - ".curl_error($curl);
		}
	}
	elseif(in_array(strtolower(ini_get("allow_url_fopen")), array("on", "1")))
	{
		$output = file_get_contents($getUrl);
	}
	// TODO other methods of requesting?

	return (!empty($output)) ? $output : false;
}

/**
 * The main shortcode function, the core of J-Flickr
 * @param $attributes
 * @param $content
 * @return string The output
 */
function jflickr_shortcode($attributes, $content)
{
	shortcode_atts(array(
		"method" => "photos.getRecent"
	), $attributes);

	// make sure attributes are always in the same order
	// e.g. [flickr uid="" bimble=""] == [flickr bimble="" uid=""]
	ksort($attributes);
	$serializedAttributes = serialize($attributes);

	// kudos be to Markus Tacker http://pear.php.net/package/Cache_Lite
	require_once("CacheLite.php");
	$cacheOptions = array(
		"cacheDir" => dirname(__FILE__).DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR,
		"lifeTime" => get_option("jflickr_cachetime", 3600),
		"readControlType" => "md5",
		"automaticCleaning" => 70
	);
	$cache = new Cache_Lite($cacheOptions);

	if(($output = $cache->get(sha1($serializedAttributes))) === false)
	{
		$output = jflickr_apicall($attributes);
		$cache->save($output, sha1($serializedAttributes));
		return $output;
	}
	else
	{
		return $output;
	}
}
add_shortcode("flickr", "jflickr_shortcode");

/**
 * Do an API call
 * @param $parameters Parameters to pass the Flickr API
 * @return string
 */
function jflickr_apicall($parameters)
{
	$xml = jflickr_request($parameters);
	if($xml !== false)
	{
		$output = $template = "";

		if(array_key_exists("jflickr_template", $parameters) &&
			file_exists(JFLICKR_TEMPLATES."{$parameters["jflickr_template"]}.xsl"))
		{
			$template = $parameters["jflickr_template"].".xsl";
		}
		elseif(file_exists(JFLICKR_TEMPLATES."{$parameters["method"]}.xsl"))
		{
			$template = $parameters["method"].".xsl";
		}
		else
		{
			switch($parameters["method"])
			{
				case "contacts.getPublicList":
					$template = "contacts.xsl";
					break;
				case "photos.comments.getList":
				case "photosets.comments.getList":
					$template = "comments.xsl";
					break;
				case "places.find":
				case "places.findByLatLon":
				case "places.getChildrenWithPhotosPublic":
				case "places.getInfo":	// TODO: method specific template
				case "places.getInfoByUrl":
				case "places.getPlaceTypes":
				case "places.getShapeHistory":
				case "places.placesForBoundingBox":
				case "places.placesForContacts":
				case "places.placesForTags":
				case "places.placesForUser":
				case "places.tagsForPlace":
					$template = "places.xsl";
					break;
				default:
					$template = "photos.xsl";
					break;
			}
		}

		$photos = array();

		// TODO this needs to be smarter
		if(version_compare(PHP_VERSION, "5.0.0", ">="))
		{
			$dom = DOMDocument::loadXML($xml);
			$xsl = new DOMDocument();
			$xsl->load(JFLICKR_TEMPLATES.$template);

			$xslt = new XSLTProcessor();
			$xslt->importStylesheet($xsl);
			$output = $xslt->transformToXml($dom);
		}
		else	// assume 4.3 as that's what wordpress 2.7.x is rated for
		{
			// ENGAGE THE SABLOTRON
			$xslt = xslt_create();
			xslt_set_encoding($xslt, "UTF-8");
			$output = xslt_process($xslt, "arg:/_xml", JFLICKR_TEMPLATES.$template, null, array("/_xml" => $xml));
			xslt_free($xslt);
		}

		if($output !== false && !empty($output))
		{
			// this is to strip the xml pre processor
			$output = (substr($output, 0, 6) == "<?xml ") ? preg_replace("/^<\?xml\s.*?\?>/", "", $output) : $output;
			$output = trim(str_replace("{{username}}", get_option("jflickr_username"), $output));
			return $output;
		}
	}
	return "Unable to do Flickr API query";
}

/**
 * Gets a list of templates from the templates/ directory
 * @return array
 */
function jflickr_get_templates()
{
	// don't know why it wouldn't be, but you never know with WordPress
	if(!defined("JFLICKR_TEMPLATES"))
	{
		define("JFLICKR_TEMPLATES", dirname(__FILE__).DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR);
	}

	$templates = array();

	// because I don't trust glob() and can't use SPL
	if(($dh = opendir(JFLICKR_TEMPLATES)) !== FALSE)
	{
		while(($file = readdir($dh)) !== FALSE)
		{
			if(($file != ".") && ($file != "..") && is_file(JFLICKR_TEMPLATES.$file))
			{
				$templates[] = $file;
			}
		}
	}

	return $templates;
}

/**
 * Gets the list of available API methods from the Flickr API itself
 * @return string An HTML list of methods
 */
function jflickr_get_apimethods()
{
	$parameters = array("methods" => "reflection.getMethods");
	return jflickr_apicall($parameters);
}

/**
 * Does some simple lexical analysis to check the right format of API key
 * @return bool Whether the currently stored API key is valid
 */
function jflickr_valid_apikey()
{
	$apiKey = get_option("jflickr_apikey");
	return ((strlen($apiKey) == 32) && preg_match("/^[0-9a-f]{32}$/i", $apiKey));
}

/**
 * Checks whether XSL is present and hence J-Flickr is able to operate
 * @return bool Whether XSL functionality is currently available
 */
function jflickr_xsl_available()
{
	if(version_compare(PHP_VERSION, "5.0.0", "<"))
	{
		// PHP4 xslt doesn't require the dom extension
		return function_exists("xslt_create");
	}
	else
	{
		// xsl is included by default in PHP5, but that doesn't mean it hasn't been disabled
		return class_exists("XSLTProcessor");
	}
}

/**
 * Checks whether the cache directory is writable
 * @return bool
 */
function jflickr_cache_writable()
{
	$dir = dirname(__FILE__).DIRECTORY_SEPARATOR."cache";
	return (file_exists($dir) && is_writable($dir));
}

/**
 * Adds the relevant settings page menu item for J-Flickr
 * @return void
 */
function jflickr_add_pages()
{
	add_options_page("J-Flickr", "J-Flickr", "manage_options", "jflickr", "jflickr_manager");
}
add_action("admin_menu", "jflickr_add_pages");

/**
 * Outputs the page for managing J-Flickr settings
 * @return void
 */
function jflickr_manager()
{
?>
	<div class="wrap">
		<h2>J-Flickr Manager</h2>
		<p>J-Flickr uses the <a href="http://www.flickr.com/services/api/">Flickr API</a> which means that you will need to <a href="http://www.flickr.com/services/api/keys/apply/">apply for an API key</a> before you can use this plugin.</p>

		<?php if(!jflickr_cache_writable()) { ?>
		<div class="error">
			<p><strong>The cache directory <code><?php echo dirname(__FILE__).DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR; ?></code> is not currently writable.</strong></p>
			<p>While not necessary for J-Flickr to function, you are <strong>strongly</strong> advised to use the cache rather than being unfriendly to Flickr.</p>
		</div>
		<?php } ?>

		<?php if(!jflickr_valid_apikey()) { ?>
		<div class="error">
			<p><strong>Your Flickr API key is not currently valid.</strong></p>
			<p>If you don't yet have a Flickr API key, you can <a href="http://www.flickr.com/services/api/keys/apply/">get one from their site</a>; if you already have one you can <a href="http://www.flickr.com/services/api/keys/">retrive it</a> once logged into the Flickr site.</p>
		</div>
		<?php } ?>

		<form method="post" action="options.php">
			<!-- Table for non-tabulated data, BAD wordpress, no biscuit -->
			<table class="form-table">
				<tr>
					<th scope="row">API Key</th>
					<td>
						<input type="text" name="jflickr_apikey" value="<?php echo get_option("jflickr_apikey"); ?>" />
						<span class="setting-description">This is the 32 character string, <strong>not</strong> the secret key</span>
					</td>
				</tr>
				<tr>
					<th scope="row">Default username</th>
					<td>
						<input type="text" name="jflickr_username" value="<?php echo get_option("jflickr_username"); ?>" />
						<span class="setting-description">Used as a template replacement macro, can be overridden on the shortcode call</span>
					</td>
				</tr>
				<tr>
					<th scope="row">Query cache time</th>
					<td>
						<input type="text" name="jflickr_cachetime" value="<?php echo get_option("jflickr_cachetime"); ?>" />
						<span class="setting-description">In seconds, recommend at least an hour (3,600 seconds) as <a href="http://www.flickr.com/services/api/flickr.activity.userPhotos.html">some API methods</a> enforce this as a minimum</span>
					</td>
				</tr>
			</table>

			<?php wp_nonce_field("update-options"); ?>
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="jflickr_apikey,jflickr_username,jflickr_cachetime" />

			<p class="submit"><input type="submit" class="button-primary" value="<?php _e("Save Changes"); ?>" /></p>
		</form>
	</div>
<?php
}

/**
 * Add the plugin table notice row if J-Flickr is not configured
 * @param $plugin
 * @return void
 */
function jflickr_plugin_notice($plugin)
{
	if(($plugin == "j-flickr/j-flickr.php") && !jflickr_valid_apikey())
	{
		echo "<td colspan=\"5\" class=\"plugin-update\">J-Flickr must be configured before usage. Go to <a href=\"".
			admin_url("options-general.php?page=jflickr")."\">the admin page</a> to configure the plugin.</td>";
	}
}
add_action("after_plugin_row", "jflickr_plugin_notice");

/**
 * Add the (usually red) admin notice bar to the plugins page if J-Flickr is not configured
 * @return void
 */
function jflickr_admin_notice()
{
	if(basename($_SERVER["PHP_SELF"]) == "plugins.php")
	{
		if(!jflickr_xsl_available())
		{
			echo "<div class=\"error\"><p><strong>J-Flickr was unable to detect any available XSLT functionality and will not function without it.
				For more details on this, please <a href=\"http://japanographia.com/jflickr/\">see the plugin site</a>.</strong></p></div>";
		}
		elseif(!jflickr_valid_apikey() || !jflickr_cache_writable())
		{
			echo "<div class=\"error\"><p><strong>J-Flickr must be configured before usage. Go to <a href=\"".
				admin_url("options-general.php?page=jflickr")."\">the admin page</a> to configure the plugin.</strong></p></div>";
		}
	}
}
add_action("admin_notices", "jflickr_admin_notice");

/**
 * Plugin activation hook, fills in default cache time
 * @return void
 */
function jflickr_activate()
{
	add_option("jflickr_cachetime", 86400);
	// TODO should I throw a fatal error here to prevent activation if XSLT functionality isn't available?
}
register_activation_hook(__FILE__, "jflickr_activate");

/**
 * Plugin deactivation hook, removes options, clears cache
 * @return unknown_type
 */
function jflickr_deactivate()
{
	delete_option("jflickr_cachetime");
	delete_option("jflickr_apikey");
	delete_option("jflickr_username");

	// TODO maybe make this global rather than always in the shortcode?
	require_once("CacheLite.php");
	$cacheOptions = array(
		"cacheDir" => dirname(__FILE__).DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR,
		"lifeTime" => get_option("jflickr_cachetime", 3600),
		"readControlType" => "md5",
		"automaticCleaning" => 70
	);
	$cache = new Cache_Lite($cacheOptions);
	$cache->clean();
}
register_deactivation_hook(__FILE__, "jflickr_deactivate");
?>