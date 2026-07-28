<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\Verification;

class VerificationController extends Controller
{
    public function verify(string $code): void
    {
        $result = Verification::verifyByCode($code);

        if (!$result) {
            $this->render('verification/verify', [
                'title' => __('certificate_verification'),
                'verified' => false,
                'code' => $code,
                'message' => __('certificate_not_found'),
            ]);
            return;
        }

        $this->render('verification/verify', [
            'title' => __('certificate_verification'),
            'verified' => true,
            'code' => $code,
            'verification' => $result['verification'],
            'document' => $result['document'],
        ]);
    }
}
