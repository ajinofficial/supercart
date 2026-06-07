<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('logged_in') === true) {
            $path = trim($request->getUri()->getPath(), '/');
            $isCustomer = (int) session()->get('us_role_id') === 2;
            if ($isCustomer && ($path === 'admin' || str_starts_with($path, 'admin/'))) {
                if ($request->isAJAX()) {
                    return service('response')->setStatusCode(403)->setJSON([
                        'status' => false,
                        'message' => 'Administrator access is required.',
                    ]);
                }

                return redirect()->to(base_url('user/dashboard'));
            }

            return null;
        }

        if (strtolower($request->getMethod()) === 'get') {
            session()->set('redirect_after_login', (string) $request->getUri());
        } else {
            session()->set('redirect_after_login', base_url('admin/dashboard'));
        }

        return redirect()->to(base_url('login'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
