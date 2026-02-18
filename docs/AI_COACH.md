# AI Coach V1

## Modes
- `vent`: reflective summary, three questions, one grounding suggestion.
- `bridge`: respectful rewrite draft with explicit "Ask before sending".
- `repair`: five-step repair plan, three micro-actions, one check-in question.

## Rules
- AI Coach is a communication coach, not therapy.
- No auto-send behavior. All bridge/repair outputs are stored as drafts.

## Provider Switching
- Default provider is configured in `config/us.php` via `us.ai.default_provider`.
- Supported now:
  - `fake` (deterministic, test-safe)
  - `ollama` (local development)

Environment placeholders:
- `US_AI_DEFAULT_PROVIDER=ollama`
- `OLLAMA_BASE_URL=http://127.0.0.1:11434`
- `OLLAMA_MODEL=llama3.1:8b`

## Local Ollama (Docker)
```bash
docker run -d --name ollama -p 11434:11434 ollama/ollama
docker exec -it ollama ollama pull llama3.1:8b
```

## API Example (preview for upcoming endpoints)
```bash
curl -X POST http://127.0.0.1:8000/ai/sessions \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"mode":"vent","title":"Tough conversation"}'
```

## Safety Limitations (MVP)
- Rule-based keyword classifier only.
- False positives/false negatives are possible.
- High-risk cues trigger de-escalation instructions and emergency-support guidance text.
