<?php

$x_url = "http://identi.ca/notice/12903371";

function is_ident($url) 
{
    $pat = "http://identi.ca/";
    return strpos($url, $pat) === 0;
}

function ident_post_id($url)
{
    $pat = "_http://identi.ca/notice/([\d]+)_";
    if (preg_match($pat, $url, $matches) == 1)
    {
        // print_r($matches);
        return $matches[1];
    } else {
        return false;
    }
}

function get_ident_post_data($in_url)
{
    $id = ident_post_id($in_url);
    if ($id !== false)
    {
        $url = "http://identi.ca/api/statuses/show/$id.json";
        $data = file_get_contents($url);
        $res = json_decode($data, true);
        return $res;
    } else {
        return false;
    }
}

if (is_ident($x_url)) 
{
    echo "Starts with http://identi.ca\n";
    var_dump(get_ident_post_data($x_url));
} else {
    echo "Does not start with http://identi.ca\n";
}

?>
