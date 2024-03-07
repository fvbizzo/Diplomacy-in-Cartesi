<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas

	This file is part of webDiplomacy.

    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('IN_CODE') or die('This script can not be run by itself.');

require_once('vendor/autoload.php');

use Auth0\SDK\Auth0;

/**
 * An class which groups authentication functions
 *
 * @package Base
 */
class libOpenID
{

    private static function getAuth0($redirect_url = null)
    {
        return new Auth0([
            'domain'=>Config::$auth0conf['domain'],
            'client_id'=>Config::$auth0conf['client_id'],
            'client_secret'=>Config::$auth0conf['client_secret'],
            'redirect_uri'=>'https://'.self::fixWWWSubdomain($redirect_url ?? Config::$auth0conf['redirect_url'])
        ]);
    }

    private static function fixWWWSubdomain($redirect_url)
    {
        if( false !== strstr(strtolower($_SERVER['SERVER_NAME']), 'www.') && false === strstr(strtolower($redirect_url), 'www.') )
        {
            // The user is using a www. domain; add this in.
            $redirect_url = 'www.'.$redirect_url;
        }
        return $redirect_url;
    }

    public static $validColumns = array('given_name','family_name','nickname','name','picture','updated_at','email_verified','email','sub','aud','locale');
    public static function getUserInfo()
    {
        $auth0=self::getAuth0();
        
        return $auth0->getUser();
    }

    public static function logIn($redirect_url = null)
    {
        $auth0=self::getAuth0($redirect_url);
        
        $auth0->login();
    }

    public static function logOut($redirect_url = null)
    {
        $auth0=self::getAuth0($redirect_url);
        
        $auth0->logout();
    }
}

