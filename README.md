[![Alledia](https://www.alledia.com/images/logo_circle_small.png)](https://www.alledia.com)

# OSHelpScout

## About

Joomla extension to display HelpScout form and user's conversations

## HelpScout webhooks
If you need to use this component in only on server, it is ok to directly link HelpScout webhook to an url for the
view 'hsusergrouphook'.
But in case you need to have multiple webhook endpoints, you will need to use a proxy script in order to
redirect a HelpScout webhook call to multiple URLs.

You can use: https://www.webscript.io and a script like:

https://gist.github.com/andergmartins/147f2dcb4264e9ee232c359e94f63552

## Requirements

* Joomla 3.x
* PHP 5.3.x
* curl

## License

[GNU General Public License v3](http://www.gnu.org/copyleft/gpl.html)
