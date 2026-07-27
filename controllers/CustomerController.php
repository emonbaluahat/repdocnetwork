<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthContext;
use App\Core\Request;
use App\Core\View;
use App\Core\Database;
use App\Core\Security;
use App\Models\Customer;
use App\Models\CustomerTimeline;
use App\Models\AuditLog;

class CustomerController extends Controller
{
    private function getShopId(): int
    {
        return AuthContext::shopId();
    }

    public function index(): void
    {
        $this->authorize('view_customers');
        $shopId = $this->getShopId();

        $search = trim(Request::input('search'));
        $tag = trim(Request::input('tag'));
        $page = max(1, (int) (Request::input('page') ?? 1));
        $perPage = (int) (Request::input('per_page') ?? 25);
        if ($perPage < 1 || $perPage > 100) $perPage = 25;

        $result = Customer::getByShop($shopId, $search, $tag, 'created_at DESC', $page, $perPage);

        $this->render('customers/list', [
            'customers' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'per_page' => $result['per_page'],
            'total_pages' => $result['total_pages'],
            'has_prev' => $result['has_prev'],
            'has_next' => $result['has_next'],
            'search' => $search,
            'tag' => $tag,
        ]);
    }

    public function search(): void
    {
        $this->authorize('view_customers');
        $shopId = $this->getShopId();

        $query = trim(Request::input('q'));
        $limit = min(50, (int) (Request::input('limit') ?? 20));

        if (strlen($query) < 1) {
            $this->json([]);
        }

        $customers = Customer::search($shopId, $query, $limit);
        $this->json($customers);
    }

    public function create(): void
    {
        $this->authorize('create_customers');
        $this->render('customers/form', [
            'customer' => null,
        ]);
    }

    public function store(): void
    {
        $this->authorize('create_customers');
        $shopId = $this->getShopId();

        $data = $this->validate([
            'name' => 'required|min:2|max:100',
            'phone' => 'required|phone',
            'email' => 'email|max:191',
            'nid' => 'nid',
            'address' => 'max:500',
            'notes' => 'max:2000',
        ]);

        $data['shop_id'] = $shopId;
        $data['created_by'] = AuthContext::id();

        $tags = Request::input('tags');
        if ($tags) {
            $tagList = array_map('trim', explode(',', $tags));
            $data['tags'] = json_encode($tagList, JSON_UNESCAPED_UNICODE);
        }

        $existingPhone = Customer::findByPhone($shopId, $data['phone']);
        if ($existingPhone) {
            flash('error', __('customer.phone_exists'));
            $this->back();
        }

        $customerId = Customer::create($data);

        CustomerTimeline::log($customerId, $shopId, 'created', __('customer.created_description', ['name' => $data['name']]));

        AuditLog::log('customer.created', 'customer', $customerId, null, $data, AuthContext::id(), $shopId);

        flash('success', __('customer.created'));
        $this->redirect('/customers/' . $customerId);
    }

    public function show(int $id): void
    {
        $this->authorize('view_customers');
        $shopId = $this->getShopId();

        $customer = Customer::find($id);
        if (!$customer || (int) $customer['shop_id'] !== $shopId) {
            flash('error', __('customer.not_found'));
            $this->redirect('/customers');
        }

        $timeline = Customer::getTimeline($id, $shopId);

        $this->render('customers/detail', [
            'customer' => $customer,
            'timeline' => $timeline,
        ]);
    }

    public function edit(int $id): void
    {
        $this->authorize('edit_customers');
        $shopId = $this->getShopId();

        $customer = Customer::find($id);
        if (!$customer || (int) $customer['shop_id'] !== $shopId) {
            flash('error', __('customer.not_found'));
            $this->redirect('/customers');
        }

        $this->render('customers/form', [
            'customer' => $customer,
        ]);
    }

    public function update(int $id): void
    {
        $this->authorize('edit_customers');
        $shopId = $this->getShopId();

        $customer = Customer::find($id);
        if (!$customer || (int) $customer['shop_id'] !== $shopId) {
            flash('error', __('customer.not_found'));
            $this->redirect('/customers');
        }

        $data = $this->validate([
            'name' => 'required|min:2|max:100',
            'phone' => 'required|phone',
            'email' => 'email|max:191',
            'nid' => 'nid',
            'address' => 'max:500',
            'notes' => 'max:2000',
        ]);

        $tags = Request::input('tags');
        $data['tags'] = $tags ? json_encode(array_map('trim', explode(',', $tags)), JSON_UNESCAPED_UNICODE) : null;

        $existingPhone = Customer::findByPhone($shopId, $data['phone']);
        if ($existingPhone && (int) $existingPhone['id'] !== $id) {
            flash('error', __('customer.phone_exists'));
            $this->back();
        }

        Customer::update($id, $data);

        CustomerTimeline::log($id, $shopId, 'updated', __('customer.updated_description', ['name' => $data['name']]));

        AuditLog::log('customer.updated', 'customer', $id, $customer, $data, AuthContext::id(), $shopId);

        flash('success', __('customer.updated'));
        $this->redirect('/customers/' . $id);
    }

    public function destroy(int $id): void
    {
        $this->authorize('delete_customers');
        $shopId = $this->getShopId();

        $customer = Customer::find($id);
        if (!$customer || (int) $customer['shop_id'] !== $shopId) {
            flash('error', __('customer.not_found'));
            $this->redirect('/customers');
        }

        Customer::delete($id);

        CustomerTimeline::log($id, $shopId, 'deleted', __('customer.deleted_description', ['name' => $customer['name']]));

        AuditLog::log('customer.deleted', 'customer', $id, $customer, null, AuthContext::id(), $shopId);

        flash('success', __('customer.deleted'));
        $this->redirect('/customers');
    }

    public function timeline(int $id): void
    {
        $this->authorize('view_customers');
        $shopId = $this->getShopId();

        $customer = Customer::find($id);
        if (!$customer || (int) $customer['shop_id'] !== $shopId) {
            $this->json([]);
        }

        $timeline = Customer::getTimeline($id, $shopId);
        $this->json($timeline);
    }

    public function import(): void
    {
        $this->authorize('create_customers');
        $shopId = $this->getShopId();

        if (!Request::isPost()) {
            $this->render('customers/import');
            return;
        }

        $file = Request::file('csv_file');
        if (!$file) {
            flash('error', __('customer.import_no_file'));
            $this->back();
        }

        $validation = Security::validateFile($file, ['csv'], 5242880);
        if (!$validation['valid']) {
            flash('error', implode(' ', $validation['errors']));
            $this->back();
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            flash('error', __('customer.import_read_error'));
            $this->back();
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            flash('error', __('customer.import_empty'));
            $this->back();
        }

        $headerMap = array_flip(array_map('strtolower', $headers));
        $requiredFields = ['name', 'phone'];
        $missingRequired = array_diff($requiredFields, array_keys($headerMap));
        if (!empty($missingRequired)) {
            fclose($handle);
            flash('error', __('customer.import_missing_columns', ['columns' => implode(', ', $missingRequired)]));
            $this->back();
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];

        $db = Database::getInstance();

        while (($row = fgetcsv($handle)) !== false) {
            $rowData = [];
            foreach ($headers as $i => $header) {
                $rowData[strtolower($header)] = $row[$i] ?? '';
            }

            $name = trim($rowData['name'] ?? '');
            $phone = trim($rowData['phone'] ?? '');

            if (empty($name) || empty($phone)) {
                $skipped++;
                continue;
            }

            $existing = Customer::findByPhone($shopId, $phone);
            if ($existing) {
                $skipped++;
                continue;
            }

            try {
                $customerData = [
                    'shop_id' => $shopId,
                    'name' => $name,
                    'phone' => $phone,
                    'email' => trim($rowData['email'] ?? '') ?: null,
                    'nid' => trim($rowData['nid'] ?? '') ?: null,
                    'address' => trim($rowData['address'] ?? '') ?: null,
                    'notes' => trim($rowData['notes'] ?? '') ?: null,
                    'created_by' => AuthContext::id(),
                ];

                $tags = trim($rowData['tags'] ?? '');
                if ($tags) {
                    $customerData['tags'] = json_encode(array_map('trim', explode(',', $tags)), JSON_UNESCAPED_UNICODE);
                }

                $db->insert('customers', $customerData);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = __('customer.import_row_error', ['name' => $name, 'error' => $e->getMessage()]);
            }
        }

        fclose($handle);

        AuditLog::log('customer.imported', 'customer', null, null, [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => count($errors),
        ], AuthContext::id(), $shopId);

        flash('success', __('customer.import_done', ['imported' => $imported, 'skipped' => $skipped]));
        if (!empty($errors)) {
            flash('warning', implode('<br>', array_slice($errors, 0, 5)));
        }

        $this->redirect('/customers');
    }

    public function export(): void
    {
        $this->authorize('view_customers');
        $shopId = $this->getShopId();

        $customers = Customer::findAll(['shop_id' => $shopId], 'name ASC');

        $filename = 'customers-' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['নাম', 'ফোন', 'ইমেইল', 'এনআইডি', 'ঠিকানা', 'ট্যাগ', 'নোট', 'তৈরির তারিখ']);

        foreach ($customers as $c) {
            $tags = $c['tags'] ? implode(', ', json_decode($c['tags'], true) ?? []) : '';
            fputcsv($output, [
                $c['name'],
                $c['phone'],
                $c['email'] ?? '',
                $c['nid'] ?? '',
                $c['address'] ?? '',
                $tags,
                $c['notes'] ?? '',
                $c['created_at'],
            ]);
        }

        fclose($output);
        exit;
    }
}