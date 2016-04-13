<?php
require_once 'lib/RedisPHPlay.php';
require_once('lib/Phirehose.php');
require_once('lib/OauthPhirehose.php');

$redis = new RedisManager();
$client = $redis->connect('127.0.0.1', 6379);
var_dump($client);
class DynamicTrackConsumer extends OauthPhirehose
{
  /**
   * Subclass specific attribs
   */
  /**
   * Enqueue each status
   *
   * @param string $status
   */
  public function enqueueStatus($status)
  {
    global $client, $count;
	$data = json_decode($status, true);
	if( is_array($data) ) {
		if( !isset($data['text']) ) {
			// deleted
		} else {
			$client->LPUSH('tweets', $status);
		}
	}
	$count++;
	if( $count >= 100 ) {
		$count = 0;
		ob_flush();
	}
  }
  /**
   * In this example, we just set the track words to a random 2 words. In a real example, you'd want to check some sort
   * of shared medium (ie: memcache, DB, filesystem) to determine if the filter has changed and set appropriately. The
   * speed of this method will affect how quickly you can update filters.
   */
  public function checkFilterPredicates() 
  {
	  global $client;
    // This is all that's required, Phirehose will detect the change and reconnect as soon as possible
    $tags = $client->LRANGE('tags', 0, -1);
    $this->setTrack($tags);
  }
}
// The OAuth credentials you received when registering your app at Twitter
define("TWITTER_CONSUMER_KEY", "pHjQFkeH4ctb81IQs0bpIe9cd");
define("TWITTER_CONSUMER_SECRET", "BKfGCBkJDb9TeM0vH65gBExp78K6K9I08IjUn1e0bL5CgstVa9");
// The OAuth data for the twitter account
define("OAUTH_TOKEN", "182698622-g8lw6bo4F3RTh07k0mqjq2wdVmXtu6nFGSN6WKuO");
define("OAUTH_SECRET", "q9mDN80Be51MVJFAvYlRQcGfHMc8ssH3wDir5pBSepsHZ");
// Start streaming
$sc = new DynamicTrackConsumer(OAUTH_TOKEN, OAUTH_SECRET, Phirehose::METHOD_FILTER);
$sc->setLocations(array(array(-180,-90,180,90)));
$sc->consume();