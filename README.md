[![Alledia](https://www.alledia.com/images/logo_circle_small.png)](https://www.alledia.com)

# OSHelpScout

## About

This is a Joomla extension to display HelpScout forms and user conversations directly inside a Joomla site. We have been using the extension on our sites for months, but it may need some work before it is easily used by more sites.

## HelpScout webhooks
If you need to use this component for only a single site, it is OK to directly link your HelpScout webhook to an URL for the
view 'hsusergrouphook'.

If you need to have multiple webhook endpoints, you will need to use a proxy script in order to
redirect a HelpScout webhook call to multiple URLs.

You can use: https://www.webscript.io and a script like this one:

https://gist.github.com/andergmartins/147f2dcb4264e9ee232c359e94f63552

## Requirements

* Joomla 3.x
* PHP 5.3.x
* curl

## License

[GNU General Public License v3](http://www.gnu.org/copyleft/gpl.html)
