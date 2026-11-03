=== AI Bridge for OpenAI-Compatible APIs ===
Contributors: linger0er
Tags: ai, artificial intelligence, openai, responses api, text generation
Requires at least: 7.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.1.5
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect WordPress AI to custom OpenAI-compatible APIs. 支持双协议、默认模型及最多 10 个模型。

== Description ==

AI Bridge for OpenAI-Compatible APIs registers an AI provider for the WordPress AI Client. It lets site administrators configure a shared API base URL, an API key through WordPress Connectors, and multiple text generation models for a service that exposes OpenAI-compatible endpoints.

This plugin is not affiliated with, endorsed by, sponsored by, or officially connected to OpenAI.

Each model can use one of two endpoint types:

* Responses-compatible endpoint: `/v1/responses`
* Chat Completions-compatible endpoint: `/v1/chat/completions`

For Responses-compatible models, the plugin adapts WordPress AI Client chat-style prompt payloads into the Responses API `input` format, then adapts Responses API output back into the chat-style response shape expected by the WordPress AI Client.

= 中文说明 =

AI Bridge for OpenAI-Compatible APIs 是面向 WordPress AI Client 的 AI 服务桥接插件，可连接自定义 OpenAI 兼容接口，适用于第三方 AI 服务、本地模型网关，以及需要自定义 API 地址和模型的站点。

主要功能：

* 自定义 API Base URL，支持包含版本路径的地址，例如 `https://example.com/v1`。
* 每个模型可独立选择 Responses 或 Chat Completions 协议。
* 最多配置 10 个模型，首次仅显示一行，点击“+ Add model”按需添加。
* 可设置默认模型，供官方 WordPress AI 插件的自动文本生成功能优先使用。
* 支持为 Responses 模型开启流式响应，并将结果汇总为标准文本生成结果。
* 通过 WordPress 的 Connectors 设置管理 API 密钥，也支持 localhost 本地网关。

配置步骤：

1. 安装并启用插件，需要 WordPress 7.0 或更高版本、PHP 7.4 或更高版本。
2. 在“设置 > Connectors”中，为“AI Bridge for OpenAI-Compatible APIs”配置 API 密钥。
3. 打开“设置 > AI Bridge for OpenAI-Compatible APIs”，填写 API Base URL、模型 ID 和显示名称，并选择接口协议。
4. 勾选需要启用的模型，在“Default”列选择默认模型，然后保存。需要更多模型时点击“+ Add model”。

默认模型适用于自动文本生成；各功能或其他插件明确指定的模型、服务提供商仍优先生效。默认模型被停用或删除后，会回退到第一个启用的模型。插件提供 AI 服务接入能力，具体的内容生成入口由 WordPress AI 插件或其他调用方提供。

本插件并非 OpenAI 官方插件，与 OpenAI 无隶属或背书关系。发起 AI 请求时，提示词、系统指令、模型设置等数据会发送至你配置的服务；使用前请了解该服务提供商的服务条款与隐私政策。

= External Services =

This plugin sends text generation requests to the API endpoint configured by the site administrator. The plugin does not contact an AI service until an administrator enters an API base URL and configures credentials.

Data sent to the configured service can include prompt text, system instructions, model configuration, and any other request fields provided by the WordPress AI Client for text generation.

If the configured endpoint is OpenAI's API, usage is subject to OpenAI's terms and privacy policy:

* Terms: https://openai.com/policies/terms-of-use
* Privacy policy: https://openai.com/policies/privacy-policy

If another OpenAI-compatible gateway is configured, usage is subject to that service provider's terms and privacy policy.

== Installation ==

1. Install the plugin ZIP through the WordPress Plugins screen.
2. Activate the plugin through the `Plugins` screen in WordPress.
3. Open `Settings > Connectors` and set the API key for `AI Bridge for OpenAI-Compatible APIs`.
4. Open `Settings > AI Bridge for OpenAI-Compatible APIs`.
5. Configure the API base URL and enabled model list.
6. Use the + Add model button to add more models (up to 10), select an enabled default model, and save.

== Frequently Asked Questions ==

= Does this plugin require an API key? =

It requires an API key for the OpenAI-compatible service configured by the site administrator.

= Where is the API key stored? =

The API key is stored through the WordPress Connectors settings for this provider.

= Can I use a local gateway? =

Yes. Configure the base URL as a full HTTP URL such as `http://localhost:1234/v1`.

= Can I choose a default model? =

Yes. Select Default beside an enabled model on this plugin's settings screen and save. The default is preferred by automatic text generation in the WordPress AI plugin and listed first within this provider. Explicit model or provider selections in individual features take precedence. Removing or disabling the default selects the first remaining enabled model.

= How many models can I configure? =

Up to 10. New installations show one model row; click + Add model for more rows. Existing model settings are retained. Model IDs must be unique.

== Screenshots ==

1. Provider settings screen for configuring the API base URL and models.

== Changelog ==

= 0.1.5 =
* Add Chinese plugin descriptions, feature explanations, and setup instructions.
* Keep repository documentation and the license file at the repository root.
* 新增中文插件介绍、功能说明与配置步骤，并整理发布包目录。

= 0.1.4 =
* Rename the plugin to AI Bridge for OpenAI-Compatible APIs while preserving existing settings and update compatibility.
* Add a default model for automatic text generation in the WordPress AI plugin.
* Allow up to 10 models with add and remove controls, starting with one row.
* Make model configuration changes available immediately without an API discovery cache.
* Correct Responses assistant history and report interrupted streams, failed requests, and output token limits.
* Apply the environment base URL consistently and respect explicitly configured request timeouts.

= 0.1.3 =
* Add an optional per-model streaming setting for Responses API requests.
* Aggregate streaming Responses API events into a standard text-generation result.

= 0.1.2 =
* Clarify the public installation instructions.
* Remove developer-only troubleshooting and local development details from the FAQ.

= 0.1.1 =
* Remove the unsupported `temperature` parameter from Responses API requests.
* Clarify that the plugin is licensed under GPLv2.

= 0.1.0 =
* Initial release.
