=== AI Provider for Any OpenAI-Compatible Provider ===
Contributors: linger0er
Tags: ai, artificial intelligence, openai, responses api, text generation
Requires at least: 7.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a configurable AI provider for the WordPress AI Client with support for OpenAI-compatible Responses and Chat Completions endpoints.

== Description ==

AI Provider for Any OpenAI-Compatible Provider registers an AI provider for the WordPress AI Client. It lets site administrators configure a shared API base URL, an API key through WordPress Connectors, and multiple text generation models for a service that exposes OpenAI-compatible endpoints.

This plugin is not affiliated with, endorsed by, sponsored by, or officially connected to OpenAI.

Each model can use one of two endpoint types:

* Responses-compatible endpoint: `/v1/responses`
* Chat Completions-compatible endpoint: `/v1/chat/completions`

For Responses-compatible models, the plugin adapts WordPress AI Client chat-style prompt payloads into the Responses API `input` format, then adapts Responses API output back into the chat-style response shape expected by the WordPress AI Client.

= External Services =

This plugin sends text generation requests to the API endpoint configured by the site administrator. The plugin does not contact an AI service until an administrator enters an API base URL and configures credentials.

Data sent to the configured service can include prompt text, system instructions, model configuration, and any other request fields provided by the WordPress AI Client for text generation.

If the configured endpoint is OpenAI's API, usage is subject to OpenAI's terms and privacy policy:

* Terms: https://openai.com/policies/terms-of-use
* Privacy policy: https://openai.com/policies/privacy-policy

If another OpenAI-compatible gateway is configured, usage is subject to that service provider's terms and privacy policy.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/ai-provider-for-any-openai-compatible-provider` directory, or install the plugin ZIP through the WordPress Plugins screen.
2. Activate the plugin through the `Plugins` screen in WordPress.
3. Open `Settings > Connectors` and set the API key for `AI Provider for Any OpenAI-Compatible Provider`.
4. Open `Settings > AI Provider for Any OpenAI-Compatible Provider`.
5. Configure the API base URL and enabled model list.

== Frequently Asked Questions ==

= Does this plugin require an API key? =

It requires an API key for the OpenAI-compatible service configured by the site administrator.

= Where is the API key stored? =

The API key is stored through the WordPress Connectors settings for this provider. For local development, the plugin can also read `AI_PROVIDER_FOR_ANY_OPENAI_COMPATIBLE_PROVIDER_API_KEY`.

= Why do I see "input is required"? =

That error means a Responses API request was sent without the required `input` field. This plugin adapts WordPress AI Client `messages` payloads into Responses API `input` payloads for models configured to use `/v1/responses`.

= Can I use a local gateway? =

Yes. Configure the base URL as a full HTTP URL such as `http://localhost:1234/v1`.

== Screenshots ==

1. Provider settings screen for configuring the API base URL and models.

== Changelog ==

= 0.1.0 =
* Initial release.
