<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Account details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;padding:24px;">
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
            <div style="padding:20px 20px 12px;border-bottom:1px solid #f1f5f9;">
                <div style="font-size:14px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#64748b;">
                    Opol Doctors Medical Clinic
                </div>
              <div style="margin-top:8px;font-size:20px;font-weight:800;color:#0891b2;">
  Your Text Here
</div>

                    Your account details
                </div>
                <div style="margin-top:6px;font-size:13px;color:#475569;">
                    Use these credentials to sign in. You will be asked to change your password on first login.
                </div>
            </div>

            <div style="padding:16px 20px;">
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:12px 14px;">
                    <div style="font-size:12px;color:#64748b;margin-bottom:8px;">Login credentials</div>
                    <div style="font-size:14px;line-height:1.6;">
                        <div><strong>Email:</strong> {{ $user->email }}</div>
                        <div><strong>Temporary password:</strong> {{ $plainPassword }}</div>
                    </div>
                </div>

                <!-- <div style="margin-top:14px;">
                    <a href="{{ url('/webadmin') }}" style="display:inline-block;background:#0f172a;color:#ffffff;text-decoration:none;padding:10px 14px;border-radius:12px;font-weight:700;font-size:13px;">
                        Sign in
                    </a>
                </div> -->

                <div style="margin-top:16px;font-size:12px;color:#64748b;line-height:1.6;">
                    If you did not expect this email, you can ignore it. For security, do not share this password with anyone.
                </div>
            </div>

            <div style="padding:12px 20px;border-top:1px solid #f1f5f9;font-size:11px;color:#94a3b8;">
                This email was sent by the Opol Doctors Medical Clinic System.
            </div>
        </div>
    </div>
</body>
</html>
