<?php

$x_url = "http://twitter.com/rossmulcahy/status/5286165228";

function is_twitter($url) 
{
    $pat = "http://twitter.com/";
    return strpos($url, $pat) === 0;
}

function twitter_post_id($url)
{
    $pat = "_http://twitter.com/([^/]+)/status/([\d]+)_";
    if (preg_match($pat, $url, $matches) == 1)
    {
        // print_r($matches);
        return $matches[2];
    } else {
        return false;
    }
}

function get_twitter_post_data($in_url)
{
    $id = twitter_post_id($in_url);
    if ($id !== false)
    {
        $url = "http://twitter.com/statuses/show/$id.json";
        $data = file_get_contents($url);
        $res = json_decode($data, true);
        return $res;
    } else {
        return false;
    }
}

if (is_twitter($x_url)) 
{
    echo "Starts with http://twitter.com\n";
    var_dump(get_twitter_post_data($x_url));
} else {
    echo "Does not start with http://twitter.com\n";
}

?>
