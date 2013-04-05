/**
 * Include CSS via Javascript
 *
 * Adapted from:  http://snippets.dzone.com/posts/show/4554
 *
 * By Jonathan Cameron
 *
 * @param css_url string the URL/path to the CSS stylesheet file
 */

function includeCSS(css_url) {
    var css_link = document.createElement('link');
    css_link.type = 'text/css';
    css_link.rel = 'stylesheet';
    css_link.href = css_url;
    css_link.media = 'screen';
    document.body.appendChild(css_link);
}
