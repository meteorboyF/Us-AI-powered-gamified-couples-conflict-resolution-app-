# ChatV2 Realtime (Phase 3)

## Required env

Set these in `.env`:

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=us-chatv2
REVERB_APP_KEY=local-key
REVERB_APP_SECRET=local-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

## Run locally

Use separate terminals:

```bash
php artisan serve
php artisan reverb:start
npm run dev
```

Then open:

- `http://127.0.0.1:8000/chat-v2`

## Manual verification

1. Sign in as Partner A1 in browser #1, Partner A2 in browser #2 (or incognito).
2. Open `/chat-v2` in both.
3. Send a message from A1:
   - A2 should see it instantly without refresh.
   - A1 should see status progress from `Sent` to `Delivered`/`Read`.
4. Send a message from A2 and confirm symmetric behavior.
5. Type in one browser:
   - The other browser should show `Typing...`.
6. Move one tab to background:
   - Partner status should show `away`.
7. Confirm realtime diagnostics (local/dev only):
   - `connected` state
   - channel names
   - last event timestamp updates

