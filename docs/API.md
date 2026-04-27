# BulkSMS Pro - API Documentation

## Authentication

All API requests require an API key sent in the `X-API-KEY` header.

```
X-API-KEY: your_api_key_here
```

Generate API keys from: Dashboard > API Keys > Generate Key

## Base URL
```
https://yourdomain.com/api/v1
```

---

## Endpoints

### Send SMS
Send a single SMS message.

**POST** `/api/v1/sms/send`

**Request Body:**
```json
{
    "to": "+1234567890",
    "message": "Hello, World!",
    "sender_id": "MyCompany"
}
```

**Response:**
```json
{
    "success": true,
    "message_id": 12345,
    "to": "+1234567890",
    "parts": 1,
    "cost": 0.01,
    "status": "queued"
}
```

---

### Send Bulk SMS
Send SMS to multiple recipients.

**POST** `/api/v1/sms/bulk`

**Request Body:**
```json
{
    "recipients": ["+1234567890", "+0987654321"],
    "message": "Bulk message here",
    "sender_id": "MyApp"
}
```

**Response:**
```json
{
    "success": true,
    "queued": 2,
    "total": 2,
    "results": [
        {"to": "+1234567890", "message_id": 123, "status": "queued"},
        {"to": "+0987654321", "message_id": 124, "status": "queued"}
    ]
}
```

---

### Check Balance
Get account SMS balance.

**GET** `/api/v1/balance`

**Response:**
```json
{
    "balance": 1500.00,
    "daily_limit": 10000,
    "monthly_limit": 100000
}
```

---

### Message Status
Get delivery status of a message.

**GET** `/api/v1/sms/{message_id}/status`

**Response:**
```json
{
    "uuid": "abc-123",
    "recipient": "+1234567890",
    "status": "delivered",
    "parts": 1,
    "cost": 0.01,
    "sent_at": "2024-01-01 12:00:00",
    "delivered_at": "2024-01-01 12:00:05",
    "created_at": "2024-01-01 12:00:00"
}
```

---

### Contacts List
Get paginated contact list.

**GET** `/api/v1/contacts?page=1`

---

### Create Contact
Add a new contact.

**POST** `/api/v1/contacts`

**Request Body:**
```json
{
    "phone": "+1234567890",
    "name": "John Doe",
    "email": "john@example.com"
}
```

---

### Contact Groups
List contact groups.

**GET** `/api/v1/contacts/groups`

---

### Health Check
Check API health.

**GET** `/api/v1/health`

---

## DLR Webhooks

Configure DLR callback URLs in your gateway settings:
```
https://yourdomain.com/dlr/callback/{gateway_slug}
```

Supported callback formats: Twilio, Vonage, MessageBird, generic HTTP.

## Error Codes

| Code | Description |
|------|-------------|
| 400 | Bad Request - Invalid parameters |
| 401 | Unauthorized - Invalid or missing API key |
| 402 | Payment Required - Insufficient balance |
| 403 | Forbidden - IP not whitelisted |
| 404 | Not Found - Resource doesn't exist |
| 429 | Too Many Requests - Rate limit exceeded |
| 503 | Service Unavailable - No gateway available |

## Rate Limiting

Default: 60 requests per minute per API key. Configurable per key.
