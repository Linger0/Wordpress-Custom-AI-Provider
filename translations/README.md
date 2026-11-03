# 翻译维护说明

插件使用英文原文，译文按语言分别维护。WordPress.org 详情页使用所在语言站点的语言；安装后的插件使用 WordPress 当前用户或站点的语言，不由插件检测浏览器语言。

## 文件用途

- `../trunk/languages/*-zh_CN.po`：插件简介及设置文案的简体中文译文源文件。
- `../trunk/languages/*-zh_CN.mo`：随插件安装的编译译文。没有对应语言译文时显示英文；WordPress.org 官方语言包优先于内置译文。
- `linger-ai-bridge-for-openai-compatible-apis.pot`：使用官方 WP-CLI 提取的代码翻译模板。
- `readme-zh_CN.po`：WordPress.org 详情页的简体中文翻译草稿，**不属于安装包，也不会因提交到 Git 或 SVN 而自动上线**。

## WordPress.org 详情页翻译

1. 由熟悉简体中文的人员审核 `readme-zh_CN.po`，检查术语、链接、格式和含义。官方不接受未经人工审核的机器翻译。
2. 登录 WordPress.org，打开 [Stable Readme 简体中文项目](https://translate.wordpress.org/projects/wp-plugins/linger-ai-bridge-for-openai-compatible-apis/stable-readme/zh-cn/default/)，通过页面的 Import Translations 入口导入审核后的 PO 文件（需具备相应权限）；也可逐条提交译文。
3. 联系简体中文翻译团队的 PTE/GTE 审核。插件作者身份本身不赋予译文批准权限；如需长期维护，可按官方流程申请该项目的 PTE 权限。
4. 审核通过后，WordPress.org 会在相应语言站点显示译文，缓存更新可能需要时间。

草稿以 0.1.6 的英文原文为目标。翻译平台同步可能滞后：若某条英文原文尚未出现，需等版本同步后再次导入或逐条提交。不要为了匹配旧原文而修改已发布的旧版本标签。

代码翻译在独立的 [Stable code 简体中文项目](https://translate.wordpress.org/projects/wp-plugins/linger-ai-bridge-for-openai-compatible-apis/stable/zh-cn/default/) 维护，可使用 `trunk/languages/` 中的 PO 文件作为审核起点。初次生成官方插件语言包需要至少 90% 的稳定版代码字符串获批；详情页翻译不计入这个比例。

本仓库仅准备译文，尚未向官方平台提交或获得批准。内置后台译文的可用状态不代表官网详情页译文已上线。

## 更新模板和安装包译文

在仓库根目录运行已安装的 WP-CLI：

```sh
wp i18n make-pot trunk translations/linger-ai-bridge-for-openai-compatible-apis.pot \
  --slug=linger-ai-bridge-for-openai-compatible-apis \
  --domain=linger-ai-bridge-for-openai-compatible-apis --exclude=languages
```

将新模板合并到 PO 文件并人工校对后，重新编译：

```sh
wp i18n make-mo trunk/languages
```

保留 `%1$s` 等占位符、HTML 标签和 URL。翻译 WordPress.org 描述时，应从平台导出当前原文；代码 POT 不包含 `readme.txt` 的详情页字符串。

## 官方文档

- [插件作者翻译指南](https://make.wordpress.org/polyglots/handbook/plugin-theme-authors-guide/)
- [WordPress.org 元数据与详情页翻译](https://make.wordpress.org/meta/handbook/documentation/translations/)
- [插件国际化](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/)
- [readme.txt 的工作方式](https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/)
