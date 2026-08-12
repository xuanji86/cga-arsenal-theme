# osa-arsenal-theme — 零售站块主题(源仓库)

> ⚠️ **先看这个:Makefile 里的 `status / deploy-dry / deploy / rollback / db-drift / clean-backups` 全部含 prod SSH(ssh oldsteel),团队角色(dev/qa/designer/reviewer)一律禁用——它们是主会话专属。**
> 团队角色允许的只有:**`make verify`**(纯本地:rsync 当前工作树 → osa-local 副本,并提示打开 http://localhost:8090)和 **`php tests/run.php`**(纯本地测试)。

oldsteelarsenal.com 的 FSE 块主题源仓库(远端 github.com/xuanji86/osa-arsenal-theme)。结构:`templates/`、`parts/`、`patterns/`、`styles/`(风格变体)、`blocks/`、`inc/`(PHP,如 product-meta 的 summary hook)、`woocommerce/`(WC 模板覆盖)、`theme.json`、`functions.php`。部署文档 `DEPLOY.md`。

## 测试与本地验证
- `php tests/run.php` — 自带 harness,每个 tests/test-*.php 独立 PHP 进程跑(可 stub 有/无 WooCommerce),无需 WP 环境
- `make verify` — 把**当前工作树**(含未提交改动)rsync 进 osa-local 的主题目录看真实效果。**注意它会 --delete 覆盖副本现场**:并行任务时 verify/浏览器验证要串行(QA 默认持锁)
- osa-local 里的 `themes/osa-arsenal` 只是运行副本;在副本里手改主题 = 脏数据,会被下次 verify 覆盖。**永远从本仓改起**

## 关键 gotcha
- 块主题的 Site Editor 自定义存 DB(`wp_template` CPT)且**覆盖主题文件**;改模板文件不生效先查 DB override(这正是 `make db-drift` 存在的原因——但那是主会话的活)
- 卡片角标(corner tag)有堆叠逻辑(见 8459c80);promo 模块是季节性的,动 patterns 前读最近提交

## 部署政策
交付止于 commit/PR + `make verify` 本地验证报告。prod 部署/回滚/对账(repo↔prod 漂移检查)只由主会话经用户确认执行;大型主题任务开工前,主会话应先跑一次 `make status` 确认 prod 无散改。

主会话部署备忘:`deploy.sh` 有交互式 `[y/N]` 确认(scripts/deploy.sh:24),非交互环境用 `printf 'y\n' | make deploy`(前提:用户已明确批准本次部署);deploy 要求干净工作树(未跟踪文件也算脏);tests/ 经 .gitattributes export-ignore 不会上 prod;每次部署自动留 predeploy 快照 + deploy-* 标签。
