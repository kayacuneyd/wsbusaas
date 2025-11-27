// Google Apps Script Code
// Paste this into a new project at script.google.com

const WEBHOOK_URL = 'https://api.yourdomain.com/api/webhook/payment'; // REPLACE THIS
const WEBHOOK_SECRET = 'your-secret-key'; // REPLACE THIS

function checkEmails() {
  // Search for emails from ruul.io with subject "Payment Received" (adjust as needed)
  // This is an example search query
  const query = 'from:noreply@ruul.io subject:"Payment Received" is:unread';
  const threads = GmailApp.search(query);

  for (const thread of threads) {
    const messages = thread.getMessages();
    for (const message of messages) {
      if (message.isUnread()) {
        const body = message.getPlainBody();
        const orderId = extractOrderId(body);
        const customerEmail = extractEmail(body); // Fallback

        if (orderId || customerEmail) {
          const success = sendWebhook(orderId, customerEmail);
          if (success) {
            message.markRead();
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

function sendWebhook(orderId, email) {
  const payload = {
    order_id: orderId,
    email: email,
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
