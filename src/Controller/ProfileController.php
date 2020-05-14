<?php

declare(strict_types=1);

namespace Pw\Pay\Controller;


use Imagick;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Pw\Pay\Model\User;

final class ProfileController
{
    private ContainerInterface $container;
    private const UPLOADS_DIR = '/app/public/uploads/';
    private const UNEXPECTED_ERROR = "An unexpected error occurred uploading the file '%s'...";
    private const INVALID_EXTENSION_ERROR = "The received file extension '%s' is not valid";
    // We use this const to define the extensions that we are going to allow
    private const ALLOWED_EXTENSIONS = ['png', 'PNG'];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showProfile(Request $request, Response $response): Response
    {
        $userInfo = $this->container->get('user_repository')->getUserWithCookie((int)$_COOKIE['logged']);
        $data['email'] = $userInfo->getEmail();
        $data['birthday'] = $userInfo->getBirthday();
        $data['phone'] = $userInfo->getPhone();


        $dirname = $_SERVER['DOCUMENT_ROOT'] . '/uploads/*.*';

        $files = glob($dirname);
        $imageok = false;
        for ($i = 0; $i < count($files); $i++) {
            $image = $files[$i];

            if (similar_text($image, $userInfo->getId() . ".png") > 4) {
                $imageok = true;
                $data['imageName'] = "/uploads/" . $userInfo->getId() . ".png";
            }
        }


        $data['imageOk'] = $imageok;


        return $this->container->get('view')->render(
            $response,
            'profile.twig',
            $data
        );
    }

    public function showProfileSecurity(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render(
            $response,
            'profileSecurity.twig',
            []
        );
    }

    public function profileAction(\Slim\Psr7\Request $request, \Slim\Psr7\Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            $response = $this->uploadFileAction($request, $response);

        } catch (\mysql_xdevapi\Exception $exception) {
            $response->getBody()
                ->write('Unexpected error: ' . $exception->getMessage());
            return $response->withStatus(500);
        }

        return $response->withStatus(201);
    }

    public function profileSecurityAction(\Slim\Psr7\Request $request, \Slim\Psr7\Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $userInfo = $this->container->get('user_repository')->getUserWithCookie((int)$_COOKIE['logged']);
            $password = hash('sha256', $data['passwordOld']);
            $info['passwordOldOK'] = false;
            $info['passwordOK'] = false;
            if ($userInfo != null) {
                if ($password == $userInfo->getPassword()) {
                    $info['passwordOldOK'] = true;
                }
                if ($this->validatePassword($data['password']) && $data['password'] == $data['password2']) {
                    $info['passwordOK'] = true;
                }
                if ($info['passwordOldOK'] && $info['passwordOK']) {
                    $newPassword = hash('sha256', $data['password']);
                    $this->container->get('user_repository')->savePassword($userInfo->getEmail(), $newPassword);
                }
                return $this->container->get('view')->render(
                    $response,
                    'profileSecurity.twig',
                    $info
                );
            }

        } catch (\mysql_xdevapi\Exception $exception) {
            $response->getBody()
                ->write('Unexpected error: ' . $exception->getMessage());
            return $response->withStatus(500);
        }

        return $response->withStatus(201);
    }

    public function uploadFileAction(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $errors = [];

        clearstatcache();

        if (isset($_FILES['file']['name'][1])) {

            $extension = explode('.', $_FILES['file']['name'])[1];
            if (isset($extension)) {

                if (!$this->isValidFormat($extension)) {
                    echo "ERROR! Bad format";
                    $errors[] = sprintf(self::INVALID_EXTENSION_ERROR, $extension);
                    return $this->container->get('view')->render($response, 'profile.twig', $data);
                }

                $size = filesize($_FILES['file']['tmp_name']);

                if ($size == 0) {
                    echo "too large";
                    return $this->container->get('view')->render($response, 'profile.twig', $data);
                }

                // Resize
                $image = $_FILES["file"]["tmp_name"];
                $imagick = new Imagick(realpath($image));
                $imagick->resizeImage(400, 400, Imagick::FILTER_LANCZOS, 1, true);
                $targetPath = self::UPLOADS_DIR . $_COOKIE['logged'] . '.png';
                $imagick->writeImage($targetPath);

            }
        }
        $userInfo = $this->container->get('user_repository')->getUserWithCookie((int)$_COOKIE['logged']);
        $dataOut['email'] = $userInfo->getEmail();
        $dataOut['birthday'] = $userInfo->getBirthday();
        $dataOut['phone'] = $userInfo->getPhone();


        $dirname = $_SERVER['DOCUMENT_ROOT'] . '/uploads/*.*';

        $files = glob($dirname);
        $imageok = false;
        for ($i = 0; $i < count($files); $i++) {
            $image = $files[$i];

            if (similar_text($image, $userInfo->getId() . ".png") > 3) {
                $imageok = true;
                $dataOut['imageName'] = "/uploads/" . $userInfo->getId() . ".png";
            }
        }

        $dataOut['imageOk'] = $imageok;

        $dataOut['phoneOK'] = $this->validatePhone($data['phone']);
        if ($dataOut['phoneOK']) {
            $this->container->get('user_repository')->savePhone($data['email'], $data['phone']);
            $dataOut['phone'] = $data['phone'];
        }
        $dataOut ['errors'] = $errors;

        return $this->container->get('view')->render($response, 'profile.twig', $dataOut);
    }


    function resizeImage($imagePath, $width, $height, $filterType, $blur, $bestFit)
    {
        //The blur factor where > 1 is blurry, < 1 is sharp.
        $imagick = new Imagick(realpath($imagePath));

        $imagick->resizeImage($width, $height, $filterType, $blur, $bestFit);
        $imagick->writeImage('new_image.png');
        header("Content-Type: image/jpg");
        echo $imagick->getImageBlob();
    }

    private function isValidFormat(string $extension): bool
    {
        return in_array($extension, self::ALLOWED_EXTENSIONS, true);
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
}