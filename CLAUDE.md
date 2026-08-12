# cga-arsenal-theme — California Guns & Ammo 块主题（源仓库）

gunsammo.club 未来站点的 FSE 块主题，**fork 自 osa-arsenal-theme @ main(22279c1)**。设计定稿在 Claude Design 画板 `c7402e0a-fa33-4e4a-8a3d-6cb1902dc263`（入口 Demo.dc.html）与 artifact demo；本主题的视觉与文案以那套定稿为准。

## 与 OSA 主题的差异（fork 要点）
- **调色**：theme.json palette 换 CGA 色 —— ground=sand `#F5F2EC`，paper-edge/tint 变暖，新增 pine `#1F3D2B` / pine-2 / pine-tint / clay / clay-tint。组件级换色都在 `assets/css/cga-base.css`（在 osa-base.css 之后加载；内部 `--cga-*` 变量桥接 theme.json 预设）。**别改 osa-base.css 来做 CGA 视觉**——CGA 层永远写在 cga-base.css。
- **内部 API 仍是 osa_***：函数名、text domain（osa-arsenal）、block 命名空间（osa/）与 `--osa-*` 变量全部保留，便于回收上游改动。只有面向用户的输出换成 CGA。
- **新增**：`inc/cga-compliance.php`（`pa_ca-status` → 合规 chips；卡片/PDP 用 `[cga_status_chips]`）、patterns/cga-*.php（首页 7 段）、logo 资产 `assets/logos/cga-*.png`。
- **删除**：dealer 全套（templates/parts/patterns + theme.json 注册）、OSA 首页 patterns（hero-catalog-first / floor-section / story-strip / three-pillar / model-index-strip / model-landing）、independence-day 促销页模板、PDP 的 condition-scale（军品品相体系）。blocks/model-* 代码保留但 OSA_MODEL_LINKS_DEFAULT 置空——CGA 暂无型号 hub。
- **商品卡**（inc/helpers.php `osa_render_product_card_compact`）多了三样：pa_manufacturer 品牌行、合规 chips、库存行（"1 left"/"N in stock"）。

## 本地验证
- `make verify` — rsync 当前工作树 → `~/Desktop/cga-local`（**:8093**）的 `themes/cga-arsenal`。副本非源码，别在副本里改。
- cga-local 管理端：admin / cga2026（仅本地）。
- `make deploy` — rsync 当前工作树 → **cga-prod**（https://caligunsandammo.com）。prod 规矩：主会话、经用户确认、先 commit 再 deploy。

## Customizer（2026-08-12 接线）
- **首页全部文案走 Customize**：外观→自定义→ **CGA · Homepage** 面板（六节按页面顺序）。**傻瓜式约定（2026-08-12 用户要求，勿回退）**：一元素一控件、白话 label（"Green button — text"），链接类拆 text+link 两格（URL 用 esc_url_raw），禁止管道/按行拆语法；hero 特色枪=真实商品下拉（cga_hero_featured_id，0=自动最新有货，注册时查 200 个最新商品），卡片内容本身来自商品记录。默认值=定稿文案，存在 `inc/cga-customizer.php` 的 CGA_* 常量（数组按元素拆）——**改默认文案改常量，别改 pattern 里的渲染**。
- **Store Facts 单一来源**（CGA · Site Text & Chrome → Store Facts）：地址/营业时间/电话/邮箱/FFL/CFD。消费途径三条：pattern 里 `cga_text()` 的 `{address}{hours}{phone}{email}{ffl}{cfd}{count}` token、页面正文 `[cga_fact key="" line=""]`、页脚 `[cga_footer_legal]`（parts/footer.html 只剩 shortcode，法务块文案在 Customize 改）。
- 即时预览（postMessage）只覆盖 osa-customize-preview.js 里绑定的 id，且 customizer.php 的 $live 名单必须与 JS 严格一致——多翻一个 id 就是静默 no-op；其余控件走 refresh。
- OSA 僵尸节（hero/marquee/floor/story/models/dealer drop）已删；marquee/model 的 helper 函数保留（block 还在，插入即用默认值）。

## 沿用 OSA 的 gotcha
- Site Editor 自定义存 DB（wp_template CPT）且覆盖主题文件；模板改了不生效先查 DB override。
- 首页 patterns 是 PHP 动态的（hero 特色枪=最新有货商品、分类瓦片=实时 count）；空目录时渲染占位块。
- 年龄门默认开启（`osa_age_gate_enabled`），截图/E2E 先设 cookie `osa_age_ok=1`。
