=== LezWatch.TV News and Information ===
Contributors: Ipstenu, liljimmi
Tags: television, queer, lesbian, tv, blocks
Requires at least: 4.8
Tested up to: 5.9
Stable tag: 2.0
License: GPLv2 (or Later)

Display information on queer female, transgender, and non-binary representation on TV. Brought to you by LezWatch.TV.

== Description ==

[LezWatch.TV](https://lezwatchtv.com/) catalogs and documents queer female, transgender, and non-binary characters, as well as their actors and shows from TV, web and streaming media. With that data, statistics are calculated on the state of international queer story representation. We provide easy and free access to finding the best shows with queer characters and stories in the genres fans want to see. We can help you keep up to date with the global happenings of queer representation on television.

Currently we provide information on:

* **... of the Day:** A character or show (or death of a character) of the current day
* **Last Death:** The latest character death

= Privacy Policy =

In using this plugin, your website will contact the LezWatch.TV API to request up-to-date information. The IP address of your domain will be tracked, in order to generate usage statistics of the service. There is no information collected from the visitors to your site.

By using this plugin, you agree to the [terms of Use of LezWatch.TV](https://lezwatchtv.com/tos/) as a service provider. All data collected falls under the [LezWatch.TV Privacy Policy](https://lezwatchtv.com/tos/privacy/).

= Usage =

The data can be displayed via a widget, a shortcode, or a Gutenberg block.

**Widgets**

* **LWTV - ... Of The Day**
* **LWTV - Last Death**

**Shortcodes**

* `[lwtv data="of-the-day" otd={character|show|death|birthday}]`
* `[lwtv data="died-on-this-day"]`
* `[lwtv data="died-on-this-day" format="MM-DD"]`
* `[lwtv data="this-year" format="YYYY"]`
* `[lwtv data="last-death"]`

**Blocks**

* Last Death
* ... Of The Day

Notes:

* If no type is passed to '... Of The Day' it will default to the character of the day.
* If no date is passed to 'On This Day' it will default to today.
* If no year is passed to 'This Year' it will default to this current year.

== Frequently Asked Questions ==

= Where do you get your data? =

Data is pulled from [LezWatch.TV](https://lezwatchtv.com).

= How accurate is the data? =

As accurate as humanly possible. LezWatch.TV content is curated by hand.

= Is this US shows only? =

No! LezWatch.TV documents international television, including streaming media.

= Who owns LezWatch.TV =

[We do](https://lezwatchtv.com/about/).

= You're missing some shows/characters =

We know. The world is a big place. [Please drop us a line and let us know](https://lezwatchtv.com/about/contact/). We're always trying to make it better.

= Why only female queers? =

Because the site data is from LezWatch.TV and that's what we specialize in. If someone wants to make a similar site for male queers, we're happy to help them output their data so it can be used.

= What information from my site is tracked? =

The IP address and domain of sites that use this plugin are tracked _only_ when the plugin is activated and used on the front-end of your website. No data is tracked until a widget, block, or shortcode is in place. For more information, please review the following:

* [Terms of Use](https://lezwatchtv.com/tos/)
* [Privacy Policy](https://lezwatchtv.com/tos/privacy/)

= Can I change the URL it calls data from? =

You can, but we caution you that the calls won't work unless you match the data. You're more than welcome to fork the code from our [Github repository](https://github.com/LezWatch/lwtv-plugin/tree/production/rest-api). Once you've built your own version, you'll need to do the following:

1. Turn on `WP_DEBUG`
2. Define `LWTV_DEV_SITE_API` as your demo url

It's only meant for in-house development, but flexibility is king.

== Screenshots ==

== Installation ==

1. Download the plugin .zip file and make note of where on your computer you downloaded it to.
2. In the WordPress admin (yourdomain.com/wp-admin) go to Plugins > Add New or click the "Add New" button on the main plugins screen.
3. On the following screen, click the "Upload Plugin" button.
4. Browse your computer to where you downloaded the plugin .zip file, select it and click the "Install Now" button.
5. After the plugin has successfully installed, click "Activate Plugin" and enjoy!

==Changelog==

= 2.0 =

* October 2021 by Ipstenu
* Updating blocks

= 1.3 =

* May 2021 by Ipstenu
* Removed broken blocks (they'd been broken yonks and undocumented)
* 5.7 compatibility (deprecation fixes)
* Ported from CGB to create-block due to the former being apparently abandoned, and the later being official.
* Escaping widgets to protect from XSS
