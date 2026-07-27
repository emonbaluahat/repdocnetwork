<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthContext;
use App\Core\Request;
use App\Core\View;
use App\Core\Security;
use App\Core\Database;
use App\Models\Shop;
use App\Models\AuditLog;

class ShopSettingsController extends Controller
{
    private function getShopId(): int
    {
        return AuthContext::shopId();
    }

    public function index(): void
    {
        $this->authorize('manage_shop_settings');
        $shopId = $this->getShopId();

        $shop = Shop::find($shopId);
        if (!$shop) {
            flash('error', __('shop.not_found'));
            $this->redirect('/');
        }

        $settings = Shop::getSettings($shopId);
        $businessHours = $settings['business_hours'] ?? $this->getDefaultBusinessHours();
        $invoiceSettings = $settings['invoice'] ?? $this->getDefaultInvoiceSettings();

        $this->render('settings/shop', [
            'shop' => $shop,
            'business_hours' => $businessHours,
            'invoice_settings' => $invoiceSettings,
        ]);
    }

    public function update(): void
    {
        $this->authorize('manage_shop_settings');
        $shopId = $this->getShopId();

        $shop = Shop::find($shopId);
        if (!$shop) {
            flash('error', __('shop.not_found'));
            $this->redirect('/');
        }

        $data = $this->validate([
            'name' => 'required|min:2|max:100',
            'phone' => 'required|phone',
            'email' => 'email|max:191',
            'address' => 'max:500',
        ]);

        $logo = Request::file('logo');
        if ($logo && $logo['error'] === UPLOAD_ERR_OK) {
            $validation = Security::validateFile($logo, ['jpg', 'jpeg', 'png', 'webp'], 2097152);
            if (!$validation['valid']) {
                flash('error', implode(' ', $validation['errors']));
                $this->back();
            }

            $ext = strtolower(pathinfo($logo['name'], PATHINFO_EXTENSION));
            $filename = 'shop-' . $shopId . '-' . time() . '.' . $ext;
            $uploadPath = APP_ROOT . '/storage/uploads/logos';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            if (move_uploaded_file($logo['tmp_name'], $uploadPath . '/' . $filename)) {
                if ($shop['logo'] && file_exists(APP_ROOT . '/storage/uploads/logos/' . $shop['logo'])) {
                    @unlink(APP_ROOT . '/storage/uploads/logos/' . $shop['logo']);
                }
                $data['logo'] = $filename;
            }
        }

        $oldData = [
            'name' => $shop['name'],
            'phone' => $shop['phone'],
            'email' => $shop['email'],
            'address' => $shop['address'],
            'logo' => $shop['logo'],
        ];

        Shop::update($shopId, $data);

        $businessHours = [];
        $days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($days as $day) {
            $open = Request::input("business_hours_{$day}_open");
            $close = Request::input("business_hours_{$day}_close");
            $closed = Request::input("business_hours_{$day}_closed") === 'on';

            $businessHours[$day] = [
                'open' => $closed ? null : $open,
                'close' => $closed ? null : $close,
                'closed' => $closed,
            ];
        }

        $invoiceSettings = [
            'prefix' => Request::input('invoice_prefix') ?? 'INV',
            'show_logo' => Request::input('invoice_show_logo') === 'on',
            'show_business_hours' => Request::input('invoice_show_business_hours') === 'on',
            'footer_text' => Request::input('invoice_footer_text') ?? '',
            'terms_conditions' => Request::input('invoice_terms_conditions') ?? '',
            'tax_rate' => (float) (Request::input('invoice_tax_rate') ?? 0),
            'tax_label' => Request::input('invoice_tax_label') ?? 'VAT',
        ];

        $settings = array_merge(Shop::getSettings($shopId), [
            'business_hours' => $businessHours,
            'invoice' => $invoiceSettings,
        ]);

        Shop::updateSettings($shopId, $settings);

        AuditLog::log('shop.settings_updated', 'shop', $shopId, $oldData, $data, AuthContext::id(), $shopId);

        flash('success', __('shop.settings_updated'));
        $this->redirect('/settings/shop');
    }

    private function getDefaultBusinessHours(): array
    {
        return [
            'saturday' => ['open' => '09:00', 'close' => '21:00', 'closed' => false],
            'sunday' => ['open' => '09:00', 'close' => '21:00', 'closed' => false],
            'monday' => ['open' => '09:00', 'close' => '21:00', 'closed' => false],
            'tuesday' => ['open' => '09:00', 'close' => '21:00', 'closed' => false],
            'wednesday' => ['open' => '09:00', 'close' => '21:00', 'closed' => false],
            'thursday' => ['open' => '09:00', 'close' => '21:00', 'closed' => false],
            'friday' => ['open' => '14:00', 'close' => '21:00', 'closed' => false],
        ];
    }

    private function getDefaultInvoiceSettings(): array
    {
        return [
            'prefix' => 'INV',
            'show_logo' => true,
            'show_business_hours' => true,
            'footer_text' => 'Thank you for your business!',
            'terms_conditions' => 'Goods once sold cannot be returned or exchanged.',
            'tax_rate' => 0,
            'tax_label' => 'VAT',
        ];
    }
}