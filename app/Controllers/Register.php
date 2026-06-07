<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class Register extends BaseController
{

    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper(['form']);
    }
    
    public function registerData(): ResponseInterface
    {
        try {
            $countryCode = preg_replace('/[^\d+]/', '', (string) $this->request->getPost('country_code'));
            $phoneNumber = preg_replace('/\D/', '', (string) $this->request->getPost('phone_number'));
            $param = [
                'us_name'         => $this->request->getPost('username'),
                'us_email'        => $this->request->getPost('email'),
                'us_country_code' => $countryCode,
                'us_phone'        => $phoneNumber !== '' ? $phoneNumber : $this->request->getPost('phone_number'),
                'us_role_id'      => $this->request->getPost('account_type'),
                'us_password'     => password_hash($this->request->getPost('password'),PASSWORD_DEFAULT)
            ];

            if (!$this->userModel->insert($param)) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Insert failed'
                ]);
            }

            return $this->response->setJSON([
                'status'  => true,
                'message' => 'Account created successfully'
            ]);

        } catch (\Throwable $e) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status'  => false,
                    'message' => $e->getMessage()
                ]);
        }
    }
    public function usernameValidation()
    {
        $username = $this->request->getPost('username');

        $exists = $this->userModel->checkUserValidation(array('username'=> $username));

        return $this->response->setJSON([
            'exists' => $exists ? true : false,
            'csrfHash'=> csrf_hash()
        ]);
    }

    public function emailValidation()
    {
        $email = $this->request->getPost('email');

        $exists = $this->userModel->checkUserValidation(array('email'=> $email));

        return $this->response->setJSON([
            'exists' => $exists ? true : false,
            'csrfHash'=> csrf_hash()
        ]);
    }

    public function loginValidate()
    {
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user     = $this->userModel->getUserByEmail($email);

        if (!$user) {
            return $this->response->setJSON([
                'status'   => false,
                'input'    => 'email',
                'message'  => 'Email not registered',
                'csrfHash' => csrf_hash()
            ]);
        }

        if (!password_verify($password, $user['us_password'])) {
            return $this->response->setJSON([
                'status'   => false,
                'input'    => 'password',
                'message'  => 'Incorrect password',
                'csrfHash' => csrf_hash()
            ]);
        }

        session()->set([
            'user_id'   => $user['id'],
            'email'     => $user['us_email'],
            'logged_in' => true
        ]);

        return $this->response->setJSON([
            'status'   => true,
            'message'  => 'Login successful',
            'redirect' => base_url('admin/dashboard'),
            'csrfHash' => csrf_hash()
        ]);
    }
}
