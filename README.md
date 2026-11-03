# AI Bridge for OpenAI-Compatible APIs

A WordPress AI Client provider with a custom API base URL, Responses and Chat Completions support, a default text model, and up to 10 configurable models.

## 中文介绍

AI Bridge for OpenAI-Compatible APIs 是一个 WordPress AI 服务桥接插件，为 WordPress AI Client 提供自定义 OpenAI 兼容接口接入，支持第三方 AI 服务和本地模型网关。

- **自定义 API 地址**：按服务商要求填写完整 Base URL，例如 `https://example.com/v1`。
- **双协议支持**：每个模型可选择 Responses 或 Chat Completions 协议，Responses 模型还可启用流式响应。
- **默认模型**：为官方 WordPress AI 插件的自动文本生成设置优先使用的模型；各功能明确指定的模型或服务提供商仍优先生效。
- **最多 10 个模型**：初始显示一行，点击“+ Add model”按需添加，可分别设置模型 ID、显示名称和启用状态。
- **统一密钥管理**：在 WordPress 的“设置 > Connectors”中配置 API 密钥。

配置方法：先在“设置 > Connectors”中配置本插件的 API 密钥，再进入“设置 > AI Bridge for OpenAI-Compatible APIs”，填写 API 地址和模型信息，启用模型、选择默认模型并保存。需要 WordPress 7.0+、PHP 7.4+；插件负责 AI 服务接入，具体的内容生成入口由 WordPress AI 插件或其他调用方提供。

本插件并非 OpenAI 官方插件。发起 AI 请求时，提示词、系统指令等数据会发送至你配置的服务，请了解相应服务条款与隐私政策。

代码位于 `trunk/`；根目录 `assets/` 用于 WordPress.org 展示素材，`trunk/assets/` 用于插件运行。发版快照保存在 SVN 的 `tags/` 中，该目录不纳入 Git。完整说明与版本记录见 [`trunk/readme.txt`](trunk/readme.txt)。

## Repository layout

```text
assets/          WordPress.org directory artwork (reserved for future assets)
trunk/           Installable plugin source and WordPress.org readme.txt
trunk/assets/    Runtime scripts, styles, and provider icon
LICENSE          Repository license
```

The Git repository mirrors the WordPress.org SVN working copy layout. Plugin code lives in [`trunk/`](trunk/). This root README contains configuration and compatibility details; [`trunk/readme.txt`](trunk/readme.txt) contains the WordPress.org plugin description and changelog. The WordPress.org plugin directory slug remains `linger-ai-bridge-for-openai-compatible-apis` so existing installations keep their settings and update identity.

The `tags/` directory contains WordPress.org release snapshots and is intentionally ignored by Git. SVN metadata and local agent settings are also excluded. Published SVN tags remain managed through WordPress.org SVN.

The root `assets/` directory is for WordPress.org listing artwork, such as banners, directory icons, and screenshots; those files are not bundled with the installed plugin. The separate `trunk/assets/` directory contains runtime JavaScript, CSS, and the provider icon referenced by the plugin, so it must stay inside `trunk/`.

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

## Installation

Install the published plugin from [WordPress.org](https://wordpress.org/plugins/linger-ai-bridge-for-openai-compatible-apis/).

To install a development checkout, package the contents of `trunk/` inside a directory named `linger-ai-bridge-for-openai-compatible-apis`, then upload that directory or its ZIP through WordPress. The ZIP of the entire GitHub repository includes the repository layout and is not the plugin installation package.

## Development and releases

Make plugin changes in `trunk/` and review them with Git. Keep GitHub repository documentation at the root. The `trunk/` directory must contain all runtime plugin files, including its own `assets/` directory.

WordPress.org publication is a separate SVN workflow: synchronize the plugin version and stable tag, prepare the release snapshot in SVN `tags/`, and explicitly commit that release to SVN when ready. A Git commit or push does not publish a WordPress.org release.

See the [WordPress.org SVN documentation](https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/) for the release workflow.

## Scope

This plugin focuses on text generation provider integration. It does not implement a standalone AI interface, chat UI, or content generation workflow.

## Community

[LINUX DO](https://linux.do/)

## License

GPL-2.0. See [`LICENSE`](LICENSE).
