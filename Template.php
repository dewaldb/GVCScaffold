<?php
class Template {
    static function load($filename,$variables = array()) {
        if(!file_exists($filename)) {
            return "";
        }
        
        $file = file_get_contents($filename);
        
        // loop through the passed variables and set them so they exist in the scope of the eval call
        foreach($variables as $key=>$val) {
            $$key = $val;
        }
        
        // use eval with ob_start so we can return the result or the evaluated html with php inserts
        ob_start();
        echo eval("?>".$file);
        $buffer = ob_get_contents();
        @ob_end_clean();

        return $buffer;
    }
}

function str_esc($str) {
    return strtr($str, array(
        "\0" => "",
        "'"  => "&#39;",
        "\"" => "&#34;",
        "\\" => "&#92;",
        // more secure
        "<"  => "&lt;",
        ">"  => "&gt;",
    ));
}
?>