// Google Apps Script Code
// Paste this into a new project at script.google.com

const WEBHOOK_URL = 'https://api.yourdomain.com/api/webhook/payment'; // REPLACE THIS
const WEBHOOK_SECRET = 'your-secret-key'; // REPLACE THIS

function checkEmails() {
  // Search for emails from ruul.io (forwarded from kayacuneyd@gmail.com to admin email)
  // This will check the admin's email inbox for Ruul payment confirmation emails
  const query = '(from:ruul.io OR from:noreply@ruul.io) AND (subject:"Payment" OR subject:"Ödeme" OR subject:"payment") is:unread';
  const threads = GmailApp.search(query);

  for (const thread of threads) {
    const messages = thread.getMessages();
    for (const message of messages) {
      if (message.isUnread()) {
        const body = message.getPlainBody();
        const orderId = extractOrderId(body);
        const customerEmail = extractEmail(body); // Fallback
        const domainName = extractDomain(body); // Extract domain if available

        if (orderId || customerEmail) {
          const success = sendWebhook(orderId, customerEmail, domainName);
          if (success) {
            message.markRead();
            Logger.log('Payment email processed: Order ' + orderId);
          }
        }
      }
    }
  }
}

function extractOrderId(body) {
  // Regex to find Order ID (e.g., WB20231120123456789)
  const regex = /(WB\d{14,})/;
  const match = body.match(regex);
  return match ? match[1] : null;
}

function extractEmail(body) {
  // Simple regex to find email address in the body
  // Adjust based on Ruul.io email format
  const regex = /([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)/;
  const match = body.match(regex);
  return match ? match[1] : null;
}

function extractDomain(body) {
  // Try to extract domain name from email body
  // Look for common patterns like "domain: example.de" or "example.de"
  const domainPatterns = [
    /domain[:\s]+([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i,
    /([a-zA-Z0-9.-]+\.(de|com|net|org|io))/i
  ];
  
  for (const pattern of domainPatterns) {
    const match = body.match(pattern);
    if (match && match[1]) {
      return match[1];
    }
  }
  return null;
}

function sendWebhook(orderId, email, domainName) {
  const payload = {
    order_id: orderId,
    email: email,
    domain_name: domainName,
    payment_status: 'paid'
  };

  const options = {
    method: 'post',
    contentType: 'application/json',
    headers: {
      'X-Webhook-Secret': WEBHOOK_SECRET
    },
    payload: JSON.stringify(payload)
  };

  try {
    const response = UrlFetchApp.fetch(WEBHOOK_URL, options);
    Logger.log('Webhook sent for ' + orderId + ': ' + response.getContentText());
    return response.getResponseCode() === 200;
  } catch (e) {
    Logger.log('Error sending webhook: ' + e.toString());
    return false;
  }
}

function setupTrigger() {
  // Run checkEmails every 5 minutes
  ScriptApp.newTrigger('checkEmails')
    .timeBased()
    .everyMinutes(5)
    .create();
}
