<?php

require_once("config.php");

echo "My networks:<ul>";
foreach($servers as $server => $key) {
  echo "<li><a href='$server'>$server</a></li>";
}
if ($twitter_user) {
  echo "<li>Twitter as " .
       "<a href='http://twitter.com/$twitter_user'>$twitter_user</a>";
}
echo "</ul>";
?>

<a href="data">browse data</a><br/>
<a href="publish">publish</a><br/>

