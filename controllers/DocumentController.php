<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthContext;
use App\Core\Request;
use App\Core\View;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Customer;
use App\Models\AuditLog;
use App\Services\DocumentGenerator;

class DocumentController extends Controller
{
    private function getShopId(): int
    {
        return AuthContext::shopId();
    }

    public function index(): void
    {
        $this->authorize('view_documents');
        $shopId = $this->getShopId();

        $search = trim(Request::input('search'));
        $status = trim(Request::input('status'));
        $page = max(1, (int) (Request::input('page') ?? 1));
        $perPage = min(100, max(1, (int) (Request::input('per_page') ?? 25)));
        $statuses = Document::getStatuses();

        $result = Document::getByShop($shopId, $search, $status, $page, $perPage);

        $this->render('documents/index', [
            'documents' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'per_page' => $result['per_page'],
            'total_pages' => $result['total_pages'],
            'has_prev' => $result['has_prev'],
            'has_next' => $result['has_next'],
            'search' => $search,
            'status' => $status,
            'statuses' => $statuses,
        ]);
    }

    public function create(): void
    {
        $this->authorize('create_documents');
        $shopId = $this->getShopId();

        $customers = Customer::getByShop($shopId, '', '', 'name ASC', 1, 1000);
        $templates = DocumentTemplate::getActiveByCategory($shopId);

        $this->render('documents/create', [
            'customers' => $customers['items'],
            'templates' => $templates,
        ]);
    }

    public function store(): void
    {
        $this->authorize('create_documents');
        $shopId = $this->getShopId();
        $userId = AuthContext::id();

        $data = $this->validate([
            'customer_id' => 'required',
            'template_id' => 'required',
        ]);

        $customerId = (int) $data['customer_id'];
        $templateId = (int) $data['template_id'];

        $customer = Customer::find($customerId);
        if (!$customer || (int) $customer['shop_id'] !== $shopId) {
            flash('error', 'গ্রাহক পাওয়া যায়নি।');
            $this->back();
        }

        $template = DocumentTemplate::find($templateId);
        if (!$template || (int) $template['shop_id'] !== $shopId) {
            flash('error', 'টেমপ্লেট পাওয়া যায়নি।');
            $this->back();
        }

        $vars = json_decode($template['variables'] ?? '[]', true) ?: [];
        $fieldData = [];
        foreach ($vars as $var) {
            $val = Request::input('var_' . $var);
            if ($val !== null) {
                $fieldData[$var] = $val;
            }
        }

        $fieldData['customer_name'] = $customer['name'];
        $fieldData['phone'] = $customer['phone'];
        $documentNumber = Document::generateNumber();
        $fieldData['document_number'] = $documentNumber;
        $fieldData['created_at'] = date('Y-m-d H:i:s');

        if ($template['paper_size'] !== 'A4') {
            DocumentGenerator::paperSize($template['paper_size']);
        }

        $pdfPath = DocumentGenerator::generateFromTemplate($template['content'], $fieldData, $documentNumber);

        $docId = Document::create([
            'shop_id' => $shopId,
            'customer_id' => $customerId,
            'template_id' => $templateId,
            'document_number' => $documentNumber,
            'data' => json_encode($fieldData, JSON_UNESCAPED_UNICODE),
            'generated_file' => $pdfPath,
            'status' => 'generated',
            'created_by' => $userId,
        ]);

        AuditLog::log('document.generate', 'document', $docId, null, null, $userId);

        flash('success', 'ডকুমেন্ট জেনারেট হয়েছে।');
        $this->redirect('/documents/' . $docId);
    }

    public function show(int $id): void
    {
        $this->authorize('view_documents');
        $shopId = $this->getShopId();

        $doc = $this->findDocument($id, $shopId);
        if (!$doc) return;

        $this->render('documents/show', [
            'document' => $doc,
        ]);
    }

    public function pdf(int $id): void
    {
        $shopId = $this->getShopId();
        $doc = $this->findDocument($id, $shopId);
        if (!$doc) return;

        $filePath = APP_ROOT . '/' . $doc['generated_file'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo 'PDF file not found.';
            exit;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $doc['document_number'] . '.pdf"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public function print(int $id): void
    {
        $shopId = $this->getShopId();
        $doc = $this->findDocument($id, $shopId);
        if (!$doc) return;

        $data = json_decode($doc['data'] ?? '{}', true) ?: [];

        $template = DocumentTemplate::find($doc['template_id']);
        $content = $template ? $template['content'] : '';

        $rendered = DocumentGenerator::renderTemplate($content, $data);

        View::setLayout('layouts/auth');
        $this->render('documents/print', [
            'document' => $doc,
            'rendered' => $rendered,
        ]);
    }

    public function void(int $id): void
    {
        $this->authorize('void_documents');
        $shopId = $this->getShopId();
        $doc = Document::find($id);
        if (!$doc || (int) $doc['shop_id'] !== $shopId) {
            flash('error', 'ডকুমেন্ট পাওয়া যায়নি।');
            $this->redirect('/documents');
        }

        if (Document::void($id)) {
            AuditLog::log('document.void', 'document', $id, null, null, AuthContext::id());
            flash('success', 'ডকুমেন্ট বাতিল করা হয়েছে।');
        } else {
            flash('error', 'ডকুমেন্ট বাতিল করা যায়নি।');
        }

        $this->redirect('/documents');
    }

    public function destroy(int $id): void
    {
        $this->authorize('void_documents');
        $shopId = $this->getShopId();
        $doc = Document::find($id);
        if (!$doc || (int) $doc['shop_id'] !== $shopId) {
            $this->json(['error' => 'Not found.'], 404);
        }
        if (Document::void($id)) {
            $this->json(['success' => true]);
        }
        $this->json(['error' => 'Could not void.'], 400);
    }

    private function findDocument(int $id, int $shopId): ?array
    {
        $db = \App\Core\Database::getInstance();
        $doc = $db->fetch(
            "SELECT d.*, c.name as customer_name, c.phone as customer_phone, c.address as customer_address,
                    t.name as template_name, t.template_type, t.content as template_content
             FROM documents d
             LEFT JOIN customers c ON d.customer_id = c.id
             LEFT JOIN document_templates t ON d.template_id = t.id
             WHERE d.id = :id AND d.shop_id = :shop_id",
            ['id' => $id, 'shop_id' => $shopId]
        );

        if (!$doc) {
            flash('error', 'ডকুমেন্ট পাওয়া যায়নি।');
            $this->redirect('/documents');
            return null;
        }

        return $doc;
    }
}
