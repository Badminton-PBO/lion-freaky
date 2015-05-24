<?php namespace App\Http\Controllers\Auth;

use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Auth\UserProvider;
use App\User;

class ToernooiNlUserProvider implements UserProvider {

    protected $model;

    public function __construct(UserContract $model)
    {
        $this->model = $model;
    }

    public function retrieveById($identifier)
    {

        return new User($identifier."+ID","");
    }

    public function retrieveByToken($identifier, $token)
    {
        return new User("TOKEN","TOKEN");
    }

    public function updateRememberToken(UserContract $user, $token)
    {
        //not needed
    }

    public function retrieveByCredentials(array $credentials)
    {
        //dd("aaa:".$credentials['username']);
        return new User($credentials['username'],"CRED");

    }

    public function validateCredentials(UserContract $user, array $credentials)
    {
        $plain = $credentials['password'];
        $username = $credentials['username'];
        $USER_AGENT='Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/41.0.2272.76 Chrome/41.0.2272.76 Safari/537.36';
        $LOGIN_STRING='__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=bVJyNakpPV0f4usZzwuN0RFVXjxNPOBoGtK6FEtcdTPSG444DYCeMxjPP%2BiPDCaICHQ%2F1dZqZeZzeXeywJGGihN0%2Fp8gYzn6OTPl87P4FkPqL58X95uuuiVhxEQX4%2FJWFtdYbgx2D9OTVR0bC5jkB%2FVU116v4UcjRXIqUnmyLwgbSmnnBKWyv6ozI718LKmcj7rg3HPgGbq8Yikllj2288lkzCDxVAJuS9MP%2BbWpLQere1BO8qdTyI10Kh8xT%2FbYdW1DJ2PIKaKG08Hho%2F8ynCX1tyfPnrXkp%2BSY96qgh5749ag3&__VIEWSTATEGENERATOR=625BA342&__EVENTVALIDATION=J835wQEgbd7lzA11VgzazffOVk1r%2BXIF%2Bmh7%2FskUlhLI18g6BjbRZrR39RELNs8I4ZS%2BV3Pg7rIB9%2FyiS%2FhudynPEygQu74u0hjhQja2FjJ5FoZPp6ItBWcVl4t4UY%2B88JU4j%2BXvrzja8NzwUIa%2BeUPiAsWG9G3pbdtKmEDVoxQnULUdmeGnRZeirmYN%2Fl7zAQ1jzO%2B73Z47Y%2BdZhP15McNE99cMoyoas8wCWpypwsyB%2F3nIghBD2sZQKVk5hKQUcR7PwQ%3D%3D&tbxSearchQuery=Zoek...&ctl00%24ctl00%24ctl00%24cphPage%24ddlSearchType=1&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24UserName='.$username.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24Password='.$plain.'&ctl00%24ctl00%24ctl00%24cphPage%24cphPage%24cphPage%24pnlLogin%24LoginButton=Inloggen';

        // create curl resource
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $USER_AGENT);
        curl_setopt($ch, CURLOPT_URL, "https://toernooi.nl/member/login.aspx");
        //set cookie to bypass "do you accept cookies" warning
        curl_setopt($ch, CURLOPT_COOKIE, "st=c=1");
        //return all http header and cookies
        curl_setopt($ch, CURLOPT_HEADER, 1);
        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //follow 302 redirects doesn't work good enough because cookie sessionid are not added to the new request
        // so need to manually do redirection
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

        if (PHP_VERSION_ID > 50500) {
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, TRUE);//PHP5.5 only option
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $LOGIN_STRING);

        //CURL DEBUG init
        //curl_setopt($ch, CURLOPT_VERBOSE, true);
        //$verbose = fopen('php://memory', 'rw+');
        //curl_setopt($ch, CURLOPT_STDERR, $verbose);

        // $output contains the output string
        $logonResponse1 = curl_exec($ch);  //should be a 302

        //Parse the cookies out of the response
        preg_match_all('|Set-Cookie: (.*);|U', $logonResponse1, $results);
        $cookies = implode(';', $results[1]);

        //Parse redirect URL out of response
        preg_match_all('|Location: (.*)|', $logonResponse1, $results);
        if (empty($results) || empty($results[1]) || empty($results[1][0]))
            return false;
        $redirectUrl = trim($results[1][0]);

        //Do second call
        curl_setopt($ch, CURLOPT_URL, "https://toernooi.nl".$redirectUrl);
        curl_setopt($ch, CURLOPT_COOKIE, "st=c=1; ".$cookies);
        curl_setopt($ch, CURLOPT_HTTPGET,true);
        $logonResponse2 = curl_exec($ch);
        //dd($logonResponse2);

        //CURL DEBUG dump
        //rewind($verbose);
        //$verboseLog = stream_get_contents($verbose);
        //dd($verboseLog);


        //TODO check for PBO competition id
        return true;
    }

}