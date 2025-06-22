<!DOCTYPE html>
<html>
<head>
    <title>Complaint Resolved</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #28a745; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f8f9fa; }
        .footer { padding: 10px; text-align: center; color: #6c757d; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Complaint Resolved</h1>
        </div>
        <div class="content">
            <p>Dear {{ $customer->name }},</p>
            
            <p>We're pleased to inform you that your complaint has been resolved.</p>
            
            <h3>Complaint Details:</h3>
            <p><strong>Title:</strong> {{ $complaint->title }}</p>
            <p><strong>Description:</strong> {{ $complaint->description }}</p>
            <p><strong>Resolved by:</strong> {{ $technician->name ?? 'System' }}</p>
            <p><strong>Resolved on:</strong> {{ $complaint->resolved_at->format('F j, Y \a\t g:i A') }}</p>
            
            <p>Thank you for your patience. If you have any questions or concerns, please don't hesitate to contact us.</p>
            
            <p>Best regards,<br>
            Customer Support Team</p>
        </div>
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>