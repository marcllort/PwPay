<?php

declare(strict_types=1);

namespace Pw\Pay\Controller;

use DateTime;
use Psr\Container\ContainerInterface;
use Pw\Pay\Model\User;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


final class SignUpController
{
    private ContainerInterface $container;
    /**
     * @var bool
     */
    private bool $emailOK;
    /**
     * @var bool
     */
    private bool $passwordOK;
    /**
     * @var bool
     */
    private bool $birthdayOK;
    /**
     * @var bool
     */
    private bool $phoneOK;
    /**
     * @var bool
     */
    private bool $emailRepeated;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->emailOK = false;
        $this->passwordOK = false;
        $this->birthdayOK = false;
        $this->phoneOK = false;
        $this->emailRepeated = false;
        $this->phoneBad = false;
    }

    public function showRegisterFormAction(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render($response, 'register.twig', []);
    }

    public function registerAction(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            $this->emailOK = $this->validateEmail($data['email']);
            $userInfo = $this->container->get('user_repository')->getUser($data['email']);
            //We check if the email is already in use
            if ($userInfo != null) {
                $this->emailRepeated = true;
            }

            $this->passwordOK = $this->validatePassword($data['password']);
            $this->phoneOK = $this->validatePhone($data['phone']);
            if ($data['birthday']) {
                $this->birthdayOK = $this->validateBirthday(date('Y-m-d H:i:s', strtotime($data['birthday'])));
            }


            if ($this->emailOK && $this->passwordOK && $this->phoneOK && $this->birthdayOK && !$this->emailRepeated) {
                $birthday = date('Y-m-d H:i:s', strtotime($data['birthday']));
                $user = User::withParams(
                    $data['email'],
                    $data['password'],
                    $data['phone'],
                    $birthday,
                    new DateTime(),
                    new DateTime(),
                    false,
                    0.0
                );

                $this->container->get('user_repository')->save($user);

                $this->createTokenSendMail($data['email']);


                return $response->withHeader('Home', '/');
            } else {

                $info = array(
                    "email" => $data['email'],
                    "emailOK" => $this->emailOK,
                    "emailRepeted" => $this->emailRepeated,
                    "password" => $data['password'],
                    "passwordOK" => $this->passwordOK,
                    "phone" => $data['phone'],
                    "phoneOK" => $this->phoneOK,
                    "phoneBad" => $this->phoneBad,
                    "birthday" => $data['birthday'],
                    "birthdayOK" => $this->birthdayOK);

                return $this->container->get('view')->render($response, 'register.twig', $info);
            }


        } catch (\mysql_xdevapi\Exception $exception) {
            $response->getBody()
                ->write('Unexpected error: ' . $exception->getMessage());
            return $response->withStatus(500);
        }

        return $response->withStatus(201);
    }

    public function createTokenSendMail($email)
    {
        $token = bin2hex(openssl_random_pseudo_bytes(10));
        $id = $this->container->get('user_repository')->getUserId($email);
        $this->container->get('user_repository')->saveToken($id, $token);
        $this->sendMail($email, $token);

        return null;
    }

    public function sendMail($email, $token)
    {
        $mail = new PHPMailer;

        $mail->isSMTP();

        $mail->Host = 'smtp.gmail.com';

        $mail->Port = 587;

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->SMTPAuth = true;

        $mail->Username = 'pwpayteam@gmail.com';

        $mail->Password = 'pwpay2020';

        $mail->setFrom('pwpayteam@gmail.com', 'Pwpay Team');

//Set an alternative reply-to address
        $mail->addReplyTo('pwpayteam@gmail.com', 'Pwpay Team');

//Set who the message is to be sent to
        $mail->addAddress($email, 'Pwpay user');

//Set the subject line
        $mail->Subject = 'Pwpay Activation';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
        $mail->Body = "
        <p>Hi,</p>
        <p>            
        Thanks for choosing PwPay!
        </p>
        <p>
        To validate your account, please click <a href=\"http://pw.pay:8030/activate?token=$token\">here</a>.  If you did not initiate this request,
        please disregard this message.
        </p>
        <p>
        With regards,
        <br>
        The PwPay Team
        </p>";
//Replace the plain text body with one created manually
        $mail->AltBody = 'To validate your account, please click http://pw.pay:8030/activate?token= $token.  
        If you did not initiate this request,
        please disregard this message.';

//send the message, check for errors
        if (!$mail->send()) {
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo "<script>
                alert('An activation mail has been sent to your email adress, click the link in that mail to activate your account.');
                window.location.href='/'
                </script>";
        }

    }

    public function activate(Request $request, Response $response, $args)
    {
        try {
            $paramValue = $request->getQueryParams();

            if ($paramValue['token'] != null) {
                $id = $this->container->get('user_repository')->getUserIdFromToken($paramValue['token']);
                if ($id != null) {
                    $this->container->get('user_repository')->activate($id);
                    // TODO - Validate if already active, then show error
                    return $this->container->get('view')->render($response, 'tokenok.twig', []);
                }
            }
            return $this->container->get('view')->render($response, 'tokenko.twig', []);
        } catch (Exception $exception) {
            $response->getBody()
                ->write('Unexpected error: ' . $exception->getMessage());
            return $this->container->get('view')->render($response, 'tokenko.twig', []);
        }


    }

    private function validateEmail($email)
    {
        if (preg_match("/salle.url.edu$/", $email)) {
            return true;
        } else {
            return false;
        }
    }

    private function validatePassword($password)
    {
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number = preg_match('@[0-9]@', $password);
        //$specialChars = preg_match('@[^\w]@', $password);

        if (!$uppercase || !$lowercase || !$number || strlen($password) < 6) {
            return false;
        } else {
            return true;
        }
    }

    private function validatePhone($phone)
    {
        $phone = str_replace(' ', '', $phone);

        if (preg_match('/^\+34/', $phone)) {
            $phone = substr($phone, 3);

            $uppercase = preg_match('@[A-Z]@', $phone);
            $lowercase = preg_match('@[a-z]@', $phone);
            $specialChars = preg_match('@[^\w]@', $phone);

            if (!$uppercase && !$lowercase && !$specialChars && strlen($phone) == 9) {
                return true;
            } else {
                $this->phoneBad = true;
                return false;
            }
        } else {
            return false;
        }
    }

    private function validateBirthday($birthday)
    {
        $lowercase = preg_match('@[a-z]@', $birthday);
        $birthday = new DateTime($birthday);
        $now = new DateTime();
        $age = $now->diff(($birthday));
        if ($lowercase || $age->y > 18) {
            return true;
        } else {
            return false;
        }
    }
}