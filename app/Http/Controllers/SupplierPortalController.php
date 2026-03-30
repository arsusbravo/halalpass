<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierPortalUploadRequest;
use App\Services\SupplierPortalService;
use Inertia\Inertia;
use Inertia\Response;

class SupplierPortalController extends Controller
{
    private SupplierPortalService $portalService;

    public function __construct(SupplierPortalService $portalService)
    {
        $this->portalService = $portalService;
    }

    public function show(string $token): Response
    {
        $accessToken = $this->portalService->validateToken($token);

        if (!$accessToken) {
            return Inertia::render('SupplierPortal/Invalid', [
                'message' => 'Link tidak valid atau sudah kadaluarsa.',
            ]);
        }

        $portalData = $this->portalService->getPortalData($accessToken);

        return Inertia::render('SupplierPortal/Show', [
            'token' => $token,
            'portalData' => $portalData,
        ]);
    }

    public function upload(SupplierPortalUploadRequest $request, string $token)
    {
        $accessToken = $this->portalService->validateToken($token);

        if (!$accessToken) {
            return Inertia::render('SupplierPortal/Invalid', [
                'message' => 'Link tidak valid atau sudah kadaluarsa.',
            ]);
        }

        $this->portalService->uploadCertificate(
            $accessToken,
            $request->validated()['ingredient_id'],
            $request->only(['sh_number', 'issuing_body', 'issuing_body_name', 'issue_date', 'expiry_date']),
            $request->file('document')
        );

        return Inertia::render('SupplierPortal/Success', [
            'message' => 'Sertifikat halal berhasil diunggah. Terima kasih!',
            'supplierName' => $accessToken->supplier->name,
        ]);
    }
}