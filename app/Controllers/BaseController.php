<?php

namespace App\Controllers;

use App\Models\SettingsModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    protected function getPaymentSettings(): array
    {
        $defaults = [
            'currency_code' => 'INR',
            'currency_symbol' => 'Rs',
            'tax_type' => 'exclusive',
            'tax_rate' => '18',
        ];

        try {
            $db = db_connect();
            if ($db->tableExists('settings') || $db->tableExists('system_settings')) {
                $settingsModel = new SettingsModel();
                $stored = $settingsModel->getGroupSettings('payment');
                if (is_array($stored)) {
                    $defaults = array_merge($defaults, $stored);
                }
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        return $defaults;
    }

    protected function getCurrencySymbol(): string
    {
        $settings = $this->getPaymentSettings();
        $symbol = trim((string) ($settings['currency_symbol'] ?? ''));
        return $symbol !== '' ? $symbol : 'Rs';
    }

    protected function getCurrencyCode(): string
    {
        $settings = $this->getPaymentSettings();
        $code = strtoupper(trim((string) ($settings['currency_code'] ?? '')));
        return $code !== '' ? $code : 'INR';
    }
}
