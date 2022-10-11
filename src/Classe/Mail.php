<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail
{

  private $api_key = '498f66b5c3ebf6de3123d3229369e3cd';
  private $api_key_secret = '7bd93465e876b48efda9fbbbd2c2fdcd';

  public function send($to_email, $to_name, $subject, $content)
  {
    // On instancie un nouvel objet mailjet
    $mj = new Client($this->api_key, $this->api_key_secret, true,['version' => 'v3.1']);

    // On créé le corps de notre mail
    $body = [
      'Messages' => [
          [
              'From' => [
                  'Email' => "coursudemy972@gmail.com",
                  'Name' => "La Boutique Française"
              ],
              'To' => [
                  [
                      'Email' => $to_email,
                      'Name' => $to_name
                  ]
              ],
              'TemplateID' => 4268827,
              'TemplateLanguage' => true,
              'Subject' => $subject,
              'Variables' => [
								'content' => $content
              ]
          ]
      ]
    ];
    // Envoie le mail en POST
    $response = $mj->post(Resources::$Email, ['body' => $body]);

    $response->success() && dd($response->getData());
  }

}