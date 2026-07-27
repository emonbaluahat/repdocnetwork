<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthContext;
use App\Core\Request;
use App\Core\View;
use App\Core\Database;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\AuditLog;

class ServiceController extends Controller
{
    private function getShopId(): int
    {
        return AuthContext::shopId();
    }

    public function index(): void
    {
        $this->authorize('view_services');
        $shopId = $this->getShopId();

        $search = trim(Request::input('search'));
        $categoryId = Request::input('category_id') ? (int) Request::input('category_id') : null;
        $status = trim(Request::input('status'));
        $page = max(1, (int) (Request::input('page') ?? 1));
        $perPage = min(100, max(1, (int) (Request::input('per_page') ?? 25)));

        $result = Service::search($shopId, $search, $categoryId, $status, 'sort_order ASC, name ASC', $page, $perPage);
        $categories = ServiceCategory::getByShop($shopId);

        $this->render('services/index', [
            'services' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'per_page' => $result['per_page'],
            'total_pages' => $result['total_pages'],
            'has_prev' => $result['has_prev'],
            'has_next' => $result['has_next'],
            'search' => $search,
            'category_id' => $categoryId,
            'status' => $status,
            'categories' => $categories,
        ]);
    }

    public function create(): void
    {
        $this->authorize('create_services');
        $shopId = $this->getShopId();

        $categories = ServiceCategory::getAsOptions($shopId);

        $this->render('services/create', [
            'categories' => $categories,
        ]);
    }

    public function store(): void
    {
        $this->authorize('create_services');
        $shopId = $this->getShopId();

        $data = $this->validate([
            'name' => 'required|min:2|max:100',
            'description' => 'max:2000',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'numeric|min:0',
            'unit' => 'max:20',
        ]);

        $categoryId = Request::input('category_id');
        if ($categoryId) {
            $category = ServiceCategory::find((int) $categoryId);
            if ($category && (int) $category['shop_id'] === $shopId) {
                $data['category_id'] = (int) $categoryId;
            }
        }

        $data['shop_id'] = $shopId;
        $data['sort_order'] = (int) (Request::input('sort_order') ?? 0);

        $serviceId = Service::create($data);

        AuditLog::log('service.created', 'service', $serviceId, null, $data, AuthContext::id(), $shopId);

        flash('success', __('service.created'));
        $this->redirect('/services');
    }

    public function show(int $id): void
    {
        $this->authorize('view_services');
        $shopId = $this->getShopId();

        $service = Service::find($id);
        if (!$service || (int) $service['shop_id'] !== $shopId) {
            flash('error', __('service.not_found'));
            $this->redirect('/services');
        }

        $this->json($service);
    }

    public function edit(int $id): void
    {
        $this->authorize('edit_services');
        $shopId = $this->getShopId();

        $service = Service::find($id);
        if (!$service || (int) $service['shop_id'] !== $shopId) {
            flash('error', __('service.not_found'));
            $this->redirect('/services');
        }

        $categories = ServiceCategory::getAsOptions($shopId);

        $this->render('services/edit', [
            'service' => $service,
            'categories' => $categories,
        ]);
    }

    public function update(int $id): void
    {
        $this->authorize('edit_services');
        $shopId = $this->getShopId();

        $service = Service::find($id);
        if (!$service || (int) $service['shop_id'] !== $shopId) {
            flash('error', __('service.not_found'));
            $this->redirect('/services');
        }

        $data = $this->validate([
            'name' => 'required|min:2|max:100',
            'description' => 'max:2000',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'numeric|min:0',
            'unit' => 'max:20',
        ]);

        $categoryId = Request::input('category_id');
        if ($categoryId) {
            $category = ServiceCategory::find((int) $categoryId);
            if ($category && (int) $category['shop_id'] === $shopId) {
                $data['category_id'] = (int) $categoryId;
            }
        } else {
            $data['category_id'] = null;
        }

        $data['sort_order'] = (int) (Request::input('sort_order') ?? 0);

        Service::update($id, $data);

        AuditLog::log('service.updated', 'service', $id, $service, $data, AuthContext::id(), $shopId);

        flash('success', __('service.updated'));
        $this->redirect('/services');
    }

    public function destroy(int $id): void
    {
        $this->authorize('delete_services');
        $shopId = $this->getShopId();

        $service = Service::find($id);
        if (!$service || (int) $service['shop_id'] !== $shopId) {
            flash('error', __('service.not_found'));
            $this->redirect('/services');
        }

        Service::delete($id);

        AuditLog::log('service.deleted', 'service', $id, $service, null, AuthContext::id(), $shopId);

        flash('success', __('service.deleted'));
        $this->redirect('/services');
    }

    public function toggleStatus(int $id): void
    {
        $this->authorize('edit_services');
        $shopId = $this->getShopId();

        $service = Service::find($id);
        if (!$service || (int) $service['shop_id'] !== $shopId) {
            $this->json(['error' => __('service.not_found')], 404);
        }

        $newStatus = $service['status'] === 'active' ? 'inactive' : 'active';
        Service::update($id, ['status' => $newStatus]);

        AuditLog::log('service.status_changed', 'service', $id, ['status' => $service['status']], ['status' => $newStatus], AuthContext::id(), $shopId);

        $this->json(['status' => $newStatus]);
    }

    public function categories(): void
    {
        $this->authorize('manage_service_categories');
        $shopId = $this->getShopId();

        $categories = ServiceCategory::getByShop($shopId);

        $this->render('services/categories', [
            'categories' => $categories,
        ]);
    }

    public function storeCategory(): void
    {
        $this->authorize('manage_service_categories');
        $shopId = $this->getShopId();

        $data = $this->validate([
            'name' => 'required|min:2|max:100',
            'description' => 'max:500',
        ]);

        $data['shop_id'] = $shopId;
        $data['sort_order'] = (int) (Request::input('sort_order') ?? 0);

        $categoryId = ServiceCategory::create($data);

        AuditLog::log('service_category.created', 'service_category', $categoryId, null, $data, AuthContext::id(), $shopId);

        flash('success', __('service_category.created'));
        $this->redirect('/services/categories');
    }

    public function updateCategory(int $id): void
    {
        $this->authorize('manage_service_categories');
        $shopId = $this->getShopId();

        $category = ServiceCategory::find($id);
        if (!$category || (int) $category['shop_id'] !== $shopId) {
            flash('error', __('service_category.not_found'));
            $this->redirect('/services/categories');
        }

        $data = $this->validate([
            'name' => 'required|min:2|max:100',
            'description' => 'max:500',
        ]);

        $data['sort_order'] = (int) (Request::input('sort_order') ?? 0);

        ServiceCategory::update($id, $data);

        AuditLog::log('service_category.updated', 'service_category', $id, $category, $data, AuthContext::id(), $shopId);

        flash('success', __('service_category.updated'));
        $this->redirect('/services/categories');
    }

    public function destroyCategory(int $id): void
    {
        $this->authorize('manage_service_categories');
        $shopId = $this->getShopId();

        $category = ServiceCategory::find($id);
        if (!$category || (int) $category['shop_id'] !== $shopId) {
            flash('error', __('service_category.not_found'));
            $this->redirect('/services/categories');
        }

        $db = Database::getInstance();
        $db->update('services', ['category_id' => null], 'category_id = :category_id AND shop_id = :shop_id', [
            'category_id' => $id,
            'shop_id' => $shopId,
        ]);

        ServiceCategory::delete($id);

        AuditLog::log('service_category.deleted', 'service_category', $id, $category, null, AuthContext::id(), $shopId);

        flash('success', __('service_category.deleted'));
        $this->redirect('/services/categories');
    }
}