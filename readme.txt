=== J-Flickr ===
Contributors: chaostangent
Tags: flickr,jflickr,j-flickr,photos,api,media,yahoo
Requires at least: 2.5
Tested up to: 2.9.1
Stable tag: 1.0.2
Donate link: http://chaostangent.com/j-flickr/

J-Flickr provides shortcode (e.g. [flickr key="value"]) syntax access to the Flickr API.

== Description ==

J-Flickr is designed to be as unintrusive as possible in giving you access to the Flickr API service. This means that you are not limited to the methods you can call or hamstrung by the output of the plugin. If you want to search for photos with an [Attribution License](http://creativecommons.org/about/licenses/) uploaded last year then you can, just as you can get a list of your public contacts or talk to the [Flickr Pandas](http://www.flickr.com/explore/panda). Essentially you construct the shortcode as if making an [API call](http://www.flickr.com/services/api/) and then either use one of the existing general templates or make your own! The templates are in XSL and operate directly on the XML sent back from Flickr so you're never wanting for data that other plugins may not expose.

== Installation ==

1. You should have some form of XSL support included in your PHP installation. If you are using any version of PHP above 5 you don't need to worry; if you are using PHP 4 then you will need to have the [XSLT module installed](http://www.php.net/manual/en/book.xslt.php).
2. Upload this directory to your plugins directory. It should create 'wp-content/plugins/j-flickr/'.
3. It is strongly advised that you make the 'wp-content/plugins/j-flickr/cache' directory writable by the web server so that API query results can be cached. The [Wordpress Codex contains in-depth instructions](http://codex.wordpress.org/Changing_File_Permissions) on how to go about this.
4. WordPress users should go to their Plugins page and activate "J-Flickr".
5. J-Flickr will not work until you have a Flickr API key. If you already have one go to Settings->J-Flickr and put it into the box. If you do not yet have a key, you can [apply for one](http://www.flickr.com/services/api/keys/apply/).
6. It's also probably a good idea to put in your Flickr username so that you can use the {{username}} macro in templates.

== Examples ==
Get a list of photos updated in April 2009:

	[flickr method="photos.recentlyUpdated" min_date="2009-04-01" extras="license,geo,tags"]

Get the photos from a specific group pool:

	[flickr method="groups.pools.getPhotos" group\_id="57342295@N00" per\_page="25" page="4"]

Get the comments for a specific photo with a custom template:

	[flickr method="photos.comments.getList" photo\_id="3459139116" max\_comment_date="2009-04-01" jflickr\_template="commentTemplate"]

== Frequently Asked Questions ==

= Can I use method x? =

You can use any method of the Flickr API that doesn't require authentication. Some methods make more sense than others to use, if a method doesn't return any output then you're using the plugin for the wrong thing.

= How do I define my own templates? =

There is a 'templates' directory within the plugin directory that contains a selection of XSL templates. The included ones are for the (what I would imagine) are the most used result sets but making your own is as easy as creating a valid XSL template and dropping it and then using it (see the Examples section).

If you wish for a certain method to always use a certain template just name the template after the method sans the 'flickr.' prefix, e.g. for the method 'interestingness.getList' name your file 'interestingness.getList.xsl'. Case sensitivity will be based on your hosts file system.

= What is the template priority? =

If you define a template in the shortcode this will be used before all others, if one is not defined or the file does not exist then the plugin will search for a method template, if that doesn't exist it will fallback to the general templates. The 'photos.xsl' template is the standard and should not be deleted.

= What is the cache for? =

The cache exists to be friendly to Flickr, they've been awesome enough to provide a solid API so you should be friendly back to them by not hammering the service. Essentially every shortcode instance you put in your blog is an API call, if the cash didn't exist then every request for a page with the shortcode would be an API request. So say you have 5 front page posts each with one shortcode instance, if 1000 visited in a day, thats 5000 API requests just for the homepage.

__You are responsible for managing the number of connections and queries to the Flickr API__

= What is a good cache time? =

I've set the default to be 1 day as that's plenty of time between requests. The higher the value the better really, so if you only update your Flickr account at weekends then feel free to set it to 5 days (432,000 seconds) or longer.

== Thanks ==

Many thanks to Fabien MARTY for his PEAR Cache_Lite work.

== Future ==

I had originally intended to include a web-based template editor with version 1.0 but decided that it muddied the purity of what I was trying to achieve. J-Flickr is still very much a developer led plugin and it seemed an odd usage of time for fluffy UI elements, especially for a 1.0 release.

In no particular order:

* Admin template manager
* Proper, remote API key validation
* WP Media integration
* Tighter error checking of prerequisites (writable cache directory etc.)
* Better error reporting than silence or crunchy PHP output
* Authentication support

These should all be included in a 2.0 release although the development of that will likely depend on interest (if any) of this version.