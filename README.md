# AI Bridge for OpenAI-Compatible APIs

A WordPress AI Client provider with a custom API base URL, Responses and Chat Completions support, a default text model, and up to 10 configurable models.

## Repository layout

```text
assets/       WordPress.org directory artwork (reserved for future assets)
trunk/        Installable plugin source and plugin documentation
LICENSE       Repository license
```

The Git repository mirrors the WordPress.org SVN working copy layout. Plugin code lives in [`trunk/`](trunk/), and [`trunk/README.md`](trunk/README.md) contains configuration and compatibility details. The WordPress.org plugin directory slug remains `linger-ai-bridge-for-openai-compatible-apis` so existing installations keep their settings and update identity.

The `tags/` directory contains WordPress.org release snapshots and is intentionally ignored by Git. SVN metadata and local agent settings are also excluded. Published SVN tags remain managed through WordPress.org SVN.

## Installation

Install the published plugin from [WordPress.org](https://wordpress.org/plugins/linger-ai-bridge-for-openai-compatible-apis/).

To install a development checkout, package the contents of `trunk/` inside a directory named `linger-ai-bridge-for-openai-compatible-apis`, then upload that directory or its ZIP through WordPress. The ZIP of the entire GitHub repository includes the repository layout and is not the plugin installation package.

## Development and releases

Make plugin changes in `trunk/` and review them with Git. Keep GitHub repository documentation at the root. The `trunk/` directory must contain all runtime plugin files, including its own `assets/` directory.

WordPress.org publication is a separate SVN workflow: synchronize the plugin version and stable tag, prepare the release snapshot in SVN `tags/`, and explicitly commit that release to SVN when ready. A Git commit or push does not publish a WordPress.org release.

See the [WordPress.org SVN documentation](https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/) for the release workflow.

## Community

[LINUX DO](https://linux.do/)

## License

GPL-2.0. See [`LICENSE`](LICENSE).
