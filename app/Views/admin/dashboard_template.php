<?php
$page = 'dashboard_template';
include('header.php');
$selectedTemplate = is_array($selectedTemplate ?? null) ? $selectedTemplate : null;
$selectedTemplateId = (int) ($selectedTemplate['id'] ?? 0);
$selectedTemplateName = (string) ($selectedTemplate['dt_name'] ?? 'New Dashboard Template');
$selectedTemplateActive = (int) ($selectedTemplate['dt_is_active'] ?? 0);
?>

<style>
    .tpl-wrap {
        width: 100%;
    }
    .tpl-editor-card {
        background: #fff;
        border: 1px solid #e3e9f2;
        border-radius: 14px;
        overflow: hidden;
    }
    .tpl-page-head {
        display: grid;
        grid-template-columns: minmax(150px, 1fr) auto minmax(150px, 1fr);
        align-items: center;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid #e8eef6;
        background: #fff;
    }
    .tpl-page-heading {
        text-align: center;
    }
    .tpl-page-actions {
        display: flex;
        justify-content: flex-end;
    }
    .tpl-editor-body {
        padding: 18px 20px;
    }
    .tpl-page-title {
        margin: 0;
        font-size: 1.22rem;
        font-weight: 800;
        color: #102a43;
    }
    .tpl-page-subtitle {
        margin-top: 3px;
        color: #607488;
        font-size: 0.84rem;
    }
    .tpl-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #d7e2ed;
        border-radius: 10px;
        background: #ffffff;
        color: #193b57;
        text-decoration: none;
        padding: 9px 12px;
        font-size: 0.83rem;
        font-weight: 800;
        box-shadow: 0 6px 14px rgba(18, 63, 102, 0.08);
    }
    .tpl-back-btn:hover {
        background: #f2f7fc;
        color: #193b57;
    }
    .tpl-save-top {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .template-meta-grid {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) auto;
        gap: 10px;
        align-items: end;
        margin-bottom: 10px;
        padding: 10px;
        border: 1px solid #e4ebf4;
        border-radius: 11px;
        background: #fbfdff;
    }
    .template-check {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 37px;
        font-size: 0.8rem;
        color: #40576e;
        font-weight: 800;
        white-space: nowrap;
    }
    .drawer-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1200;
        display: none;
        background: rgba(15, 31, 48, 0.28);
    }
    .drawer-backdrop.active {
        display: block;
    }
    .drawer-panel {
        position: fixed;
        top: 0;
        right: 0;
        z-index: 1210;
        width: min(460px, calc(100vw - 18px));
        height: 100vh;
        overflow-y: auto;
        margin: 0;
        border-radius: 0;
        border: 0;
        border-left: 1px solid #dce6f1;
        background: #ffffff;
        box-shadow: -18px 0 42px rgba(18, 63, 102, 0.18);
        padding: 18px;
        transform: translateX(105%);
        transition: transform 0.22s ease;
    }
    .drawer-panel.active {
        transform: translateX(0);
    }
    .drawer-panel h3 {
        padding-right: 42px;
    }
    .drawer-close {
        position: absolute;
        top: 14px;
        right: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border: 1px solid #d7e2ed;
        border-radius: 8px;
        background: #fff;
        color: #28445f;
        cursor: pointer;
    }
    .drawer-close i {
        width: 16px;
        height: 16px;
    }
    .tpl-shell {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 14px;
        min-height: 78vh;
    }
    .tpl-layers,
    .tpl-main {
        background: #fff;
        border: 1px solid #e3e9f2;
        border-radius: 14px;
        box-shadow: 0 10px 28px rgba(18, 63, 102, 0.08);
    }
    .tpl-layers {
        padding: 14px;
        align-self: start;
        position: sticky;
        top: 14px;
    }
    .tpl-title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: #20384f;
    }
    .tpl-sub {
        margin: 5px 0 12px;
        font-size: 0.8rem;
        color: #5e7388;
    }
    .layer-list {
        display: grid;
        gap: 8px;
    }
    .layer-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border: 1px solid #e4ebf3;
        border-radius: 10px;
        padding: 8px 10px;
        background: #f9fbfe;
    }
    .layer-item strong {
        font-size: 0.86rem;
        color: #24374d;
    }
    .layer-item label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.77rem;
        color: #50657b;
    }
    .tpl-clear {
        margin-top: 12px;
        border: 0;
        border-radius: 10px;
        padding: 9px 12px;
        font-size: 0.8rem;
        font-weight: 700;
        color: #fff;
        background: #8b2cf5;
        cursor: pointer;
        width: 100%;
    }
    .content-nav {
        margin-top: 16px;
        padding-top: 12px;
        border-top: 1px solid #e4ebf3;
    }
    .content-nav-list {
        display: grid;
        gap: 7px;
        margin-top: 10px;
    }
    .content-nav-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        border: 1px solid #e0e8f1;
        border-radius: 9px;
        background: #ffffff;
        color: #29445f;
        padding: 9px 10px;
        font-size: 0.8rem;
        font-weight: 800;
        text-align: left;
        cursor: pointer;
    }
    .content-nav-btn:hover,
    .content-nav-btn.active {
        border-color: var(--theme-color);
        background: #eefbfb;
        color: var(--theme-color-dark);
    }
    .content-nav-btn i {
        width: 15px;
        height: 15px;
        flex-shrink: 0;
    }

    .tpl-main {
        padding: 14px;
    }
    .tpl-alert {
        display: none;
        margin-bottom: 10px;
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 0.82rem;
        font-weight: 700;
    }
    .tpl-alert.success { background: #e9f8ee; color: #0e7b3d; border: 1px solid #bde7cc; }
    .tpl-alert.error { background: #fdecee; color: #ad2c35; border: 1px solid #f5c2c7; }

    .preview-frame {
        border: 1px solid #dde6f0;
        border-radius: 12px;
        overflow: hidden;
        background: #f0f4f9;
    }
    .preview-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
        border-bottom: 1px solid #dde6f0;
        background: #fff;
    }
    .preview-toolbar strong {
        color: #243b53;
        font-size: 0.82rem;
    }
    .preview-page-tabs {
        display: inline-flex;
        gap: 6px;
        padding: 4px;
        border-radius: 10px;
        background: #eef3f8;
    }
    .preview-page-tab {
        border: 0;
        border-radius: 7px;
        padding: 7px 12px;
        background: transparent;
        color: #53677c;
        font-size: 0.78rem;
        font-weight: 800;
        cursor: pointer;
    }
    .preview-page-tab.active {
        background: var(--theme-color);
        color: #fff;
        box-shadow: 0 5px 12px rgba(var(--theme-rgb), .2);
    }
    .pv-page-view[hidden] {
        display: none !important;
    }
    .preview-canvas {
        --pv-page: #f4f7fb;
        --pv-surface: #ffffff;
        --pv-ink: #1b2432;
        --pv-muted: #5f6f86;
        --pv-hero-a: #79a9d1;
        --pv-hero-b: #d4e9f7;
        --pv-accent: #f15a3b;
        --pv-radius-xl: 12px;
        --pv-radius-lg: 10px;
        --pv-radius-md: 8px;
        --pv-shadow: none;
        background: var(--pv-page);
        color: var(--pv-ink);
        padding: 14px;
        font-family: "Nunito Sans", sans-serif;
    }
    .pv-header {
        background: var(--pv-surface);
        border: 1px solid #dfe7f1;
        border-radius: var(--pv-radius-md);
        padding: 10px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .pv-brand {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-weight: 800;
        font-size: 1rem;
    }
    .pv-brand img {
        width: 90px;
        height: 32px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #d5dfeb;
        background: #ecf1f6;
    }
    .pv-nav {
        display: inline-flex;
        gap: 18px;
        align-items: center;
        font-size: 0.86rem;
        font-weight: 700;
    }
    .pv-nav .btn {
        border-radius: var(--pv-radius-md);
        padding: 6px 10px;
        border: 0;
        font-weight: 700;
    }
    .pv-nav .btn.primary {
        background: var(--pv-accent);
        color: #fff;
    }
    .pv-nav .btn.secondary {
        background: #2a3244;
        color: #fff;
    }

    .pv-hero {
        position: relative;
        margin-top: 10px;
        min-height: 270px;
        border-radius: var(--pv-radius-xl);
        border: 1px solid #d6e0ec;
        overflow: hidden;
        background: linear-gradient(120deg, var(--pv-hero-a), var(--pv-hero-b));
        display: grid;
        place-items: center;
    }
    .pv-hero-bg {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
        opacity: 0.72;
    }
    .pv-hero-overlay {
        position: relative;
        z-index: 2;
        width: min(760px, calc(100% - 30px));
        color: #fff;
        text-shadow: 0 2px 12px rgba(0,0,0,.35);
    }
    .pv-hero-title {
        margin: 0;
        font-size: 2.25rem;
        font-weight: 800;
    }
    .pv-hero-text {
        margin: 10px 0 0;
        font-size: 1.05rem;
        font-weight: 700;
        max-width: 760px;
    }
    .pv-search {
        margin-top: 14px;
        background: rgba(255,255,255,.92);
        border: 1px solid rgba(255,255,255,.95);
        color: #1f3348;
        border-radius: 999px;
        padding: 9px 14px;
        max-width: 290px;
        font-size: .85rem;
        font-weight: 700;
    }

    .pv-section {
        margin-top: 12px;
        border: 1px solid #d9e2ee;
        border-radius: var(--pv-radius-xl);
        background: var(--pv-surface);
        padding: 18px;
        box-shadow: var(--pv-shadow);
    }
    .pv-section h3 {
        margin: 0;
        font-size: 2rem;
        text-align: center;
    }
    .pv-section p {
        margin: 8px auto 0;
        max-width: 760px;
        text-align: center;
        color: var(--pv-muted);
        font-size: .95rem;
        line-height: 1.5;
    }
    .pv-biz {
        margin-top: 12px;
        border: 1px solid #d9e2ee;
        border-radius: var(--pv-radius-lg);
        background: #e9eef6;
        padding: 12px;
    }
    .pv-biz-top {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 14px;
        align-items: center;
    }
    .pv-biz-top.reverse {
        grid-template-columns: 1fr 260px;
    }
    .pv-biz-image {
        min-height: 160px;
        border-radius: 6px;
        border: 1px solid #cdd8e6;
        background: #c5ccd6;
        color: #3d526d;
        display: grid;
        place-items: center;
        font-weight: 700;
    }
    .pv-biz-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 800;
        color: #20395a;
    }
    .pv-biz-text {
        margin: 8px 0 0;
        color: #354f6f;
        font-size: .9rem;
        line-height: 1.55;
    }
    .pv-biz-services-title {
        margin: 14px 0 8px;
        text-align: center;
        font-size: 1.7rem;
        font-weight: 800;
        color: #142943;
    }
    .pv-biz-services {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }
    .pv-biz-card {
        border: 1px solid #cfd9e7;
        background: #f5f8fd;
        border-radius: var(--pv-radius-md);
        padding: 10px;
    }
    .pv-biz-card h5 {
        margin: 0;
        font-size: 0.95rem;
        color: #1f3553;
        font-weight: 800;
    }
    .pv-biz-card p {
        margin: 5px 0 0;
        font-size: 0.8rem;
        color: #445d79;
        line-height: 1.45;
    }
    .pv-testimonials,
    .pv-newsletter {
        margin-top: 12px;
        padding: 14px;
        border: 1px solid #d9e2ee;
        border-radius: var(--pv-radius-lg);
        background: var(--pv-surface);
    }
    .pv-feature-title {
        margin: 0;
        color: var(--pv-ink);
        text-align: center;
        font-size: 1.2rem;
        font-weight: 900;
    }
    .pv-feature-subtitle {
        margin: 5px 0 12px;
        color: var(--pv-muted);
        text-align: center;
        font-size: 0.75rem;
    }
    .pv-testimonial-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }
    .pv-testimonial-card {
        padding: 10px;
        border-radius: var(--pv-radius-md);
        background: var(--pv-page);
        color: var(--pv-ink);
        font-size: 0.7rem;
    }
    .pv-testimonial-card strong {
        display: block;
        margin-top: 8px;
    }
    .pv-testimonial-card span {
        color: var(--pv-muted);
    }
    .pv-newsletter {
        display: grid;
        grid-template-columns: 1fr auto;
        align-items: center;
        gap: 14px;
        background: linear-gradient(125deg, var(--pv-hero-a), var(--pv-hero-b));
    }
    .pv-newsletter h4,
    .pv-newsletter p {
        margin: 0;
    }
    .pv-newsletter p {
        margin-top: 4px;
        color: var(--pv-muted);
        font-size: 0.72rem;
    }
    .pv-newsletter-action {
        padding: 8px 12px;
        border-radius: 999px;
        background: var(--pv-accent);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 900;
    }
    .pv-about {
        padding-top: 12px;
    }
    .pv-about-hero {
        display: grid;
        grid-template-columns: 1.1fr .9fr;
        gap: 12px;
    }
    .pv-about-copy,
    .pv-about-promise,
    .pv-about-stat,
    .pv-about-story,
    .pv-about-value,
    .pv-contact-copy,
    .pv-contact-form {
        border: 1px solid #d9e2ee;
        border-radius: var(--pv-radius-lg);
        background: var(--pv-surface);
        box-shadow: var(--pv-shadow);
    }
    .pv-about-copy,
    .pv-about-promise {
        padding: 22px;
    }
    .pv-about-copy h2 {
        margin: 0;
        color: var(--pv-ink);
        font-size: 2.3rem;
        line-height: 1.05;
        font-weight: 900;
    }
    .pv-about-copy p,
    .pv-about-promise p,
    .pv-about-story p,
    .pv-about-value p,
    .pv-contact-copy p {
        color: var(--pv-muted);
        line-height: 1.55;
    }
    .pv-about-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 14px;
    }
    .pv-about-tag {
        padding: 6px 10px;
        border: 1px solid #ded7cf;
        border-radius: 999px;
        background: #f6f1eb;
        font-size: .72rem;
        font-weight: 800;
    }
    .pv-about-promise {
        background: #3f8780;
        color: #fff;
    }
    .pv-about-promise h3,
    .pv-about-promise p {
        color: #fff;
    }
    .pv-about-promise h3,
    .pv-about-story h3,
    .pv-about-value h4 {
        margin: 0;
        font-weight: 900;
    }
    .pv-about-stats,
    .pv-about-values {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }
    .pv-about-stat {
        padding: 14px;
        text-align: center;
    }
    .pv-about-stat strong {
        display: block;
        color: #246b68;
        font-size: 1.35rem;
    }
    .pv-about-stat span {
        color: var(--pv-muted);
        font-size: .75rem;
    }
    .pv-about-stories {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }
    .pv-about-story,
    .pv-about-value {
        padding: 14px;
    }
    .pv-about-value {
        background: #fff8f1;
    }
    .pv-about-value i {
        color: var(--pv-accent);
        margin-bottom: 7px;
    }
    .pv-contact {
        display: grid;
        grid-template-columns: 1.05fr .95fr;
        gap: 12px;
        padding-top: 12px;
    }
    .pv-contact-copy,
    .pv-contact-form {
        padding: 20px;
    }
    .pv-contact-copy h2,
    .pv-contact-form h3 {
        margin: 0;
        color: var(--pv-ink);
        font-weight: 900;
    }
    .pv-contact-info {
        display: grid;
        gap: 7px;
        margin-top: 14px;
    }
    .pv-contact-info div,
    .pv-contact-input {
        padding: 9px 11px;
        border: 1px solid #dce6df;
        border-radius: var(--pv-radius-md);
        background: #f4f8f6;
        color: var(--pv-muted);
        font-size: .76rem;
    }
    .pv-contact-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: 12px;
    }
    .pv-contact-input.full {
        grid-column: 1 / -1;
        min-height: 60px;
    }
    .pv-contact-submit {
        display: inline-block;
        margin-top: 9px;
        padding: 8px 12px;
        border-radius: var(--pv-radius-md);
        background: var(--pv-accent);
        color: #fff;
        font-size: .75rem;
        font-weight: 900;
    }

    .pv-footer {
        margin-top: 12px;
        background: #1f2a40;
        border-radius: var(--pv-radius-lg);
        color: #dbe1ed;
        padding: 14px;
    }
    .pv-footer-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }
    .pv-footer h4 {
        margin: 0 0 6px;
        color: #fff;
        font-size: 0.88rem;
    }
    .pv-footer p {
        margin: 0;
        font-size: 0.8rem;
        line-height: 1.45;
    }
    .pv-copy {
        margin-top: 8px;
        border-top: 1px solid rgba(255,255,255,.2);
        padding-top: 8px;
        font-size: .78rem;
    }

    .settings-grid {
        display: block;
        margin: 0;
    }
    .setting-card {
        border: 1px solid #e4ebf4;
        border-radius: 11px;
        padding: 10px;
        background: #fbfdff;
    }
    .setting-card h3 {
        margin: 0 0 8px;
        font-size: .86rem;
        color: #24384f;
        font-weight: 800;
    }
    .field {
        display: flex;
        flex-direction: column;
        gap: 4px;
        margin-top: 7px;
    }
    .field:first-child {
        margin-top: 0;
    }
    .field label {
        font-size: .74rem;
        color: #4e6379;
        font-weight: 700;
    }
    .field input,
    .field textarea {
        border: 1px solid #d5e0eb;
        border-radius: 8px;
        padding: 7px 10px;
        font-size: .8rem;
        outline: none;
        background: #fff;
    }
    .field textarea {
        min-height: 62px;
        resize: vertical;
    }
    .field input[type="color"] {
        height: 36px;
        padding: 4px;
    }
    .template-image-upload {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 8px;
        align-items: center;
    }
    .template-image-upload input[type="file"] {
        min-width: 0;
        padding: 6px;
        background: #fff;
    }
    .template-image-upload .tpl-btn {
        white-space: nowrap;
    }
    .template-upload-status {
        min-height: 16px;
        margin-top: 5px;
        color: #607488;
        font-size: 0.74rem;
        font-weight: 700;
    }
    .template-upload-status.error {
        color: #b32b39;
    }
    .template-upload-status.success {
        color: #167447;
    }

    .advanced-box {
        margin-top: 10px;
        border: 1px solid #d8e2ee;
        border-radius: 10px;
        padding: 9px;
    }
    #templateJson {
        width: 100%;
        min-height: 220px;
        border: 1px solid #d6e0ea;
        border-radius: 8px;
        padding: 10px;
        font-size: 0.8rem;
        font-family: Consolas, "Courier New", monospace;
        line-height: 1.45;
        resize: vertical;
        outline: none;
    }
    .tpl-error {
        display: block;
        min-height: 16px;
        margin-top: 6px;
        color: #b32b39;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .tpl-actions {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 10px;
    }
    .tpl-btn {
        border: 0;
        border-radius: 9px;
        padding: 9px 14px;
        font-size: 0.82rem;
        font-weight: 700;
        background: var(--theme-color);
        color: #fff;
        cursor: pointer;
    }
    .tpl-btn.secondary {
        background: #e7eef7;
        color: #22374e;
    }

    @media (max-width: 1080px) {
        .tpl-page-head {
            grid-template-columns: 1fr auto;
        }
        .tpl-page-heading {
            grid-column: 1 / -1;
            grid-row: 2;
            text-align: left;
        }
        .tpl-shell {
            grid-template-columns: 1fr;
        }
        .tpl-layers {
            position: static;
        }
        .settings-grid {
            display: block;
        }
        .template-meta-grid {
            grid-template-columns: 1fr;
        }
        .pv-biz-top,
        .pv-biz-top.reverse,
        .pv-biz-services,
        .pv-about-hero,
        .pv-about-stories,
        .pv-contact {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="tpl-wrap">
    <div class="tpl-editor-card shadow-sm">
        <div class="tpl-page-head">
            <a class="tpl-back-btn" href="<?= base_url('admin/dashboard-template') ?>">
                <i data-lucide="arrow-left"></i>
                <span>Back to List</span>
            </a>
            <div class="tpl-page-heading">
                <h2 class="tpl-page-title"><?= $selectedTemplateId > 0 ? 'Edit Dashboard Template' : 'Create Dashboard Template' ?></h2>
                <div class="tpl-page-subtitle">Customize the user dashboard template without the admin sidebar.</div>
            </div>
            <div class="tpl-page-actions">
                <button type="submit" form="dashboardTemplateForm" class="tpl-btn tpl-save-top" id="saveTemplateBtn">
                    <i data-lucide="save"></i>
                    <span>Save Template</span>
                </button>
            </div>
        </div>

        <div class="tpl-editor-body">
    <div class="tpl-shell">
        <aside class="tpl-layers">
            <h2 class="tpl-title">Layers</h2>
            <p class="tpl-sub">Toggle sections to customize page layout.</p>
            <div class="layer-list">
                <div class="layer-item">
                    <strong>Header</strong>
                    <label><input type="checkbox" id="visHeader"> Show</label>
                </div>
                <div class="layer-item">
                    <strong>Hero</strong>
                    <label><input type="checkbox" id="visHero"> Show</label>
                </div>
                <div class="layer-item">
                    <strong>Section</strong>
                    <label><input type="checkbox" id="visSection"> Show</label>
                </div>
                <div class="layer-item">
                    <strong>Business</strong>
                    <label><input type="checkbox" id="visBusiness"> Show</label>
                </div>
                <div class="layer-item">
                    <strong>Business Alt</strong>
                    <label><input type="checkbox" id="visBusinessAlt"> Show</label>
                </div>
                <div class="layer-item">
                    <strong>Testimonials</strong>
                    <label><input type="checkbox" id="visTestimonials"> Show</label>
                </div>
                <div class="layer-item">
                    <strong>Newsletter</strong>
                    <label><input type="checkbox" id="visNewsletter"> Show</label>
                </div>
                <div class="layer-item">
                    <strong>Footer</strong>
                    <label><input type="checkbox" id="visFooter"> Show</label>
                </div>
            </div>
            <button type="button" class="tpl-clear" id="clearLayersBtn">Clear all layers</button>

            <div class="content-nav">
                <h2 class="tpl-title">Content</h2>
                <p class="tpl-sub">Jump to a template content panel.</p>
                <div class="content-nav-list">
                    <button type="button" class="content-nav-btn" data-target="templateMeta">
                        <i data-lucide="file-text"></i>
                        <span>Template Info</span>
                    </button>
                    <button type="button" class="content-nav-btn" data-target="panelBrand">
                        <i data-lucide="navigation"></i>
                        <span>Brand & Navigation</span>
                    </button>
                    <button type="button" class="content-nav-btn" data-target="panelHero">
                        <i data-lucide="image"></i>
                        <span>Hero</span>
                    </button>
                    <button type="button" class="content-nav-btn" data-target="panelSection">
                        <i data-lucide="panel-top"></i>
                        <span>Section</span>
                    </button>
                    <button type="button" class="content-nav-btn" data-target="panelAboutPage">
                        <i data-lucide="info"></i>
                        <span>About Page</span>
                    </button>
                    <button type="button" class="content-nav-btn" data-target="panelContactPage">
                        <i data-lucide="contact"></i>
                        <span>Contact Page</span>
                    </button>
                    <button type="button" class="content-nav-btn" data-target="panelBusiness">
                        <i data-lucide="briefcase"></i>
                        <span>Business</span>
                    </button>
                    <button type="button" class="content-nav-btn" data-target="panelTestimonials">
                        <i data-lucide="message-square-quote"></i>
                        <span>Testimonials</span>
                    </button>
                    <button type="button" class="content-nav-btn" data-target="panelNewsletter">
                        <i data-lucide="mail"></i>
                        <span>Newsletter</span>
                    </button>
                    <button type="button" class="content-nav-btn" data-target="panelDesign">
                        <i data-lucide="palette"></i>
                        <span>Design Colors</span>
                    </button>
                    <button type="button" class="content-nav-btn" data-target="panelFooter">
                        <i data-lucide="panel-bottom"></i>
                        <span>Footer</span>
                    </button>
                    <button type="button" class="content-nav-btn" data-target="panelJson">
                        <i data-lucide="braces"></i>
                        <span>Advanced JSON</span>
                    </button>
                </div>
            </div>
        </aside>

        <section class="tpl-main">
            <div id="tplAlert" class="tpl-alert" role="alert"></div>
            <div class="drawer-backdrop" id="drawerBackdrop"></div>

            <div class="preview-frame">
                <div class="preview-toolbar">
                    <strong>Live Page Preview</strong>
                    <div class="preview-page-tabs" role="tablist" aria-label="Preview page">
                        <button type="button" class="preview-page-tab active" data-preview-page="home">Home</button>
                        <button type="button" class="preview-page-tab" data-preview-page="about">About</button>
                        <button type="button" class="preview-page-tab" data-preview-page="contact">Contact</button>
                    </div>
                </div>
                <div class="preview-canvas" id="previewCanvas">
                    <header class="pv-header" id="pvHeader">
                        <div class="pv-brand">
                            <img id="pvLogo" src="" alt="logo">
                            <span id="pvBrandName">child.com</span>
                        </div>
                        <nav class="pv-nav">
                            <span id="pvNavHome">Home</span>
                            <span id="pvNavCourses">Courses</span>
                            <span id="pvNavContacts">Contact</span>
                            <span id="pvNavAbout">About</span>
                            <button class="btn primary" id="pvNavRegister" type="button">Register</button>
                            <button class="btn secondary" id="pvNavLogin" type="button">Login</button>
                        </nav>
                    </header>

                    <div class="pv-page-view" id="pvHomePage" data-page-view="home">
                    <section class="pv-hero" id="pvHero">
                        <div class="pv-hero-bg" id="pvHeroBg"></div>
                        <div class="pv-hero-overlay">
                            <h1 class="pv-hero-title" id="pvHeroTitle">{{PAGE_TITLE}}</h1>
                            <p class="pv-hero-text" id="pvHeroSubtitle">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                            <div class="pv-search" id="pvHeroSearch">Search Courses</div>
                        </div>
                    </section>

                    <section class="pv-section" id="pvSection">
                        <h3 id="pvSectionTitle">Title for this section</h3>
                        <p id="pvSectionDesc">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                    </section>

                    <section class="pv-biz" id="pvBiz">
                        <div class="pv-biz-top">
                            <div class="pv-biz-image" id="pvBusinessImage">Featured business image</div>
                            <div>
                                <h4 class="pv-biz-title" id="pvBusinessTitle">Elevating Business Performance Through Strategic Solutions</h4>
                                <p class="pv-biz-text" id="pvBusinessDesc">Increasingly many people use digital media for learning and continuing education. E-learning content formats that are individually modified to meet each learner needs are fundamental for successful outcomes.</p>
                            </div>
                        </div>
                        <h4 class="pv-biz-services-title" id="pvBusinessServicesTitle">Featured Services</h4>
                        <div class="pv-biz-services">
                            <article class="pv-biz-card">
                                <h5 id="pvService1Title">Talent Management Strategy</h5>
                                <p id="pvService1Desc">Build stronger teams with focused hiring, role mapping, and continuous capability development.</p>
                            </article>
                            <article class="pv-biz-card">
                                <h5 id="pvService2Title">Innovation &amp; Digital Transformation</h5>
                                <p id="pvService2Desc">Modernize operations through practical digital workflows, automation, and customer-first experiences.</p>
                            </article>
                            <article class="pv-biz-card">
                                <h5 id="pvService3Title">Market Expansion Advisory</h5>
                                <p id="pvService3Desc">Scale into new regions with structured research, channel planning, and measurable growth strategy.</p>
                            </article>
                        </div>
                    </section>

                    <section class="pv-biz" id="pvBizAlt">
                        <div class="pv-biz-top reverse">
                            <div>
                                <h4 class="pv-biz-title" id="pvBusinessAltTitle">Driving Better Outcomes With Practical Business Execution</h4>
                                <p class="pv-biz-text" id="pvBusinessAltDesc">From planning to delivery, structured execution and clear communication help teams move faster, reduce risk, and create long-term business value.</p>
                            </div>
                            <div class="pv-biz-image" id="pvBusinessAltImage">Featured business image</div>
                        </div>
                    </section>

                    <section class="pv-testimonials" id="pvTestimonials">
                        <h4 class="pv-feature-title" id="pvTestimonialsTitle">Loved by families</h4>
                        <p class="pv-feature-subtitle" id="pvTestimonialsSubtitle">Real feedback from customers who shop with us.</p>
                        <div class="pv-testimonial-grid">
                            <article class="pv-testimonial-card"><div id="pvTestimonial1Quote">Excellent products and quick delivery.</div><strong id="pvTestimonial1Name">Priya Sharma</strong><span id="pvTestimonial1Role">Verified customer</span></article>
                            <article class="pv-testimonial-card"><div id="pvTestimonial2Quote">Easy to find safe products.</div><strong id="pvTestimonial2Name">Rahul Mehta</strong><span id="pvTestimonial2Role">Parent</span></article>
                            <article class="pv-testimonial-card"><div id="pvTestimonial3Quote">Helpful support and smooth checkout.</div><strong id="pvTestimonial3Name">Ananya Patel</strong><span id="pvTestimonial3Role">Returning customer</span></article>
                        </div>
                    </section>

                    <section class="pv-newsletter" id="pvNewsletter">
                        <div>
                            <h4 id="pvNewsletterTitle">Get offers and new arrivals</h4>
                            <p id="pvNewsletterDescription">Join our newsletter for updates and exclusive deals.</p>
                        </div>
                        <div class="pv-newsletter-action" id="pvNewsletterButton">Subscribe</div>
                    </section>
                    </div>

                    <div class="pv-page-view pv-about" id="pvAboutPage" data-page-view="about" hidden>
                        <section class="pv-about-hero">
                            <div class="pv-about-copy">
                                <h2 id="pvAboutHeroTitle">Built for joyful childhoods.</h2>
                                <p id="pvAboutHeroDescription">We curate thoughtful products and experiences for families who value quality, safety, and delight.</p>
                                <div class="pv-about-tags">
                                    <span class="pv-about-tag" id="pvAboutTag1">Safety-first materials</span>
                                    <span class="pv-about-tag" id="pvAboutTag2">Family-led sourcing</span>
                                    <span class="pv-about-tag" id="pvAboutTag3">Crafted for comfort</span>
                                </div>
                            </div>
                            <div class="pv-about-promise">
                                <h3 id="pvAboutPromiseTitle">Our Promise</h3>
                                <p id="pvAboutPromiseText1">We partner with trusted makers and verify every item for durability, comfort, and responsible sourcing.</p>
                                <p id="pvAboutPromiseText2">Every purchase supports community initiatives for early learning and childcare.</p>
                            </div>
                        </section>
                        <section class="pv-about-stats">
                            <div class="pv-about-stat"><strong id="pvAboutStat1Value">12k+</strong><span id="pvAboutStat1Label">Families served</span></div>
                            <div class="pv-about-stat"><strong id="pvAboutStat2Value">350+</strong><span id="pvAboutStat2Label">Trusted brands</span></div>
                            <div class="pv-about-stat"><strong id="pvAboutStat3Value">98%</strong><span id="pvAboutStat3Label">Happy parent reviews</span></div>
                        </section>
                        <section class="pv-about-stories">
                            <article class="pv-about-story"><h3 id="pvAboutStoryTitle">Our Story</h3><p id="pvAboutStoryDescription"></p></article>
                            <article class="pv-about-story"><h3 id="pvAboutWorkTitle">How We Work</h3><p id="pvAboutWorkDescription"></p></article>
                        </section>
                        <section class="pv-about-values">
                            <article class="pv-about-value"><i data-lucide="heart"></i><h4 id="pvAboutValue1Title">Care Driven</h4><p id="pvAboutValue1Description"></p></article>
                            <article class="pv-about-value"><i data-lucide="leaf"></i><h4 id="pvAboutValue2Title">Responsible</h4><p id="pvAboutValue2Description"></p></article>
                            <article class="pv-about-value"><i data-lucide="star"></i><h4 id="pvAboutValue3Title">Delightful</h4><p id="pvAboutValue3Description"></p></article>
                        </section>
                    </div>

                    <div class="pv-page-view pv-contact" id="pvContactPage" data-page-view="contact" hidden>
                        <section class="pv-contact-copy">
                            <h2 id="pvContactHeroTitle">Let us talk.</h2>
                            <p id="pvContactHeroDescription"></p>
                            <div class="pv-contact-info">
                                <div id="pvContactEmail">support@child.com</div>
                                <div id="pvContactPhone">+91 90000 00000</div>
                                <div id="pvContactAddress">9/14 Lake View Road, Chennai</div>
                            </div>
                        </section>
                        <section class="pv-contact-form">
                            <h3 id="pvContactFormTitle">Send a message</h3>
                            <div class="pv-contact-form-grid">
                                <div class="pv-contact-input" id="pvContactNameLabel">Full Name</div>
                                <div class="pv-contact-input" id="pvContactEmailLabel">Email</div>
                                <div class="pv-contact-input" id="pvContactPhoneLabel">Phone</div>
                                <div class="pv-contact-input full" id="pvContactMessageLabel">Message</div>
                            </div>
                            <span class="pv-contact-submit" id="pvContactSubmitText">Send Message</span>
                        </section>
                    </div>

                    <footer class="pv-footer" id="pvFooter">
                        <div class="pv-footer-grid">
                            <div>
                                <h4 id="pvFooterCol1Title">Customer Care</h4>
                                <p id="pvFooterCol1Lines">Help Center<br>Track Order<br>Return Policy</p>
                            </div>
                            <div>
                                <h4 id="pvFooterCol2Title">Company</h4>
                                <p id="pvFooterCol2Lines">About Us<br>Privacy Policy<br>Terms &amp; Conditions</p>
                            </div>
                            <div>
                                <h4 id="pvFooterCol3Title">Contact</h4>
                                <p id="pvFooterContact">Email: support@child.com<br>Phone: +91 90000 00000</p>
                            </div>
                        </div>
                        <div class="pv-copy" id="pvFooterCopy">Copyright {year} child.com. All rights reserved.</div>
                    </footer>
                </div>
            </div>

            <form id="dashboardTemplateForm">
                <input type="hidden" id="templateId" name="id" value="<?= esc((string) $selectedTemplateId) ?>">
                <div class="template-meta-grid drawer-panel" id="templateMeta">
                    <button type="button" class="drawer-close" aria-label="Close panel"><i data-lucide="x"></i></button>
                    <div class="field">
                        <label>Template Name</label>
                        <input type="text" id="templateName" name="template_name" value="<?= esc($selectedTemplateName) ?>">
                    </div>
                    <label class="template-check">
                        <input type="checkbox" id="templateActive" name="is_active" value="1" <?= $selectedTemplateActive === 1 ? 'checked' : '' ?>>
                        <span>Use as active dashboard</span>
                    </label>
                </div>
                <div class="settings-grid">
                    <div class="setting-card drawer-panel" id="panelBrand">
                        <button type="button" class="drawer-close" aria-label="Close panel"><i data-lucide="x"></i></button>
                        <h3>Brand & Navigation</h3>
                        <div class="field"><label>Brand Name</label><input type="text" data-path="branding.website_name"></div>
                        <div class="field"><label>Logo URL</label><input type="text" data-path="branding.logo_url"></div>
                        <div class="field"><label>Home</label><input type="text" data-path="nav.home"></div>
                        <div class="field"><label>Courses</label><input type="text" data-path="nav.courses"></div>
                        <div class="field"><label>Contacts</label><input type="text" data-path="nav.contacts"></div>
                        <div class="field"><label>About</label><input type="text" data-path="nav.about"></div>
                        <div class="field"><label>Register</label><input type="text" data-path="nav.register"></div>
                        <div class="field"><label>Login</label><input type="text" data-path="nav.login"></div>
                    </div>

                    <div class="setting-card drawer-panel" id="panelHero">
                        <button type="button" class="drawer-close" aria-label="Close panel"><i data-lucide="x"></i></button>
                        <h3>Hero</h3>
                        <div class="field"><label>Hero Title</label><input type="text" data-path="hero.title"></div>
                        <div class="field"><label>Hero Subtitle</label><textarea data-path="hero.subtitle"></textarea></div>
                        <div class="field"><label>Search Placeholder</label><input type="text" data-path="hero.search_placeholder"></div>
                        <div class="field"><label>Hero Background Image URL</label><input type="text" data-path="hero.background_image"></div>
                        <div class="field">
                            <label>Upload Hero Background</label>
                            <div class="template-image-upload">
                                <input type="file" class="template-image-file" accept=".jpg,.jpeg,.png,.webp,.gif" data-target-path="hero.background_image">
                                <button type="button" class="tpl-btn template-image-upload-btn">Upload</button>
                            </div>
                            <div class="template-upload-status"></div>
                        </div>
                    </div>

                    <div class="setting-card drawer-panel" id="panelSection">
                        <button type="button" class="drawer-close" aria-label="Close panel"><i data-lucide="x"></i></button>
                        <h3>Section</h3>
                        <div class="field"><label>Section Title</label><input type="text" data-path="about.title"></div>
                        <div class="field"><label>Section Description</label><textarea data-path="about.description"></textarea></div>
                    </div>

                    <div class="setting-card drawer-panel" id="panelAboutPage">
                        <button type="button" class="drawer-close" aria-label="Close panel"><i data-lucide="x"></i></button>
                        <h3>About Page</h3>
                        <div class="field"><label>Hero Title</label><input type="text" data-path="about_page.hero_title"></div>
                        <div class="field"><label>Hero Description</label><textarea data-path="about_page.hero_description"></textarea></div>
                        <div class="field"><label>Tag 1</label><input type="text" data-path="about_page.tags.0"></div>
                        <div class="field"><label>Tag 2</label><input type="text" data-path="about_page.tags.1"></div>
                        <div class="field"><label>Tag 3</label><input type="text" data-path="about_page.tags.2"></div>
                        <div class="field"><label>Promise Title</label><input type="text" data-path="about_page.promise_title"></div>
                        <div class="field"><label>Promise Paragraph 1</label><textarea data-path="about_page.promise_text_1"></textarea></div>
                        <div class="field"><label>Promise Paragraph 2</label><textarea data-path="about_page.promise_text_2"></textarea></div>
                        <div class="field"><label>Statistic 1 Value</label><input type="text" data-path="about_page.stats.0.value"></div>
                        <div class="field"><label>Statistic 1 Label</label><input type="text" data-path="about_page.stats.0.label"></div>
                        <div class="field"><label>Statistic 2 Value</label><input type="text" data-path="about_page.stats.1.value"></div>
                        <div class="field"><label>Statistic 2 Label</label><input type="text" data-path="about_page.stats.1.label"></div>
                        <div class="field"><label>Statistic 3 Value</label><input type="text" data-path="about_page.stats.2.value"></div>
                        <div class="field"><label>Statistic 3 Label</label><input type="text" data-path="about_page.stats.2.label"></div>
                        <div class="field"><label>Story Title</label><input type="text" data-path="about_page.story_title"></div>
                        <div class="field"><label>Story Description</label><textarea data-path="about_page.story_description"></textarea></div>
                        <div class="field"><label>How We Work Title</label><input type="text" data-path="about_page.work_title"></div>
                        <div class="field"><label>How We Work Description</label><textarea data-path="about_page.work_description"></textarea></div>
                        <div class="field"><label>Value 1 Title</label><input type="text" data-path="about_page.values.0.title"></div>
                        <div class="field"><label>Value 1 Description</label><textarea data-path="about_page.values.0.description"></textarea></div>
                        <div class="field"><label>Value 2 Title</label><input type="text" data-path="about_page.values.1.title"></div>
                        <div class="field"><label>Value 2 Description</label><textarea data-path="about_page.values.1.description"></textarea></div>
                        <div class="field"><label>Value 3 Title</label><input type="text" data-path="about_page.values.2.title"></div>
                        <div class="field"><label>Value 3 Description</label><textarea data-path="about_page.values.2.description"></textarea></div>
                    </div>

                    <div class="setting-card drawer-panel" id="panelContactPage">
                        <button type="button" class="drawer-close" aria-label="Close panel"><i data-lucide="x"></i></button>
                        <h3>Contact Page</h3>
                        <div class="field"><label>Hero Title</label><input type="text" data-path="contact_page.hero_title"></div>
                        <div class="field"><label>Hero Description</label><textarea data-path="contact_page.hero_description"></textarea></div>
                        <div class="field"><label>Contact Email</label><input type="email" data-path="contact_page.email"></div>
                        <div class="field"><label>Contact Phone</label><input type="text" data-path="contact_page.phone"></div>
                        <div class="field"><label>Address</label><textarea data-path="contact_page.address"></textarea></div>
                        <div class="field"><label>Form Title</label><input type="text" data-path="contact_page.form_title"></div>
                        <div class="field"><label>Name Label</label><input type="text" data-path="contact_page.name_label"></div>
                        <div class="field"><label>Email Label</label><input type="text" data-path="contact_page.email_label"></div>
                        <div class="field"><label>Phone Label</label><input type="text" data-path="contact_page.phone_label"></div>
                        <div class="field"><label>Message Label</label><input type="text" data-path="contact_page.message_label"></div>
                        <div class="field"><label>Phone Placeholder</label><input type="text" data-path="contact_page.phone_placeholder"></div>
                        <div class="field"><label>Submit Button</label><input type="text" data-path="contact_page.submit_text"></div>
                        <div class="field"><label>Reset Button</label><input type="text" data-path="contact_page.reset_text"></div>
                        <div class="field"><label>Success Message</label><textarea data-path="contact_page.success_message"></textarea></div>
                    </div>

                    <div class="setting-card drawer-panel" id="panelBusiness">
                        <button type="button" class="drawer-close" aria-label="Close panel"><i data-lucide="x"></i></button>
                        <h3>Business Segments</h3>
                        <div class="field"><label>Business Title</label><input type="text" data-path="business.title"></div>
                        <div class="field"><label>Business Description</label><textarea data-path="business.description"></textarea></div>
                        <div class="field"><label>Main Business Image URL</label><input type="text" data-path="business.image_url"></div>
                        <div class="field">
                            <label>Upload Main Business Image</label>
                            <div class="template-image-upload">
                                <input type="file" class="template-image-file" accept=".jpg,.jpeg,.png,.webp,.gif" data-target-path="business.image_url">
                                <button type="button" class="tpl-btn template-image-upload-btn">Upload</button>
                            </div>
                            <div class="template-upload-status"></div>
                        </div>
                        <div class="field"><label>Services Title</label><input type="text" data-path="business.services_title"></div>
                        <div class="field"><label>Service 1 Title</label><input type="text" id="fld_service1_title"></div>
                        <div class="field"><label>Service 1 Description</label><textarea id="fld_service1_desc"></textarea></div>
                        <div class="field"><label>Service 2 Title</label><input type="text" id="fld_service2_title"></div>
                        <div class="field"><label>Service 2 Description</label><textarea id="fld_service2_desc"></textarea></div>
                        <div class="field"><label>Service 3 Title</label><input type="text" id="fld_service3_title"></div>
                        <div class="field"><label>Service 3 Description</label><textarea id="fld_service3_desc"></textarea></div>
                        <div class="field"><label>Business Alt Title</label><input type="text" data-path="business_alt.title"></div>
                        <div class="field"><label>Business Alt Description</label><textarea data-path="business_alt.description"></textarea></div>
                        <div class="field"><label>Alternate Business Image URL</label><input type="text" data-path="business_alt.image_url"></div>
                        <div class="field">
                            <label>Upload Alternate Business Image</label>
                            <div class="template-image-upload">
                                <input type="file" class="template-image-file" accept=".jpg,.jpeg,.png,.webp,.gif" data-target-path="business_alt.image_url">
                                <button type="button" class="tpl-btn template-image-upload-btn">Upload</button>
                            </div>
                            <div class="template-upload-status"></div>
                        </div>
                    </div>

                    <div class="setting-card drawer-panel" id="panelTestimonials">
                        <button type="button" class="drawer-close" aria-label="Close panel"><i data-lucide="x"></i></button>
                        <h3>Testimonials</h3>
                        <div class="field"><label>Section Title</label><input type="text" data-path="testimonials.title"></div>
                        <div class="field"><label>Section Subtitle</label><textarea data-path="testimonials.subtitle"></textarea></div>
                        <div class="field"><label>Testimonial 1 Quote</label><textarea data-path="testimonials.items.0.quote"></textarea></div>
                        <div class="field"><label>Testimonial 1 Name</label><input type="text" data-path="testimonials.items.0.name"></div>
                        <div class="field"><label>Testimonial 1 Role</label><input type="text" data-path="testimonials.items.0.role"></div>
                        <div class="field"><label>Testimonial 2 Quote</label><textarea data-path="testimonials.items.1.quote"></textarea></div>
                        <div class="field"><label>Testimonial 2 Name</label><input type="text" data-path="testimonials.items.1.name"></div>
                        <div class="field"><label>Testimonial 2 Role</label><input type="text" data-path="testimonials.items.1.role"></div>
                        <div class="field"><label>Testimonial 3 Quote</label><textarea data-path="testimonials.items.2.quote"></textarea></div>
                        <div class="field"><label>Testimonial 3 Name</label><input type="text" data-path="testimonials.items.2.name"></div>
                        <div class="field"><label>Testimonial 3 Role</label><input type="text" data-path="testimonials.items.2.role"></div>
                    </div>

                    <div class="setting-card drawer-panel" id="panelNewsletter">
                        <button type="button" class="drawer-close" aria-label="Close panel"><i data-lucide="x"></i></button>
                        <h3>Newsletter</h3>
                        <div class="field"><label>Title</label><input type="text" data-path="newsletter.title"></div>
                        <div class="field"><label>Description</label><textarea data-path="newsletter.description"></textarea></div>
                        <div class="field"><label>Email Placeholder</label><input type="text" data-path="newsletter.placeholder"></div>
                        <div class="field"><label>Button Text</label><input type="text" data-path="newsletter.button_text"></div>
                        <div class="field"><label>Success Message</label><input type="text" data-path="newsletter.success_message"></div>
                    </div>

                    <div class="setting-card drawer-panel" id="panelDesign">
                        <button type="button" class="drawer-close" aria-label="Close panel"><i data-lucide="x"></i></button>
                        <h3>Design Colors</h3>
                        <div class="field"><label>Page Background</label><input type="color" data-path="design.page_bg"></div>
                        <div class="field"><label>Surface</label><input type="color" data-path="design.surface"></div>
                        <div class="field"><label>Text</label><input type="color" data-path="design.text_color"></div>
                        <div class="field"><label>Muted Text</label><input type="color" data-path="design.muted_text"></div>
                        <div class="field"><label>Hero Gradient Start</label><input type="color" data-path="design.hero_gradient_start"></div>
                        <div class="field"><label>Hero Gradient End</label><input type="color" data-path="design.hero_gradient_end"></div>
                        <div class="field"><label>Accent</label><input type="color" data-path="design.accent"></div>
                        <div class="field"><label>Font Family</label><input type="text" data-path="design.font_family"></div>
                        <div class="field"><label>Content Max Width</label><input type="text" data-path="design.content_max_width"></div>
                        <div class="field"><label>Large Radius</label><input type="text" data-path="design.radius_xl"></div>
                        <div class="field"><label>Medium Radius</label><input type="text" data-path="design.radius_lg"></div>
                        <div class="field"><label>Small Radius</label><input type="text" data-path="design.radius_md"></div>
                        <div class="field"><label>Soft Shadow</label><input type="text" data-path="design.shadow_soft"></div>
                        <div class="field"><label>Hero Min Height</label><input type="text" data-path="design.hero_min_height"></div>
                        <div class="field"><label>Product Columns</label><input type="text" data-path="design.product_columns"></div>
                        <div class="field"><label>Custom CSS</label><textarea data-path="design.custom_css"></textarea></div>
                    </div>

                    <div class="setting-card drawer-panel" id="panelFooter">
                        <button type="button" class="drawer-close" aria-label="Close panel"><i data-lucide="x"></i></button>
                        <h3>Footer</h3>
                        <div class="field"><label>Column 1 Title</label><input type="text" data-path="footer.column1_title"></div>
                        <div class="field"><label>Column 1 Links (one per line)</label><textarea id="fld_footer_col1_lines"></textarea></div>
                        <div class="field"><label>Column 2 Title</label><input type="text" data-path="footer.column2_title"></div>
                        <div class="field"><label>Column 2 Links (one per line)</label><textarea id="fld_footer_col2_lines"></textarea></div>
                        <div class="field"><label>Column 3 Title</label><input type="text" data-path="footer.column3_title"></div>
                        <div class="field"><label>Email</label><input type="text" data-path="footer.email"></div>
                        <div class="field"><label>Phone</label><input type="text" data-path="footer.phone"></div>
                        <div class="field"><label>Copyright</label><input type="text" data-path="footer.copyright"></div>
                    </div>
                </div>

                <details class="advanced-box drawer-panel" id="panelJson">
                    <button type="button" class="drawer-close" aria-label="Close panel"><i data-lucide="x"></i></button>
                    <summary><strong>Advanced JSON</strong></summary>
                    <textarea id="templateJson" name="template_json"><?= esc((string) ($templateJson ?? '{}')) ?></textarea>
                </details>

                <span class="tpl-error" id="templateJsonError"></span>
                <div class="tpl-actions">
                    <div>
                        <button type="button" class="tpl-btn secondary" id="loadFromJsonBtn">Load From JSON</button>
                        <button type="button" class="tpl-btn secondary" id="syncToJsonBtn">Update JSON</button>
                    </div>
                </div>
            </form>
        </section>
    </div>
        </div>
    </div>
</div>

<script>
$(function () {
    const form = $('#dashboardTemplateForm');
    const alertBox = $('#tplAlert');
    const errorBox = $('#templateJsonError');
    const btn = $('#saveTemplateBtn');
    const jsonBox = $('#templateJson');
    const templateId = $('#templateId');
    const templateName = $('#templateName');
    const templateActive = $('#templateActive');
    const loadFromJsonBtn = $('#loadFromJsonBtn');
    const syncToJsonBtn = $('#syncToJsonBtn');
    const clearLayersBtn = $('#clearLayersBtn');
    const contentNavBtns = $('.content-nav-btn');
    const drawerBackdrop = $('#drawerBackdrop');
    const drawerPanels = $('.drawer-panel');
    const drawerCloseBtns = $('.drawer-close');
    const fields = $('[data-path]');
    const imageUploadButtons = $('.template-image-upload-btn');
    const previewPageTabs = $('.preview-page-tab');

    const visHeader = $('#visHeader');
    const visHero = $('#visHero');
    const visSection = $('#visSection');
    const visBusiness = $('#visBusiness');
    const visBusinessAlt = $('#visBusinessAlt');
    const visTestimonials = $('#visTestimonials');
    const visNewsletter = $('#visNewsletter');
    const visFooter = $('#visFooter');

    const footerCol1Lines = $('#fld_footer_col1_lines');
    const footerCol2Lines = $('#fld_footer_col2_lines');
    const service1Title = $('#fld_service1_title');
    const service1Desc = $('#fld_service1_desc');
    const service2Title = $('#fld_service2_title');
    const service2Desc = $('#fld_service2_desc');
    const service3Title = $('#fld_service3_title');
    const service3Desc = $('#fld_service3_desc');

    let templateObj = {};
    let activePreviewPage = 'home';

    function showAlert(type, msg) {
        alertBox.removeClass('success error').addClass(type).text(msg).fadeIn(120);
        setTimeout(function () { alertBox.fadeOut(220); }, 2600);
    }

    function parseJsonValue() {
        try {
            const raw = String(jsonBox.val() || '').trim();
            if (raw === '') return {};
            const parsed = JSON.parse(raw);
            return (parsed && typeof parsed === 'object') ? parsed : {};
        } catch (err) {
            return null;
        }
    }

    function getByPath(obj, path) {
        return path.split('.').reduce(function (carry, key) {
            if (!carry || typeof carry !== 'object') return undefined;
            return carry[key];
        }, obj);
    }

    function setByPath(obj, path, value) {
        const parts = path.split('.');
        let ref = obj;
        for (let i = 0; i < parts.length - 1; i++) {
            if (!ref[parts[i]] || typeof ref[parts[i]] !== 'object') {
                ref[parts[i]] = {};
            }
            ref = ref[parts[i]];
        }
        ref[parts[parts.length - 1]] = value;
    }

    function parseLines(value) {
        return String(value || '')
            .split(/\r?\n/)
            .map(function (v) { return v.trim(); })
            .filter(function (v) { return v !== ''; });
    }

    function joinLines(value) {
        if (!Array.isArray(value)) return '';
        return value.map(function (v) { return String(v || '').trim(); }).filter(function (v) { return v !== ''; }).join('\n');
    }

    function ensureDefaults() {
        if (!templateObj.layers || typeof templateObj.layers !== 'object') {
            templateObj.layers = {};
        }
        if (typeof templateObj.layers.header !== 'boolean') templateObj.layers.header = true;
        if (typeof templateObj.layers.hero !== 'boolean') templateObj.layers.hero = true;
        if (typeof templateObj.layers.section !== 'boolean') templateObj.layers.section = true;
        if (typeof templateObj.layers.business !== 'boolean') templateObj.layers.business = true;
        if (typeof templateObj.layers.business_alt !== 'boolean') templateObj.layers.business_alt = true;
        if (typeof templateObj.layers.testimonials !== 'boolean') templateObj.layers.testimonials = true;
        if (typeof templateObj.layers.newsletter !== 'boolean') templateObj.layers.newsletter = true;
        if (typeof templateObj.layers.footer !== 'boolean') templateObj.layers.footer = true;
    }

    function syncServicesFieldsFromObject() {
        const services = Array.isArray(templateObj?.business?.services) ? templateObj.business.services : [];
        service1Title.val(String(services?.[0]?.title || ''));
        service1Desc.val(String(services?.[0]?.description || ''));
        service2Title.val(String(services?.[1]?.title || ''));
        service2Desc.val(String(services?.[1]?.description || ''));
        service3Title.val(String(services?.[2]?.title || ''));
        service3Desc.val(String(services?.[2]?.description || ''));
    }

    function syncObjectServicesFromFields() {
        if (!templateObj.business || typeof templateObj.business !== 'object') {
            templateObj.business = {};
        }
        templateObj.business.services = [
            {
                title: String(service1Title.val() || '').trim(),
                description: String(service1Desc.val() || '').trim()
            },
            {
                title: String(service2Title.val() || '').trim(),
                description: String(service2Desc.val() || '').trim()
            },
            {
                title: String(service3Title.val() || '').trim(),
                description: String(service3Desc.val() || '').trim()
            }
        ];
    }

    function syncFieldsFromObject() {
        fields.each(function () {
            const path = $(this).data('path');
            const value = getByPath(templateObj, path);
            if (typeof value === 'string') {
                $(this).val(value);
            }
        });

        footerCol1Lines.val(joinLines(templateObj.footer && templateObj.footer.column1_links));
        footerCol2Lines.val(joinLines(templateObj.footer && templateObj.footer.column2_links));
        syncServicesFieldsFromObject();

        ensureDefaults();
        visHeader.prop('checked', !!templateObj.layers.header);
        visHero.prop('checked', !!templateObj.layers.hero);
        visSection.prop('checked', !!templateObj.layers.section);
        visBusiness.prop('checked', !!templateObj.layers.business);
        visBusinessAlt.prop('checked', !!templateObj.layers.business_alt);
        visTestimonials.prop('checked', !!templateObj.layers.testimonials);
        visNewsletter.prop('checked', !!templateObj.layers.newsletter);
        visFooter.prop('checked', !!templateObj.layers.footer);
    }

    function syncObjectFromFields() {
        fields.each(function () {
            const path = $(this).data('path');
            const value = String($(this).val() || '').trim();
            if (value !== '') {
                setByPath(templateObj, path, value);
            }
        });

        if (!templateObj.footer || typeof templateObj.footer !== 'object') {
            templateObj.footer = {};
        }
        templateObj.footer.column1_links = parseLines(footerCol1Lines.val());
        templateObj.footer.column2_links = parseLines(footerCol2Lines.val());
        syncObjectServicesFromFields();

        ensureDefaults();
        templateObj.layers.header = visHeader.is(':checked');
        templateObj.layers.hero = visHero.is(':checked');
        templateObj.layers.section = visSection.is(':checked');
        templateObj.layers.business = visBusiness.is(':checked');
        templateObj.layers.business_alt = visBusinessAlt.is(':checked');
        templateObj.layers.testimonials = visTestimonials.is(':checked');
        templateObj.layers.newsletter = visNewsletter.is(':checked');
        templateObj.layers.footer = visFooter.is(':checked');

        jsonBox.val(JSON.stringify(templateObj, null, 2));
        renderPreview();
    }

    function setText(id, value) {
        if (typeof value !== 'string') return;
        $(id).text(value);
    }

    function toHexColor(value, fallback) {
        const v = String(value || '').trim();
        if (/^#[0-9a-fA-F]{6}$/.test(v)) return v;
        return fallback;
    }

    function cssSize(value, fallback) {
        const v = String(value || '').trim();
        if (/^\d+(\.\d+)?(px|rem|em|%|vh|vw)$/.test(v)) return v;
        return fallback;
    }

    function cssShadow(value, fallback) {
        const v = String(value || '').trim();
        if (v === '' || /[{}<>]/.test(v)) return fallback;
        return v;
    }

    function cssFont(value, fallback) {
        const v = String(value || '').trim();
        if (v === '' || /[{}<>;]/.test(v)) return fallback;
        return v;
    }

    function renderPreviewImage(selector, value, fallbackText) {
        const node = $(selector);
        const url = String(value || '').trim();
        if (url === '') {
            node.css({
                backgroundImage: 'none',
                backgroundSize: '',
                backgroundPosition: ''
            }).text(fallbackText);
            return;
        }
        node.css({
            backgroundImage: 'url("' + url.replace(/"/g, '\\"') + '")',
            backgroundSize: 'cover',
            backgroundPosition: 'center'
        }).text('');
    }

    function switchPreviewPage(page) {
        const nextPage = ['home', 'about', 'contact'].includes(page) ? page : 'home';
        activePreviewPage = nextPage;
        $('[data-page-view]').prop('hidden', true);
        $('[data-page-view="' + nextPage + '"]').prop('hidden', false);
        previewPageTabs.removeClass('active');
        previewPageTabs.filter('[data-preview-page="' + nextPage + '"]').addClass('active');
    }

    function renderPreview() {
        const year = String(new Date().getFullYear());

        const design = templateObj.design || {};
        const brand = templateObj.branding || {};
        const nav = templateObj.nav || {};
        const hero = templateObj.hero || {};
        const about = templateObj.about || {};
        const aboutPage = templateObj.about_page || {};
        const contactPage = templateObj.contact_page || {};
        const footer = templateObj.footer || {};
        const layers = templateObj.layers || {};

        const canvas = document.getElementById('previewCanvas');
        canvas.style.setProperty('--pv-page', toHexColor(design.page_bg, '#f4f7fb'));
        canvas.style.setProperty('--pv-surface', toHexColor(design.surface, '#ffffff'));
        canvas.style.setProperty('--pv-ink', toHexColor(design.text_color, '#1b2432'));
        canvas.style.setProperty('--pv-muted', toHexColor(design.muted_text, '#5f6f86'));
        canvas.style.setProperty('--pv-hero-a', toHexColor(design.hero_gradient_start, '#79a9d1'));
        canvas.style.setProperty('--pv-hero-b', toHexColor(design.hero_gradient_end, '#d4e9f7'));
        canvas.style.setProperty('--pv-accent', toHexColor(design.accent, '#f15a3b'));
        canvas.style.setProperty('--pv-radius-xl', cssSize(design.radius_xl, '12px'));
        canvas.style.setProperty('--pv-radius-lg', cssSize(design.radius_lg, '10px'));
        canvas.style.setProperty('--pv-radius-md', cssSize(design.radius_md, '8px'));
        canvas.style.setProperty('--pv-shadow', cssShadow(design.shadow_soft, 'none'));
        canvas.style.fontFamily = cssFont(design.font_family, '"Nunito Sans", sans-serif');
        $('#pvHero').css('min-height', cssSize(design.hero_min_height, '270px'));

        setText('#pvBrandName', brand.website_name || 'child.com');
        $('#pvLogo').attr('src', String(brand.logo_url || '').trim() || '');

        setText('#pvNavHome', nav.home || 'Home');
        setText('#pvNavCourses', nav.courses || 'Courses');
        setText('#pvNavContacts', nav.contacts || 'Contact');
        setText('#pvNavAbout', nav.about || 'About');
        setText('#pvNavRegister', nav.register || 'Register');
        setText('#pvNavLogin', nav.login || 'Login');

        setText('#pvHeroTitle', hero.title || '{{PAGE_TITLE}}');
        setText('#pvHeroSubtitle', hero.subtitle || 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.');
        setText('#pvHeroSearch', hero.search_placeholder || 'Search Courses');

        const bgUrl = String(hero.background_image || '').trim();
        $('#pvHeroBg').css('background-image', bgUrl ? 'url("' + bgUrl.replace(/"/g, '\\"') + '")' : 'none');
        renderPreviewImage('#pvBusinessImage', templateObj?.business?.image_url, 'Featured business image');
        renderPreviewImage('#pvBusinessAltImage', templateObj?.business_alt?.image_url, 'Featured business image');

        setText('#pvSectionTitle', about.title || 'Title for this section');
        setText('#pvSectionDesc', about.description || 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.');
        setText('#pvBusinessTitle', templateObj?.business?.title || 'Elevating Business Performance Through Strategic Solutions');
        setText('#pvBusinessDesc', templateObj?.business?.description || 'Increasingly many people use digital media for learning and continuing education. E-learning content formats that are individually modified to meet each learner needs are fundamental for successful outcomes.');
        setText('#pvBusinessServicesTitle', templateObj?.business?.services_title || 'Featured Services');
        setText('#pvBusinessAltTitle', templateObj?.business_alt?.title || 'Driving Better Outcomes With Practical Business Execution');
        setText('#pvBusinessAltDesc', templateObj?.business_alt?.description || 'From planning to delivery, structured execution and clear communication help teams move faster, reduce risk, and create long-term business value.');
        setText('#pvTestimonialsTitle', templateObj?.testimonials?.title || 'Loved by families');
        setText('#pvTestimonialsSubtitle', templateObj?.testimonials?.subtitle || 'Real feedback from customers who shop with us.');
        setText('#pvNewsletterTitle', templateObj?.newsletter?.title || 'Get offers and new arrivals');
        setText('#pvNewsletterDescription', templateObj?.newsletter?.description || 'Join our newsletter for updates and exclusive deals.');
        setText('#pvNewsletterButton', templateObj?.newsletter?.button_text || 'Subscribe');

        setText('#pvAboutHeroTitle', aboutPage.hero_title || 'Built for joyful childhoods.');
        setText('#pvAboutHeroDescription', aboutPage.hero_description || 'We curate thoughtful products and experiences for families who value quality, safety, and delight.');
        setText('#pvAboutPromiseTitle', aboutPage.promise_title || 'Our Promise');
        setText('#pvAboutPromiseText1', aboutPage.promise_text_1 || 'We partner with trusted makers and verify every item for durability, comfort, and responsible sourcing.');
        setText('#pvAboutPromiseText2', aboutPage.promise_text_2 || 'Every purchase supports community initiatives for early learning and childcare.');
        setText('#pvAboutStoryTitle', aboutPage.story_title || 'Our Story');
        setText('#pvAboutStoryDescription', aboutPage.story_description || 'Started by parents who wanted better options for their children, we have grown into a trusted marketplace.');
        setText('#pvAboutWorkTitle', aboutPage.work_title || 'How We Work');
        setText('#pvAboutWorkDescription', aboutPage.work_description || 'We review supplier practices, test product durability, and prioritize responsible makers.');

        const aboutTags = Array.isArray(aboutPage.tags) ? aboutPage.tags : [];
        const aboutStats = Array.isArray(aboutPage.stats) ? aboutPage.stats : [];
        const aboutValues = Array.isArray(aboutPage.values) ? aboutPage.values : [];
        for (let i = 0; i < 3; i++) {
            setText('#pvAboutTag' + (i + 1), aboutTags[i] || ['Safety-first materials', 'Family-led sourcing', 'Crafted for comfort'][i]);
            setText('#pvAboutStat' + (i + 1) + 'Value', aboutStats?.[i]?.value || ['12k+', '350+', '98%'][i]);
            setText('#pvAboutStat' + (i + 1) + 'Label', aboutStats?.[i]?.label || ['Families served', 'Trusted brands', 'Happy parent reviews'][i]);
            setText('#pvAboutValue' + (i + 1) + 'Title', aboutValues?.[i]?.title || ['Care Driven', 'Responsible', 'Delightful'][i]);
            setText('#pvAboutValue' + (i + 1) + 'Description', aboutValues?.[i]?.description || 'Add a short value description.');
        }

        setText('#pvContactHeroTitle', contactPage.hero_title || 'Let us talk.');
        setText('#pvContactHeroDescription', contactPage.hero_description || 'Questions about products, sizing, delivery, or partnerships? Reach out to our support team.');
        setText('#pvContactEmail', contactPage.email || 'support@child.com');
        setText('#pvContactPhone', contactPage.phone || '+91 90000 00000');
        setText('#pvContactAddress', contactPage.address || '9/14 Lake View Road, Chennai');
        setText('#pvContactFormTitle', contactPage.form_title || 'Send a message');
        setText('#pvContactNameLabel', contactPage.name_label || 'Full Name');
        setText('#pvContactEmailLabel', contactPage.email_label || 'Email');
        setText('#pvContactPhoneLabel', contactPage.phone_label || 'Phone');
        setText('#pvContactMessageLabel', contactPage.message_label || 'Message');
        setText('#pvContactSubmitText', contactPage.submit_text || 'Send Message');

        const services = Array.isArray(templateObj?.business?.services) ? templateObj.business.services : [];
        setText('#pvService1Title', services?.[0]?.title || 'Talent Management Strategy');
        setText('#pvService1Desc', services?.[0]?.description || 'Build stronger teams with focused hiring, role mapping, and continuous capability development.');
        setText('#pvService2Title', services?.[1]?.title || 'Innovation & Digital Transformation');
        setText('#pvService2Desc', services?.[1]?.description || 'Modernize operations through practical digital workflows, automation, and customer-first experiences.');
        setText('#pvService3Title', services?.[2]?.title || 'Market Expansion Advisory');
        setText('#pvService3Desc', services?.[2]?.description || 'Scale into new regions with structured research, channel planning, and measurable growth strategy.');

        const testimonials = Array.isArray(templateObj?.testimonials?.items) ? templateObj.testimonials.items : [];
        for (let i = 0; i < 3; i++) {
            const item = testimonials[i] || {};
            setText('#pvTestimonial' + (i + 1) + 'Quote', item.quote || 'Customer feedback');
            setText('#pvTestimonial' + (i + 1) + 'Name', item.name || 'Customer');
            setText('#pvTestimonial' + (i + 1) + 'Role', item.role || 'Verified customer');
        }

        setText('#pvFooterCol1Title', footer.column1_title || 'Customer Care');
        setText('#pvFooterCol2Title', footer.column2_title || 'Company');
        setText('#pvFooterCol3Title', footer.column3_title || 'Contact');

        const c1 = Array.isArray(footer.column1_links) ? footer.column1_links : [];
        const c2 = Array.isArray(footer.column2_links) ? footer.column2_links : [];
        $('#pvFooterCol1Lines').html(c1.map(function (v) { return $('<div/>').text(String(v)).html(); }).join('<br>') || 'Help Center<br>Track Order<br>Return Policy');
        $('#pvFooterCol2Lines').html(c2.map(function (v) { return $('<div/>').text(String(v)).html(); }).join('<br>') || 'About Us<br>Privacy Policy<br>Terms & Conditions');

        const email = String(footer.email || 'support@child.com');
        const phone = String(footer.phone || '+91 90000 00000');
        $('#pvFooterContact').html('Email: ' + $('<div/>').text(email).html() + '<br>Phone: ' + $('<div/>').text(phone).html());

        const copy = String(footer.copyright || 'Copyright {year} child.com. All rights reserved.').replace('{year}', year);
        setText('#pvFooterCopy', copy);

        $('#pvHeader').toggle(layers.header !== false);
        $('#pvHero').toggle(layers.hero !== false);
        $('#pvSection').toggle(layers.section !== false);
        $('#pvBiz').toggle(layers.business !== false);
        $('#pvBizAlt').toggle(layers.business_alt !== false);
        $('#pvTestimonials').toggle(layers.testimonials !== false);
        $('#pvNewsletter').toggle(layers.newsletter !== false);
        $('#pvFooter').toggle(layers.footer !== false);
        switchPreviewPage(activePreviewPage);
    }

    loadFromJsonBtn.on('click', function () {
        const parsed = parseJsonValue();
        if (parsed === null) {
            errorBox.text('Invalid JSON: unable to load fields.');
            return;
        }
        errorBox.text('');
        templateObj = parsed;
        syncFieldsFromObject();
        renderPreview();
        showAlert('success', 'Builder loaded from JSON.');
    });

    syncToJsonBtn.on('click', function () {
        errorBox.text('');
        syncObjectFromFields();
        showAlert('success', 'JSON updated from builder.');
    });

    clearLayersBtn.on('click', function () {
        visHeader.prop('checked', false);
        visHero.prop('checked', false);
        visSection.prop('checked', false);
        visBusiness.prop('checked', false);
        visBusinessAlt.prop('checked', false);
        visTestimonials.prop('checked', false);
        visNewsletter.prop('checked', false);
        visFooter.prop('checked', false);
        syncObjectFromFields();
    });

    function closeDrawer() {
        drawerPanels.removeClass('active');
        drawerBackdrop.removeClass('active');
        contentNavBtns.removeClass('active');
    }

    function openDrawer(targetId) {
        const target = $('#' + targetId);
        if (!target.length) return;

        drawerPanels.removeClass('active');
        drawerBackdrop.addClass('active');
        target.addClass('active');

        if (targetId === 'panelJson') {
            target.prop('open', true);
        }

        contentNavBtns.removeClass('active');
        contentNavBtns.filter('[data-target="' + targetId + '"]').addClass('active');
    }

    contentNavBtns.on('click', function () {
        const targetId = String($(this).data('target') || '');
        if (targetId === 'panelAboutPage') {
            switchPreviewPage('about');
        } else if (targetId === 'panelContactPage') {
            switchPreviewPage('contact');
        } else if (['panelHero', 'panelSection', 'panelBusiness', 'panelTestimonials', 'panelNewsletter'].includes(targetId)) {
            switchPreviewPage('home');
        }
        openDrawer(targetId);
    });

    previewPageTabs.on('click', function () {
        const page = String($(this).data('preview-page') || 'home');
        switchPreviewPage(page);
        if (page === 'about') {
            openDrawer('panelAboutPage');
        } else if (page === 'contact') {
            openDrawer('panelContactPage');
        }
    });

    drawerCloseBtns.on('click', closeDrawer);
    drawerBackdrop.on('click', closeDrawer);
    $(document).on('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDrawer();
        }
    });

    fields.on('input change', syncObjectFromFields);
    imageUploadButtons.on('click', function () {
        const button = $(this);
        const wrapper = button.closest('.field');
        const fileInput = wrapper.find('.template-image-file');
        const status = wrapper.find('.template-upload-status');
        const file = fileInput[0] && fileInput[0].files ? fileInput[0].files[0] : null;
        const targetPath = String(fileInput.data('target-path') || '');

        status.removeClass('error success').text('');
        if (!file || targetPath === '') {
            status.addClass('error').text('Please select an image first.');
            return;
        }

        const formData = new FormData();
        formData.append('template_image', file);
        button.prop('disabled', true).text('Uploading...');

        $.ajax({
            url: "<?= base_url('admin/dashboard-template/upload-image') ?>",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (response) {
            if (!response || !response.status || !response.url) {
                status.addClass('error').text(response?.message || 'Unable to upload image.');
                return;
            }

            setByPath(templateObj, targetPath, String(response.url));
            fields.filter('[data-path="' + targetPath + '"]').val(String(response.url));
            jsonBox.val(JSON.stringify(templateObj, null, 2));
            renderPreview();
            fileInput.val('');
            status.addClass('success').text('Image uploaded.');
        }).fail(function (xhr) {
            const response = xhr.responseJSON || {};
            status.addClass('error').text(response.message || 'Unable to upload image.');
        }).always(function () {
            button.prop('disabled', false).text('Upload');
        });
    });
    footerCol1Lines.on('input', syncObjectFromFields);
    footerCol2Lines.on('input', syncObjectFromFields);
    service1Title.on('input', syncObjectFromFields);
    service1Desc.on('input', syncObjectFromFields);
    service2Title.on('input', syncObjectFromFields);
    service2Desc.on('input', syncObjectFromFields);
    service3Title.on('input', syncObjectFromFields);
    service3Desc.on('input', syncObjectFromFields);
    visHeader.on('change', syncObjectFromFields);
    visHero.on('change', syncObjectFromFields);
    visSection.on('change', syncObjectFromFields);
    visBusiness.on('change', syncObjectFromFields);
    visBusinessAlt.on('change', syncObjectFromFields);
    visTestimonials.on('change', syncObjectFromFields);
    visNewsletter.on('change', syncObjectFromFields);
    visFooter.on('change', syncObjectFromFields);

    const initialParsed = parseJsonValue();
    templateObj = initialParsed === null ? {} : initialParsed;
    syncFieldsFromObject();
    syncObjectFromFields();

    form.on('submit', function (e) {
        e.preventDefault();
        errorBox.text('');

        const value = jsonBox.val();
        const name = String(templateName.val() || '').trim();
        if (name === '') {
            errorBox.text('Template name is required.');
            templateName.focus();
            return;
        }

        try {
            JSON.parse(value);
        } catch (err) {
            errorBox.text('Invalid JSON: ' + err.message);
            return;
        }

        btn.prop('disabled', true).text('Saving...');
        $.ajax({
            url: "<?= base_url('admin/dashboard-template/save') ?>",
            type: 'POST',
            data: {
                id: templateId.val(),
                template_name: name,
                is_active: templateActive.is(':checked') ? 1 : 0,
                template_json: value
            },
            dataType: 'json',
            success: function (response) {
                btn.prop('disabled', false).text('Save Template');
                if (!response.status) {
                    if (response.errors && (response.errors.template_name || response.errors.template_json)) {
                        errorBox.text(response.errors.template_name || response.errors.template_json);
                    } else {
                        showAlert('error', response.message || 'Unable to save template.');
                    }
                    return;
                }

                if (response.template) {
                    if (response.id) {
                        templateId.val(response.id);
                    }
                    templateObj = response.template;
                    jsonBox.val(JSON.stringify(response.template, null, 2));
                    syncFieldsFromObject();
                    renderPreview();
                }
                showAlert('success', response.message || 'Template saved.');
                setTimeout(function () {
                    window.location.href = "<?= base_url('admin/dashboard-template/edit') ?>/" + templateId.val();
                }, 700);
            },
            error: function () {
                btn.prop('disabled', false).text('Save Template');
                showAlert('error', 'Unexpected error while saving template.');
            }
        });
    });
});
</script>

<script>
    lucide.createIcons();
</script>

</body>
</html>
