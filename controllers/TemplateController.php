<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthContext;
use App\Core\Request;
use App\Core\View;
use App\Models\DocumentTemplate;

class TemplateController extends Controller
{
    private function getShopId(): int
    {
        return AuthContext::shopId();
    }

    public function index(): void
    {
        $this->authorize('manage_templates');
        $shopId = $this->getShopId();

        $search = trim(Request::input('search'));
        $category = trim(Request::input('category'));
        $type = trim(Request::input('type'));
        $status = trim(Request::input('status'));
        $page = max(1, (int) (Request::input('page') ?? 1));
        $perPage = min(100, max(1, (int) (Request::input('per_page') ?? 25)));

        $result = DocumentTemplate::getByShop($shopId, $search, $category, $type, $status, $page, $perPage);
        $categories = DocumentTemplate::getCategories($shopId);
        $types = DocumentTemplate::getTypes();

        $this->render('documents/templates', [
            'templates' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'per_page' => $result['per_page'],
            'total_pages' => $result['total_pages'],
            'has_prev' => $result['has_prev'],
            'has_next' => $result['has_next'],
            'search' => $search,
            'category' => $category,
            'type' => $type,
            'status' => $status,
            'categories' => $categories,
            'types' => $types,
        ]);
    }

    public function create(): void
    {
        $this->authorize('manage_templates');
        $types = DocumentTemplate::getTypes();
        $paperSizes = DocumentTemplate::paperSizes();

        $this->render('documents/template-create', [
            'types' => $types,
            'paper_sizes' => $paperSizes,
        ]);
    }

    public function store(): void
    {
        $this->authorize('manage_templates');
        $shopId = $this->getShopId();

        $data = $this->validate([
            'name' => 'required|max:200',
            'category' => 'max:100',
            'template_type' => 'required',
            'content' => 'required',
            'paper_size' => 'max:20',
        ]);

        $variables = Request::input('variables');
        $data['variables'] = $variables ? json_encode(array_map('trim', explode(',', $variables)), JSON_UNESCAPED_UNICODE) : '[]';
        $data['shop_id'] = $shopId;
        $data['created_by'] = AuthContext::id();
        if (empty($data['paper_size'])) $data['paper_size'] = 'A4';

        DocumentTemplate::create($data);

        flash('success', 'টেমপ্লেট তৈরি হয়েছে।');
        $this->redirect('/templates');
    }

    public function show(int $id): void
    {
        $this->authorize('manage_templates');
        $template = DocumentTemplate::find($id);
        if (!$template || (int) $template['shop_id'] !== $this->getShopId()) {
            flash('error', 'টেমপ্লেট পাওয়া যায়নি।');
            $this->redirect('/templates');
        }

        $this->json($template);
    }

    public function edit(int $id): void
    {
        $this->authorize('manage_templates');
        $template = DocumentTemplate::find($id);
        if (!$template || (int) $template['shop_id'] !== $this->getShopId()) {
            flash('error', 'টেমপ্লেট পাওয়া যায়নি।');
            $this->redirect('/templates');
        }

        $types = DocumentTemplate::getTypes();
        $paperSizes = DocumentTemplate::paperSizes();

        $this->render('documents/template-edit', [
            'template' => $template,
            'types' => $types,
            'paper_sizes' => $paperSizes,
        ]);
    }

    public function update(int $id): void
    {
        $this->authorize('manage_templates');
        $template = DocumentTemplate::find($id);
        if (!$template || (int) $template['shop_id'] !== $this->getShopId()) {
            flash('error', 'টেমপ্লেট পাওয়া যায়নি।');
            $this->redirect('/templates');
        }

        $data = $this->validate([
            'name' => 'required|max:200',
            'category' => 'max:100',
            'template_type' => 'required',
            'content' => 'required',
            'paper_size' => 'max:20',
        ]);

        $variables = Request::input('variables');
        $data['variables'] = $variables ? json_encode(array_map('trim', explode(',', $variables)), JSON_UNESCAPED_UNICODE) : '[]';
        if (empty($data['paper_size'])) $data['paper_size'] = 'A4';

        DocumentTemplate::update($id, $data);

        flash('success', 'টেমপ্লেট আপডেট হয়েছে।');
        $this->redirect('/templates');
    }

    public function duplicate(int $id): void
    {
        $this->authorize('manage_templates');
        $newId = DocumentTemplate::duplicate($id);
        if ($newId) {
            flash('success', 'টেমপ্লেট ডুপ্লিকেট হয়েছে।');
        } else {
            flash('error', 'টেমপ্লেট ডুপ্লিকেট করা যায়নি।');
        }
        $this->redirect('/templates');
    }

    public function toggleStatus(int $id): void
    {
        $this->authorize('manage_templates');
        $template = DocumentTemplate::find($id);
        if (!$template || (int) $template['shop_id'] !== $this->getShopId()) {
            $this->json(['error' => 'টেমপ্লেট পাওয়া যায়নি।'], 404);
        }

        $newStatus = $template['status'] === 'active' ? 'inactive' : 'active';
        DocumentTemplate::update($id, ['status' => $newStatus]);

        if (Request::isAjax()) {
            $this->json(['status' => $newStatus]);
        }

        flash('success', 'স্ট্যাটাস পরিবর্তন হয়েছে।');
        $this->redirect('/templates');
    }

    public function destroy(int $id): void
    {
        $this->authorize('manage_templates');
        $template = DocumentTemplate::find($id);
        if (!$template || (int) $template['shop_id'] !== $this->getShopId()) {
            $this->json(['error' => 'Not found.'], 404);
        }
        DocumentTemplate::delete($id);
        $this->json(['success' => true]);
    }

    public function preview(int $id): void
    {
        $this->authorize('manage_templates');
        $template = DocumentTemplate::find($id);
        if (!$template || (int) $template['shop_id'] !== $this->getShopId()) {
            flash('error', 'টেমপ্লেট পাওয়া যায়নি।');
            $this->redirect('/templates');
        }

        $sampleData = [];
        $vars = json_decode($template['variables'] ?? '[]', true) ?: [];
        foreach ($vars as $var) {
            $sampleData[$var] = '[' . $var . ']';
        }
        $sampleData['document_number'] = 'DOC-2501-ABCDEF';
        $sampleData['created_at'] = date('Y-m-d H:i:s');

        $rendered = \App\Services\DocumentGenerator::renderTemplate($template['content'], $sampleData);

        View::setLayout('layouts/auth');
        $this->render('documents/preview', [
            'template' => $template,
            'rendered' => $rendered,
        ]);
    }
}
