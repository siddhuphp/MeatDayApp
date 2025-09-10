<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestMailController extends Controller
{
    use HttpResponses;

    public function testMail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string',
            'username' => 'nullable|string'
        ]);

        try {
            $data = [
                'subject' => $request->subject,
                'message' => $request->message,
                'username' => $request->username ?? 'Test User',
                'verification_link' => 'https://example.com/test-link' // Placeholder for testing
            ];

            $viewName = 'emails.register'; // Using existing email template

            $mail = Mail::to($request->email);

            // Only add BCC if the constant is defined and not null
            $bccRecipients = config('constants.BCC');
            if ($bccRecipients) {
                $mail->bcc([$bccRecipients]);
            }

            $mail->send(new TestEmail($data, $viewName));

            return $this->success([
                'message' => 'Test email sent successfully',
                'recipient' => $request->email,
                'subject' => $request->subject
            ], 'Test email sent successfully', 200);
        } catch (\Exception $e) {
            Log::error('Test email failed: ' . $e->getMessage(), [
                'email' => $request->email,
                'subject' => $request->subject
            ]);

            return $this->error([
                'message' => 'Failed to send test email',
                'error' => $e->getMessage()
            ], 'Email sending failed', 500);
        }
    }
}
