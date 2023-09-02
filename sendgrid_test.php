<?php
curl -X PUT \
  https://api.sendgrid.com/v3/marketing/lists/8b0f4ea5-f433-480b-8ce3-67b664f8352b/contacts \
  -H 'Authorization: Bearer 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' \
  -H 'Content-Type: application/json' \
  -d '{
        "contacts": [
          {
            "email": "test@example.com"
          }
        ]
      }'
