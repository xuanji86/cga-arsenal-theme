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
- 无 prod：本仓目前没有部署面，交付止于 commit + verify。

## 沿用 OSA 的 gotcha
- Site Editor 自定义存 DB（wp_template CPT）且覆盖主题文件；模板改了不生效先查 DB override。
- 首页 patterns 是 PHP 动态的（hero 特色枪=最新有货商品、分类瓦片=实时 count）；空目录时渲染占位块。
- 年龄门默认开启（`osa_age_gate_enabled`），截图/E2E 先设 cookie `osa_age_ok=1`。
