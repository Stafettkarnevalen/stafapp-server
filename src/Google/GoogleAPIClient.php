<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 26/05/2018
 * Time: 11.09
 */

namespace App\Google;


class GoogleAPIClient
{
    /**
     * @const APPLICATION_NAME The Google App name
     */
    const APPLICATION_NAME   = 'Groups API';

    /**
     * @const CREDENTIALS_PATH The file containing the Google App credentials
     */
    const CREDENTIALS_PATH   = __DIR__ . '/client_credentials.json';

    /**
     * @const CLIENT_SECRET_PATH The file containing the secret for the Google App credentials
     */
    const CLIENT_SECRET_PATH = __DIR__ . '/client_secret.json';

    /**
     * @const SCOPES The scopes that the Google App needs to perform tasks
     */
    const SCOPES             = [
        \Google_Service_Groupssettings::APPS_GROUPS_SETTINGS,
        \Google_Service_Directory::ADMIN_DIRECTORY_GROUP,
        \Google_Service_Directory::ADMIN_DIRECTORY_GROUP_MEMBER];

    /**
     * @const TYPE The type of the Google App access
     */
    const TYPE               = 'offline';

    /**
     * Gets an instance of the Google_Client.
     *
     * @return \Google_Client
     * @throws \Google_Exception
     */
    public static function clientInstance()
    {
        $client = new \Google_Client();
        $client->setApplicationName(self::APPLICATION_NAME);
        $client->setScopes(self::SCOPES);
        $client->setAuthConfig(self::CLIENT_SECRET_PATH);
        $client->setAccessType(self::TYPE);
        $accessToken = null;
        if (file_exists(self::CREDENTIALS_PATH)) {
            $accessToken = json_decode(file_get_contents(self::CREDENTIALS_PATH), true);
        } else {
            $accessToken = null;
        }
        $client->setAccessToken($accessToken);
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents(self::CREDENTIALS_PATH, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    /**
     * Gets an instance of the Google_Service_Directory. This is where groups and users are held.
     *
     * @return \Google_Service_Directory
     * @throws \Google_Exception
     */
    public static function serviceInstance()
    {
        $client = self::clientInstance();
        $service = new \Google_Service_Directory($client);
        return $service;
    }

}