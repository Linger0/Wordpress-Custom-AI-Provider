# AI Bridge for OpenAI-Compatible APIs

AI Bridge for OpenAI-Compatible APIs is a WordPress AI Client provider plugin for text generation services that expose OpenAI-compatible endpoints.

The plugin registers a configurable AI provider that supports both Responses-compatible and Chat Completions-compatible endpoints. It is designed for sites that need to connect the WordPress AI Client to an administrator-configured service with custom model and base URL settings.

## Features

- Custom WordPress AI Client provider registration.
- API key integration through WordPress Connectors.
- Configurable API base URL.
- Up to 10 text generation models, starting with one row and an add (+) button.
- A default model for automatic text generation in the WordPress AI plugin.
- Per-model endpoint selection for `/v1/responses` and `/v1/chat/completions`.
- Conversion of chat-style prompt payloads into Responses API `input` payloads.
- Localhost-compatible endpoint support for local gateways.

## Model settings

Open **Settings > AI Bridge for OpenAI-Compatible APIs**, enter the API base URL, and configure the first model. Click **+ Add model** for additional models, up to 10. Each model has its own ID, display label, endpoint type, enabled setting, and optional Responses streaming setting. Existing saved models are preserved.

Select **Default** next to an enabled model and save. The default is placed first in this provider's model list and added to the WordPress AI plugin's preferred text models through `wpai_preferred_text_models` (and the older `ai_experiments_preferred_models_for_text_generation` hook). Features using automatic text model selection can then use it without a separate per-feature model setting. Explicit model or provider selections made by a feature or another plugin still take precedence. If the default is removed or disabled, the first enabled model becomes the default. No model is registered when all models are disabled.

The base URL includes the service's API prefix, for example `https://example.com/v1`. Endpoint paths `/responses` or `/chat/completions` are appended once. `LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_BASE_URL` overrides the saved URL consistently for requests and availability checks.

## Upstream review

The development changes were reviewed against [AI Provider for OpenAI](https://github.com/WordPress/ai-provider-for-openai) (`24ba3bee14`, stable 1.2.0) and [WordPress AI](https://github.com/WordPress/ai) (`93eefdfb57`, stable 1.5.0) on November 3, 2026. Relevant improvements include correct assistant `output_text` content in Responses history, token-limit errors when supported by the SDK, explicit failures for unsuccessful or interrupted Responses requests, and the official text model preference hooks. Locally configured model metadata is read directly so saved changes take effect without the SDK's API discovery cache.

This bridge keeps its own Responses/Chat Completions adapter and does not require the official OpenAI provider to be installed.

## Repository workflow

Following the [WordPress.org SVN documentation](https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/), plugin changes belong in `trunk/`, and versioned snapshots in `tags/` are created only for an explicitly requested release. Keep the plugin header version, version constant, `Stable tag`, and tag directory name synchronized. Review the local changes before submitting a release to WordPress.org.

## Scope

This plugin focuses on text generation provider integration. It does not implement a standalone AI interface, chat UI, or content generation workflow.

## License

GPL-2.0
