<pre>
										  :                      :
										  ::                    ::
										  ::`.     .-""-.     .'::
										  : `.`-._ : '>': _.-'.' :
										  :`. `=._`'.  .''_.=' .':
										   : `=._ `- '' -' _.-'.:
											:`=._`=.    .='_.=':
											 `.._`.      .'_..'                
											   `-.:      :.-'
												  :      :
												  `:.__.:'
												   :    :
												  -'=  -'=                                           
						
											  Simplon/Twitter
</pre>

-------------------------------------------------

# Intro

### What is simplon/twitter?

It's a library which helps you to communicate with twitter's api. In particular it will let you generate ```access tokens``` and publish/interact with a tweet.

### Any dependencies?

- PHP 5.4
- CURL

-------------------------------------------------

# Install

Easy install via composer. Still no idea what composer is? Inform yourself [here](http://getcomposer.org).

```json
{
    "require": {
        "simplon/twitter": "*"
    }
}
```

-------------------------------------------------

# Requirements

In order to interact with twitter's api you need to register your app in order to receive a ```consumer key``` and a ```consumer secret```. [Register your app](https://apps.twitter.com/) at twitter's application management.

Eventually you should receive something similar to the following example:

__Consumer key (apiKey):__ ```E3yaXX2LvOFXXXXikwMUyvlQD```  
__Consumer secret (apiSecret):__ ```WkhXXZPbyFdLiHqDCkSEqJodxm5XdIPLuFNzs1sXXdv09ZXXX7```

-------------------------------------------------

# Request Access Tokens

The following lines will describe how to generate a pair of ```access tokens``` which are needed for twitter's api communication. If anything goes wrong a ```TwitterException``` will be thrown.

### 1. Oauth Request Token

Just follow-up with the code below. You should receive a class (```OauthRequestTokenVo```) which holds among other things an ```OauthToken```. Call ```Twitter::getAuthenticationUrl()``` by passing in the mentioned token. As a result you will receive a ```URL``` - redirect the user to this url. The user will end up at twitter's authentication page and will grant you permission to access his/her profile.

```php
require __DIR__ . '/vendor/autoload.php';

// your app's consumer tokens
$apiKey = 'E3yaXX2LvOFXXXXikwMUyvlQD';
$apiSecret = 'WkhXXZPbyFdLiHqDCkSEqJodxm5XdIPLuFNzs1sXXdv09ZXXX7';

// where should twitter redirect the user?
$callbackUrl = 'https://yourdomain.com/twitter';

// twitter session with your credentials
$twitter = new \Simplon\Twitter\Twitter($apiKey, $apiSecret);

try
{
    $oauthRequestTokenVo = $twitter->requestOauthRequestToken($callbackUrl);

    echo "Twitter redirect url:\n";
    echo $twitter->getAuthenticationUrl($oauthRequestTokenVo->getOauthToken());
    echo "\n\n";
}
catch (\Simplon\Twitter\TwitterException $e)
{
    var_dump($e->getMessage());
}
```

### 2. Oauth Access Token

When the user gave you permissions twitter will redirect to your prior defined ```callback url``` by attaching two GET parameters which looks something like the following example: ```?oauth_token=AuNV9QXXXXXXelElAAABT0C1Sw4&oauth_verifier=UpVzmg0tiNXXXwuddcmIxgIb5PVuSn```.

You will need both parameters to receive the ```user's profile data``` and ```access tokens``` in order to interact with his/her twitter account. The received data will be passed back as a ```OauthAccessTokenVo``` class.

__YOU NEED BOTH ACCESS TOKENS: Make sure to save all data away for later use. For instance into a database.__

```php
require __DIR__ . '/vendor/autoload.php';

// your app's consumer tokens
$apiKey = 'E3yaXX2LvOFXXXXikwMUyvlQD';
$apiSecret = 'WkhXXZPbyFdLiHqDCkSEqJodxm5XdIPLuFNzs1sXXdv09ZXXX7';

// retrieved params
$oauthToken = 'AuNV9QXXXXXXelElAAABT0C1Sw4';
$oauthVerifier = 'UpVzmg0tiNXXXwuddcmIxgIb5PVuSn';

// twitter session with your credentials
$twitter = new \Simplon\Twitter\Twitter($apiKey, $apiSecret);

try
{
	// retrieve access tokens and profile data from user
	$oauthAccessTokenVo = $twitter->requestOauthAccessToken($oauthToken, $oauthVerifier);
	
	var_dump($oauthAccessTokenVo);
   
   // var_dump result would look something like this:
    
    // class Simplon\Twitter\OauthAccessTokenVo#4 (5) {
	// protected $oauthToken =>
	// string(50) "3197060333-xxxx4chX0Sega3iMF0r55PP96BAGyXXXFTwjpgW"
	// protected $oauthTokenSecret =>
	// string(45) "FeIpfZ1qK4jTaKXXXXTaQAlfny0dFgBV4K15vbnFd3XX"
	// protected $userId =>
	// string(10) "1234567899"
	// protected $screenName =>
	// string(12) "foobar user"
	// protected $xAuthExpires =>
	// string(1) "0"
	// }
}
catch (\Simplon\Twitter\TwitterException $e)
{
    var_dump($e->getMessage());
}
```

That's all there is. Go ahead and try to interact with twitter's api.

-------------------------------------------------

# Interacting with Twitter's API

The following examples will show you how to publish, read and to destroy a tweet. [Visit twitter's dev center](https://dev.twitter.com/rest/public) to see all api resources.

Simplon/Twitter supports now as well ```media uploads```. An example can be found at the end of the other examples.

### 1. Publish a tweet

```php
require __DIR__ . '/vendor/autoload.php';

// your app's consumer tokens
$apiKey = 'E3yaXX2LvOFXXXXikwMUyvlQD';
$apiSecret = 'WkhXXZPbyFdLiHqDCkSEqJodxm5XdIPLuFNzs1sXXdv09ZXXX7';

// user's prior retrieved access tokens
$accessToken = '3197060333-xxxx4chX0Sega3iMF0r55PP96BAGyXXXFTwjpgW';
$accessSecret = 'FeIpfZ1qK4jTaKXXXXTaQAlfny0dFgBV4K15vbnFd3XX';

// twitter session with your credentials
$twitter = new \Simplon\Twitter\Twitter($apiKey, $apiSecret);

try
{
	// bind user account to twitter session
	$twitter->setOauthTokens($accessToken, $accessSecret);
	
	// publish tweet
	$response = $twitter->post('statuses/update', ['status' => 'My first tweet!']);
	
	// should hold all tweet specific data in an array
	var_dump($response); // $response['id'] holds the tweet ID
}
catch (\Simplon\Twitter\TwitterException $e)
{
    var_dump($e->getMessage());
}
```

### 2. Read a tweet

You will need a ```tweet ID``` in order to load its data.

```php
require __DIR__ . '/vendor/autoload.php';

// your app's consumer tokens
$apiKey = 'E3yaXX2LvOFXXXXikwMUyvlQD';
$apiSecret = 'WkhXXZPbyFdLiHqDCkSEqJodxm5XdIPLuFNzs1sXXdv09ZXXX7';

// user's prior retrieved access tokens
$accessToken = '3197060333-xxxx4chX0Sega3iMF0r55PP96BAGyXXXFTwjpgW';
$accessSecret = 'FeIpfZ1qK4jTaKXXXXTaQAlfny0dFgBV4K15vbnFd3XX';

// twitter session with your credentials
$twitter = new \Simplon\Twitter\Twitter($apiKey, $apiSecret);

try
{
	// bind user account to twitter session
	$twitter->setOauthTokens($accessToken, $accessSecret);
	
	// load tweet data by ID
	$response = $twitter->get('statuses/show/633611781445959680');
	
	// should hold all tweet specific data in an array
	var_dump($response);
}
catch (\Simplon\Twitter\TwitterException $e)
{
    var_dump($e->getMessage());
}
```

### 3. Destroy a tweet

You will need a ```tweet ID``` in order to destroy it.

```php
require __DIR__ . '/vendor/autoload.php';

// your app's consumer tokens
$apiKey = 'E3yaXX2LvOFXXXXikwMUyvlQD';
$apiSecret = 'WkhXXZPbyFdLiHqDCkSEqJodxm5XdIPLuFNzs1sXXdv09ZXXX7';

// user's prior retrieved access tokens
$accessToken = '3197060333-xxxx4chX0Sega3iMF0r55PP96BAGyXXXFTwjpgW';
$accessSecret = 'FeIpfZ1qK4jTaKXXXXTaQAlfny0dFgBV4K15vbnFd3XX';

// twitter session with your credentials
$twitter = new \Simplon\Twitter\Twitter($apiKey, $apiSecret);

try
{
	// bind user account to twitter session
	$twitter->setOauthTokens($accessToken, $accessSecret);
	
	// destroy tweet by ID
	$response = $twitter->get('statuses/destroy/633611781445959680');
	
	// should hold all tweet specific data in an array
	var_dump($response);
}
catch (\Simplon\Twitter\TwitterException $e)
{
    var_dump($e->getMessage());
}
```

### 4. Uploand and tweet an image

```php
require __DIR__ . '/vendor/autoload.php';

// your app's consumer tokens
$apiKey = 'E3yaXX2LvOFXXXXikwMUyvlQD';
$apiSecret = 'WkhXXZPbyFdLiHqDCkSEqJodxm5XdIPLuFNzs1sXXdv09ZXXX7';

// user's prior retrieved access tokens
$accessToken = '3197060333-xxxx4chX0Sega3iMF0r55PP96BAGyXXXFTwjpgW';
$accessSecret = 'FeIpfZ1qK4jTaKXXXXTaQAlfny0dFgBV4K15vbnFd3XX';

// twitter session with your credentials
$twitter = new \Simplon\Twitter\Twitter($apiKey, $apiSecret);

try
{
	// bind user account to twitter session
	$twitter->setOauthTokens($accessToken, $accessSecret);
	
	// upload image
	$response = $twitter->upload('http://example-image.png'); 

	// should hold all media specific data in an array
	var_dump($response); // $response['media_id'] holds the media ID
	
	// publish tweet w/ media
	$response = $twitter->post('statuses/update', ['status' => 'Crazy summer vacation!', 'media_ids' => $response['media_id']);
	
	// should hold all tweet specific data in an array
	var_dump($response); // $response['id'] holds the tweet ID
}
catch (\Simplon\Twitter\TwitterException $e)
{
    var_dump($e->getMessage());
}
```
-------------------------------------------------

# License
simplon/twitter is freely distributable under the terms of the MIT license.

Copyright (c) 2015 Tino Ehrich ([tino@bigpun.me](mailto:tino@bigpun.me))

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.