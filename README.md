# Custom AI Provider

Custom AI Provider is a WordPress AI Client provider plugin for OpenAI-compatible text generation services.

The plugin registers a configurable AI provider that supports both Responses-compatible and Chat Completions-compatible endpoints. It is designed for sites that need to connect the WordPress AI Client to OpenAI or to an OpenAI-compatible gateway with custom model and base URL settings.

## Features

- Custom WordPress AI Client provider registration.
- API key integration through WordPress Connectors.
- Configurable API base URL.
- Multiple text generation model entries.
- Per-model endpoint selection for `/v1/responses` and `/v1/chat/completions`.
- Conversion of chat-style prompt payloads into Responses API `input` payloads.
- Localhost-compatible endpoint support for local gateways.

## Scope

This plugin focuses on text generation provider integration. It does not implement a standalone AI interface, chat UI, or content generation workflow.

## License

GPL-2.0-or-later
