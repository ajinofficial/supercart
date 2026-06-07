<?php
$footerName = trim((string) ($websiteName ?? 'child.com'));
if ($footerName === '') {
    $footerName = 'child.com';
}
?>
<style>
    .footer {
        margin-top: 28px;
        background: #1f2a40;
        color: #dbe1ed;
        border-radius: 16px;
        padding: 20px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .footer-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }
    .footer h4 {
        margin: 0 0 8px;
        font-size: 0.95rem;
        color: #fff;
    }
    .footer p,
    .footer a {
        margin: 0;
        color: #dbe1ed;
        font-size: 0.84rem;
        text-decoration: none;
        line-height: 1.6;
    }
    .copyright {
        margin-top: 12px;
        border-top: 1px solid rgba(219, 225, 237, 0.2);
        padding-top: 10px;
        font-size: 0.82rem;
        color: #b9c3d4;
    }
    @media (max-width: 700px) {
        .footer-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<footer class="footer">
    <div class="footer-grid">
        <div>
            <h4 id="footerCol1Title">Customer Care</h4>
            <p id="footerCol1Link1">Help Center</p>
            <p id="footerCol1Link2">Track Order</p>
            <p id="footerCol1Link3">Return Policy</p>
        </div>
        <div>
            <h4 id="footerCol2Title">Company</h4>
            <p id="footerCol2Link1">About Us</p>
            <p id="footerCol2Link2">Privacy Policy</p>
            <p id="footerCol2Link3">Terms &amp; Conditions</p>
        </div>
        <div>
            <h4 id="footerCol3Title">Contact</h4>
            <p id="footerEmail">Email: support@child.com</p>
            <p id="footerPhone">Phone: +91 90000 00000</p>
            <a href="<?= base_url('logout') ?>">Logout</a>
        </div>
    </div>
    <div id="footerCopyright" class="copyright">Copyright &copy; <?= date('Y') ?> <?= esc($footerName) ?>. All rights reserved.</div>
</footer>
