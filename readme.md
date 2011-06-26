States Syntax Highlighter Plugin for WordPress
==============================================

This plugin uses GNU enscript/states to do syntax highlighting of source code
inside WordPress.

The newest version of this plugin should be available at:
https://github.com/wooster/wp-states-highlight

USAGE
=====
Once installed, there are two ways to use the plugin. The first way is to
embed the code directly in a post:

```html
<code lang="sh">
echo "Hello there"
</code>
```

The above option is tricky and doesn't always work correctly. If you use it,
you should be careful to avoid and/or escape HTML characters like <, >, &, etc.
I haven't figured out how to properly undo a lot of the formatting WordPress
does on code in between the code tags. Until I do, the above may not work 
reliably for you.

The second way to use the plugin is to specify a directory on your website,
like `/blog/code`, where you upload source files to. Then, if you want to
upload a PHP file named `foo.php`, you could name it `/blog/code/foo.txt`, and
then use:

```html
<code lang="php" file="foo.txt" />
```

And the file would be included in the post, syntax highlighted and everything,
with a link to the original file at the bottom of the code. I personally
prefer this method, and it's the reason I wrote the plugin. Thanks to Dunstan
Orchard for the idea[1].

The valid values of the `lang` attribute are: ada, asm, awk, c, changelog, cpp, 
diff, diffu, delphi, elisp, fortran, haskell, html, idl, java, javascript, mail, 
makefile, nroff, objc, pascal, perl, postscript, php, python, scheme, sh, sql, 
states, synopsys, tcl, verilog, vhdl, and vba. These correspond, with my own 
addition of php, to the descriptions on enscript's highlightings page[2].

INSTALLATION
============
Chances are, if you're running WordPress on a Unix system, you've already got enscript installed. If not, you can install it by running the following:

```sh
curl -O "http://www.codento.com/people/mtr/genscript/enscript-1.6.4.tar.gz"
gunzip enscript-1.6.4.tar.gz
tar -xvf enscript-1.6.4.tar
rm enscript-1.6.4.tar
cd enscript-1.6.4/
./configure --prefix /some-safe-path/enscript
make
make install
```

enscript should now be installed in `/some-safe-path/enscript`, where 
`some-safe-path` is hopefully some path outside of your HTTP server's 
document root.

Now, you'll need to get my modified enscript.st file (available at 
http://www.nextthing.org/code/enscript/) and put it in your
`wordpress/wp-content/plugins` directory. Also place the `states-highlight.php`
file in the same directory.

Once you've done that, you'll need to edit some variables `states-highlight.php`
to match your environment:

```php
    // Configuration information.
    $states_bin = "/usr/bin/states";
    $states_code_path = "/Library/WebServer/Documents/wordpress/blog/code";
    $states_code_uri = "http://www.yourdomain.blah/blog/code";
    $states_code_link_text = 
        '<div style="text-align: center;">Download this code: '.
        '<a href="%s" title="Download the above '.
        'code as a text file.">/code/%s</a></div>';
    $states_file = 
        "/Library/WebServer/Documents/wordpress/wp-content/plugins/enscript.st";
```

`$states_bin` is the path to the `states` binary, which ships with enscript and
does the actual syntax highlighting.

`$states_code_path` is a path to a directory on your webserver in which code
files can be placed for inclusion via the `files=""` attribute.

`$states_code_uri` is the URI for these code files.

`$states_code_link_text` is the text which will appear below the `<pre>` which
contains the highlighted source code, when that code was pulled in from a file.

`$states_file` is the path (it needs to be absolute) to my custom `enscript.st`
file.

Now, activate the "States Syntax Highlighter" plugin in the WordPress admin 
section. You should also uncheck "WordPress should correct invalidly nested 
XHTML automatically" in the WordPress writing options.

Finally, if you want the syntax highlighting to show up, you should add the
following styles to your `wordpress/wp-layout.css` and 
`wordpress/wp-admin/wp-admin.css` stylesheets:

```css
  code.source {
    border: 1px solid lightgrey; 
    padding: 5px;
    display: block;
    white-space: pre;
    overflow: auto;
  }
  .enscript-comment {font-style: italic; color: #B22222;}
  .enscript-function-name {font-weight: bold; color: #0000FF;}
  .enscript-variable-name {font-weight: bold; color: #B8860B;}
  .enscript-keyword {font-weight: bold; color: #A020F0;}
  .enscript-reference {font-weight: bold; color: #5F9EA0;}
  .enscript-string {font-weight: bold; color: #BC8F8F;}
  .enscript-builtin {font-weight: bold; color: #DA70D6;}
  .enscript-type {font-weight: bold; color: #228B22;}
```

CONTACT INFO
============
For copyright information, see COPYING.

        Andrew Wooster
        <http://www.nextthing.org/>
        
        WordPress WWW home page:
        <http://www.wordpress.org/>
        
        GNU Enscript WWW home page:
        <http://www.codento.com/people/mtr/genscript/>

CITATIONS
=========
[1] http://www.1976design.com/blog/archive/site-news/2004/07/29/redesign-tag-transform/
[2] http://www.codento.com/people/mtr/genscript/highlightings.html
