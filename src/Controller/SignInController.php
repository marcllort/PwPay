<?php


namespace Pw\Pay\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


final class SignInController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->usernameOK = true;
        $this->passwordOK = true;
    }

    public function showLoginFormAction(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render($response, 'login.twig', []);
    }

    public function loginAction(Request $request, Response $response): Response
    {
        // This method decodes the received json
        $data = $request->getParsedBody();

        // Error validation handling
        $errors = $this->validate($data);

        if (count($errors) > 0) {
            $response->getBody()->write(json_encode(['errors' => $errors]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $user = $data['username'];
        $password = hash('sha256', $data['password']);

        // DB user data retrieval
        $userInfo = $this->container->get('user_repository')->getUser($user);

        if ($userInfo != null) {
            if ($password == $userInfo->getPassword() && $userInfo->isActive()) {
                // Cookie logged in
                setcookie("logged", $userInfo->getId(), time() + 31536000, '/');
                if (isset($_COOKIE['logged'])) {
                    //echo "Logged in";
                    header("Location: /dashboard");
                    die();
                    return $response->withHeader('Dashboard', '/dashboard');
                    return $this->container->get('view')->render($response, 'dashboard.twig', $info);

                }
            } else {
                $this->passwordOK = false;

            }
        } else {
            $this->usernameOK = false;
        }

        $info = array(
            "username" => $data['username'],
            "usernameOK" => $this->usernameOK,
            "password" => $data['password'],
            "passwordOK" => $this->passwordOK);

        return $this->container->get('view')->render($response, 'login.twig', $info);

    }

    public function showLogoutFormAction(Request $request, Response $response): Response
    {
        $this->logoutAction($request, $response);
        return $this->container->get('view')->render($response, 'home.twig', []);
    }

    public function logoutAction(Request $request, Response $response): Response
    {
        setcookie("logged", 0, time() + 31536000, '/');
        header("Location: /");
        echo "LOGOUT";
        die();
        return $response->withHeader('Home', '/');
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (empty($data['username'])) {
            $errors['username'] = 'The username cannot be empty.';
        }

        if (empty($data['password']) || strlen($data['password']) < 6) {
            $errors['password'] = 'The password must contain at least 6 characters.';
        }
        return $errors;
    }

}