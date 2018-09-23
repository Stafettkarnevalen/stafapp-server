<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 22/06/2018
 * Time: 2.28
 */

namespace App\Entity\Communication;


use JWage\APNS\Payload;

class APNSPayLoad extends Payload
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $urlArgs = [];

    public function __construct(string $title, string $body, array $urlArgs = [])
    {
        parent::__construct($title, $body);
        $this->urlArgs = $urlArgs;
    }

    public function getPayload()
    {
        return [
            'aps' => [
                'alert' => [
                    'title' => $this->title,
                    'body' => $this->body,
                ],
                'url-args' => $this->urlArgs
            ]
        ];
    }
}