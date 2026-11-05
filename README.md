# AI Bridge for OpenAI-Compatible APIs

A WordPress AI Client provider with a custom API base URL, Responses and Chat Completions support, a default text model, and up to 10 configurable models.

## Features

- Connect WordPress AI to custom OpenAI-compatible services.
- API key integration through WordPress Connectors.
- Configurable API base URL.
- Up to 10 text generation models, starting with one row and an add (+) button.
- A default model for automatic text generation in the WordPress AI plugin.
- Per-model selection of Responses or Chat Completions, with optional Responses streaming.
- Localhost-compatible endpoint support for local gateways.

## Model settings

Open **Settings > AI Bridge for OpenAI-Compatible APIs**, enter the API base URL, and configure the first model. Click **+ Add model** for additional models, up to 10. Each model has its own ID, display label, endpoint type, enabled setting, and optional Responses streaming setting. Existing saved models are preserved.

Select **Default** next to an enabled model and save. Features using automatic text model selection can then use it without a separate per-feature model setting. Explicit model or provider selections made by a feature or another plugin still take precedence. If the default is removed or disabled, the first enabled model becomes the default. No models are available when all models are disabled.

The base URL includes the service's API prefix, for example `https://example.com/v1`.

## Installation

Install the published plugin from [WordPress.org](https://wordpress.org/plugins/linger-ai-bridge-for-openai-compatible-apis/).

The official OpenAI provider does not need to be installed.

## Scope

This plugin focuses on text generation provider integration. It does not implement a standalone AI interface, chat UI, or content generation workflow.

## Community

[LINUX DO](https://linux.do/)

## License

GPL-2.0. See [`LICENSE`](LICENSE).
