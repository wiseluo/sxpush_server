<?php
namespace App\HttpController\Push\Platform\Ios;

require_once dirname(__FILE__) . '/ApnsPHP/Autoload.php';

use App\HttpController\Push\Platform\Gateway;

use ApnsPHP_Push;
use ApnsPHP_Abstract;
use ApnsPHP_Message;

class IosGateway extends Gateway
{
    private $entrust_root_certification_authority = EASYSWOOLE_ROOT.'/Public/File/entrust_root_certification_authority.pem';

    public function push($token, $notification, $options)
    {
        try {
            if($options['apns_production'] == true) {
                $nEnvironment = ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION;
                $sProviderCertificateFile = EASYSWOOLE_ROOT . '/Public/'. $this->config->get('ios_cert_production');
            }else{
                $nEnvironment = ApnsPHP_Abstract::ENVIRONMENT_SANDBOX;
                //'server_certificates_bundle_sandbox.pem'
                $sProviderCertificateFile = EASYSWOOLE_ROOT . '/Public/'. $this->config->get('ios_cert_sandbox');
            }

            // Instanciate a new ApnsPHP_Push object
            $push = new ApnsPHP_Push($nEnvironment, $sProviderCertificateFile);

            // Set the Provider Certificate passphrase
            // $push->setProviderCertificatePassphrase('test');

            // Set the Root Certificate Autority to verify the Apple remote peer  entrust_root_certification_authority.pem 委托根授权证书
            $push->setRootCertificationAuthority($this->entrust_root_certification_authority);

            // Increase write interval to 100ms (default value is 10ms).
            // This is an example value, the 10ms default value is OK in most cases.
            // To speed up the sending operations, use Zero as parameter but
            // some messages may be lost.
            // $push->setWriteInterval(100 * 1000);

            // Connect to the Apple Push Notification Service
            $push->connect();

            foreach($token as $k => $v) {
                // Instantiate a new Message with a single recipient
                $message = new ApnsPHP_Message($v);

                // Set a custom identifier. To get back this identifier use the getCustomIdentifier() method
                // over a ApnsPHP_Message object retrieved with the getErrors() message.
                $message->setCustomIdentifier(sprintf("Message-Badge-%03d", $notification['badge']));

                // Set badge icon to "1"
                $message->setBadge((int)$notification['badge']);

                // Set a simple welcome title
                if(isset($notification['title'])) {
                    $message->setTitle($notification['title']);
                }

                // Set a simple welcome text
                $message->setText($notification['alert']);

                // Play the default sound
                $message->setSound($notification['sound']);

                if(isset($notification['extras']) && count($notification['extras']) > 0) {
                    foreach($notification['extras'] as $m => $n) {
                        // Set a custom property
                        $message->setCustomProperty($m, $n);
                    }
                }
                
                // Add the message to the message queue
                $push->add($message);
            }

            // Send all messages in the message queue
            $push->send();

            // Disconnect from the Apple Push Notification Service
            $push->disconnect();

            // Examine the error message container
            $aErrorQueue = $push->getErrors();
            if (!empty($aErrorQueue)) {
                var_dump($aErrorQueue);
                return ['status' => 0, 'msg' => 'ios '. $aErrorQueue];
            }
            return ['status' => 1, 'msg' => 'ios success'];
        }catch(\Exception $e) {
            var_dump($e->getMessage());
            return ['status' => 0, 'msg' => 'ios '. $e->getMessage()];
        }
    }

}