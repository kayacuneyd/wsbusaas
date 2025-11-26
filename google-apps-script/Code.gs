const WEBHOOK_URL = 'https://your-backend.com/api/ruul-webhook';
const SECRET = 'change-me-apps-script-secret';
const HMAC_SECRET = 'change-me-hmac';

function hexSignature(raw) {
  const sig = Utilities.computeHmacSha256Signature(raw, HMAC_SECRET);
  return sig.map(b => ('0' + (b & 0xff).toString(16)).slice(-2)).join('');
}

function processEmails() {
  const threads = GmailApp.search('label:ruul-payments from:@ruul.io subject:paid newer_than:7d');
  threads.forEach(t => {
    t.getMessages().forEach(m => {
      const body = m.getPlainBody();
      const orderId = body.match(/order_id[:=]\s*(\d+)/i)?.[1];
      const amount = body.match(/amount[:=]\s*([\d.]+)/i)?.[1];
      const paymentId = body.match(/payment_id[:=]\s*([\w-]+)/i)?.[1];
      const email = body.match(/email[:=]\s*([\w@.\-]+)/i)?.[1];
      if (!orderId) return;
      const payload = {
        order_id: Number(orderId),
        payment_id: paymentId || '',
        amount: Number(amount || 0),
        currency: 'USD',
        email: email || '',
        status: 'paid'
      };
      const raw = JSON.stringify(payload);
      UrlFetchApp.fetch(WEBHOOK_URL, {
        method: 'post',
        contentType: 'application/json',
        payload: raw,
        headers: {
          'X-Apps-Script-Token': SECRET,
          'X-Signature': hexSignature(raw)
        },
        muteHttpExceptions: true
      });
    });
    t.moveToArchive();
  });
}

function createTimeTrigger() {
  ScriptApp.newTrigger('processEmails').timeBased().everyMinutes(1).create();
}
