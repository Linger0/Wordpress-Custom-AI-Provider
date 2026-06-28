# My OpenAI Responses Provider

WordPress AI Client provider plugin for OpenAI-compatible text generation services, with per-model support for both the Responses API and Chat Completions API.

## Repository

- Suggested repository name: `my-openai-responses-provider`
- Suggested description: `WordPress AI Client provider for OpenAI-compatible Responses and Chat Completions endpoints.`

## Features

- Registers a custom `openai_responses` provider with the WordPress AI Client.
- Uses the WordPress Connectors screen for API key storage.
- Supports a configurable API base URL.
- Supports multiple enabled models.
- Lets each model choose one endpoint type:
  - `/v1/responses`
  - `/v1/chat/completions`
- Adapts chat-style WordPress AI Client text prompts into Responses API `input` payloads.
- Allows localhost-compatible base URLs for local OpenAI-compatible gateways.

## Requirements

- WordPress 7.0 or newer
- PHP 7.4 or newer
- WordPress AI Client available in WordPress
- OpenAI API key or an OpenAI-compatible gateway key

## Installation

1. Copy this plugin directory to `wp-content/plugins/my-openai-responses-provider`.
2. Activate `My OpenAI Responses Provider` in WordPress admin.
3. Open `Settings > Connectors`.
4. Set the API key for `My OpenAI Responses Provider`.
5. Open `Settings > My OpenAI Responses Provider`.
6. Configure the API base URL and model list.

## Configuration

The default API base URL is:

```text
https://api.openai.com/v1
```

Each model row has:

- `Enabled`: whether the model is registered.
- `Model ID`: the exact model name sent to the API.
- `Label`: the display name in WordPress.
- `Endpoint Type`: either `/v1/responses` or `/v1/chat/completions`.

Default models:

- `gpt-4.1` using `/v1/responses`
- `gpt-4o-mini` using `/v1/chat/completions`

## Environment Variables

The plugin can also read these values:

```bash
OPENAI_RESPONSES_API_KEY=sk-...
OPENAI_RESPONSES_BASE_URL=https://api.openai.com/v1
```

The WordPress Connector API key is preferred for normal admin-managed usage.

## Endpoint Behavior

For models configured as `responses`, requests are sent to:

```text
{baseUrl}/responses
```

The plugin converts WordPress AI Client chat-style payloads from `messages` into Responses API `input`.

For models configured as `chat_completions`, requests are sent to:

```text
{baseUrl}/chat/completions
```

Those requests keep the Chat Completions payload shape.

## Troubleshooting

### `input is required`

This means a `/v1/responses` request was sent without a Responses API `input` field. The plugin now adapts `messages` into `input` for Responses models. If you still see this error, confirm the updated plugin files are deployed and that WordPress is not loading an older copy.

### API key is not configured

Open `Settings > Connectors` and set the key for `My OpenAI Responses Provider`. For local development, you can also set `OPENAI_RESPONSES_API_KEY`.

### Localhost endpoint requests fail

Use a full HTTP URL such as:

```text
http://localhost:1234/v1
```

The plugin relaxes WordPress HTTP restrictions for configured localhost endpoints.

## Limitations

- The admin UI currently uses fixed model rows instead of a dynamic add/remove repeater.
- This revision focuses on text generation.
- The underlying WordPress AI Client base class is chat-oriented, so some strict Responses-only providers may still need response parsing adaptation.

## License

GPL-2.0-or-later
