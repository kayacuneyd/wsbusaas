# Google Apps Script Setup for Ruul.io Email Parsing

This script monitors your Gmail for payment confirmation emails from Ruul.io and automatically triggers the deployment webhook on your website.

## Setup Instructions

1.  Go to [script.google.com](https://script.google.com/) and click **"New Project"**.
2.  Name the project "Ruul Payment Parser".
3.  Delete any code in the editor and paste the code below.
4.  Update the `WEBHOOK_URL` and `WEBHOOK_SECRET` variables at the top of the script.
5.  Save the script (Cmd/Ctrl + S).
6.  Run the `setupTrigger` function once manually:
    *   Select `setupTrigger` from the dropdown menu in the toolbar.
    *   Click **"Run"**.
    *   Grant the necessary permissions when asked (you might see a "Unverified app" warning, click "Advanced" -> "Go to ... (unsafe)").

## The Code

```javascript
// Configuration
var WEBHOOK_URL = "https://api.bezmidar.de/api/webhook/payment.php";
var WEBHOOK_SECRET = "your-secret-key"; // Must match 'webhook_secret' in backend/config/config.php
var SEARCH_QUERY = "from:billing@ruul.io subject:\"You've got a new sale\" -label:processed";

function processRuulEmails() {
  // Find threads that match the query
  var threads = GmailApp.search(SEARCH_QUERY);
  
  if (threads.length === 0) {
    console.log("No new payment emails found.");
    return;
  }

  for (var i = 0; i < threads.length; i++) {
    var thread = threads[i];
    var messages = thread.getMessages();
    
    // Process the last message in the thread
    var message = messages[messages.length - 1];
    var body = message.getPlainBody();
    var subject = message.getSubject();
    
    // Extract Data based on screenshot
    // Subject: You've got a new sale 🎉
    // Body: "NAME SURNAME just made a purchase from your Ruul Space."
    // Body: "Product/Service sold: "Product Name""
    
    var nameMatch = body.match(/^(.*?) just made a purchase/m);
    var productMatch = body.match(/Product\/Service sold: "(.*?)"/);
    
    var payload = {
      payment_status: 'paid',
      source: 'ruul_email',
      raw_subject: subject
    };

    if (nameMatch) {
      // Clean up the name (remove asterisks if markdown is parsed, though getPlainBody usually handles it)
      payload.customer_name = nameMatch[1].replace(/\*/g, '').trim();
    }
    
    if (productMatch) {
      payload.product_name = productMatch[1].trim();
    }
    
    // Log for debugging
    console.log("Found: " + JSON.stringify(payload));

    // Send Webhook
    if (sendWebhook(payload)) {
      // Mark as processed to avoid double-processing
      var label = GmailApp.getUserLabelByName("processed");
      if (!label) {
        label = GmailApp.createLabel("processed");
      }
      thread.addLabel(label);
      console.log("Processed Order: " + (payload.order_id || "Unknown"));
    }
  }
}

function sendWebhook(payload) {
  var options = {
    'method': 'post',
    'contentType': 'application/json',
    'headers': {
      'X-Webhook-Secret': WEBHOOK_SECRET
    },
    'payload': JSON.stringify(payload)
  };

  try {
    var response = UrlFetchApp.fetch(WEBHOOK_URL, options);
    console.log("Webhook Response: " + response.getContentText());
    return response.getResponseCode() === 200;
  } catch (e) {
    console.error("Webhook Failed: " + e.toString());
    return false;
  }
}

function setupTrigger() {
  // Check if trigger already exists
  var triggers = ScriptApp.getProjectTriggers();
  for (var i = 0; i < triggers.length; i++) {
    if (triggers[i].getHandlerFunction() === "processRuulEmails") {
      return; // Already set up
    }
  }

  // Create a trigger to run every 5 minutes
  ScriptApp.newTrigger("processRuulEmails")
    .timeBased()
    .everyMinutes(5)
    .create();
    
  console.log("Trigger set up successfully.");
}
```

## Testing

1.  Send a test email to yourself with the subject "Payment Received" (and make sure it matches the `SEARCH_QUERY` criteria, or temporarily change the query for testing).
2.  Include "Order #12345" in the body.
3.  Run the `processRuulEmails` function manually in the script editor.
4.  Check the "Executions" tab in Apps Script to see logs.
5.  Check your Admin Panel logs to see if the webhook was received.
