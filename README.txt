=== Plugin Name ===
Contributors: dtygel
Tags: who owns your country, power networks, corporations, economy, politics, economic chain
Requires at least: 3.6
Tested up to: 4.1
Stable tag: 0.92
License: AFFERO License
License URI: http://www.gnu.org/licenses/agpl-3.0-standalone.html

Calculate the Economic Power Networks in your country, showing enterprises, people and government networks through charts, maps and factsheets.

== Description ==

= About =

What is the economic power structure of the private groups in your country? Who are the actors who accumulate the most power in this structure, and what is the relationship amongst them? What is the degree of influence of this invisible power structure, over government decisions in terms of development and economic policies? How does the State relate and feed this power structure and what are the counterparts of this relationship for the well being of society? It is with the objective to respond to these and other questions that we built the “Brazil's Owners” Project and corresponding methodology.

"Who Owns your Country" is a plugin for Wordpress which uses the methodology developped by EITA (http://eita.org.br) and IMD (http://maisdemocracia.org.br) for the project "WHO OWNS BRAZIL" (http://proprietariosdobrasil.eita.org.br).

It connects enterprises, people and government's ownership and revenue information to build Power Networks and a ranking of the most powerful actors in this constellation, allowing visitors to see the data in a variety of ways, including charts, factsheets and interactive network maps.

For more information on the methodology, visit http://www.proprietariosdobrasil.org.br/index.php/en/ . We also have a paper about the methodology in English which will soon be available.

= Contributions =

We will be glad to collaborate with other actors who wish to use the plugin in their country and make it better. Don't hesitate to join us in this common effort to contribute for a real democratic world, in which the economic powers are not directing the whole economy of the countries!

If you would like to send a question to us, just write to the [support forum](http://wordpress.org/support/plugin/who-owns-your-country).

== Installation ==

1. Download the zip file and unzip it in the wp-content/plugins folder of your wordpress installation
1. Go to the plugins administration panel in your WP website and activate the plugin
1. You can change settings in the "Owners" configuration menu.

= Using the plugin in your template =

When you activate the plugin, you'll see an "Owners" item in the left menu of the WP Admin Panel. You can easily insert new owners, with the shareholders, logo, revenue, type (state, enterprise, person) and other related information.

The plugin creates automatically a special "factsheet" page for each owner added (see screnshots). Besides that, it creates a search utility for listing owners with different filters and orderings.

You can make changes in the "Owners" settings page. For example, you can decide what slug defines the owner's URLs. By default, it's *owners*. If your WordPress site is configured to have permalinks (post name), then the address of an owner will be: yourwebsite/owners/name-of-the-owner. And the place where the guests can look for owners will be: yourwebsite/owners.

Please let us know if you have any doubts by writing to the [support forum](http://wordpress.org/support/plugin/who-owns-your-country).

== Translations ==

Who Owns Your Country is a fully internationalized (i.e. fully translateable) plugin.

If you would like to add a translation of the plugin to your language, you can do it online at our [Translation platform: https://osx6cfk.oneskyapp.com/collaboration/project?id=6944](https://osx6cfk.oneskyapp.com/collaboration/project?id=6944). 

If your language is not listed there, please send a message to us at contato@proprietariosdobrasil.org.br . We'll immediately add your language for you to start translating.

= Available languages =

* English
* Brazilian Portuguese, by daniel tygel

== Screenshots ==

1. Factsheet page of an owner (Section: global vision). The factsheet comes out of the box with the following sections: *Global Vision*, *Power Network* (graphic and list views), *Related articles*, *In the media*. This screenshot exemplifies the plugin's hooks: the hooks are used to add two more sections (*electoral donations* and *public fundings*), and also to change the name of a section ("Related articles" was renamed to "Power analysis")
1. Factsheet page - section "Power network". At the left you have the list view, and in the center there is the graphic view. The map is fully interactive, and clicking on any element opens up a box on the top left with its details.
1. Factsheet page - section "In the media". In the configuration of the plugin, you can choose which news websites will be consulted regularly to show the last news related to the owner's power network.
1. Page for searching the owners. Hooks in the plugin also allow the template owner to add custom columns and orderings to this page. Besides the filters (by enterprises, persons, state institutions and "ranked" corporations) and ordering, the text search form has an autocomplete feature, leading the guest directly to the factsheet og the owner found.

== Changelog ==

= 0.92 =
* Fixed some PHP warnings for empty objects
* Changed the way whoowns tables are called

= 0.91 =
* Fixed the header of whoowns.php to point to most recent file
* Fixed typo in utils.php (from <? to <?php)
* Added translators website in the README.txt file.


= 0.9 =
* The plugin now offers a built-in "factsheet" page of each owner, so that the plugin works on any template out of the box without the need to change it.
* The plugin now offers a built-in "look for owners" page, so that the this feature works on any template out of the box without the need to change it.
* Added hooks for the factsheet for templates (or other plugins) customizations
* Added hooks for the owners search page for templates (or other plugins) customizations
* Updated the javascript and stylesheets files
* Updated the Brazilian Portuguese translation


= 0.8 =
* Added the plugin to wordpress repository, with all the basic infrastructure available.

