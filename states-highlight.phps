<?php
/*
Plugin Name: States Syntax Highlighter
Version: 1.1
Plugin URI: http://www.nextthing.org/code/
Description: Adds valid XHTML/CSS syntax highlighting to your code tags with 
an optional lang attribute. Inspired by Scott Yang's WordPress plugin which 
does essentially the same thing (see 
http://scott.yang.id.au/category/project/syntax-hilite/ 
... it's really quite nifty).
Author: Andrew Wooster
Author URI: http://www.nextthing.org/
*/

// Configuration information.
$states_bin = "/usr/bin/states";
$states_code_path = "/Library/WebServer/Documents/wordpress/blog/code";
$states_code_uri = "http://www.yourdomain.blah/blog/code";
$states_code_link_text = 
    '<span style="display: block; text-align: center;">Download this code: '.
    '<a href="%s" title="Download the above '.
    'code as a text file.">/code/%s</a></span>';
$states_file = 
    "/Library/WebServer/Documents/wordpress/wp-content/plugins/enscript.st";


// No need to change anything below this line.
function states_syntax_highlight($code, $lang, $filename = '') {
    global $states_bin, $states_code_path, $states_file, 
        $states_code_link_text, $states_code_uri;
    $command = "$states_bin -s $lang -W all --define=toc=0 ".
        "--define=colormodel=emacs --define=language=xhtml ".
       "-f $states_file";
    $from_file = false;
    if ($filename != '') {
        $fullpath = $states_code_path.DIRECTORY_SEPARATOR.$filename;
        if (strpos($filename, '..') === false && file_exists($fullpath)) {
            $code = shell_exec($command.' '.escapeshellarg($fullpath).' 2>&1');
            $from_file = true;
        }
    } elseif (function_exists('proc_open')) {
        $descriptorspec = array(
            0 => array("pipe", "r"),    // stdin
            1 => array("pipe", "w"),    // stdout
            2 => array("pipe", "w"));   // stderr
        $process = proc_open($command, $descriptorspec, $pipes);
        if (is_resource($process)) {
            fwrite($pipes[0], $code);   // Write the code to states.
            fclose($pipes[0]);          // Send eof.
            
            $code = '';
            while (!feof($pipes[1])) {  // Get stdout.
                $code .= fgets($pipes[1], 4096);
            }
            while (!feof($pipes[2])) {  // Append stderr.
                $code .= fgets($pipes[2], 1024);
            }
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
        }
    }
    
    $code = preg_replace("|^.*?<pre>|si", '<code class="source">', $code);
    $code = preg_replace("|</pre>.*?$|si", "</code>", $code);
    if ($from_file) {
        $code .= sprintf($states_code_link_text, $states_code_uri.'/'.$filename,
                $filename);
    }
    return $code;
}

function states_validate_language ($language) {
    $default = "passthrough";
    $available = array (
        "ada", "asm", "awk", "c", "changelog", "cpp", "diff", "diffu", "delphi",        "elisp", "fortran", "haskell", "html", "idl", "java", "javascript",
        "mail", "makefile", "nroff", "objc", "pascal", "perl", "postscript",
        "php", "python", "scheme", "sh", "sql", "states", "synopsys", "tcl", 
        "verilog", "vhdl", "vba");
    $language = strtolower($language);
    if (in_array($language, $available)) {
        return $language;
    } else {
        return $default;
    }
}

if (function_exists('add_filter')) {
    function __states_highlight ($text) {
        return preg_replace_callback("/<code([^\/>]*)>(.*?)<\/code>/is",
            '__states_highlight_callback',
            $text);
    }
    
    function __states_highlight_file ($text) {
        return preg_replace_callback("/<code([^>]*)\/>(\s*<\/code>)?/is",
            '__states_highlight_file_callback',
            $text);
    }
    
    function __states_highlight_callback ($match) {
        $attribute = $match[1];
        $code = $match[2];
        
        $code = str_replace("<br />", "", $code);
        $code = preg_replace("/\\s*<p>/s", "\r\n\r\n", $code);
        $code = preg_replace("/<\/p>/s", "", $code);
        $code = str_replace("&#8216;", '\'', $code);
        $code = str_replace("&#8217;", '\'', $code);
        $code = str_replace("&#8211;", '--', $code);
        $code = str_replace("&#8212;", '-', $code);
        $code = str_replace("&#8220;", '"', $code);
        $code = str_replace("&#8221;", '"', $code);
        $code = str_replace("&#8230;", '…', $code);
        $trans_table = array_flip(get_html_translation_table(HTML_ENTITIES));
        strtr($code, $trans_table);
        $code = preg_replace('/\&\#([0-9]+)\;/me', "chr('\\1')", $code);
    
        if (preg_match('/\s+lang\s*=\s*["\']?([^"\']+)["\']?/xi', 
            $attribute, $lang)) {
            $lang[1] = states_validate_language($lang[1]);
            $code = states_syntax_highlight($code, $lang[1]);
        }
        return $code;
    }
    
    function __states_highlight_file_callback ($match) {
        global $states_code_uri, $states_code_link_text;
        
        $attributes = $match[1];
        $language = 'passthrough';
        $filename = '';
        if (preg_match('/lang\s*=\s*["\']?([^"\']+)["\']?/xi', $attributes, $lang)) {
            $language = $lang[1];
        }
        if (preg_match('/file\s*=\s*["\']?([^"\']+)["\']?/xi', $attributes, $file)) {
            $filename = $file[1];
        }
        $language = states_validate_language($language);
        return states_syntax_highlight('', $language, $filename);
    }
    
    add_filter('the_content', '__states_highlight');
    add_filter('the_content', '__states_highlight_file');
}


?>