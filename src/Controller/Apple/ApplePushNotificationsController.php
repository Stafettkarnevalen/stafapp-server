<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 14/06/2018
 * Time: 20.39
 */

namespace App\Controller\Apple;
use App\Controller\Traits\RESTFulControllerTrait;
use App\Entity\Security\User;
use App\Entity\Security\UserProfile;
use FOS\RestBundle\Controller\FOSRestController;
use JWage\APNS\Certificate;
use JWage\APNS\Client;
use JWage\APNS\Sender;
use JWage\APNS\SocketClient;
use Psr\Log\LoggerInterface;
use Sinergi\BrowserDetector\Os;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation as Nelmio;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class ApplePushNotificationsController
 * - Handles Apple Push Notifications
 * - API calls.
 *
 * @package App\Controller\Apple
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class ApplePushNotificationsController extends FOSRestController
{
    /** Use some REST methods */
    use RESTFulControllerTrait;

    const CERT_PATH = '/src/Resources/ApplePushNotifications/Safari/certs/StafAppWeb.p12';
    const PEM_PATH = '/src/Resources/ApplePushNotifications/Safari/certs/StafAppWeb.pem';
    const CERT_PASS = 'Uvregnnxtv6e5j9';
    const WEBSITE_JSON_PATH = '/src/Resources/ApplePushNotifications/Safari/json/website.json';
    const APNS_SERVER = 'gateway.push.apple.com';

    static $PKG_FILE_NAMES = [
        'icon.iconset/icon_16x16.png',
        'icon.iconset/icon_16x16@2x.png',
        'icon.iconset/icon_32x32.png',
        'icon.iconset/icon_32x32@2x.png',
        'icon.iconset/icon_128x128.png',
        'icon.iconset/icon_128x128@2x.png',
        'website.json'
    ];

    /**
     *
     *
     * @Route("/safaripush/v1/devices/{deviceToken}/registrations/{websitePushId}",
     *     methods={"POST", "GET"},
     *     name="apple.apns_register_device_permission1")
     * @Route("/safaripush/v2/devices/{deviceToken}/registrations/{websitePushId}",
     *     methods={"POST", "GET"},
     *     name="apple.apns_register_device_permission2")
     *
     * @SWG\Patch(
     *     path="/v1/devices/{deviceToken}/registrations/{websitePushId}",
     *     summary="Registers or updates a permission for a device",
     *     description="Registers or updates a permission fo a device using a device token and an authentication token that was created in getPushPackage.",
     *     operationId="registerDevicePermission",
     *     @SWG\Response(
     *         response=Response::HTTP_OK,
     *         description="Returned the updated persmission",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=UserProfile::class, groups={"Authenticated","Default"})
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="deviceToken",
     *         in="path",
     *         type="string",
     *         description="the id for the device"
     *     ),
     *     @SWG\Parameter(
     *         name="websitePushId",
     *         in="path",
     *         type="string",
     *         description="the push id of this website"
     *     )
     * )
     *
     * @SWG\Patch(
     *     path="/v2/devices/{deviceToken}/registrations/{websitePushId}",
     *     summary="Registers or updates a permission for a device",
     *     description="Registers or updates a permission fo a device using a device token and an authentication token that was created in getPushPackage.",
     *     operationId="registerDevicePermission",
     *     @SWG\Response(
     *         response=Response::HTTP_OK,
     *         description="Returned the updated persmission",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=UserProfile::class, groups={"Authenticated","Default"})
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="deviceToken",
     *         in="path",
     *         type="string",
     *         description="the id for the device"
     *     ),
     *     @SWG\Parameter(
     *         name="websitePushId",
     *         in="path",
     *         type="string",
     *         description="the push id of this website"
     *     )
     * )
     *
     * @param string $deviceToken
     * @param string $websitePushId
     * @param LoggerInterface $logger
     * @param Request $request
     * @return Response
     */
    public function registerDevicePermissionAction($deviceToken, $websitePushId, LoggerInterface $logger, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $headers = $request->headers->all();
        if (!array_key_exists('authorization', $headers))
            return $this->error('Authorization header missing from request');
        $authHeader = $headers['authorization'];

        //$logger->critical(print_r($request->headers->all(), true));
        $authToken = substr($authHeader[0], strlen('ApplePushNotifications '));
        $authToken = explode(':', $authToken);

        /** @var User $user */
        $user = $em->getRepository($authToken[0])->find($authToken[1]);

        if ($user && $authToken[1]) {
            /** @var UserProfile $profile */
            $profile = $user->getProfile();
            $data = $profile->getData();
            $apnSafari = ['deviceToken' => $deviceToken];
            $data['ApplePushNotifications']['Safari'] = $apnSafari;
            $profile->setData($data);
            $em->merge($profile);
            $em->flush();
            $logger->info('APN-Safari: registered ' . $authToken[0] . ' (' . $authToken[1] . ') with device token ' . $deviceToken);

            return new JsonResponse($data);
        }
        return new JsonResponse(['errors' => ['User not found']], Response::HTTP_NOT_FOUND);
    }

    /**
     *
     *
     * @Route("/safaripush/v1/devices/{deviceToken}/registrations/{websitePushId}",
     *     methods={"DELETE"},
     *     name="apple.apns_delete_device_permission1")
     * @Route("/safaripush/v2/devices/{deviceToken}/registrations/{websitePushId}",
     *     methods={"DELETE"},
     *     name="apple.apns_delete_device_permission2")
     *
     * @SWG\Patch(
     *     path="/v1/devices/{deviceToken}/registrations/{websitePushId}",
     *     summary="Deletes a permission for a device",
     *     description="Deletes a permission fo a device using a device token and an authentication token that was created in getPushPackage.",
     *     operationId="deleteDevicePermission",
     *     @SWG\Response(
     *         response=Response::HTTP_OK,
     *         description="Returns true if deleted, false otherwise",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=UserProfile::class, groups={"Authenticated","Default"})
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="deviceToken",
     *         in="path",
     *         type="string",
     *         description="the id for the device"
     *     ),
     *     @SWG\Parameter(
     *         name="websitePushId",
     *         in="path",
     *         type="string",
     *         description="the push id of this website"
     *     )
     * )
     *
     * @SWG\Patch(
     *     path="/v2/devices/{deviceToken}/registrations/{websitePushId}",
     *     summary="Deletes a permission for a device",
     *     description="Deletes a permission fo a device using a device token and an authentication token that was created in getPushPackage.",
     *     operationId="deleteDevicePermission",
     *     @SWG\Response(
     *         response=Response::HTTP_OK,
     *         description="Returns true if deleted, false otherwise",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=UserProfile::class, groups={"Authenticated","Default"})
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="deviceToken",
     *         in="path",
     *         type="string",
     *         description="the id for the device"
     *     ),
     *     @SWG\Parameter(
     *         name="websitePushId",
     *         in="path",
     *         type="string",
     *         description="the push id of this website"
     *     )
     * )
     *
     * @param string $deviceToken
     * @param string $websitePushId
     * @param LoggerInterface $logger
     * @param Request $request
     * @return Response
     */
    public function deleteDevicePermissionAction($deviceToken, $websitePushId, LoggerInterface $logger, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $headers = $request->headers->all();
        if (!array_key_exists('authorization', $headers))
            return $this->error('Authorization header missing from request');
        $authHeader = $headers['authorization'];

        //$logger->critical(print_r($request->headers->all(), true));
        $authToken = substr($authHeader[0], strlen('ApplePushNotifications '));
        $authToken = explode(':', $authToken);

        /** @var User $user */
        $user = $em->getRepository($authToken[0])->find($authToken[1]);

        if ($user && $authToken[1]) {
            /** @var UserProfile $profile */
            $profile = $user->getProfile();
            $data = $profile->getData();
            $data['ApplePushNotifications']['Safari'] = [];
            $profile->setData($data);
            $em->merge($profile);
            $em->flush();
            $logger->info('APN-Safari: deleted ' . $authToken[0] . ' (' . $authToken[1] . ') with device token ' . $deviceToken);

            return new JsonResponse($data);
        }

        $logger->critical('APN-Safari: user or auth token not found.');
        return new JsonResponse(['errors' => ['User not found']], Response::HTTP_NOT_FOUND);
    }


    /**
     *
     *
     * @Route("/safaripush/v1/pushPackages/{websitePushId}",
     *     methods={"POST", "GET"},
     *     name="apple.apns_fetch_push_package1")
     * @Route("/safaripush/v2/pushPackages/{websitePushId}",
     *     methods={"POST", "GET"},
     *     name="apple.apns_fetch_push_package2")
     *
     * @SWG\Patch(
     *     path="/v1/pushPackages/{websitePushId}",
     *     summary="Returns a push package for this website",
     *     description="Returns an apns puch package to use with push notifications for safari.",
     *     operationId="getPushPackage",
     *     produces={"application/zip"},
     *     @SWG\Response(
     *         response=Response::HTTP_OK,
     *         description="Returned the updated user's profile",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=UserProfile::class, groups={"Authenticated","Default"})
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="websitePushId",
     *         in="path",
     *         type="string",
     *         description="the push id of this website"
     *     ),
     *     @SWG\Parameter(
     *         name="userInfo",
     *         in="body",
     *         type="string",
     *         description="A query string containing user information"
     *     )
     * )
     *
     * @SWG\Patch(
     *     path="/v2/pushPackages/{websitePushId}",
     *     summary="Returns a push package for this website",
     *     description="Returns an apns puch package to use with push notifications for safari.",
     *     operationId="getPushPackage",
     *     produces={"application/zip"},
     *     @SWG\Response(
     *         response=Response::HTTP_OK,
     *         description="Returned the updated user's profile",
     *         @SWG\Schema(
     *             ref=@Nelmio\Model(type=UserProfile::class, groups={"Authenticated","Default"})
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="websitePushId",
     *         in="path",
     *         type="string",
     *         description="the push id of this website"
     *     ),
     *     @SWG\Parameter(
     *         name="userInfo",
     *         in="body",
     *         type="string",
     *         description="A query string containing user information"
     *     )
     * )
     *
     * @param string $websitePushId
     * @return Response
     */
    public function getPushPackageAction($websitePushId, LoggerInterface $logger, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $userInfo = $request->request->all();
        // $userInfo['userId'] = 2;

        $uid = $userInfo['userId'];
        /** @var User $user */
        $user = $em->getRepository(User::class)->find($uid);

        $zip_dir = "/tmp/apn.safari." . time();
        mkdir($zip_dir);
        $pkg_dir = $zip_dir . "/StafApp.pushpackage";
        mkdir($pkg_dir);

        $route = $request->get('_route');
        $version = ($route == 'apple.apns_fetch_push_package2') ? 2 : 1;

        $this->createRawPackage($pkg_dir, $user, $request->getHost());

        $this->addManifest($pkg_dir, $version);

        $this->addSignature($pkg_dir);

        $zip = $this->createZipFile($pkg_dir);

        /** @var Kernel $kernel */
        //$kernel = $this->get('kernel');
        //$zip = $kernel->getProjectDir() . '/temp/pp.zip';

        $response = new Response(file_get_contents($zip));
        $response->headers->set('Content-Type', 'application/zip');
//        $response->headers->set('Content-Disposition', 'inline');
//        $response->headers->set('Content-length', filesize($zip));
        return $response;
    }

    /**
     *
     * @Route("/safaripush/v1/log",
     *     methods={"POST", "GET"},
     *     name="apple.apns_log1")
     *
     * @Route("/safaripush/v2/log",
     *     methods={"POST", "GET"},
     *     name="apple.apns_log2")
     */
    public function logAction(LoggerInterface $logger, Request $request)
    {
        $logs = $request->request->all();
        foreach ($logs as $log) {
            if (is_array($log)) {
                foreach ($log as $l) {
                    $logger->critical($l);
                }
            } else {
                $logger->critical($log);
            }
        }
        return new Response();
    }

    private function createZipFile($package)
    {
        //$zip_file = "{$package}/StafApp.pushpackage.zip";
        $zip_file = "{$package}.zip";

        $zip = new \ZipArchive();
        if (!$zip->open($zip_file, \ZIPARCHIVE::CREATE)) {
            return false;
        }

        $files = self::$PKG_FILE_NAMES;
        $files[] = 'manifest.json';
        $files[] = 'signature';

        foreach ($files as $file) {
            //$zip->addFile("{$package}/StafApp.pushpackage/{$file}", "/StafApp.pushpackage/" . $file);
            $zip->addFile("{$package}/{$file}",  $file);
        }
        $zip->close();
        return $zip_file;
    }

    private function createRawPackage($dir, User $user, $host)
    {
        /** @var Kernel $kernel */
        $kernel = $this->get('kernel');

        mkdir("$dir/icon.iconset");

        foreach (self::$PKG_FILE_NAMES as $file) {
            if($file == "website.json") {
                $path = self::WEBSITE_JSON_PATH;
                $json = file_get_contents("{$kernel->getProjectDir()}{$path}");
                $fptr = fopen("$dir/$file", "w");
                fwrite($fptr, str_replace(["{AuthToken}", "{Host}"], [str_replace("\\", "\\\\", get_class($user)) . ":" . $user->getId(), $host], $json));
                fclose($fptr);
            } else {
                copy("{$kernel->getProjectDir()}/assets/images/{$file}", "{$dir}/{$file}");
            }
        }
        return true;
    }

    private function addManifest($dir, $version) {

        // Obtain SHA1 hashes of all the files in the push package
        $manifest_data = [];
        foreach (self::$PKG_FILE_NAMES as $raw_file) {
            $manifest_data[$raw_file] = (($version === 1) ?
                sha1(file_get_contents("{$dir}/$raw_file")) :
                ['hashType' => 'sha512', 'hashValue' => hash('sha512', file_get_contents("{$dir}/$raw_file"))]
            );
        }
        file_put_contents("{$dir}/manifest.json", json_encode((object)$manifest_data));

        return true;
    }

    private function addSignature($dir)
    {
        /** @var Kernel $kernel */
        $kernel = $this->get('kernel');

        $pkcs12 = file_get_contents($kernel->getProjectDir() . self::CERT_PATH);
        $certs = [];
        if(!openssl_pkcs12_read($pkcs12, $certs, self::CERT_PASS)) {
            return false;
        }
        $signature_path = "{$dir}/signature";

        try {
            $cert_data = openssl_x509_read($certs['cert']);
            $priv_key = openssl_pkey_get_private($certs['pkey'], self::CERT_PASS);
            openssl_pkcs7_sign("{$dir}/manifest.json", $signature_path, $cert_data, $priv_key, [],
                PKCS7_BINARY | PKCS7_DETACHED);//, $kernel->getProjectDir() . self::WWDRCA_PATH);

        } catch (\Exception $e) {
            file_put_contents('/tmp/exc', $e->getMessage());
        }

        // Convert the signature from PEM to DER
        $signature_pem = file_get_contents($signature_path);
        $matches = [];
        if (!preg_match('~Content-Disposition:[^\n]+\s*?([A-Za-z0-9+=/\r\n]+)\s*?-----~', $signature_pem, $matches)) {
            return false;
        }
        $signature_der = base64_decode($matches[1]);
        file_put_contents($signature_path, $signature_der);

        return true;
    }


    /**
     * @Route("/{_locale}/authenticated/users/profile/apns",
     *     options={"expose"=true},
     *     name="nav.authuser_apns")
     */
    public function sendApplePushNotificationSafariAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Kernel $kernel */
        $kernel = $this->get('kernel');

        $cert = new Certificate(file_get_contents($kernel->getProjectDir() . self::PEM_PATH));
        $client = new Client(new SocketClient($cert, 'gateway.push.apple.com', 2195));
        $sender = new Sender($client);

        /** @var User $user */
        $user = $this->getUser();
        /** @var UserProfile $profile */
        $profile = $user->getProfile();
        $token = $profile->getData()['ApplePushNotifications']['Safari']['deviceToken'];

        $sender->send($token, 'Testing StafApp', 'This is a test message', 'sv');

        return new Response('Sent');
    }
}