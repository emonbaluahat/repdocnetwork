<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthContext;
use App\Core\TemplateRenderer;
use App\Core\View;
use App\Models\CertificateType;
use App\Models\CertificateField;
use App\Models\CertificateRequest;
use App\Models\DocumentTemplate;
use App\Models\Document;
use App\Models\Customer;
use App\Models\Verification;

class CertificateController extends Controller
{
    public function index(): void
    {
        $this->authorize('view_documents');

        $types = CertificateType::findActive();
        $categories = CertificateType::getCategories();

        $this->render('certificates/index', [
            'title' => __('certificate_types'),
            'types' => $types,
            'categories' => $categories,
        ]);
    }

    public function storeType(): void
    {
        $this->authorize('manage_templates');

        $data = $this->validate([
            'slug' => 'required|unique:certificate_types,slug|max:64',
            'name_bn' => 'required|max:255',
            'name_en' => 'required|max:255',
            'category' => 'required|max:64',
            'fee' => 'numeric',
        ]);

        $data['status'] = 'active';

        $id = CertificateType::create($data);
        if ($id) {
            $this->redirectWith('certificates', 'success', __('certificate_type_created'));
        } else {
            $this->backWith('error', __('certificate_type_create_failed'));
        }
    }

    public function updateType(int $id): void
    {
        $this->authorize('manage_templates');

        $type = CertificateType::find($id);
        if (!$type) {
            $this->redirectWith('certificates', 'error', __('not_found'));
            return;
        }

        $data = $this->validate([
            'name_bn' => 'required|max:255',
            'name_en' => 'required|max:255',
            'category' => 'required|max:64',
            'fee' => 'numeric',
        ]);

        CertificateType::update($id, $data);
        $this->redirectWith('certificates', 'success', __('certificate_type_updated'));
    }

    public function destroyType(int $id): void
    {
        $this->authorize('manage_templates');

        $type = CertificateType::find($id);
        if (!$type) {
            $this->redirectWith('certificates', 'error', __('not_found'));
            return;
        }

        CertificateType::update($id, ['status' => 'inactive']);
        $this->redirectWith('certificates', 'success', __('certificate_type_disabled'));
    }

    public function fields(int $typeId): void
    {
        $this->authorize('view_documents');

        $type = CertificateType::find($typeId);
        if (!$type) {
            $this->redirectWith('certificates', 'error', __('not_found'));
            return;
        }

        $fields = CertificateField::getByType($typeId);

        $this->render('certificates/fields', [
            'title' => __('fields_for') . ' ' . $type['name_bn'],
            'type' => $type,
            'fields' => $fields,
        ]);
    }

    public function storeField(int $typeId): void
    {
        $this->authorize('manage_templates');

        $type = CertificateType::find($typeId);
        if (!$type) {
            $this->redirectWith('certificates', 'error', __('not_found'));
            return;
        }

        $data = $this->validate([
            'field_name' => 'required|max:64',
            'label_bn' => 'required|max:255',
            'field_type' => 'required',
        ]);

        $data['certificate_type_id'] = $typeId;
        $data['required'] = !empty($_POST['required']) ? 1 : 0;
        $data['position'] = (int) ($_POST['position'] ?? 0);

        if (!empty($_POST['options'])) {
            $options = explode("\n", $_POST['options']);
            $options = array_map('trim', $options);
            $options = array_filter($options);
            $data['options'] = json_encode(array_values($options));
        }

        CertificateField::create($data);
        $this->redirectWith('certificates/types/' . $typeId . '/fields', 'success', __('field_created'));
    }

    public function updateField(int $id): void
    {
        $this->authorize('manage_templates');

        $field = CertificateField::find($id);
        if (!$field) {
            $this->redirectWith('certificates', 'error', __('not_found'));
            return;
        }

        $data = $this->validate([
            'label_bn' => 'required|max:255',
            'field_type' => 'required',
        ]);

        $data['required'] = !empty($_POST['required']) ? 1 : 0;
        $data['position'] = (int) ($_POST['position'] ?? 0);

        if (!empty($_POST['options'])) {
            $options = explode("\n", $_POST['options']);
            $options = array_map('trim', $options);
            $options = array_filter($options);
            $data['options'] = json_encode(array_values($options));
        } else {
            $data['options'] = null;
        }

        CertificateField::update($id, $data);
        $this->redirectWith('certificates/types/' . $field['certificate_type_id'] . '/fields', 'success', __('field_updated'));
    }

    public function destroyField(int $id): void
    {
        $this->authorize('manage_templates');

        $field = CertificateField::find($id);
        if (!$field) {
            $this->redirectWith('certificates', 'error', __('not_found'));
            return;
        }

        $typeId = $field['certificate_type_id'];
        CertificateField::delete($id);
        $this->redirectWith('certificates/types/' . $typeId . '/fields', 'success', __('field_deleted'));
    }

    public function requests(): void
    {
        $this->authorize('view_documents');

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? '';

        $conditions = [];
        if ($status) {
            $conditions['status'] = $status;
        }

        $result = CertificateRequest::paginate($page, 25, $conditions, 'created_at DESC');
        $requests = $result['items'];

        foreach ($requests as &$req) {
            $req['type'] = CertificateType::find($req['certificate_type_id']);
            if ($req['customer_id']) {
                $req['customer'] = Customer::find($req['customer_id']);
            }
        }

        $this->render('certificates/requests', [
            'title' => __('certificate_requests'),
            'requests' => $requests,
            'current_page' => $result['current_page'],
            'total' => $result['total'],
            'total_pages' => $result['total_pages'],
            'status' => $status,
        ]);
    }

    public function createRequest(): void
    {
        $this->authorize('create_documents');

        $types = CertificateType::findActive();
        $customers = Customer::findAll([], 'name ASC', 200);

        $this->render('certificates/create', [
            'title' => __('create_certificate_request'),
            'types' => $types,
            'customers' => $customers,
        ]);
    }

    public function storeRequest(): void
    {
        $this->authorize('create_documents');

        $data = $this->validate([
            'certificate_type_id' => 'required|numeric',
            'customer_id' => 'nullable|numeric',
        ]);

        $formData = $_POST['fields'] ?? [];

        $customerData = [];
        if (!empty($data['customer_id'])) {
            $customer = Customer::find($data['customer_id']);
            if ($customer) {
                $customerData = [
                    'name_bn' => $customer['name'],
                    'father_name' => $customer['father_name'] ?? '',
                    'mother_name' => $customer['mother_name'] ?? '',
                    'nid' => $customer['nid'] ?? '',
                    'phone' => $customer['phone'] ?? '',
                ];
            }
        }

        $mergedData = array_merge($customerData, $formData);

        $reqId = CertificateRequest::create([
            'certificate_type_id' => (int) $data['certificate_type_id'],
            'customer_id' => !empty($data['customer_id']) ? (int) $data['customer_id'] : null,
            'data' => json_encode($mergedData),
            'status' => 'submitted',
            'created_by' => AuthContext::id(),
        ]);

        if ($reqId) {
            $this->redirectWith('certificates/requests', 'success', __('request_created'));
        } else {
            $this->backWith('error', __('request_create_failed'));
        }
    }

    public function showRequest(int $id): void
    {
        $this->authorize('view_documents');

        $request = CertificateRequest::find($id);
        if (!$request) {
            $this->redirectWith('certificates/requests', 'error', __('not_found'));
            return;
        }

        $type = CertificateType::find($request['certificate_type_id']);
        $customer = $request['customer_id'] ? Customer::find($request['customer_id']) : null;

        $this->render('certificates/show', [
            'title' => __('request') . ' #' . $id,
            'request' => $request,
            'type' => $type,
            'customer' => $customer,
        ]);
    }

    public function updateStatus(int $id): void
    {
        $this->authorize('edit_documents');

        $request = CertificateRequest::find($id);
        if (!$request) {
            $this->json(['error' => __('not_found')], 404);
            return;
        }

        $newStatus = $_POST['status'] ?? '';
        if (!in_array($newStatus, ['submitted', 'completed', 'cancelled'])) {
            $this->json(['error' => __('invalid_status')], 400);
            return;
        }

        CertificateRequest::update($id, ['status' => $newStatus]);
        $this->json(['status' => $newStatus]);
    }

    public function generate(int $id): void
    {
        $this->authorize('create_documents');

        $request = CertificateRequest::find($id);
        if (!$request) {
            $this->redirectWith('certificates/requests', 'error', __('not_found'));
            return;
        }

        $type = CertificateType::find($request['certificate_type_id']);
        if (!$type) {
            $this->redirectWith('certificates/requests', 'error', __('certificate_type_not_found'));
            return;
        }

        $templates = DocumentTemplate::findWhere('category', 'certificate');
        $templates = $templates ? [$templates] : DocumentTemplate::findAll(['category' => 'certificate', 'status' => 'active']);

        $template = null;
        foreach ($templates as $t) {
            $content = $t['content'] ?? '';
            if (strpos($content, $type['slug']) !== false || stripos($t['name'], $type['name_bn']) !== false) {
                $template = $t;
                break;
            }
        }

        if (!$template && !empty($templates)) {
            $template = $templates[0];
        }

        if (!$template) {
            $this->backWith('error', __('no_template_found'));
            return;
        }

        $documentNumber = Document::generateNumber();
        $requestData = json_decode($request['data'], true) ?: [];

        $docId = Document::create([
            'customer_id' => $request['customer_id'],
            'template_id' => $template['id'],
            'document_number' => $documentNumber,
            'data' => json_encode($requestData),
            'status' => 'final',
            'created_by' => AuthContext::id(),
        ]);

        if ($docId) {
            $verification = Verification::createForDocument($docId);

            $doc = Document::find($docId);
            $renderData = Document::getTemplateData($doc);
            $renderData['verification_code'] = $verification['verification_code'];
            $renderData['cert_type_name_bn'] = $type['name_bn'];

            $pdfDir = APP_ROOT . '/storage/shops/' . (AuthContext::shopId() ?: '0') . '/documents/' . date('Y') . '/' . date('m');
            if (!is_dir($pdfDir)) {
                mkdir($pdfDir, 0755, true);
            }
            $pdfFile = $pdfDir . '/' . $documentNumber . '.pdf';

            TemplateRenderer::renderPDF($template, $renderData, [], $pdfFile);
            Document::update($docId, ['generated_file' => str_replace(APP_ROOT . '/', '', $pdfFile)]);

            CertificateRequest::update($id, ['status' => 'completed']);

            $this->redirectWith('documents/' . $docId, 'success', __('certificate_generated'));
        } else {
            $this->backWith('error', __('certificate_generate_failed'));
        }
    }
}
