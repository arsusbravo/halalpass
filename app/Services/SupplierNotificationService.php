<?php

namespace App\Services;

use App\Models\HalalCertificate;
use App\Models\Ingredient;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Mocked WhatsApp notification service via Twilio/FBM API.
 *
 * Replace the send methods with actual Twilio API calls when ready:
 * - Twilio WhatsApp: https://www.twilio.com/docs/whatsapp
 * - Facebook Business Messaging: https://developers.facebook.com/docs/whatsapp
 */
class SupplierNotificationService
{
    private SupplierPortalService $portalService;

    public function __construct(SupplierPortalService $portalService)
    {
        $this->portalService = $portalService;
    }

    /**
     * Notify suppliers about their expiring certificates.
     * Generates portal access tokens and sends WhatsApp messages.
     *
     * @return array Summary of notifications sent
     */
    public function notifyExpiringSoon(int $companyId, int $withinDays = 90): array
    {
        $certificates = HalalCertificate::where('company_id', $companyId)
            ->where('status', 'expiring_soon')
            ->with(['ingredient.supplier'])
            ->get();

        $notifications = [];

        // Group by supplier
        $grouped = $certificates->groupBy(fn ($cert) => $cert->ingredient?->supplier_id);

        foreach ($grouped as $supplierId => $certs) {
            if (!$supplierId) {
                continue;
            }

            $supplier = $certs->first()->ingredient->supplier;

            if (!$supplier || !$supplier->phone) {
                continue;
            }

            // Generate portal access token
            $token = $this->portalService->generateAccessToken(
                $companyId,
                $supplierId,
                null, // General access — not ingredient-specific
                30
            );

            $portalUrl = $this->generatePortalUrl($token->token);

            $message = $this->buildExpiryMessage($supplier, $certs, $portalUrl);

            $result = $this->sendWhatsApp($supplier->phone, $message);

            $notifications[] = [
                'supplier_id' => $supplierId,
                'supplier_name' => $supplier->name,
                'phone' => $supplier->phone,
                'certificates_count' => $certs->count(),
                'portal_url' => $portalUrl,
                'sent' => $result['success'],
                'message_id' => $result['message_id'] ?? null,
            ];
        }

        return [
            'total_suppliers_notified' => count($notifications),
            'total_certificates' => $certificates->count(),
            'notifications' => $notifications,
        ];
    }

    /**
     * Notify a specific supplier about a missing certificate.
     */
    public function notifyMissingCertificate(
        int $companyId,
        Supplier $supplier,
        Ingredient $ingredient
    ): array {
        if (!$supplier->phone) {
            return ['success' => false, 'reason' => 'No phone number'];
        }

        $token = $this->portalService->generateAccessToken(
            $companyId,
            $supplier->id,
            $ingredient->id,
            30
        );

        $portalUrl = $this->generatePortalUrl($token->token);

        $message = "Yth. {$supplier->pic_name},\n\n"
            . "Kami membutuhkan Sertifikat Halal untuk bahan berikut:\n"
            . "- *{$ingredient->name}* (Kode: {$ingredient->code})\n\n"
            . "Mohon upload sertifikat melalui portal berikut:\n"
            . "{$portalUrl}\n\n"
            . "Link berlaku selama 30 hari.\n\n"
            . "Terima kasih atas kerjasamanya.";

        $result = $this->sendWhatsApp($supplier->phone, $message);

        return [
            'success' => $result['success'],
            'supplier' => $supplier->name,
            'ingredient' => $ingredient->name,
            'portal_url' => $portalUrl,
            'message_id' => $result['message_id'] ?? null,
        ];
    }

    /**
     * Send a custom notification to a supplier.
     */
    public function sendCustomNotification(Supplier $supplier, string $message): array
    {
        if (!$supplier->phone) {
            return ['success' => false, 'reason' => 'No phone number'];
        }

        return $this->sendWhatsApp($supplier->phone, $message);
    }

    // ----------------------------------------------------------------
    //  Private helpers
    // ----------------------------------------------------------------

    /**
     * Build the expiry notification message.
     */
    private function buildExpiryMessage(Supplier $supplier, Collection $certificates, string $portalUrl): string
    {
        $name = $supplier->pic_name ?? $supplier->name;
        $lines = ["Yth. {$name},\n"];
        $lines[] = "Sertifikat Halal berikut akan segera kadaluarsa:\n";

        foreach ($certificates as $cert) {
            $daysLeft = $cert->days_until_expiry;
            $lines[] = "- *{$cert->ingredient->name}* (SH: {$cert->sh_number}) — {$daysLeft} hari lagi";
        }

        $lines[] = "\nMohon segera perpanjang dan upload sertifikat baru melalui:";
        $lines[] = $portalUrl;
        $lines[] = "\nLink berlaku selama 30 hari.";
        $lines[] = "\nTerima kasih atas kerjasamanya.";

        return implode("\n", $lines);
    }

    /**
     * Generate the supplier portal URL from a token.
     */
    private function generatePortalUrl(string $token): string
    {
        return config('app.url') . '/supplier-portal/' . $token;
    }

    /**
     * Send a WhatsApp message via Twilio/FBM API.
     *
     * MOCKED: Replace with actual Twilio implementation.
     *
     * @return array{success: bool, message_id: string|null}
     */
    private function sendWhatsApp(string $phoneNumber, string $message): array
    {
        // --- MOCK IMPLEMENTATION ---
        // Replace this with actual Twilio WhatsApp API call:
        //
        // $twilio = new \Twilio\Rest\Client(
        //     config('services.twilio.sid'),
        //     config('services.twilio.token')
        // );
        //
        // $msg = $twilio->messages->create(
        //     "whatsapp:{$phoneNumber}",
        //     [
        //         'from' => 'whatsapp:' . config('services.twilio.whatsapp_from'),
        //         'body' => $message,
        //     ]
        // );

        Log::info('WhatsApp Mock: Message sent', [
            'to' => $phoneNumber,
            'message_length' => strlen($message),
            'preview' => substr($message, 0, 100) . '...',
        ]);

        return [
            'success' => true,
            'message_id' => 'WA-MOCK-' . uniqid(),
            'phone' => $phoneNumber,
        ];
    }
}