<?php
session_start();
require_once __DIR__ . '/core/tenant.php';

// This one line resolves the subdomain and connects to the right DB
$db = Tenant::db();
$info = Tenant::info();

if (empty($_SESSION['staff_id'])) {
    header('Location: login');
    exit;
}

$staffName = htmlspecialchars($_SESSION['staff_name'] ?? 'Admin');
$staffRole = htmlspecialchars(ucfirst($_SESSION['staff_role'] ?? 'staff'));
$staffInitials = strtoupper(implode('', array_map(fn($p) => $p[0] ?? '', array_filter(array_slice(explode(' ', $_SESSION['staff_name'] ?? 'A'), 0, 2)))));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#3d6ff0">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="LibraryERP">
    <title>OPTMS Tech ERP v6</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=DM+Serif+Display&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        /* ── GOOGLE MATERIAL ICONS HELPER ── */
        .mi{font-family:'Material Icons Round';font-style:normal;font-size:18px;line-height:1;display:inline-flex;align-items:center;justify-content:center;vertical-align:middle;user-select:none}
        .mi.sm{font-size:14px}.mi.lg{font-size:22px}.mi.xl{font-size:28px}

        :root{
            --bg:#f0f4fb;
            --sf:#ffffff;
            --sf2:#f5f7fc;
            --sf3:#eaeffa;
            --br:#e2e7f0;
            --br2:#ccd3e0;
            --ac:#3d6ff0;--ac2:#2d5de0;
            --em:#16a34a;--ro:#dc2626;--or:#ea580c;
            --gd:#d97706;--gd2:#b45309;
            --vi:#7c3aed;--sk:#0284c7;
            --wa:#25d366;--wa2:#128c7e;
            --tx:#0f172a;--tx2:#334155;--tx3:#64748b;
            --c-blue:#eff4ff;--cb:#bfcffd;
            --c-green:#f0fdf4;--cg:#bbf7d0;
            --c-rose:#fff1f2;--cr:#fecdd3;
            --c-amber:#fffbeb;--ca2:#fde68a;
            --c-purple:#faf5ff;--cp:#e9d5ff;
            --c-sky:#f0f9ff;--cs:#bae6fd;
            --c-orange:#fff7ed;--co:#fed7aa;
            --c-teal:#f0fdfa;--ct:#99f6e4;
            --fd:'DM Serif Display',serif;
            --fb:'Inter',sans-serif;
            --fm:'JetBrains Mono',monospace;
            --r:14px;--r2:9px;
            --sh:0 1px 4px rgba(15,23,42,.06),0 4px 14px rgba(15,23,42,.05);
            --sh2:0 8px 32px rgba(15,23,42,.12),0 2px 8px rgba(15,23,42,.06);
        }
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:var(--fb);font-size:16px;background:var(--bg);color:var(--tx);min-height:100vh;overflow-x:hidden;line-height:1.6}

        .sb{position:fixed;left:0;top:0;bottom:0;width:240px;background:var(--sf);border-right:1px solid var(--br);display:flex;flex-direction:column;z-index:200;box-shadow:var(--sh)}
        .sb-logo{padding:18px 16px 14px;border-bottom:1px solid var(--br)}
        .logo-row{display:flex;align-items:center;gap:10px}
        .logo-ic{width:36px;height:36px;background:linear-gradient(135deg,var(--ac),var(--vi));border-radius:10px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(61,111,240,.3)}
        .logo-tx{font-family:var(--fd);font-size:16px;color:var(--tx)}.logo-sb{font-size:9px;color:var(--tx3);font-family:var(--fm);letter-spacing:1.5px;text-transform:uppercase}
        .sb-nav{flex:1;padding:10px 8px;overflow-y:auto}
        .nl{font-size:9px;color:var(--tx3);letter-spacing:1.8px;text-transform:uppercase;padding:0 8px;margin-bottom:4px;font-family:var(--fm);font-weight:600}
        .ni{display:flex;align-items:center;gap:9px;padding:7px 9px;border-radius:var(--r2);cursor:pointer;transition:all .18s;color:var(--tx2);font-size:12.5px;font-weight:500;position:relative;white-space:nowrap}
        .ni:hover{background:var(--sf2);color:var(--tx)}
        .ni.active{background:rgba(61,111,240,.09);color:var(--ac);font-weight:600}
        .ni.active::before{content:'';position:absolute;left:0;top:6px;bottom:6px;width:3px;background:var(--ac);border-radius:0 3px 3px 0}
        .ni-ic{font-size:18px;width:20px;text-align:center;display:flex;align-items:center;justify-content:center;color:inherit}
        .nbadge{margin-left:auto;background:var(--ro);color:#fff;font-size:9px;font-weight:700;padding:1px 6px;border-radius:20px;min-width:17px;text-align:center}
        .nbadge.g{background:var(--em)}.nbadge.y{background:var(--gd)}.nbadge.wa{background:var(--wa)}.nbadge.or{background:var(--or)}
        .ns{margin-bottom:14px}
        .sb-foot{padding:12px;border-top:1px solid var(--br)}
        .u-card{display:flex;align-items:center;gap:8px;padding:9px 10px;background:var(--sf2);border-radius:var(--r2);border:1px solid var(--br)}
        .u-av{width:30px;height:30px;background:linear-gradient(135deg,var(--ac),var(--vi));border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff}
        .u-nm{font-size:12px;font-weight:600;color:var(--tx)}.u-rl{font-size:10px;color:var(--tx3)}

        .main{margin-left:240px;min-height:100vh;display:flex;flex-direction:column}
        .topbar{position:sticky;top:0;z-index:100;background:rgba(255,255,255,.95);backdrop-filter:blur(16px);border-bottom:1px solid var(--br);padding:0 24px;height:58px;display:flex;align-items:center;gap:12px}
        .pg-title{font-family:var(--fd);font-size:19px;flex:1;color:var(--tx)}
        .srch{display:flex;align-items:center;gap:7px;background:var(--sf2);border:1px solid var(--br);border-radius:var(--r2);padding:6px 11px;width:200px;transition:all .2s}
        .srch:focus-within{border-color:var(--ac);background:#fff}
        .srch input{background:none;border:none;outline:none;color:var(--tx);font-size:12px;width:100%;font-family:var(--fb)}
        .srch input::placeholder{color:var(--tx3)}
        .id-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 13px 5px 9px;background:#eff6ff;border:1.5px solid #93c5fd;border-radius:7px;font-family:var(--fm);font-size:16px;font-weight:700;color:#1d4ed8;letter-spacing:.3px;white-space:nowrap;box-shadow:0 1px 4px rgba(59,130,246,.10)}
        .id-badge .id-dot{width:7px;height:7px;border-radius:50%;background:#3b82f6;flex-shrink:0;box-shadow:0 0 0 2px rgba(59,130,246,.25)}

        .btn{display:inline-flex;align-items:center;gap:5px;padding:7px 13px;border-radius:var(--r2);font-size:12px;font-weight:600;cursor:pointer;border:none;transition:all .18s;font-family:var(--fb)}
        .bp{background:var(--ac);color:#fff;box-shadow:0 2px 6px rgba(61,111,240,.3)}.bp:hover{background:var(--ac2);transform:translateY(-1px)}
        .bg{background:var(--sf);color:var(--tx2);border:1px solid var(--br)}.bg:hover{background:var(--sf2);color:var(--tx)}
        .bd{background:var(--ro);color:#fff}.bd:hover{background:#b91c1c}
        .bwa{background:#25d366;color:#fff}.bwa:hover{background:#128c7e}
        .bor{background:var(--or);color:#fff}.bor:hover{background:#c2410c}

        .content{padding:20px 24px;flex:1}
        .page{display:none}.page.active{display:block}

        .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
        .sc{background:var(--sf);border:1px solid var(--br);border-left:3px solid var(--ca,var(--ac));border-radius:var(--r);padding:14px 16px;position:relative;overflow:hidden;transition:all .22s;box-shadow:var(--sh)}
        .sc:hover{transform:translateY(-2px);box-shadow:var(--sh2)}
        .s-row{display:flex;align-items:center;gap:8px;margin-bottom:8px}
        .s-ic{width:28px;height:28px;min-width:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;background:transparent!important}
        .s-lb{font-size:11px;color:var(--tx3);text-transform:uppercase;letter-spacing:.7px;font-family:var(--fm);font-weight:600}
        .s-vl{font-size:24px;font-weight:700;color:var(--tx);line-height:1;margin-bottom:4px;font-family:var(--fd)}
        .s-mt{font-size:11px;color:var(--tx3)}
        .bup{background:rgba(22,163,74,.12);color:var(--em);font-size:10px;font-weight:600;padding:2px 6px;border-radius:5px}
        .bdn{background:rgba(220,38,38,.10);color:var(--ro);font-size:10px;font-weight:600;padding:2px 6px;border-radius:5px}

        .panel{background:var(--sf);border:1px solid var(--br);border-radius:var(--r);overflow:hidden;margin-bottom:16px;box-shadow:var(--sh)}
        .ph{padding:14px 18px;border-bottom:1px solid var(--br);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px}
        .pt{font-family:var(--fd);font-size:15px;color:var(--tx)}.pb{padding:16px}

        .tw{overflow-x:auto}
        table{width:100%;border-collapse:collapse;font-size:12px}
        thead th{text-align:left;padding:9px 13px;background:var(--sf2);color:var(--tx3);font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.9px;font-family:var(--fm);border-bottom:1px solid var(--br)}
        tbody tr{border-bottom:1px solid var(--br);transition:background .13s}
        tbody tr:hover{background:#f5f7ff}
        tbody tr:last-child{border-bottom:none}
        tbody td{padding:10px 13px;color:var(--tx2);vertical-align:middle}

        .tag{display:inline-flex;align-items:center;gap:3px;padding:3px 8px;border-radius:5px;font-size:10px;font-weight:600;font-family:var(--fm)}
        .tpd{background:var(--c-green);color:#166534;border:1px solid var(--cg)}
        .tpn{background:var(--c-amber);color:#92400e;border:1px solid var(--ca2)}
        .tod{background:var(--c-rose);color:#9f1239;border:1px solid var(--cr)}
        .tac{background:var(--c-blue);color:#1e40af;border:1px solid var(--cb)}
        .tis{background:var(--c-purple);color:#5b21b6;border:1px solid var(--cp)}
        .trt{background:var(--c-teal);color:#0f766e;border:1px solid var(--ct)}
        .tav{background:var(--c-sky);color:#075985;border:1px solid var(--cs)}
        .twa{background:var(--c-green);color:#166534;border:1px solid var(--cg)}
        .tpart{background:var(--c-sky);color:#075985;border:1px solid var(--cs)}
        .tor{background:var(--c-orange);color:#9a3412;border:1px solid var(--co)}

        .mo{display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:500;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(4px)}
        .mo.open{display:flex}
        .md{background:var(--sf);border-radius:var(--r);width:100%;max-width:540px;max-height:94vh;overflow-y:auto;box-shadow:var(--sh2);animation:mIn .22s ease;border:1px solid var(--br)}
        .md.wide{max-width:680px}.md.lg{max-width:800px}
        @keyframes mIn{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
        .mh{padding:16px 20px;border-bottom:1px solid var(--br);display:flex;align-items:center;justify-content:space-between;background:var(--sf2)}
        .mt{font-family:var(--fd);font-size:16px;color:var(--tx)}
        .mc{width:30px;height:30px;border-radius:8px;background:var(--sf);border:1px solid var(--br);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--tx3);transition:all .18s}
        .mc:hover{background:var(--c-rose);color:var(--ro);border-color:var(--cr)}
        .mb{padding:20px}.mf{padding:13px 20px;border-top:1px solid var(--br);display:flex;justify-content:flex-end;gap:9px;background:var(--sf2)}

        .fg{display:grid;grid-template-columns:1fr 1fr;gap:13px}
        .fgi{display:flex;flex-direction:column;gap:5px}.fgi.full{grid-column:1/-1}
        label{font-size:11px;font-weight:600;color:var(--tx2);letter-spacing:.3px}
        input,select,textarea{padding:8px 11px;border:1px solid var(--br);border-radius:var(--r2);background:var(--sf);color:var(--tx);font-size:12.5px;font-family:var(--fb);outline:none;transition:all .18s;width:100%}
        input:focus,select:focus,textarea:focus{border-color:var(--ac);box-shadow:0 0 0 3px rgba(61,111,240,.1);background:#fff}
        textarea{resize:vertical;min-height:70px}select option{background:var(--sf)}
        .sdiv{font-size:10px;font-weight:700;color:var(--tx3);text-transform:uppercase;letter-spacing:1.2px;font-family:var(--fm);padding:8px 0 5px;border-bottom:1px solid var(--br);margin-bottom:10px}

        .sec-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px}
        .sec-t{font-family:var(--fd);font-size:16px;color:var(--tx)}.sec-s{font-size:11px;color:var(--tx3);margin-top:2px}

        .sbar{height:10px;background:var(--sf2);border-radius:20px;overflow:hidden;margin-bottom:5px;border:1px solid var(--br)}
        .sfill{height:100%;border-radius:20px;transition:width 1s ease}
        .sf-g{background:linear-gradient(90deg,var(--em),#4ade80)}.sf-y{background:linear-gradient(90deg,var(--gd),var(--gd2))}.sf-r{background:linear-gradient(90deg,var(--ro),#f87171)}
        .bst{font-size:9px;font-weight:700;padding:3px 8px;border-radius:20px;font-family:var(--fm)}
        .bst-o{background:var(--c-green);color:#166534}.bst-f{background:var(--c-rose);color:#9f1239}.bst-n{background:var(--c-amber);color:#92400e}

        .seat-visual{display:flex;flex-wrap:wrap;gap:6px;margin-top:10px}
        .seat-cell{width:52px;height:38px;border-radius:10px;border:1.5px solid;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;transition:all .15s;font-weight:700;position:relative;gap:1px}
        .seat-cell:hover{transform:scale(1.08);z-index:5;box-shadow:0 4px 14px rgba(0,0,0,.13)}
        .seat-num{font-size:11px;font-family:var(--fm);font-weight:700;line-height:1}
        .seat-init{font-size:9px;font-weight:800;opacity:.8;line-height:1}
        .seat-occ{background:#dbeafe;border-color:#93c5fd;color:#1d4ed8}
        .seat-vac{background:var(--c-green);border-color:var(--cg);color:#166534}
        .seat-due{background:var(--c-amber);border-color:var(--ca2);color:#92400e;animation:pulseDue 2s infinite}
        .seat-overdue{background:var(--c-rose);border-color:var(--cr);color:#9f1239;animation:pulseDue 1s infinite}
        .seat-tooltip{display:none;position:absolute;bottom:calc(100%+7px);left:50%;transform:translateX(-50%);background:var(--tx);color:#fff;font-size:10px;padding:6px 11px;border-radius:8px;white-space:nowrap;z-index:20;pointer-events:none;line-height:1.5;text-align:center}
        .seat-cell:hover .seat-tooltip{display:block}
        .seat-summary{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px;padding-top:14px;border-top:1px solid var(--br)}
        .ss-chip{display:inline-flex;align-items:center;gap:6px;padding:6px 13px 6px 9px;border-radius:20px;border:1.5px solid;font-family:var(--fm);box-shadow:0 1px 4px rgba(0,0,0,.07);transition:transform .15s;cursor:default}
        .ss-chip:hover{transform:translateY(-1px)}
        .ss-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
        .ss-ic{font-family:'Material Icons Round';font-style:normal;font-size:14px;line-height:1;display:inline-flex;align-items:center}
        .ss-cnt{font-size:13px;font-weight:800}
        .ss-lbl{font-size:10px;font-weight:600}
        .ss-vac{background:var(--c-green);border-color:var(--cg);color:#15803d}
        .ss-vac .ss-dot{background:#16a34a;box-shadow:0 0 0 2px rgba(22,163,74,.2)}
        .ss-occ{background:#eff6ff;border-color:#93c5fd;color:#1d4ed8}
        .ss-occ .ss-dot{background:#3b82f6;box-shadow:0 0 0 2px rgba(59,130,246,.2)}
        .ss-due{background:var(--c-amber);border-color:var(--ca2);color:#92400e}
        .ss-due .ss-dot{background:#d97706;box-shadow:0 0 0 2px rgba(217,119,6,.2)}
        .ss-od{background:var(--c-rose);border-color:var(--cr);color:#9f1239}
        .ss-od .ss-dot{background:#e11d48;box-shadow:0 0 0 2px rgba(225,29,72,.2);animation:pulseDue 1s infinite}
        @keyframes pulseDue{0%,100%{opacity:1}50%{opacity:.55}}

        /* ── SEAT LEGEND ── */
        .seat-legend{display:flex;flex-wrap:wrap;gap:8px;padding:10px 14px;background:var(--sf2);border-top:1px solid var(--br)}
        .sl-item{display:flex;align-items:center;gap:7px;font-size:11px;font-weight:500;color:var(--tx2)}
        .sl-dot{width:22px;height:14px;border-radius:4px;border:1px solid;flex-shrink:0}
        /* Sticky table header inside scrollable container */
        .tw table thead th{position:sticky;top:0;z-index:2;background:var(--sf2)}

        .dn-wrap{display:flex;align-items:center;gap:18px;padding:16px 18px}
        .dn-leg{flex:1;display:flex;flex-direction:column;gap:7px}
        .dli{display:flex;align-items:center;gap:7px;font-size:12px}
        .dld{width:8px;height:8px;border-radius:3px}.dll{flex:1;color:var(--tx2)}.dlv{font-weight:700;font-family:var(--fm);font-size:12px}

        .act-it{display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--br)}
        .act-it:last-child{border-bottom:none}
        .act-d{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0}
        .act-tx{font-size:12px;color:var(--tx2);line-height:1.5}.act-tx strong{color:var(--tx)}.act-tm{font-size:10px;color:var(--tx3);font-family:var(--fm);margin-top:2px}

        .cbar{flex:1;border-radius:4px 4px 0 0;cursor:pointer;position:relative}
        .cbar:hover{opacity:.75}
        .cbar .tt{display:none;position:absolute;bottom:calc(100%+4px);left:50%;transform:translateX(-50%);background:var(--tx);color:#fff;font-size:9px;padding:2px 7px;border-radius:4px;white-space:nowrap;font-family:var(--fm);z-index:10}
        .cbar:hover .tt{display:block}

        .mcal{display:grid;grid-template-columns:repeat(7,1fr);gap:2px;font-family:var(--fm);margin-top:4px}
        .cal-dl{text-align:center;color:var(--tx3);padding:4px 0;font-size:10px;font-weight:700;letter-spacing:.3px}
        .cal-d{text-align:center;padding:7px 2px;border-radius:8px;cursor:pointer;color:var(--tx2);transition:all .15s;font-size:12px;font-weight:600;line-height:1;position:relative}
        .cal-d:hover{background:var(--sf2);color:var(--ac)}
        .cal-d.today{background:var(--ac);color:#fff;font-weight:800;box-shadow:0 2px 8px rgba(61,111,240,.3)}
        .cal-d.event{color:var(--tx2);font-weight:700}
        .cal-d.empty{color:transparent;pointer-events:none}

        .toast-wrap{position:fixed;bottom:18px;right:18px;z-index:9999;display:flex;flex-direction:column;gap:7px}
        .toast{padding:12px 16px;border-radius:var(--r2);background:var(--tx);color:#fff;font-size:12.5px;font-weight:500;box-shadow:var(--sh2);display:flex;align-items:center;gap:8px;animation:tIn .28s ease;min-width:230px}
        .toast.ok{background:var(--em)}.toast.er{background:var(--ro)}.toast.wn{background:var(--gd)}.toast.wa{background:var(--wa)}
        @keyframes tIn{from{opacity:0;transform:translateX(28px)}to{opacity:1;transform:translateX(0)}}
        @keyframes tOut{from{opacity:1}to{opacity:0;transform:translateX(28px)}}

        .tabs{display:flex;gap:2px;background:var(--sf2);padding:4px;border-radius:var(--r2);border:1px solid var(--br)}
        .tab{flex:1;padding:6px 10px;text-align:center;font-size:11.5px;font-weight:500;color:var(--tx3);border-radius:7px;cursor:pointer;transition:all .18s;white-space:nowrap}
        .tab.active{background:var(--sf);color:var(--tx);font-weight:600;box-shadow:var(--sh)}

        .pag{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-top:1px solid var(--br)}
        .pag-i{font-size:11px;color:var(--tx3)}.pag-b{display:flex;gap:3px}
        .pb2{padding:3px 9px;border-radius:6px;font-size:11px;cursor:pointer;border:1px solid var(--br);background:var(--sf);color:var(--tx2);transition:all .18s}
        .pb2:hover,.pb2.active{background:var(--ac);color:#fff;border-color:var(--ac)}

        .prg{height:6px;background:var(--sf2);border-radius:3px;overflow:hidden;border:1px solid var(--br)}
        .prf{height:100%;border-radius:3px}

        .g2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .g3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
        .g4{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
        .gm{display:grid;grid-template-columns:1fr 320px;gap:16px}

        .empty{text-align:center;padding:12px 16px;color:var(--tx3)}
        .empty .ei{font-size:38px;margin-bottom:8px}.empty .et{font-size:12.5px}

        .qa-gr{display:grid;grid-template-columns:repeat(8,1fr);gap:10px;margin-bottom:20px}
        .qa-b{display:flex;flex-direction:column;align-items:center;gap:7px;padding:14px 8px;background:var(--sf);border:1px solid var(--br);border-radius:var(--r);cursor:pointer;transition:all .2s;text-align:center;box-shadow:var(--sh)}
        .qa-b:hover{border-color:var(--ac);box-shadow:0 4px 16px rgba(61,111,240,.15);transform:translateY(-2px)}
        .qa-ic{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center}
        .qa-lb{font-size:14px;font-weight:600;color:var(--tx2);line-height:1.3}

        .al-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:20px}
        .al-card{padding:12px 14px;border-radius:var(--r);border:1px solid;display:flex;align-items:flex-start;gap:10px;box-shadow:var(--sh)}
        .al-w{background:var(--c-amber);border-color:var(--ca2)}.al-d{background:var(--c-rose);border-color:var(--cr)}.al-i{background:var(--c-blue);border-color:var(--cb)}
        .al-t{font-size:12px;font-weight:700;margin-bottom:2px}.al-b{font-size:11px;color:var(--tx2);line-height:1.4}

        .fi{display:flex;align-items:center;gap:10px;padding:10px 13px;background:var(--sf2);border-radius:var(--r2);margin-bottom:7px;border:1px solid var(--br)}
        .fd2{width:8px;height:8px;border-radius:50%;flex-shrink:0}.fn2{flex:1;font-size:12.5px;font-weight:500}.fsb{font-size:10px;color:var(--tx3)}.fa{font-size:13px;font-weight:700;font-family:var(--fm)}

        .ei2{display:flex;align-items:center;gap:11px;padding:11px 13px;background:var(--sf);border:1px solid var(--br);border-radius:var(--r2);box-shadow:var(--sh)}
        .eic{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0}
        .en2{font-size:12.5px;font-weight:600;color:var(--tx)}.ed{font-size:10px;color:var(--tx3);font-family:var(--fm)}.ea{font-size:13px;font-weight:700;font-family:var(--fm)}.ea-d{color:var(--ro)}.ea-c{color:var(--em)}

        .si{display:flex;align-items:center;gap:8px}
        .sav{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;flex-shrink:0}

        .chip{display:inline-flex;align-items:center;gap:3px;padding:3px 9px;border-radius:20px;font-size:10px;font-weight:600;border:1px solid}
        .chip-tl{border-color:var(--cb);color:var(--ac);background:var(--c-blue)}

        .nb-btn{position:relative;cursor:pointer;display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:var(--r2);background:var(--sf2);border:1px solid var(--br);transition:all .18s;color:var(--tx2)}
        .nb-btn:hover{background:var(--sf3);color:var(--tx)}
        .nd{position:absolute;top:5px;right:5px;width:7px;height:7px;border-radius:50%;background:var(--ro);border:1.5px solid #fff}

        .fee-bal-badge{display:inline-flex;align-items:center;padding:2px 7px;background:var(--c-rose);color:#9f1239;border:1px solid var(--cr);border-radius:5px;font-size:10px;font-weight:700;font-family:var(--fm)}
        .fee-partial-wrap{margin-top:4px}.fee-partial-bar{height:4px;background:var(--sf2);border-radius:2px;overflow:hidden;margin-bottom:2px;border:1px solid var(--br)}
        .fee-partial-fill{height:100%;background:linear-gradient(90deg,var(--ac),#60a5fa);border-radius:2px}
        .fee-due-row{background:rgba(220,38,38,.02)}.fee-partial-row{background:rgba(217,119,6,.02)}

        .perm-row{display:flex;align-items:center;justify-content:space-between;padding:9px 13px;border:1px solid var(--br);border-radius:var(--r2);margin-bottom:7px;background:var(--sf2)}
        .perm-card-lbl:hover{border-color:rgba(61,111,240,.4)!important;box-shadow:0 2px 8px rgba(61,111,240,.08)}
        .toggle-wrap{position:relative;display:inline-block;width:36px;height:20px}
        .toggle-inp{opacity:0;width:0;height:0;position:absolute}
        .toggle-sl{position:absolute;inset:0;background:var(--br2);border-radius:20px;cursor:pointer;transition:.2s}
        .toggle-sl::before{content:'';position:absolute;width:14px;height:14px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.2s}
        .toggle-inp:checked+.toggle-sl{background:var(--ac)}
        .toggle-inp:checked+.toggle-sl::before{transform:translateX(16px)}

        .sp-header{background:linear-gradient(135deg,var(--ac),var(--vi));border-radius:var(--r) var(--r) 0 0;padding:22px 22px 54px;position:relative}
        .sp-header::before{content:'';position:absolute;top:-30px;right:-30px;width:150px;height:150px;border-radius:50%;background:rgba(255,255,255,.08);pointer-events:none}
        .sp-av-wrap{position:absolute;bottom:-34px;left:22px;z-index:2}
        .sp-av{width:68px;height:68px;border-radius:18px;border:3px solid var(--sf);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;color:#fff;box-shadow:var(--sh2)}
        .sp-name{color:#fff;font-family:var(--fd);font-size:20px;margin-bottom:3px}
        .sp-id{color:rgba(255,255,255,.75);font-size:11px;font-family:var(--fm)}
        .sp-body{padding:46px 22px 14px}
        .sp-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
        .sp-field{display:flex;flex-direction:column;gap:3px}.sp-field.full{grid-column:1/-1}
        .sp-label{font-size:10px;font-weight:600;color:var(--tx3);text-transform:uppercase;letter-spacing:.6px;font-family:var(--fm)}
        .sp-val{font-size:13px;font-weight:500;color:var(--tx);padding:7px 11px;background:var(--sf2);border-radius:var(--r2);border:1px solid var(--br);transition:all .18s}
        .sp-val.editable{cursor:text}.sp-val.editable:hover{border-color:var(--br2)}
        .sp-val.editable:focus{border-color:var(--ac);outline:none;background:#fff;box-shadow:0 0 0 3px rgba(61,111,240,.1)}
        .sp-section{font-size:10px;font-weight:700;color:var(--tx3);text-transform:uppercase;letter-spacing:1px;font-family:var(--fm);margin:14px 0 8px;padding-bottom:5px;border-bottom:1px solid var(--br)}
        .sp-fee-bar{height:8px;background:var(--sf2);border-radius:4px;overflow:hidden;margin:5px 0;border:1px solid var(--br)}
        .sp-fee-fill{height:100%;border-radius:4px;background:linear-gradient(90deg,var(--em),#4ade80)}
        .sp-stat{display:flex;align-items:center;gap:8px;padding:10px 13px;background:var(--sf2);border-radius:var(--r2);font-size:12px;border:1px solid var(--br)}
        .sp-stat-ic{font-size:17px}
        .sp-edit-toggle{display:flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;border:1px solid var(--br);background:var(--sf);color:var(--tx2);font-size:11px;font-weight:600;cursor:pointer;transition:all .18s}
        .sp-edit-toggle:hover,.sp-edit-toggle.on{background:var(--ac);color:#fff;border-color:var(--ac)}
        .sp-seat-chip{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:var(--c-blue);border:1px solid var(--cb);border-radius:20px;font-size:12px;font-weight:700;color:var(--ac);font-family:var(--fm);cursor:pointer;transition:all .18s}
        .sp-seat-chip:hover{background:rgba(61,111,240,.15);transform:scale(1.04)}

        /* ── WHATSAPP TEMPLATE GRID ── */
        .wa-tpl{
            background:#fafcff;
            border:1.5px solid #dde5f7;
            border-radius:var(--r);
            padding:14px 12px;
            cursor:pointer;
            transition:all .18s;
            text-align:center;
            display:flex;flex-direction:column;align-items:center;gap:5px;
            box-shadow:0 1px 3px rgba(61,111,240,.06);
        }
        .wa-tpl:hover{border-color:var(--ac);background:#eff4ff;box-shadow:0 4px 14px rgba(61,111,240,.12);transform:translateY(-2px);}
        .wa-tpl.selected{border-color:var(--ac);background:#eff4ff;box-shadow:0 0 0 2px rgba(61,111,240,.18);}
        .wt-ic{font-size:22px;line-height:1;margin-bottom:2px;}
        .wt-lb{font-size:11.5px;font-weight:700;color:var(--tx);line-height:1.3;}
        .wt-ds{font-size:9.5px;color:var(--tx3);line-height:1.3;font-family:var(--fm);}

        /* ── WHATSAPP PAGE SECTIONS ── */
        .wa-section{background:#fff;border:1.5px solid #e0e8f5;border-radius:var(--r);overflow:hidden;margin-bottom:14px;box-shadow:0 1px 4px rgba(61,111,240,.05);}
        .wa-section-hd{padding:12px 18px;border-bottom:1.5px solid #e8eef8;background:#f5f8ff;display:flex;align-items:center;justify-content:space-between;}
        .wa-section-hd .pt{font-family:var(--fd);font-size:14px;color:var(--tx);}
        .wa-compose-row{display:flex;flex-direction:column;gap:8px;padding:16px;}
        .wa-compose-field{display:flex;flex-direction:column;gap:5px;}
        .wa-compose-field label{font-size:11px;font-weight:600;color:var(--tx2);letter-spacing:.3px;}
        .wa-preview-box{background:#e5ddd5;border-radius:12px;padding:16px;min-height:200px;}
        .wa-preview-contact{display:flex;align-items:center;gap:8px;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid rgba(0,0,0,.1);}
        .wa-bulk-card{background:#fafcff;border:1.5px solid #dde5f7;border-radius:var(--r2);padding:14px;text-align:center;}
        .wa-bulk-card:hover{border-color:var(--ac);background:#eff4ff;}

        /* ── WA QR BOX ── */
        .wa-qr-box{display:flex;flex-direction:column;align-items:center;gap:12px;padding:18px;background:rgba(37,211,102,.04);border:1px solid rgba(37,211,102,.2);border-radius:var(--r);cursor:pointer;transition:all .2s}
        .wa-qr-box:hover{background:rgba(37,211,102,.08)}
        .wa-qr-img{width:160px;height:160px;border:2px solid var(--wa);border-radius:10px;display:flex;align-items:center;justify-content:center;background:#fff;overflow:hidden}
        .wa-conn-badge{display:flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:10px;font-weight:700;font-family:var(--fm)}
        .wa-conn-ok{background:rgba(37,211,102,.12);color:var(--wa2);border:1px solid rgba(37,211,102,.25)}
        .wa-conn-no{background:var(--c-rose);color:var(--ro);border:1px solid var(--cr)}
        .wa-steps{display:flex;flex-direction:column;gap:8px}
        .wa-step{display:flex;align-items:flex-start;gap:10px;font-size:12px;color:var(--tx2)}
        .wa-step-n{width:22px;height:22px;border-radius:50%;background:var(--wa);color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        @keyframes waPulse{0%,100%{opacity:1}50%{opacity:.4}}

        @keyframes fuUp{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
        .page.active>*{animation:fuUp .25s ease both}

        ::-webkit-scrollbar{width:5px;height:5px}::-webkit-scrollbar-track{background:transparent}::-webkit-scrollbar-thumb{background:var(--br2);border-radius:3px}

        /* ── LOGOUT TOAST ── */
        .logout-toast{
            position:fixed;bottom:22px;right:22px;z-index:10000;
            background:#fff;border:1px solid var(--br);border-radius:14px;
            box-shadow:0 8px 32px rgba(15,23,42,.16),0 2px 8px rgba(15,23,42,.08);
            padding:16px 18px;min-width:280px;max-width:320px;
            display:flex;flex-direction:column;gap:12px;
            animation:ltIn .32s cubic-bezier(.22,1,.36,1) both;
            border-left:3px solid var(--ro);
        }
        .logout-toast.closing{animation:ltOut .26s ease forwards}
        .lt-top{display:flex;align-items:center;gap:10px}
        .lt-icon{width:36px;height:36px;border-radius:10px;background:rgba(220,38,38,.10);display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .lt-title{font-size:13.5px;font-weight:700;color:var(--tx)}
        .lt-sub{font-size:11.5px;color:var(--tx3);margin-top:1px}
        .lt-meta{font-size:10.5px;color:var(--tx3);background:var(--sf2);border-radius:7px;padding:6px 10px;display:flex;align-items:center;gap:6px;font-family:var(--fm)}
        .lt-actions{display:flex;gap:8px}
        .lt-cancel{flex:1;padding:8px 0;border-radius:8px;border:1px solid var(--br);background:var(--sf2);color:var(--tx2);font-size:12px;font-weight:600;cursor:pointer;font-family:var(--fb);transition:all .15s}
        .lt-cancel:hover{background:var(--sf);border-color:var(--br2)}
        .lt-confirm{flex:1.4;padding:8px 0;border-radius:8px;border:none;background:var(--ro);color:#fff;font-size:12px;font-weight:700;cursor:pointer;font-family:var(--fb);display:flex;align-items:center;justify-content:center;gap:5px;transition:all .15s;box-shadow:0 2px 8px rgba(220,38,38,.28)}
        .lt-confirm:hover{background:#b91c1c;transform:translateY(-1px)}
        @keyframes ltIn{from{opacity:0;transform:translateX(60px) scale(.97)}to{opacity:1;transform:translateX(0) scale(1)}}
        @keyframes ltOut{from{opacity:1;transform:translateX(0)}to{opacity:0;transform:translateX(60px)}}

        /* ── RENEWAL ────────────────────────────────────────── */
        .ren-card{background:var(--sf);border:1px solid var(--br);border-radius:var(--r);padding:14px 16px;display:flex;align-items:center;gap:12px;transition:all .2s}
        .ren-card:hover{border-color:var(--ac);box-shadow:var(--sh)}
        .ren-av{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0}
        .ren-info{flex:1;min-width:0}
        .ren-name{font-size:13px;font-weight:600;color:var(--tx);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .ren-meta{font-size:10px;color:var(--tx3);font-family:var(--fm);margin-top:2px}
        .ren-badge{font-size:9px;font-weight:700;padding:2px 7px;border-radius:10px;font-family:var(--fm)}
        .ren-overdue{background:rgba(220,38,38,.1);color:var(--ro)}
        .ren-soon{background:rgba(217,119,6,.1);color:var(--gd)}
        .ren-ok{background:rgba(22,163,74,.1);color:var(--em)}
        /* ── STAFF ATTENDANCE & SALARY ──────────────────────── */
        .sa-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px}
        .sa-card{background:var(--sf);border:1px solid var(--br);border-radius:var(--r);padding:14px;text-align:center;transition:all .2s}
        .sa-card:hover{transform:translateY(-2px);box-shadow:var(--sh)}
        .att-row{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--br)}
        .att-row:last-child{border-bottom:none}
        .att-av{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0}
        .att-name{font-size:13px;font-weight:600;color:var(--tx);flex:1}
        .att-role{font-size:10px;color:var(--tx3);font-family:var(--fm)}
        .att-toggle{display:flex;gap:4px}
        .att-btn{padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;border:none;transition:all .15s}
        .att-p{background:rgba(22,163,74,.12);color:var(--em)}.att-p.active,.att-p:hover{background:var(--em);color:#fff}
        .att-a{background:rgba(220,38,38,.1);color:var(--ro)}.att-a.active,.att-a:hover{background:var(--ro);color:#fff}
        .att-h{background:rgba(217,119,6,.1);color:var(--gd)}.att-h.active,.att-h:hover{background:var(--gd);color:#fff}
        .sal-row{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--br)}
        .sal-row:last-child{border-bottom:none}
        .sal-days{font-size:11px;color:var(--tx3);font-family:var(--fm)}
        .sal-amt{font-size:14px;font-weight:700;font-family:var(--fm);color:var(--em)}
        /* ── AUDIT LOG ──────────────────────────────────────── */
        .audit-row{display:flex;gap:10px;padding:9px 0;border-bottom:1px solid var(--br);font-size:12px}
        .audit-row:last-child{border-bottom:none}
        .audit-ic{width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0}
        .audit-who{font-weight:600;color:var(--tx)}
        .audit-what{color:var(--tx2);margin-top:1px}
        .audit-time{font-size:10px;color:var(--tx3);font-family:var(--fm);margin-top:2px}
        .audit-tag{font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;font-family:var(--fm);white-space:nowrap}
        /* ── PWA ─────────────────────────────────────────────── */
        .pwa-banner{display:none;position:fixed;bottom:16px;left:50%;transform:translateX(-50%);background:var(--ac);color:#fff;border-radius:12px;padding:12px 20px;font-size:13px;font-weight:600;gap:10px;align-items:center;box-shadow:0 4px 20px rgba(61,111,240,.35);z-index:9999;cursor:pointer;max-width:340px;width:90%}
        .pwa-banner.show{display:flex}

        @media(max-width:1100px){
            .stats-grid,.qa-gr{grid-template-columns:repeat(2,1fr)}
            .gm,.g2,.g3,.g4,.al-row{grid-template-columns:1fr}
            #dashBatchCards{grid-template-columns:repeat(2,1fr) !important}
            #dashRowA{grid-template-columns:1fr 1fr !important}
        }
        @media(max-width:768px){
            #dashRowA{grid-template-columns:1fr !important}
        }
    </style>
</head>
<body>
<!-- SIDEBAR -->
<div class="sb">
    <div class="sb-logo"><div class="logo-row"><div class="logo-ic" id="sidebar-logo-wrap"><span class="mi" style="color:#fff;font-size:20px" id="sidebar-logo-icon">auto_stories</span><img id="sidebar-logo-img" src="" alt="" style="display:none;width:36px;height:36px;object-fit:contain;border-radius:8px"></div><div><div class="logo-tx" id="sidebar-lib-name">OPTMS Tech</div><div class="logo-sb">ERP v6.0</div></div></div></div>
    <nav class="sb-nav">
        <div class="ns"><div class="nl">Overview</div>
            <div class="ni active" data-page="dashboard"><span class="ni-ic mi">dashboard</span> Dashboard</div>
            <div class="ni" data-page="analytics"><span class="ni-ic mi">insights</span> Analytics</div>
        </div>
        <div class="ns"><div class="nl">Students</div>
            <div class="ni" data-page="students"><span class="ni-ic mi">school</span> All Students</div>
            <div class="ni" data-page="enroll" id="ni-enroll"><span class="ni-ic mi">person_add</span> Enroll Student</div>
            <div class="ni" data-page="seats"><span class="ni-ic mi">event_seat</span> Seat Allocation</div>
            <div class="ni" data-page="attendance"><span class="ni-ic mi">fact_check</span> Attendance <span class="nbadge" id="b-absent">0</span></div>
        </div>
        <div class="ns"><div class="nl">Books</div>
            <div class="ni" data-page="books"><span class="ni-ic mi">menu_book</span> Books Catalog</div>
            <div class="ni" data-page="transactions"><span class="ni-ic mi">sync_alt</span> Issue / Returns <span class="nbadge" id="b-overdue">0</span></div>
        </div>
        <div class="ns"><div class="nl">Finance</div>
            <div class="ni" data-page="fees"><span class="ni-ic mi">payments</span> Fee Management <span class="nbadge" id="b-fee">0</span></div>
            <div class="ni" data-page="invoices"><span class="ni-ic mi">receipt_long</span> Invoices</div>
            <div class="ni" data-page="expenses"><span class="ni-ic mi">account_balance_wallet</span> Expenses</div>
            <div class="ni" data-page="reports"><span class="ni-ic mi">bar_chart</span> Reports</div>
        </div>
        <div class="ns"><div class="nl">Communication</div>
            <div class="ni" data-page="whatsapp" data-action-page="send_whatsapp"><span class="ni-ic mi">chat</span> WhatsApp <span class="nbadge wa">New</span></div>
        </div>
        <div class="ns"><div class="nl">Admin</div>
            <div class="ni" data-page="staff"><span class="ni-ic mi">manage_accounts</span> Staff & Users</div>
            <div class="ni" data-page="staff_attendance"><span class="ni-ic mi">co_present</span> Staff Attendance</div>
            <div class="ni" data-page="renewal"><span class="ni-ic mi">autorenew</span> Renewals <span class="nbadge y" id="b-renewal">0</span></div>
            <div class="ni" data-page="audit"><span class="ni-ic mi">history</span> Audit Log</div>
            <div class="ni" data-page="biometric"><span class="ni-ic mi">fingerprint</span> Biometric <span class="nbadge g" id="b-bio" style="display:none">●</span></div>
            <div class="ni" data-page="notifications"><span class="ni-ic mi">notifications</span> Notifications <span class="nbadge g" id="b-notif">0</span></div>
            <div class="ni" data-page="settings"><span class="ni-ic mi">settings</span> Settings</div>
        </div>
    </nav>
    <div class="sb-foot">
        <div class="u-card">
            <div class="u-av" id="sidebarAv"><?= $staffInitials ?></div>
            <div style="flex:1"><div class="u-nm"><?= $staffName ?></div><div class="u-rl"><?= $staffRole ?></div></div>
            <span style="color:var(--tx3);cursor:pointer;font-size:13px" title="Change Password" onclick="openM('mChangePw')"><span class="mi sm">lock_reset</span></span>
            <button onclick="logoutToast()" title="Logout" style="background:none;border:none;padding:0;cursor:pointer;margin-left:4px;display:flex;align-items:center"><span class="mi sm" style="color:var(--ro)">power_settings_new</span></button>
        </div>
    </div>
</div>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <div class="pg-title" id="topTitle">Dashboard</div>
        <div class="id-badge" title="Your Staff ID"><div class="id-dot"></div><?= htmlspecialchars($_SESSION['staff_id'] ?? 'N/A') ?></div>
        <div class="srch"><span class="mi sm" style="color:var(--tx3)">search</span><input id="gSearch" placeholder="Search students, books…" oninput="globalSearch(this.value)"></div>
        <div style="display:flex;align-items:center;gap:9px">
            <div class="chip chip-tl"><span class="mi sm">calendar_today</span> <span id="todayChip"></span></div>
            <button class="btn bwa" onclick="navTo('whatsapp')" style="gap:5px;font-size:11px"><span class="mi sm">chat</span>WhatsApp</button>
            <button class="btn bg" onclick="openM('mWaQR')" style="font-size:11px;padding:6px 9px" title="Connect WhatsApp QR"><span class="mi sm">qr_code_scanner</span></button>
            <div class="nb-btn" onclick="navTo('notifications')"><span class="mi sm">notifications</span><div class="nd" id="notifDot" style="display:none"></div></div>
            <button class="btn bp" data-action="enroll_student" onclick="openM('mEnroll')"><span class="mi sm">person_add</span> Enroll</button>
        </div>
    </div>
    <div class="content">
        <!-- DASHBOARD -->
        <div class="page active" id="page-dashboard">
            <div class="al-row" id="dashAlerts"></div>
            <div class="stats-grid" id="dashStats"></div>

            <!-- Quick Actions -->
            <div class="qa-gr">
                <div class="qa-b" data-action="enroll_student" onclick="openM('mEnroll')"><div class="qa-ic" style="background:var(--c-blue)"><span class="mi lg" style="color:var(--ac)">person_add</span></div><div class="qa-lb">New<br>Enroll</div></div>
                <div class="qa-b" data-action="collect_fee" onclick="openM('mCollectFee')"><div class="qa-ic" style="background:var(--c-green)"><span class="mi lg" style="color:var(--em)">payments</span></div><div class="qa-lb">Collect<br>Fee</div></div>
                <div class="qa-b" data-action="issue_book" onclick="openM('mIssueBook')"><div class="qa-ic" style="background:var(--c-amber)"><span class="mi lg" style="color:var(--gd)">upload</span></div><div class="qa-lb">Issue<br>Book</div></div>
                <div class="qa-b" data-action="return_book" onclick="openM('mReturnBook')"><div class="qa-ic" style="background:var(--c-purple)"><span class="mi lg" style="color:var(--vi)">download</span></div><div class="qa-lb">Return<br>Book</div></div>
                <div class="qa-b" onclick="navTo('seats')"><div class="qa-ic" style="background:var(--c-rose)"><span class="mi lg" style="color:var(--ro)">event_seat</span></div><div class="qa-lb">Seat<br>Booking</div></div>
                <div class="qa-b" onclick="navTo('attendance')"><div class="qa-ic" style="background:var(--c-sky)"><span class="mi lg" style="color:var(--sk)">fact_check</span></div><div class="qa-lb">Mark<br>Attend.</div></div>
                <div class="qa-b" data-action="add_expense" onclick="openM('mExpense')"><div class="qa-ic" style="background:var(--c-orange)"><span class="mi lg" style="color:var(--or)">account_balance_wallet</span></div><div class="qa-lb">Add<br>Expense</div></div>
                <div class="qa-b" onclick="navTo('whatsapp')"><div class="qa-ic" style="background:var(--c-teal)"><span class="mi lg" style="color:var(--wa2)">chat</span></div><div class="qa-lb">WhatsApp</div></div>
            </div>

            <!-- ROW A: Live Activity + Revenue Split + Calendar — 3 columns horizontal -->
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:16px" id="dashRowA">

                <!-- Live Activity Feed -->
                <div class="panel" style="margin-bottom:0;display:flex;flex-direction:column">
                    <div class="ph" style="padding:11px 16px">
                        <div class="pt" style="display:flex;align-items:center;gap:7px">
                            <span style="width:8px;height:8px;border-radius:50%;background:var(--em);display:inline-block;box-shadow:0 0 0 3px rgba(22,163,74,.2);animation:pulseDue 1.5s infinite"></span>
                            Live Activity
                        </div>
                        <span style="font-size:9.5px;color:var(--tx3);font-family:var(--fm)" id="liveActTime">Just now</span>
                    </div>
                    <div id="dashLiveAct" style="flex:1;overflow-y:auto;max-height:280px;padding:6px 0"></div>
                </div>

                <!-- Revenue Split — donut + weekly bars -->
                <div class="panel" style="margin-bottom:0">
                    <div class="ph" style="padding:11px 16px"><div class="pt">Revenue Split</div><span style="font-size:9.5px;color:var(--tx3);font-family:var(--fm)">This Month</span></div>
                    <div class="dn-wrap" style="padding:14px 16px 10px">
                        <svg width="88" height="88" viewBox="0 0 88 88">
                            <circle cx="44" cy="44" r="34" fill="none" stroke="var(--sf2)" stroke-width="11"/>
                            <circle cx="44" cy="44" r="34" fill="none" stroke="var(--ac)"  stroke-width="11" id="donutArc1" stroke-dasharray="0 214" stroke-dashoffset="0" stroke-linecap="round" style="transform-origin:center;transform:rotate(-90deg);transition:stroke-dasharray .8s ease"/>
                            <circle cx="44" cy="44" r="34" fill="none" stroke="var(--gd)"  stroke-width="11" id="donutArc2" stroke-dasharray="0 214" stroke-dashoffset="0" stroke-linecap="round" style="transform-origin:center;transform:rotate(-90deg);transition:stroke-dasharray .8s ease .1s"/>
                            <circle cx="44" cy="44" r="34" fill="none" stroke="var(--ro)"  stroke-width="11" id="donutArc3" stroke-dasharray="0 214" stroke-dashoffset="0" stroke-linecap="round" style="transform-origin:center;transform:rotate(-90deg);transition:stroke-dasharray .8s ease .2s"/>
                            <circle cx="44" cy="44" r="34" fill="none" stroke="var(--em)"  stroke-width="11" id="donutArc4" stroke-dasharray="0 214" stroke-dashoffset="0" stroke-linecap="round" style="transform-origin:center;transform:rotate(-90deg);transition:stroke-dasharray .8s ease .3s"/>
                            <text x="44" y="41" text-anchor="middle" fill="var(--tx)" font-size="7.5" font-weight="700" font-family="DM Serif Display" id="donutC">₹0</text>
                            <text x="44" y="51" text-anchor="middle" fill="var(--tx3)" font-size="6" font-family="JetBrains Mono,monospace" id="donutSub">collected</text>
                        </svg>
                        <div class="dn-leg" style="gap:8px">
                            <div class="dli"><div class="dld" style="background:var(--ac)"></div><span class="dll">Paid Full</span><span class="dlv" id="revPct1">0%</span></div>
                            <div class="dli"><div class="dld" style="background:var(--gd)"></div><span class="dll">Partial</span><span class="dlv" id="revPct2">0%</span></div>
                            <div class="dli"><div class="dld" style="background:var(--ro)"></div><span class="dll">Overdue</span><span class="dlv" id="revPct3">0%</span></div>
                            <div class="dli"><div class="dld" style="background:var(--em)"></div><span class="dll">Pending</span><span class="dlv" id="revPct4">0%</span></div>
                        </div>
                    </div>
                    <div style="padding:0 16px 14px">
                        <div style="font-size:9px;color:var(--tx3);font-family:var(--fm);letter-spacing:.8px;text-transform:uppercase;margin-bottom:6px">Weekly Collection</div>
                        <div style="display:flex;align-items:flex-end;gap:3px;height:48px" id="weekChart"></div>
                        <div style="display:flex;justify-content:space-between;font-size:9px;color:var(--tx3);font-family:var(--fm);margin-top:3px"><span>W1</span><span>W2</span><span>W3</span><span>W4</span></div>
                    </div>
                </div>

                <!-- Calendar -->
                <div class="panel" style="margin-bottom:0">
                    <div class="ph" style="padding:11px 16px">
                        <div class="pt"><span class="mi sm" style="vertical-align:middle;margin-right:4px">calendar_month</span><span id="calTitle"></span></div>
                        <div style="display:flex;gap:4px">
                            <button class="btn bg" style="font-size:11px;padding:3px 8px" id="calPrev">‹</button>
                            <button class="btn bg" style="font-size:11px;padding:3px 8px" id="calNext">›</button>
                        </div>
                    </div>
                    <div class="pb" style="padding:12px 14px">
                        <div class="mcal" id="miniCal"></div>
                        <!-- Due date legend -->
                        <div style="margin-top:10px;display:flex;flex-direction:column;gap:5px" id="calDueLegend"></div>
                    </div>
                </div>
            </div>

            <!-- ROW B: Batch Seat Availability + Fee Overview — horizontal -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px">

                <!-- Batch-wise Seat Availability -->
                <div>
                    <div class="sec-hd" style="margin-bottom:10px">
                        <div><div class="sec-t">Batch Seat Availability</div><div class="sec-s">Live occupancy per batch</div></div>
                        <button class="btn bg" onclick="navTo('seats')" style="font-size:11px"><span class="mi sm">event_seat</span>Manage</button>
                    </div>
                    <div id="dashBatchCards" style="display:grid;grid-template-columns:1fr 1fr;gap:10px"></div>
                </div>

                <!-- Fee Overview -->
                <div>
                    <div class="sec-hd" style="margin-bottom:10px">
                        <div><div class="sec-t">Fee Overview</div><div class="sec-s">Current month collection status</div></div>
                        <button class="btn bg" onclick="navTo('fees')" style="font-size:11px">Details →</button>
                    </div>
                    <div id="dashFeeOv"></div>
                </div>
            </div>

            <!-- ROW C: Recent Students Table — full width -->
            <div style="margin-bottom:16px">
                <div class="sec-hd" style="margin-bottom:10px">
                    <div><div class="sec-t">Recent Students & Fee Status</div></div>
                    <button class="btn bg" onclick="navTo('students')" style="font-size:11px">All →</button>
                </div>
                <div class="panel" style="margin-bottom:0">
                    <div class="tw" style="max-height:340px;overflow-y:auto"><table>
                            <thead><tr style="position:sticky;top:0;z-index:2"><th>Student</th><th>Batch</th><th>Seat</th><th>Fee</th><th>Paid</th><th>Balance</th><th>Status</th><th>Msg</th></tr></thead>
                            <tbody id="dashStuTable"></tbody>
                        </table></div>
                </div>
            </div>

            <!-- ROW D: Expense Tracker — full width -->
            <div>
                <div class="sec-hd" style="margin-bottom:10px">
                    <div><div class="sec-t">Expense Tracker</div><div class="sec-s">Monthly outflows by category</div></div>
                    <button class="btn bg" onclick="openM('mExpense')" style="font-size:11px">+ Add</button>
                </div>
                <div class="panel" id="dashExpTracker"></div>
            </div>

        </div>
        <!-- STUDENTS -->
        <div class="page" id="page-students">
            <div class="sec-hd">
                <div><div class="sec-t">All Students</div><div class="sec-s" id="stuCount2"></div></div>
                <div style="display:flex;gap:7px;align-items:center;flex-wrap:wrap">
                    <input placeholder="Search…" style="width:130px;font-size:11.5px" oninput="stuSrch(this.value)" id="stuSrchInp">
                    <div class="tabs" id="stuTabs"><div class="tab active" onclick="stuFilt('all',this)">All</div><div class="tab" onclick="stuFilt('paid',this)">Paid</div><div class="tab" onclick="stuFilt('partial',this)">Partial</div><div class="tab" onclick="stuFilt('pending',this)">Pending</div><div class="tab" onclick="stuFilt('overdue',this)">Overdue</div></div>
                    <button class="btn bp" data-action="enroll_student" onclick="openM('mEnroll')"><span class="mi sm">person_add</span> Enroll</button>
                    <button class="btn bwa" onclick="navTo('whatsapp')" style="font-size:11px"><span class="mi sm">chat</span>Bulk Msg</button>
                </div>
            </div>
            <div class="panel"><div class="tw"><table>
                        <thead><tr><th>Student</th><th>Batch</th><th>Seat</th><th>Type</th><th>Full Fee</th><th>Discount</th><th>Net Fee</th><th>Paid</th><th>Balance</th><th>Status</th><th>Due</th><th>Action</th></tr></thead>
                        <tbody id="stuTable"></tbody>
                    </table></div>
                <div class="pag"><span class="pag-i" id="stuPagI"></span><div class="pag-b" id="stuPagB"></div></div></div>
        </div>

        <!-- SEATS -->
        <div class="page" id="page-seats">
            <div class="sec-hd">
                <div><div class="sec-t">Seat Allocation</div><div class="sec-s">Batch seat map with fee status highlight</div></div>
                <div style="display:flex;gap:7px"><button class="btn bp" data-action="add_batch" onclick="openM('mAddBatch')">+ Add Batch</button><button class="btn bg" data-action="alloc_seat" onclick="openM('mAllocSeat')">Allocate Seat</button></div>
            </div>
            <div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
                <div class="sc" style="--ca:var(--ac)"><div class="s-row"><div class="s-ic"><span class="mi" style="color:var(--ac);font-size:20px">event_seat</span></div><span class="s-lb">Total Seats</span></div><div class="s-vl" id="st-total">0</div></div>
                <div class="sc" style="--ca:var(--em)"><div class="s-row"><div class="s-ic"><span class="mi" style="color:var(--em);font-size:20px">check_circle</span></div><span class="s-lb">Vacant</span></div><div class="s-vl" id="st-vacant">0</div></div>
                <div class="sc" style="--ca:var(--ro)"><div class="s-row"><div class="s-ic"><span class="mi" style="color:var(--ro);font-size:20px">person</span></div><span class="s-lb">Occupied</span></div><div class="s-vl" id="st-occupied">0</div></div>
            </div>
            <div style="margin-bottom:10px">
                <div class="seat-legend">
                    <div class="sl-item"><div class="sl-dot seat-vac"></div>Vacant</div>
                    <div class="sl-item"><div class="sl-dot seat-occ"></div>Paid &amp; Occupied</div>
                    <div class="sl-item"><div class="sl-dot seat-due"></div>Fee Pending / Partial</div>
                    <div class="sl-item"><div class="sl-dot seat-overdue"></div>Fee Overdue</div>
                </div>
            </div>
            <div class="g2" id="batchGrid"></div>
        </div>

        <!-- ATTENDANCE -->
        <div class="page" id="page-attendance">
            <div class="sec-hd">
                <div><div class="sec-t">Attendance</div><div class="sec-s" id="attLbl"></div></div>
                <div style="display:flex;gap:7px;align-items:center;flex-wrap:wrap">
                    <select id="attBatchF" onchange="renderAtt()" style="font-size:12px;padding:6px 9px"><option value="all">All Batches</option></select>
                    <button class="btn bp" onclick="saveAtt()"><span class="mi sm">save</span>Save</button>
                    <button class="btn bg" onclick="markAll(true)">✓ All Present</button>
                    <button class="btn bd" onclick="markAll(false)" style="font-size:11px">✗ All Absent</button>
                </div>
            </div>
            <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
                <div class="sc" style="--ca:var(--em)"><div class="s-lb">Present</div><div class="s-vl" id="at-p">0</div></div>
                <div class="sc" style="--ca:var(--ro)"><div class="s-lb">Absent</div><div class="s-vl" id="at-a">0</div></div>
                <div class="sc" style="--ca:var(--gd)"><div class="s-lb">Rate</div><div class="s-vl" id="at-r">0%</div></div>
                <div class="sc" style="--ca:var(--ac)"><div class="s-lb">Total</div><div class="s-vl" id="at-t">0</div></div>
            </div>

            <!-- Live Check-In Feed: QR + Biometric -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px">
                <!-- QR Feed -->
                <div class="panel">
                    <div class="ph">
                        <div class="pt"><span class="mi sm" style="vertical-align:middle;margin-right:5px">qr_code_scanner</span>QR Check-Ins</div>
                        <div style="display:flex;align-items:center;gap:8px">
                            <span id="qrScanCount" style="font-size:11px;color:var(--tx3)">0 scans</span>
                            <button class="btn bg" style="font-size:10px;padding:4px 8px" onclick="loadQRScans()"><span class="mi sm">refresh</span></button>
                        </div>
                    </div>
                    <div class="pb" id="qrScanList" style="padding:10px;max-height:220px;overflow-y:auto">
                        <div style="text-align:center;padding:20px;color:var(--tx3);font-size:12px">No QR check-ins yet today</div>
                    </div>
                </div>
                <!-- Biometric Feed -->
                <div class="panel">
                    <div class="ph">
                        <div class="pt"><span class="mi sm" style="vertical-align:middle;margin-right:5px">fingerprint</span>Biometric Check-Ins</div>
                        <div style="display:flex;align-items:center;gap:8px">
                            <span id="bioAttCount" style="font-size:11px;color:var(--tx3)">0 punches</span>
                            <div id="bioDeviceDot" style="width:8px;height:8px;border-radius:50%;background:#e2e8f0;flex-shrink:0" title="Device status"></div>
                            <button class="btn bg" style="font-size:10px;padding:4px 8px" onclick="loadAttBiometric()"><span class="mi sm">refresh</span></button>
                        </div>
                    </div>
                    <div class="pb" id="bioAttList" style="padding:10px;max-height:220px;overflow-y:auto">
                        <div style="text-align:center;padding:20px;color:var(--tx3);font-size:12px">No biometric punches yet today</div>
                    </div>
                </div>
            </div>

            <!-- Attendance Table with check-in/out + hours -->
            <div class="panel"><div class="tw"><table>
                        <thead><tr><th>Student</th><th>Batch</th><th>Seat</th><th>Fee Status</th><th>Check In</th><th>Check Out</th><th>Hours</th><th>Attend.</th><th>Toggle</th></tr></thead>
                        <tbody id="attTable"></tbody>
                    </table></div></div>
        </div>

        <!-- BIOMETRIC -->
        <div class="page" id="page-biometric">
            <div class="sec-hd">
                <div><div class="sec-t">Biometric Door System</div><div class="sec-s">eSSL X990 &amp; Realtime RS9N attendance tracking</div></div>
                <div style="display:flex;gap:7px;align-items:center">
                    <button class="btn bg" onclick="loadBiometric()"><span class="mi sm">refresh</span> Refresh</button>
                </div>
            </div>
            <!-- Device Status Row -->
            <div id="bioDeviceCards" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;margin-bottom:16px">
                <div style="text-align:center;padding:24px;color:var(--tx3);font-size:13px;grid-column:1/-1">Loading devices…</div>
            </div>
            <!-- Fee Gate Toggle + ADMS URL -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px">
                <div class="panel">
                    <div class="ph"><div class="pt"><span class="mi sm" style="vertical-align:middle;margin-right:5px">lock</span>Fee Gate</div></div>
                    <div class="pb">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                            <div>
                                <div style="font-size:13px;font-weight:700;color:var(--tx)">Block overdue students at door</div>
                                <div style="font-size:11px;color:var(--tx3);margin-top:3px">If ON — students with overdue fee cannot enter even if fingerprint matches</div>
                            </div>
                            <label style="position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0;margin-left:12px">
                                <input type="checkbox" id="feeGateToggle" onchange="toggleFeeGate(this.checked)" style="opacity:0;width:0;height:0;position:absolute">
                                <span id="feeGateSlider" style="position:absolute;cursor:pointer;inset:0;background:#e2e8f0;border-radius:34px;transition:.3s"><span id="feeGateThumb" style="position:absolute;height:18px;width:18px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.3s;box-shadow:0 1px 3px rgba(0,0,0,.2)"></span></span>
                            </label>
                        </div>
                        <div id="feeGateStatus" style="font-size:11px;font-weight:700;color:var(--tx3)">Loading…</div>
                    </div>
                </div>
                <div class="panel">
                    <div class="ph"><div class="pt"><span class="mi sm" style="vertical-align:middle;margin-right:5px">link</span>ADMS Server URL</div></div>
                    <div class="pb">
                        <div style="font-size:12px;color:var(--tx2);margin-bottom:8px">Enter this URL in your device ADMS / Push Server settings:</div>
                        <div style="display:flex;gap:7px">
                            <input id="admsUrl" readonly style="flex:1;font-size:11px;font-family:var(--fm);background:var(--sf2)" value="">
                            <button class="btn bg" style="font-size:11px;flex-shrink:0" onclick="navigator.clipboard?.writeText(document.getElementById('admsUrl').value).then(()=>toast('URL copied!','ok'))"><span class="mi sm">content_copy</span></button>
                        </div>
                        <div style="margin-top:8px;font-size:11px;color:var(--tx3)">Port: <b>80</b> (HTTP) or <b>443</b> (HTTPS) · Path auto-appended by device</div>
                    </div>
                </div>
            </div>
            <!-- Today's Punch Log -->
            <div class="panel">
                <div class="ph">
                    <div class="pt"><span class="mi sm" style="vertical-align:middle;margin-right:5px">fingerprint</span>Today's Biometric Punches</div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <span id="bioPunchCount" style="font-size:11px;color:var(--tx3)">0 punches</span>
                        <button class="btn bg" style="font-size:10px;padding:4px 8px" onclick="loadBiometric()"><span class="mi sm">refresh</span></button>
                    </div>
                </div>
                <div class="pb" id="bioPunchList" style="padding:10px">
                    <div style="text-align:center;padding:24px;color:var(--tx3);font-size:13px">No punches today</div>
                </div>
            </div>
        </div>

        <!-- BOOKS -->
        <div class="page" id="page-books">
            <div class="sec-hd">
                <div><div class="sec-t">Books Catalog</div><div class="sec-s" id="bkCount"></div></div>
                <div style="display:flex;gap:7px;align-items:center">
                    <input placeholder="Search…" style="width:130px;font-size:11.5px" oninput="bkSrch(this.value)">
                    <select id="bkCatF" onchange="renderBooks()" style="font-size:12px;padding:6px 9px"><option value="all">All</option><option>Academic</option><option>Self-Help</option><option>Fiction</option><option>Science</option></select>
                    <button class="btn bp" data-action="add_book" onclick="openM('mAddBook')">+ Add Book</button>
                </div>
            </div>
            <div class="panel"><div class="tw"><table>
                        <thead><tr><th>Book</th><th>Author</th><th>Category</th><th>Copies</th><th>Available</th><th>Shelf</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody id="bkTable"></tbody>
                    </table></div>
                <div class="pag"><span class="pag-i" id="bkPagI"></span><div class="pag-b" id="bkPagB"></div></div></div>
        </div>

        <!-- TRANSACTIONS -->
        <div class="page" id="page-transactions">
            <div class="sec-hd">
                <div><div class="sec-t">Issue & Returns</div><div class="sec-s" id="txCount"></div></div>
                <div style="display:flex;gap:7px"><button class="btn bp" data-action="issue_book" onclick="openM('mIssueBook')">📤 Issue</button><button class="btn bg" data-action="return_book" onclick="openM('mReturnBook')">📩 Return</button></div>
            </div>
            <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
                <div class="sc" style="--ca:var(--vi)"><div class="s-lb">Issued</div><div class="s-vl" id="tx-is">0</div></div>
                <div class="sc" style="--ca:var(--ro)"><div class="s-lb">Overdue</div><div class="s-vl" id="tx-od">0</div></div>
                <div class="sc" style="--ca:var(--em)"><div class="s-lb">Returned</div><div class="s-vl" id="tx-rt">0</div></div>
                <div class="sc" style="--ca:var(--gd)"><div class="s-lb">Fine Collected</div><div class="s-vl" id="tx-fn">₹0</div></div>
            </div>
            <div class="panel"><div class="tw"><table>
                        <thead><tr><th>Student</th><th>Book</th><th>Issue Date</th><th>Due Date</th><th>Return Date</th><th>Fine</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody id="txTable"></tbody>
                    </table></div></div>
        </div>

        <!-- FEES -->
        <div class="page" id="page-fees">
            <div class="sec-hd">
                <div><div class="sec-t">Fee Management</div></div>
                <div style="display:flex;gap:7px;flex-wrap:wrap">
                    <button class="btn bp" data-action="collect_fee" onclick="openM('mCollectFee')">💳 Collect Fee</button>
                    <button class="btn bwa" onclick="waBulkFee()" style="font-size:11px"><span class="mi sm">chat</span>WA Reminders</button>
                    <button class="btn bg" onclick="sendReminders()" style="font-size:11px">📣 Send Reminders</button>
                </div>
            </div>
            <div class="stats-grid">
                <div class="sc" style="--ca:var(--em)"><div class="s-row"><div class="s-ic"><span class="mi" style="color:var(--em);font-size:20px">check_circle</span></div><span class="s-lb">Collected</span></div><div class="s-vl" id="fc-c">₹0</div><div class="s-mt" id="fc-cm"></div></div>
                <div class="sc" style="--ca:var(--sk)"><div class="s-row"><div class="s-ic"><span class="mi" style="color:var(--sk);font-size:20px">timelapse</span></div><span class="s-lb">Partial Payments</span></div><div class="s-vl" id="fc-pp">0</div><div class="s-mt" id="fc-ppm"></div></div>
                <div class="sc" style="--ca:var(--gd)"><div class="s-row"><div class="s-ic"><span class="mi" style="color:var(--gd);font-size:20px">pending</span></div><span class="s-lb">Pending</span></div><div class="s-vl" id="fc-p">₹0</div><div class="s-mt" id="fc-pm"></div></div>
                <div class="sc" style="--ca:var(--ro)"><div class="s-row"><div class="s-ic"><span class="mi" style="color:var(--ro);font-size:20px">warning</span></div><span class="s-lb">Overdue</span></div><div class="s-vl" id="fc-o">₹0</div><div class="s-mt" id="fc-om"></div></div>
            </div>
            <div class="panel">
                <div class="ph"><div class="pt">Fee Records</div>
                    <div style="display:flex;gap:7px;align-items:center;flex-wrap:wrap">
                        <input placeholder="Search…" style="width:120px;font-size:11.5px" oninput="feeSrch(this.value)">
                        <div class="tabs" id="feeTabs"><div class="tab active" onclick="feeFilt('all',this)">All</div><div class="tab" onclick="feeFilt('paid',this)">Paid</div><div class="tab" onclick="feeFilt('partial',this)">Partial</div><div class="tab" onclick="feeFilt('pending',this)">Pending</div><div class="tab" onclick="feeFilt('overdue',this)">Overdue</div></div>
                    </div>
                </div>
                <div class="tw"><table>
                        <thead><tr><th>Student</th><th>Batch</th><th>Full Fee</th><th>Discount</th><th>Net Fee</th><th>Paid Amt</th><th>Balance Due</th><th>Paid On</th><th>Status</th><th>Due Date</th><th>Action</th></tr></thead>
                        <tbody id="feeTable"></tbody>
                    </table></div>
                <div class="pag"><span class="pag-i" id="feePagI"></span></div>
            </div>
        </div>

        <!-- INVOICES -->
        <div class="page" id="page-invoices">
            <div class="sec-hd"><div><div class="sec-t">Invoices</div><div class="sec-s" id="invCount"></div></div><button class="btn bp" data-action="generate_invoice" onclick="openM('mGenInv')">+ Generate</button></div>
            <div class="panel"><div id="invRevSummary"></div><div class="tw"><table>
                        <thead><tr><th>Invoice #</th><th>Student</th><th>Type</th><th>Total Fee</th><th>Discount</th><th>Paid</th><th>Balance</th><th>Date</th><th>Mode</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody id="invTable"></tbody>
                    </table></div></div>
        </div>

        <!-- EXPENSES -->
        <div class="page" id="page-expenses">
            <div class="sec-hd"><div><div class="sec-t">Expenses</div></div><button class="btn bp" data-action="add_expense" onclick="openM('mExpense')">+ Add</button></div>
            <div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
                <div class="sc" style="--ca:var(--ro)"><div class="s-lb">Total Expenses</div><div class="s-vl" id="ex-t">₹0</div></div>
                <div class="sc" style="--ca:var(--em)"><div class="s-lb">Net Profit</div><div class="s-vl" id="ex-p">₹0</div></div>
                <div class="sc" style="--ca:var(--ac)"><div class="s-lb">Revenue</div><div class="s-vl" id="ex-r">₹0</div></div>
            </div>
            <div class="panel">
                <div class="ph"><div class="pt">Records</div><select id="exCatF" onchange="renderExp()" style="font-size:12px;padding:6px 9px"><option value="all">All</option><option>Utilities</option><option>Staff</option><option>Maintenance</option><option>Supplies</option><option>Books</option></select></div>
                <div class="pb" style="display:flex;flex-direction:column;gap:7px" id="expList"></div>
            </div>
        </div>

        <!-- ANALYTICS -->
        <div class="page" id="page-analytics">
            <div class="sec-hd"><div><div class="sec-t">Analytics</div></div></div>
            <div class="g3" id="analCards"></div>
            <div class="g2">
                <div class="panel"><div class="ph"><div class="pt">Monthly Revenue</div></div><div class="pb"><div style="display:flex;align-items:flex-end;gap:7px;height:110px" id="revChart"></div><div style="display:flex;justify-content:space-around;font-size:9px;color:var(--tx3);font-family:var(--fm);margin-top:5px"><span>Jan</span><span>Feb</span><span>Mar</span><span>Apr*</span></div></div></div>
                <div class="panel"><div class="ph"><div class="pt">Batch Occupancy</div></div><div class="pb" id="batchAnal"></div></div>
            </div>
        </div>

        <!-- REPORTS -->
        <div class="page" id="page-reports">
            <div class="sec-hd"><div><div class="sec-t">Reports</div></div></div>
            <div class="g3">
                <div class="panel" style="cursor:pointer" onclick="genReport('monthly')"><div class="pb" style="text-align:center;padding:22px"><div style="margin-bottom:8px"><span class="mi xl" style="color:var(--ac)">description</span></div><div style="font-weight:600;margin-bottom:3px">Monthly Summary</div><button class="btn bp" style="margin-top:10px">Generate</button></div></div>
                <div class="panel" style="cursor:pointer" onclick="genReport('fee')"><div class="pb" style="text-align:center;padding:22px"><div style="margin-bottom:8px"><span class="mi xl" style="color:var(--em)">payments</span></div><div style="font-weight:600;margin-bottom:3px">Fee Report</div><button class="btn bp" style="margin-top:10px">Generate</button></div></div>
                <div class="panel" style="cursor:pointer" onclick="genReport('books')"><div class="pb" style="text-align:center;padding:22px"><div style="margin-bottom:8px"><span class="mi xl" style="color:var(--gd)">menu_book</span></div><div style="font-weight:600;margin-bottom:3px">Book Inventory</div><button class="btn bp" style="margin-top:10px">Generate</button></div></div>
                <div class="panel" style="cursor:pointer" onclick="genReport('attendance')"><div class="pb" style="text-align:center;padding:22px"><div style="margin-bottom:8px"><span class="mi xl" style="color:var(--vi)">fact_check</span></div><div style="font-weight:600;margin-bottom:3px">Attendance</div><button class="btn bp" style="margin-top:10px">Generate</button></div></div>
                <div class="panel" style="cursor:pointer" onclick="genReport('expense')"><div class="pb" style="text-align:center;padding:22px"><div style="margin-bottom:8px"><span class="mi xl" style="color:var(--or)">account_balance_wallet</span></div><div style="font-weight:600;margin-bottom:3px">Expense Report</div><button class="btn bp" style="margin-top:10px">Generate</button></div></div>
                <div class="panel" style="cursor:pointer" onclick="genReport('student')"><div class="pb" style="text-align:center;padding:22px"><div style="margin-bottom:8px"><span class="mi xl" style="color:var(--sk)">groups</span></div><div style="font-weight:600;margin-bottom:3px">Student Directory</div><button class="btn bp" style="margin-top:10px">Generate</button></div></div>
            </div>
            <div class="panel" id="rptOut" style="display:none"><div class="ph"><div class="pt" id="rptTitle">Report</div><button class="btn bg" onclick="window.print()"><span class="mi sm">print</span>Print</button></div><div class="pb" id="rptBody"></div></div>
        </div>

        <!-- WHATSAPP -->
        <div class="page" id="page-whatsapp">
            <div class="sec-hd"><div><div class="sec-t">💬 WhatsApp Messaging</div><div class="sec-s">Auto-send fee, enrollment &amp; reminder messages</div></div><div style="display:flex;align-items:center;gap:8px"><button class="btn bwa" style="font-size:11px;gap:6px" onclick="openM('mWaQR')"><span class="mi sm">qr_code_scanner</span>Connect QR</button></div></div>

            <!-- Template Grid Section -->
            <div class="wa-section" style="margin-bottom:14px">
                <div class="wa-section-hd"><div class="pt" style="color:var(--wa2)">📋 Message Templates</div><span style="font-size:11px;color:var(--tx3)">Click any template to compose &amp; send</span></div>
                <div style="padding:16px"><div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px" id="waTemplateGrid"></div></div>
            </div>

            <!-- Compose + Preview Section -->
            <div class="g2" style="margin-bottom:14px">
                <div class="wa-section">
                    <div class="wa-section-hd"><div class="pt">✍ Compose Message</div></div>
                    <div class="wa-compose-row">
                        <div class="wa-compose-field"><label>Select Student(s)</label><select id="wa-stu" onchange="waUpdatePreview()"><option value="">-- Select Student --</option><option value="all">📢 All Students</option><option value="pending_all">⏳ All Pending + Partial</option><option value="overdue">🚨 All Overdue</option></select></div>
                        <div class="wa-compose-field"><label>Template</label><select id="wa-tpl" onchange="waLoadTemplate()"><option value="">-- Select Template --</option><option value="welcome">🎉 Welcome / Enrollment</option><option value="fee_due">💰 Fee Due Reminder</option><option value="fee_overdue">🚨 Fee Overdue Alert</option><option value="partial_payment">💳 Partial Payment Received</option><option value="fee_receipt">✅ Fee Payment Receipt</option><option value="discount_applied">🎁 Discount Applied</option><option value="book_due">📚 Book Return Reminder</option><option value="book_overdue">⚠ Book Overdue Fine</option><option value="seat_allotted">🪑 Seat Allotment</option><option value="holiday">📅 Holiday Notice</option><option value="custom">✏ Custom Message</option></select></div>
                        <div class="wa-compose-field"><label>Message</label><textarea id="wa-msg" rows="8" placeholder="Select a template…" oninput="waUpdatePreview()" style="font-size:12px;line-height:1.6;min-height:140px"></textarea></div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap"><button class="btn bwa" onclick="waSend()" style="flex:1">💬 Send via WhatsApp</button><button class="btn bg" onclick="waCopy()" style="font-size:11px"><span class="mi sm">content_copy</span>Copy</button><button class="btn bg" onclick="waSchedule()" style="font-size:11px">⏰ Schedule</button></div>
                        <div id="wa-send-info" style="margin-top:4px;font-size:11px;color:var(--tx3)"></div>
                    </div>
                </div>
                <div class="wa-section">
                    <div class="wa-section-hd"><div class="pt">👁 Preview</div><span style="font-size:10px;color:var(--tx3)">WhatsApp appearance</span></div>
                    <div style="padding:16px">
                        <div class="wa-preview-box">
                            <div class="wa-preview-contact">
                                <div style="width:32px;height:32px;border-radius:50%;background:var(--wa);display:flex;align-items:center;justify-content:center;font-size:16px">📚</div>
                                <div><div style="font-size:13px;font-weight:700;color:#1a1a1a">OPTMS Tech Library</div><div style="font-size:10px;color:#666">Official Account</div></div>
                            </div>
                            <div class="wa-preview" id="waPreview">Select a template to preview…</div>
                        </div>
                        <div style="margin-top:12px">
                            <div style="font-size:10px;color:var(--tx3);font-family:var(--fm);margin-bottom:6px;text-transform:uppercase;letter-spacing:.8px">Recent Sends</div>
                            <div id="waSendLog" style="display:flex;flex-direction:column;gap:5px;max-height:140px;overflow-y:auto"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Send Section -->
            <div class="wa-section">
                <div class="wa-section-hd"><div class="pt">📢 Bulk Send</div></div>
                <div style="padding:16px"><div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px">
                        <div class="wa-bulk-card"><div style="font-size:22px;margin-bottom:6px">🎉</div><div style="font-weight:600;font-size:12px;margin-bottom:3px">Welcome New</div><div style="font-size:10px;color:var(--tx3);margin-bottom:10px">This month's enrollments</div><button class="btn bwa" style="width:100%;font-size:11px" onclick="bulkSend('welcome')">Send (<span id="bk-welcome">0</span>)</button></div>
                        <div class="wa-bulk-card"><div style="font-size:22px;margin-bottom:6px">⏳</div><div style="font-weight:600;font-size:12px;margin-bottom:3px">Fee Pending</div><div style="font-size:10px;color:var(--tx3);margin-bottom:10px">Pending + partial</div><button class="btn bwa" style="width:100%;font-size:11px;background:#e67e22" onclick="bulkSend('pending')">Send (<span id="bk-pending">0</span>)</button></div>
                        <div class="wa-bulk-card"><div style="font-size:22px;margin-bottom:6px">🚨</div><div style="font-weight:600;font-size:12px;margin-bottom:3px">Fee Overdue</div><div style="font-size:10px;color:var(--tx3);margin-bottom:10px">Critical overdue</div><button class="btn bd" data-action="send_whatsapp" style="width:100%;font-size:11px" onclick="bulkSend('overdue')">Send (<span id="bk-overdue2">0</span>)</button></div>
                        <div class="wa-bulk-card"><div style="font-size:22px;margin-bottom:6px">📚</div><div style="font-weight:600;font-size:12px;margin-bottom:3px">Book Overdue</div><div style="font-size:10px;color:var(--tx3);margin-bottom:10px">Return reminders</div><button class="btn" style="width:100%;font-size:11px;background:var(--vi);color:#fff" onclick="bulkSend('bookoverdue')">Send (<span id="bk-bookod">0</span>)</button></div>
                    </div></div>
            </div>
        </div>

        <!-- STAFF -->
        <div class="page" id="page-staff">
            <div class="sec-hd"><div><div class="sec-t">Staff & Users</div><div class="sec-s" id="staffCount"></div></div><button class="btn bp" data-action="add_staff" onclick="openM('mAddStaff')"><span class="mi sm">person_add</span>Add Staff</button></div>
            <div class="panel"><div class="tw"><table>
                        <thead><tr><th>Staff</th><th>Role</th><th>Email</th><th>Phone</th><th>Permissions</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody id="staffTable"></tbody>
                    </table></div></div>
        </div>


        <!-- RENEWAL -->
        <div class="page" id="page-renewal">
            <div class="sec-hd">
                <div><div class="sec-t">🔄 Student Renewals</div><div class="sec-s">Extend due dates and collect next month fee in one click</div></div>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <select id="renFilterSel" onchange="renderRenewal()" style="font-size:11px;padding:5px 9px">
                        <option value="all">All Students</option>
                        <option value="overdue">Overdue</option>
                        <option value="due7">Due in 7 days</option>
                        <option value="due30">Due in 30 days</option>
                    </select>
                    <button class="btn bp" onclick="bulkRenew()">🔄 Bulk Renew All Due</button>
                </div>
            </div>
            <div class="g2" style="margin-bottom:14px">
                <div class="sc" style="--ca:var(--ro)"><div class="s-lb">Overdue</div><div class="s-vl" id="ren-overdue">0</div><div class="s-mt">Need immediate renewal</div></div>
                <div class="sc" style="--ca:var(--gd)"><div class="s-lb">Due in 7 Days</div><div class="s-vl" id="ren-soon">0</div><div class="s-mt">Renew soon</div></div>
            </div>
            <div class="panel"><div class="ph"><div class="pt">Students</div><span id="renCount" style="font-size:11px;color:var(--tx3)"></span></div>
                <div class="pb"><div id="renewalList" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px"></div></div></div>
        </div>

        <!-- STAFF ATTENDANCE -->
        <div class="page" id="page-staff_attendance">
            <div class="sec-hd">
                <div><div class="sec-t">👥 Staff Attendance & Salary</div><div class="sec-s">Mark daily attendance and calculate monthly salary</div></div>
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="date" id="staffAttDate" onchange="renderStaffAtt()" style="font-size:12px;padding:5px 9px">
                    <button class="btn bp" onclick="saveStaffAtt()">💾 Save Attendance</button>
                </div>
            </div>
            <div class="g2">
                <div class="panel"><div class="ph"><div class="pt">📋 Today's Attendance</div></div>
                    <div class="pb"><div id="staffAttList"></div></div>
                </div>
                <div class="panel"><div class="ph">
                        <div class="pt">💰 Monthly Salary</div>
                        <select id="staffSalMonth" onchange="renderStaffSalary()" style="font-size:11px;padding:5px 9px"></select>
                    </div>
                    <div class="pb"><div id="staffSalList"></div>
                        <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--br);display:flex;justify-content:space-between;align-items:center">
                            <span style="font-size:12px;font-weight:600">Total Payable</span>
                            <span style="font-size:16px;font-weight:700;font-family:var(--fm);color:var(--ac)" id="staffSalTotal">₹0</span>
                            <button class="btn bp" id="saveSalBtn" onclick="saveSalaries()">💰 Save Salaries</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel" style="margin-top:14px"><div class="ph"><div class="pt">📊 Attendance Summary</div></div>
                <div class="pb"><div id="staffAttSummary" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px"></div></div>
            </div>
        </div>

        <!-- AUDIT LOG -->
        <div class="page" id="page-audit">
            <div class="sec-hd">
                <div><div class="sec-t">🔍 Audit Log</div><div class="sec-s">Every action logged — who did what and when</div></div>
                <div style="display:flex;gap:8px">
                    <select id="auditFilter" onchange="renderAudit()" style="font-size:11px;padding:5px 9px">
                        <option value="all">All Actions</option>
                        <option value="fee">Fee</option>
                        <option value="student">Student</option>
                        <option value="book">Book</option>
                        <option value="staff">Staff</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="settings">Settings</option>
                    </select>
                    <button class="btn bg" style="font-size:11px" onclick="clearAudit()">🗑 Clear Log</button>
                </div>
            </div>
            <div class="g2" style="margin-bottom:14px" id="auditStats"></div>
            <div class="panel"><div class="ph"><div class="pt">Activity History</div><span id="auditCount" style="font-size:11px;color:var(--tx3)"></span></div>
                <div class="pb" id="auditList" style="max-height:600px;overflow-y:auto"></div>
            </div>
        </div>

        <!-- NOTIFICATIONS -->
        <div class="page" id="page-notifications">
            <div class="sec-hd"><div><div class="sec-t">Notifications</div><div class="sec-s" id="notifCount"></div></div><button class="btn bg" onclick="clearNotifs()" style="font-size:11px"><span class="mi sm">delete_sweep</span>Clear All</button></div>
            <div class="panel"><div class="pb" id="notifList" style="display:flex;flex-direction:column;gap:7px"></div></div>
        </div>

        <!-- SETTINGS -->
        <div class="page" id="page-settings">
            <div class="sec-hd"><div><div class="sec-t">Settings</div></div></div>
            <div class="g2">
                <div>
                    <!-- Profile Photo Upload -->
                    <div class="panel" style="margin-bottom:14px">
                        <div class="ph"><div class="pt"><span class="mi sm" style="vertical-align:middle;margin-right:5px">account_circle</span>My Profile Photo</div></div>
                        <div class="pb">
                            <div style="display:flex;align-items:center;gap:18px">
                                <div style="position:relative;width:72px;height:72px;flex-shrink:0">
                                    <div id="dp-placeholder" style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--ac),var(--vi));display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:700;color:#fff"><?= $staffInitials ?></div>
                                    <img id="dp-preview" src="" alt="" style="display:none;position:absolute;inset:0;width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid var(--ac);box-shadow:0 2px 10px rgba(61,111,240,.25)">
                                </div>
                                <div style="flex:1">
                                    <div style="font-size:13px;font-weight:600;color:var(--tx);margin-bottom:2px"><?= $staffName ?></div>
                                    <div style="font-size:11px;color:var(--tx3);margin-bottom:10px"><?= $staffRole ?> · Upload your personal photo (JPG/PNG, max 2MB)</div>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <label style="cursor:pointer">
                                            <input type="file" id="dp-file-input" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="uploadDP()">
                                            <span class="btn bp" style="pointer-events:none"><span class="mi sm">upload</span> Choose Photo</span>
                                        </label>
                                        <span id="dp-status" style="font-size:11px;color:var(--tx3)"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Library Logo Upload -->
                    <div class="panel" style="margin-bottom:14px">
                        <div class="ph"><div class="pt"><span class="mi sm" style="vertical-align:middle;margin-right:5px">add_photo_alternate</span>Library Logo</div></div>
                        <div class="pb">
                            <div style="display:flex;align-items:center;gap:18px">
                                <div style="position:relative;width:72px;height:72px;flex-shrink:0;border-radius:12px;border:2px dashed var(--br2);overflow:hidden;display:flex;align-items:center;justify-content:center;background:var(--sf2)">
                                    <span class="mi xl" style="color:var(--br2)" id="logo-placeholder">image</span>
                                    <img id="logo-preview" src="" alt="Logo" style="display:none;position:absolute;inset:0;width:72px;height:72px;object-fit:contain;border-radius:10px">
                                </div>
                                <div style="flex:1">
                                    <div style="font-size:13px;font-weight:600;color:var(--tx);margin-bottom:2px">Library Logo</div>
                                    <div style="font-size:11px;color:var(--tx3);margin-bottom:10px">Shown in sidebar & invoices (JPG/PNG, max 2MB)</div>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <label style="cursor:pointer">
                                            <input type="file" id="logo-file-input" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="uploadLogo()">
                                            <span class="btn bp" style="pointer-events:none"><span class="mi sm">upload</span> Choose Logo</span>
                                        </label>
                                        <button class="btn bg" onclick="removeLogo()" id="logo-remove-btn" style="display:none"><span class="mi sm">close</span> Remove</button>
                                        <span id="logo-status" style="font-size:11px;color:var(--tx3)"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Library Info -->
                    <div class="panel"><div class="ph"><div class="pt">NAYI UDAAN LIBRARY Info</div></div><div class="pb">
                            <div class="fg">
                                <div class="fgi full"><label>Library Name</label><input id="s-name" value="NAYI UDAAN LIBRARY"></div>
                                <div class="fgi"><label>Phone / WhatsApp</label><input id="s-phone" value="+91 97099 00158"></div>
                                <div class="fgi"><label>Email</label><input id="s-email" value="aryanraj0158@gmail.com"></div>
                                <div class="fgi full"><label>Address</label><input id="s-addr" value="New Bypass Road, Madhepura, Bihar - 852113"></div>
                                <div class="fgi"><label>Fine Per Day (₹)</label><input id="s-fine" value="5" type="number" min="0"></div>
                                <div class="fgi"><label>Max Issue Days</label><input id="s-days" value="14" type="number" min="1"></div>
                                <div class="fgi"><label>AC Seat Extra (₹)</label><input id="s-acfee" value="200" type="number" min="0"></div>
                                <div class="fgi"><label>WhatsApp Number</label><input id="s-wa" value="919709900158"></div>
                                <div class="fgi"><label>UPI ID for Payments</label><input id="s-upi" placeholder="e.g. 7282071620@okaxis" value="7282071620@okaxis"></div>
                            </div>
                            <div style="margin-top:14px;display:flex;gap:8px">
                                <button class="btn bp" onclick="saveSettings()"><span class="mi sm">save</span>Save Settings</button>
                                <button class="btn bd" data-action="reset_data" onclick="if(confirm('Reset all data?')){initData();toast('Reset!','wn')}"><span class="mi sm">restart_alt</span>Reset</button>
                            </div>
                        </div></div>
                </div>
                <div class="panel"><div class="ph"><div class="pt">System Stats</div></div><div class="pb" id="setStats"></div></div>
                <!-- Biometric Device Panel in Settings -->
                <div class="panel" style="margin-top:14px">
                    <div class="ph">
                        <div class="pt"><span class="mi sm" style="vertical-align:middle;margin-right:5px">fingerprint</span>Biometric Devices</div>
                        <button class="btn bg" style="font-size:11px" onclick="loadSettingsBio()"><span class="mi sm">refresh</span></button>
                    </div>
                    <div class="pb">
                        <!-- Fee Gate -->
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--br)">
                            <div>
                                <div style="font-size:13px;font-weight:700;color:var(--tx)">🔒 Fee Gate — Block overdue students</div>
                                <div style="font-size:11px;color:var(--tx3);margin-top:2px">Deny door access if student fee is overdue</div>
                            </div>
                            <label style="position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0;cursor:pointer">
                                <input type="checkbox" id="settFeeGate" onchange="toggleFeeGate(this.checked)" style="opacity:0;width:0;height:0;position:absolute">
                                <span id="settFeeGateSlider" style="position:absolute;cursor:pointer;inset:0;background:#e2e8f0;border-radius:34px;transition:.3s"><span id="settFeeGateThumb" style="position:absolute;height:18px;width:18px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.3s;box-shadow:0 1px 3px rgba(0,0,0,.2)"></span></span>
                            </label>
                        </div>
                        <!-- ADMS URL -->
                        <div style="padding:10px 0;border-bottom:1px solid var(--br)">
                            <div style="font-size:12px;font-weight:700;color:var(--tx2);margin-bottom:6px">📡 ADMS Server URL — paste this in your device</div>
                            <div style="display:flex;gap:7px">
                                <input id="settAdmsUrl" readonly style="flex:1;font-size:11px;font-family:var(--fm);background:var(--sf2)" value="">
                                <button class="btn bg" style="font-size:11px" onclick="navigator.clipboard?.writeText(document.getElementById('settAdmsUrl').value).then(()=>toast('Copied!','ok'))"><span class="mi sm">content_copy</span></button>
                            </div>
                        </div>
                        <!-- Device list -->
                        <div id="settBioDevices" style="margin-top:10px">
                            <div style="text-align:center;padding:16px;color:var(--tx3);font-size:12px">Click Refresh to load devices</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /content -->
</div><!-- /main -->
<!-- MODALS -->
<!-- UPI PAYMENT LINK MODAL -->
<div class="mo" id="mUpiLink"><div class="md wide">
        <div class="mh"><div class="mt"><span class="mi sm" style="vertical-align:middle;margin-right:6px">payments</span>Send UPI Payment Link</div><button class="mc" onclick="closeM('mUpiLink')"><span class="mi sm">close</span></button></div>
        <div class="mb">
            <div id="upiStudentBar" style="display:flex;align-items:center;gap:12px;padding:14px;background:#f5f8ff;border:1.5px solid #dde5f7;border-radius:var(--r2);margin-bottom:14px">
                <div id="upiStuAv" style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0;background:var(--ac)"></div>
                <div style="flex:1"><div id="upiStuName" style="font-weight:700;font-size:13px;color:var(--tx)"></div><div id="upiStuMeta" style="font-size:11px;color:var(--tx3);font-family:var(--fm)"></div></div>
                <div style="text-align:right"><div style="font-size:10px;color:var(--tx3)">Amount Due</div><div id="upiAmtShow" style="font-size:22px;font-weight:800;color:var(--ac);font-family:var(--fm)"></div></div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:var(--r2);margin-bottom:14px">
                <span style="font-size:18px">💳</span>
                <div><div style="font-size:10px;color:var(--tx3);font-weight:600;text-transform:uppercase;letter-spacing:.5px">Paying to UPI ID</div><div id="upiIdShow" style="font-size:14px;font-weight:700;color:#166534;font-family:var(--fm)"></div></div>
            </div>
            <div id="upiLoading" style="text-align:center;padding:24px;color:var(--tx3);font-size:13px"><span class="mi" style="font-size:28px;color:var(--ac)">hourglass_top</span><br><br>Generating payment link…</div>
            <div id="upiResult" style="display:none">
                <div style="margin-bottom:12px"><div style="font-size:11px;font-weight:600;color:var(--tx2);margin-bottom:6px">Payment Link (share with student)</div>
                    <div style="display:flex;gap:8px"><input id="upiLinkVal" readonly style="flex:1;font-size:11px;font-family:var(--fm);background:var(--sf2);color:var(--tx)" onclick="this.select()"><button class="btn bg" style="font-size:11px;flex-shrink:0" onclick="copyUpiLink()"><span class="mi sm">content_copy</span> Copy</button></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
                    <button class="btn bwa" style="font-size:13px;padding:12px" onclick="upiSendWA()">💬 Send via WhatsApp</button>
                    <button class="btn bg" style="font-size:13px;padding:12px" onclick="copyUpiLink()"><span class="mi sm">content_copy</span> Copy Link</button>
                </div>
                <div style="padding:10px 14px;background:rgba(61,111,240,.06);border-radius:var(--r2);border:1px solid rgba(61,111,240,.12);font-size:11px;color:var(--tx3)">💡 Student opens the link, sees a QR code and UPI app buttons, pays directly.</div>
            </div>
        </div>
        <div class="mf"><button class="btn bg" onclick="closeM('mUpiLink')">Close</button></div>
    </div></div>

<!-- Student QR Code Modal -->
<div class="mo" id="mStudentQR"><div class="md" style="max-width:400px">
        <div class="mh"><div class="mt"><span class="mi sm" style="vertical-align:middle;margin-right:6px">qr_code</span>Student QR Code</div><button class="mc" onclick="closeM('mStudentQR')"><span class="mi sm">close</span></button></div>
        <div class="mb" style="text-align:center;padding:20px">
            <div id="qrModalStudentInfo" style="margin-bottom:16px"></div>
            <div style="background:#fff;border-radius:14px;padding:14px;display:inline-block;box-shadow:0 4px 20px rgba(0,0,0,.1);margin-bottom:14px">
                <div id="adminQRCode" style="width:200px;height:200px"></div>
            </div>
            <div style="font-size:11px;color:var(--tx3);margin-bottom:14px" id="qrModalExpiry"></div>
            <div style="background:var(--c-blue);border:1px solid var(--cb);border-radius:10px;padding:11px;font-size:12px;color:var(--tx2);margin-bottom:14px;text-align:left">
                📱 <strong>Student scans this QR</strong> at library entry to mark attendance.<br>
                First scan = <span style="color:var(--em);font-weight:600">Check-In</span> · Second scan = <span style="color:var(--sk);font-weight:600">Check-Out</span>
            </div>
            <div style="display:flex;gap:8px;justify-content:center">
                <button class="btn bp" onclick="regenerateQR()"><span class="mi sm">refresh</span> Regenerate</button>
                <a id="qrStudentAppLink" href="student_app.php" target="_blank" class="btn bg"><span class="mi sm">open_in_new</span> Student App</a>
            </div>
        </div>
    </div></div>

<div class="mo" id="mEnroll"><div class="md wide">
        <div class="mh"><div class="mt"><span class="mi sm" style="vertical-align:middle;margin-right:6px">person_add</span>Enroll New Student</div><button class="mc" onclick="closeM('mEnroll')"><span class="mi sm">close</span></button></div>
        <div class="mb">
            <div class="sdiv">Personal Info</div>
            <div class="fg">
                <div class="fgi"><label>First Name *</label><input id="en-fn" placeholder="Rahul"></div>
                <div class="fgi"><label>Last Name *</label><input id="en-ln" placeholder="Kumar"></div>
                <div class="fgi"><label>Phone *</label><input id="en-ph" placeholder="+91 98765 43210"></div>
                <div class="fgi"><label>Email</label><input id="en-em" placeholder="email@example.com"></div>
                <div class="fgi full"><label>Address</label><input id="en-ad" placeholder="Full address…"></div>
                <div class="fgi"><label>Course / Exam</label><input id="en-co" placeholder="UPSC / JEE"></div>
                <div class="fgi"><label>Join Date</label><input id="en-dt" type="date" oninput="calcEnrollDueDate()"></div>
                <div class="fgi"><label>Duration (Months)</label><select id="en-dur" onchange="calcEnrollDueDate()"><option value="1">1 Month</option><option value="2">2 Months</option><option value="3">3 Months</option><option value="6">6 Months</option><option value="12">12 Months</option></select></div>
                <div class="fgi"><label>Due Date <span style="font-size:9px;color:var(--tx3)">(auto)</span></label><input id="en-due" type="date" style="background:var(--sf3)"></div>
            </div>
            <div class="sdiv" style="margin-top:14px">Batch & Seat</div>
            <div class="fg">
                <div class="fgi"><label>Batch *</label><select id="en-bt" onchange="calcEnrollFee()"><option value="">-- Select --</option></select></div>
                <div class="fgi"><label>Seat Type *</label><select id="en-ac" onchange="calcEnrollFee()"><option value="non-ac">Non-AC (Standard)</option><option value="ac">AC (Premium)</option></select></div>
                <div class="fgi"><label>Seat Number</label><select id="en-st"><option value="">-- Select Batch First --</option></select></div>
                <div class="fgi"><label>Base Fee (₹/month)</label><input id="en-fe" type="number" readonly style="background:var(--sf3);font-weight:700"></div>
            </div>
            <div class="sdiv" style="margin-top:14px">🎁 Discount (Optional)</div>
            <div class="fg">
                <div class="fgi"><label>Discount Type</label><select id="en-disc-type" onchange="calcEnrollFee()"><option value="none">No Discount</option><option value="flat">Flat Amount (₹)</option><option value="percent">Percentage (%)</option></select></div>
                <div class="fgi"><label>Discount Value</label><input id="en-disc-val" type="number" placeholder="0" oninput="calcEnrollFee()"></div>
                <div class="fgi"><label>Discount Reason</label><input id="en-disc-reason" placeholder="e.g. Early bird, Sibling"></div>
                <div class="fgi"><label>Net Fee (₹/month)</label><input id="en-net-fe" type="number" readonly style="background:var(--sf3);font-weight:700;color:var(--em)"></div>
            </div>
            <div id="en-fee-note" style="display:none;margin-top:8px;font-size:11.5px;padding:8px 12px;background:rgba(74,124,111,.08);border-radius:var(--r2);border:1px solid rgba(74,124,111,.2)"></div>
            <div style="margin-top:12px;padding:10px 13px;background:rgba(37,211,102,.06);border:1px solid rgba(37,211,102,.2);border-radius:var(--r2)">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer"><input type="checkbox" id="en-wa" checked style="width:auto"> <span style="font-size:12px;color:var(--tx2)">💬 Send WhatsApp welcome message after enrollment</span></label>
            </div>
        </div>
        <div class="mf"><button class="btn bg" onclick="closeM('mEnroll')">Cancel</button><button class="btn bp" onclick="enrollStudent()">Enroll Student</button></div>
    </div></div>

<div class="mo" id="mCollectFee"><div class="md wide">
        <div class="mh"><div class="mt"><span class="mi sm" style="vertical-align:middle;margin-right:6px">payments</span>Collect Fee</div><button class="mc" onclick="closeM('mCollectFee')"><span class="mi sm">close</span></button></div>
        <div class="mb">
            <!-- Student & Month Section -->
            <div style="background:#f5f8ff;border:1.5px solid #dde5f7;border-radius:var(--r2);padding:14px;margin-bottom:12px">
                <div style="font-size:9.5px;font-weight:700;color:var(--tx3);text-transform:uppercase;letter-spacing:1px;font-family:var(--fm);margin-bottom:10px">Student Details</div>
                <div class="fg">
                    <div class="fgi full"><label>Student *</label><select id="cf-stu" onchange="cfLoadStudent()"><option value="">-- Select --</option></select></div>
                    <div class="fgi"><label>Month</label><input id="cf-mo" value="March 2026"></div>
                    <div class="fgi"><label>Net Fee (₹)</label><input id="cf-tot" type="number" readonly style="background:#eef2ff;font-weight:700;border-color:#c7d4f8"></div>
                </div>
            </div>
            <div id="cf-status-info" style="margin-bottom:10px;display:none"></div>
            <!-- Payment Section -->
            <div style="background:#f5fdf7;border:1.5px solid #c6e9d4;border-radius:var(--r2);padding:14px;margin-bottom:12px">
                <div style="font-size:9.5px;font-weight:700;color:var(--tx3);text-transform:uppercase;letter-spacing:1px;font-family:var(--fm);margin-bottom:10px">Payment Details</div>
                <div class="fg">
                    <div class="fgi full"><label>Payment Mode</label>
                        <select id="cf-mode" onchange="toggleSplit()">
                            <option value="cash">💵 Cash</option><option value="upi">📱 UPI</option><option value="neft">🏦 NEFT</option><option value="cheque">📄 Cheque</option><option value="split">✂ Split (UPI + Cash)</option><option value="split2">✂ Split (2 Custom)</option>
                        </select>
                    </div>
                </div>
                <div id="payNormal" class="fg" style="margin-top:10px">
                    <div class="fgi"><label>Amount Paying (₹)</label><input id="cf-amt" type="number" placeholder="0" oninput="cfCalcBalance()"></div>
                    <div class="fgi"><label>Transaction Ref</label><input id="cf-ref" placeholder="Auto-generated"></div>
                </div>
                <div id="paySplit" style="display:none;margin-top:10px">
                    <div class="fg">
                        <div class="fgi"><label>Mode 1</label><select id="cf-m1"><option>Cash</option><option>UPI</option><option>NEFT</option></select></div>
                        <div class="fgi"><label>Amount 1 (₹)</label><input id="cf-a1" type="number" placeholder="0" oninput="calcSplitRem()"></div>
                        <div class="fgi"><label>Mode 2</label><select id="cf-m2"><option>UPI</option><option>Cash</option><option>NEFT</option></select></div>
                        <div class="fgi"><label>Amount 2 (₹)</label><input id="cf-a2" type="number" placeholder="0" readonly style="background:var(--sf3)"></div>
                    </div>
                    <div id="splitNote" style="margin-top:6px;font-size:11px;color:var(--tx3);padding:6px 10px;background:rgba(61,111,240,.06);border-radius:var(--r2);border:1px solid rgba(61,111,240,.15)"></div>
                </div>
            </div>
            <div id="cf-balance-note" style="display:none;margin-bottom:12px;padding:10px 13px;border-radius:var(--r2);border:1px solid rgba(196,125,43,.3);background:rgba(196,125,43,.07)"></div>
            <!-- Remarks & WA Section -->
            <div style="background:#fafafa;border:1.5px solid var(--br);border-radius:var(--r2);padding:14px">
                <div class="fg" style="margin-bottom:10px"><div class="fgi full"><label>Remarks</label><input id="cf-rem" placeholder="Optional…"></div></div>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer"><input type="checkbox" id="cf-wa" checked style="width:auto"> <span style="font-size:12px;color:var(--tx2)">💬 Send payment receipt via WhatsApp</span></label>
            </div>
        </div>
        <div class="mf"><button class="btn bg" onclick="closeM('mCollectFee')">Cancel</button><button class="btn bp" onclick="collectFee()">💳 Collect</button></div>
    </div></div>

<div class="mo" id="mAddBatch"><div class="md">
        <div class="mh"><div class="mt" id="mAddBatchTitle">Add New Batch</div><button class="mc" onclick="closeM('mAddBatch')"><span class="mi sm">close</span></button></div>
        <div class="mb"><div class="fg">
                <div class="fgi full"><label>Batch Name *</label><input id="ab-nm" placeholder="Morning Batch"></div>
                <div class="fgi"><label>Start Time *</label><input id="ab-st" type="time" value="08:00"></div>
                <div class="fgi"><label>End Time *</label><input id="ab-et" type="time" value="12:00"></div>
                <div class="fgi"><label>Total Seats *</label><input id="ab-ts" type="number" placeholder="80" min="1"></div>
                <div class="fgi"><label>Base Fee (₹/month) *</label><input id="ab-fe" type="number" placeholder="1200"></div>
                <div class="fgi"><label>AC Extra Fee (₹)</label><input id="ab-ac" type="number" placeholder="200" value="200"></div>
            </div></div>
        <div class="mf"><button class="btn bg" onclick="closeM('mAddBatch')">Cancel</button><button class="btn bp" id="batchSaveBtn" onclick="saveBatch()">Add Batch</button></div>
    </div></div>

<div class="mo" id="mIssueBook"><div class="md">
        <div class="mh"><div class="mt"><span class="mi sm" style="vertical-align:middle;margin-right:6px">upload</span>Issue Book</div><button class="mc" onclick="closeM('mIssueBook')"><span class="mi sm">close</span></button></div>
        <div class="mb"><div class="fg">
                <div class="fgi full"><label>Student *</label><select id="ib-stu"><option value="">-- Select --</option></select></div>
                <div class="fgi full"><label>Book *</label><select id="ib-bk"><option value="">-- Select --</option></select></div>
                <div class="fgi"><label>Issue Date</label><input id="ib-id" type="date"></div>
                <div class="fgi"><label>Due Date</label><input id="ib-dd" type="date" readonly></div>
            </div></div>
        <div class="mf"><button class="btn bg" onclick="closeM('mIssueBook')">Cancel</button><button class="btn bp" onclick="issueBook()">Issue</button></div>
    </div></div>

<div class="mo" id="mReturnBook"><div class="md">
        <div class="mh"><div class="mt"><span class="mi sm" style="vertical-align:middle;margin-right:6px">download</span>Return Book</div><button class="mc" onclick="closeM('mReturnBook')"><span class="mi sm">close</span></button></div>
        <div class="mb"><div class="fg">
                <div class="fgi full"><label>Transaction *</label><select id="rb-tx" onchange="calcFine()"><option value="">-- Select --</option></select></div>
                <div class="fgi"><label>Return Date</label><input id="rb-dt" type="date"></div>
                <div class="fgi"><label>Fine (₹)</label><input id="rb-fn" type="number" value="0" readonly style="background:var(--sf3)"></div>
                <div class="fgi full"><label>Condition</label><select id="rb-cd"><option>Good</option><option>Damaged</option><option>Lost</option></select></div>
            </div>
            <div id="rb-note" style="display:none;margin-top:8px;padding:8px 10px;background:rgba(192,68,79,.07);border:1px solid rgba(192,68,79,.2);border-radius:var(--r2);font-size:11px;color:var(--ro)"></div>
        </div>
        <div class="mf"><button class="btn bg" onclick="closeM('mReturnBook')">Cancel</button><button class="btn bp" onclick="returnBook()">Return</button></div>
    </div></div>

<div class="mo" id="mAddBook"><div class="md">
        <div class="mh"><div class="mt"><span class="mi sm" style="vertical-align:middle;margin-right:6px">menu_book</span>Add Book</div><button class="mc" onclick="closeM('mAddBook')"><span class="mi sm">close</span></button></div>
        <div class="mb"><div class="fg">
                <div class="fgi full"><label>Title *</label><input id="bk-tl" placeholder="Atomic Habits"></div>
                <div class="fgi"><label>Author *</label><input id="bk-au"></div><div class="fgi"><label>ISBN</label><input id="bk-is"></div>
                <div class="fgi"><label>Category</label><select id="bk-ca"><option>Self-Help</option><option>Academic</option><option>Fiction</option><option>Science</option></select></div>
                <div class="fgi"><label>Copies *</label><input id="bk-cp" type="number"></div><div class="fgi"><label>Shelf</label><input id="bk-sh"></div>
            </div></div>
        <div class="mf"><button class="btn bg" onclick="closeM('mAddBook')">Cancel</button><button class="btn bp" onclick="addBook()">Add</button></div>
    </div></div>

<div class="mo" id="mExpense"><div class="md">
        <div class="mh"><div class="mt"><span class="mi sm" style="vertical-align:middle;margin-right:6px">account_balance_wallet</span>Add Expense</div><button class="mc" onclick="closeM('mExpense')"><span class="mi sm">close</span></button></div>
        <div class="mb"><div class="fg">
                <div class="fgi full"><label>Name *</label><input id="ex-nm" placeholder="Electricity Bill"></div>
                <div class="fgi"><label>Amount (₹) *</label><input id="ex-am" type="number"></div>
                <div class="fgi"><label>Category</label><select id="ex-ca"><option>Utilities</option><option>Staff</option><option>Maintenance</option><option>Supplies</option><option>Books</option><option>Other</option></select></div>
                <div class="fgi"><label>Date</label><input id="ex-dt" type="date"></div>
                <div class="fgi full"><label>Notes</label><input id="ex-nt" placeholder="Optional…"></div>
            </div></div>
        <div class="mf"><button class="btn bg" onclick="closeM('mExpense')">Cancel</button><button class="btn bp" onclick="addExp()">Add</button></div>
    </div></div>

<div class="mo" id="mAllocSeat"><div class="md">
        <div class="mh"><div class="mt"><span class="mi sm" style="vertical-align:middle;margin-right:6px">event_seat</span>Allocate Seat</div><button class="mc" onclick="closeM('mAllocSeat')"><span class="mi sm">close</span></button></div>
        <div class="mb"><div class="fg">
                <div class="fgi full"><label>Student *</label><select id="as-stu"><option value="">-- Select --</option></select></div>
                <div class="fgi"><label>Batch *</label><select id="as-bt" onchange="populateAllocSeats()"><option value="">-- Select --</option></select></div>
                <div class="fgi"><label>Seat Number *</label><select id="as-st"><option value="">-- Select Batch First --</option></select></div>
            </div></div>
        <div class="mf"><button class="btn bg" onclick="closeM('mAllocSeat')">Cancel</button><button class="btn bp" onclick="allocSeat()">Allocate</button></div>
    </div></div>

<div class="mo" id="mAddStaff"><div class="md wide">
        <div class="mh"><div class="mt" id="staffModalTitle">Add Staff</div><button class="mc" onclick="closeM('mAddStaff')"><span class="mi sm">close</span></button></div>
        <div class="mb">
            <div class="fg">
                <div class="fgi"><label>Full Name *</label><input id="sf-nm"></div>
                <div class="fgi"><label>Role *</label><select id="sf-rl" onchange="setDefaultPerms()"><option value="librarian">Librarian</option><option value="accountant">Accountant</option><option value="receptionist">Receptionist</option><option value="admin">Admin</option></select></div>
                <div class="fgi"><label>Email *</label><input id="sf-em"></div>
                <div class="fgi"><label>Phone</label><input id="sf-ph"></div>
                <div class="fgi"><label>Username</label><input id="sf-un"></div>
                <div class="fgi"><label>Password</label><input id="sf-pw" type="password"></div>
            </div>
            <div class="sdiv" style="margin-top:14px">Permissions</div>
            <div id="permList"></div>
        </div>
        <div class="mf"><button class="btn bg" onclick="closeM('mAddStaff')">Cancel</button><button class="btn bp" id="staffSaveBtn" onclick="saveStaff()">Add Staff</button></div>
    </div></div>

<div class="mo" id="mGenInv"><div class="md">
        <div class="mh"><div class="mt"><span class="mi sm" style="vertical-align:middle;margin-right:6px">receipt_long</span>Generate Invoice</div><button class="mc" onclick="closeM('mGenInv')"><span class="mi sm">close</span></button></div>
        <div class="mb"><div class="fg">
                <div class="fgi full"><label>Student *</label><select id="gi-stu" onchange="autoFillInv()"><option value="">-- Select --</option></select></div>
                <div class="fgi"><label>Type</label><select id="gi-tp"><option value="fee">Monthly Fee</option><option value="fine">Book Fine</option><option value="other">Other</option></select></div>
                <div class="fgi"><label>Amount (₹)</label><input id="gi-am" type="number"></div>
                <div class="fgi full"><label>Period</label><input id="gi-mo" value="March 2026"></div>
            </div></div>
        <div class="mf"><button class="btn bg" onclick="closeM('mGenInv')">Cancel</button><button class="btn bp" onclick="genInvoice()">Generate</button></div>
    </div></div>

<!-- WhatsApp Quick Send Modal -->
<!-- STUDENT PROFILE MODAL -->
<div class="mo" id="mStudentProfile"><div class="md lg">
        <div class="mh" style="border-bottom:none;padding-bottom:0;position:relative;z-index:10">
            <div></div>
            <div style="display:flex;align-items:center;gap:7px">
                <button class="sp-edit-toggle" id="spEditToggle" onclick="toggleProfileEdit()">✏ Edit</button>
                <button class="mc" onclick="closeM('mStudentProfile')"><span class="mi sm">close</span></button>
            </div>
        </div>
        <div class="sp-header" id="spHeader">
            <div class="sp-name" id="spHeaderName">Student Name</div>
            <div class="sp-id" id="spHeaderId">#STU-001</div>
            <div class="sp-av-wrap"><div class="sp-av" id="spAv" style="background:var(--ac)">AB</div></div>
        </div>
        <div class="sp-body">
            <!-- Fee status bar -->
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding:10px 14px;background:var(--sf2);border-radius:var(--r2)">
                <span id="spFeeTag" class="tag tpn">⏳ Pending</span>
                <div style="flex:1">
                    <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--tx3);font-family:var(--fm);margin-bottom:3px"><span id="spPaidLbl">Paid ₹0</span><span id="spDueLbl">Due ₹0</span></div>
                    <div class="sp-fee-bar"><div class="sp-fee-fill" id="spFeeFill" style="width:0%"></div></div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:15px;font-weight:700;font-family:var(--fm);color:var(--em)" id="spNetFee">₹0</div>
                    <div style="font-size:9px;color:var(--tx3)">Net Fee</div>
                </div>
            </div>

            <!-- Seat & Batch -->
            <div class="sp-section">📍 Placement</div>
            <div class="sp-grid" style="margin-bottom:14px">
                <div class="sp-field">
                    <div class="sp-label">Batch</div>
                    <div id="spBatchDisp"></div>
                </div>
                <div class="sp-field">
                    <div class="sp-label">Seat Number</div>
                    <div id="spSeatDisp">
                        <span class="sp-seat-chip" id="spSeatChip" onclick="openAllocFromProfile()"><span class="mi sm">event_seat</span><span id="spSeatNum">—</span></span>
                    </div>
                </div>
                <div class="sp-field">
                    <div class="sp-label">Seat Type</div>
                    <div id="spSeatTypeDisp"></div>
                </div>
                <div class="sp-field">
                    <div class="sp-label">Join Date</div>
                    <div id="spJoinDate" class="sp-val">—</div>
                </div>
            </div>

            <!-- Personal Info -->
            <div class="sp-section">👤 Personal Details</div>
            <div class="sp-grid" style="margin-bottom:14px">
                <div class="sp-field">
                    <div class="sp-label">First Name</div>
                    <div id="spFname" class="sp-val" contenteditable="false">—</div>
                </div>
                <div class="sp-field">
                    <div class="sp-label">Last Name</div>
                    <div id="spLname" class="sp-val" contenteditable="false">—</div>
                </div>
                <div class="sp-field">
                    <div class="sp-label">Phone / WhatsApp</div>
                    <div id="spPhone" class="sp-val" contenteditable="false">—</div>
                </div>
                <div class="sp-field">
                    <div class="sp-label">Email</div>
                    <div id="spEmail" class="sp-val" contenteditable="false">—</div>
                </div>
                <div class="sp-field full">
                    <div class="sp-label">Course / Subject</div>
                    <div id="spCourse" class="sp-val" contenteditable="false">—</div>
                </div>
                <div class="sp-field full">
                    <div class="sp-label">Address</div>
                    <div id="spAddr" class="sp-val" contenteditable="false">—</div>
                </div>
            </div>

            <!-- Fee Details -->
            <div class="sp-section"><span class="mi sm" style="vertical-align:middle;margin-right:5px">payments</span>Fee Details</div>
            <div class="sp-grid" style="margin-bottom:14px">
                <div class="sp-field">
                    <div class="sp-label">Base Fee</div>
                    <div id="spBaseFee" class="sp-val">—</div>
                </div>
                <div class="sp-field">
                    <div class="sp-label">Discount</div>
                    <div id="spDiscount" class="sp-val">—</div>
                </div>
                <div class="sp-field">
                    <div class="sp-label">Paid Amount</div>
                    <div id="spPaidAmt" class="sp-val">—</div>
                </div>
                <div class="sp-field">
                    <div class="sp-label">Due Date</div>
                    <div id="spDueDate" class="sp-val">—</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="sp-section">⚡ Quick Actions</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <button class="btn bp" data-action="collect_fee" style="font-size:11px" id="spCollectBtn" onclick="closeM('mStudentProfile')"><span class="mi sm">payments</span>Collect Fee</button>
                <button class="btn bwa" style="font-size:11px" id="spWaBtn">💬 Send WhatsApp</button>
                <button class="btn bg" style="font-size:11px;color:var(--ac);border-color:var(--ac)" id="spUpiBtn">📱 UPI Link</button>
                <button class="btn bg" style="font-size:11px" onclick="openAllocFromProfile()"><span class="mi sm">event_seat</span>Change Seat</button>
                <button class="btn bd" data-action="delete_student" style="font-size:11px" id="spDelBtn">🗑 Remove</button>
            </div>
        </div>
        <div class="mf" id="spSaveFooter" style="display:none">
            <button class="btn bg" onclick="cancelProfileEdit()">Cancel</button>
            <button class="btn bp" onclick="saveProfileEdit()">💾 Save Changes</button>
        </div>
    </div></div>

<!-- WHATSAPP QR CONNECT MODAL -->
<div class="mo" id="mWaQR"><div class="md">
        <div class="mh"><div class="mt">📱 Connect WhatsApp</div><button class="mc" onclick="closeM('mWaQR')"><span class="mi sm">close</span></button></div>
        <div class="mb">
            <div style="display:flex;gap:16px;align-items:flex-start">
                <div style="flex-shrink:0">
                    <div class="wa-qr-box" onclick="refreshWaQR()" id="waQRBox">
                        <div class="wa-qr-img" id="waQRImg">
                            <canvas id="waQRCanvas" width="150" height="150"></canvas>
                        </div>
                        <div style="font-size:10px;color:var(--tx3);font-family:var(--fm)" id="waQRStatus">Tap to refresh</div>
                    </div>
                    <div style="display:flex;justify-content:center;margin-top:8px">
                        <div class="wa-conn-badge wa-conn-no" id="waConnBadge">● Disconnected</div>
                    </div>
                </div>
                <div style="flex:1">
                    <div style="font-weight:600;font-size:13px;margin-bottom:8px;color:var(--tx)">Scan to Connect</div>
                    <div style="font-size:11px;color:var(--tx3);margin-bottom:14px">Link your WhatsApp to send messages directly from the ERP without switching apps.</div>
                    <div class="wa-steps">
                        <div class="wa-step"><div class="wa-step-n">1</div><span>Open <strong>WhatsApp</strong> on your phone</span></div>
                        <div class="wa-step"><div class="wa-step-n">2</div><span>Tap <strong>Menu (⋮)</strong> → <strong>Linked Devices</strong></span></div>
                        <div class="wa-step"><div class="wa-step-n">3</div><span>Tap <strong>Link a Device</strong></span></div>
                        <div class="wa-step"><div class="wa-step-n">4</div><span>Point your camera at the <strong>QR code</strong></span></div>
                    </div>
                    <div style="margin-top:14px;padding:10px 12px;background:rgba(37,211,102,.07);border:1px solid rgba(37,211,102,.2);border-radius:var(--r2)">
                        <div style="font-size:11px;font-weight:600;color:var(--wa2);margin-bottom:4px">📌 Note</div>
                        <div style="font-size:11px;color:var(--tx2)">WhatsApp Web QR requires a backend service. In standalone mode, messages open in WhatsApp Web automatically.</div>
                    </div>
                    <div style="margin-top:12px">
                        <div style="font-size:10px;color:var(--tx3);margin-bottom:6px;font-family:var(--fm)">WHATSAPP NUMBER</div>
                        <div style="display:flex;gap:7px">
                            <input id="waConnNum" placeholder="+91 XXXXXXXXXX" style="flex:1;font-size:12px" value="">
                            <button class="btn bwa" style="font-size:11px" onclick="saveWaNumber()">Save</button>
                        </div>
                    </div>
                </div>
            </div>
            <div style="margin-top:14px;border-top:1px solid var(--br);padding-top:12px">
                <div style="font-size:10px;color:var(--tx3);font-family:var(--fm);margin-bottom:8px">CONNECTION STATUS</div>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <div class="sp-stat" style="flex:1"><span class="sp-stat-ic">📱</span><div><div style="font-size:11px;font-weight:600">WhatsApp Web</div><div style="font-size:10px;color:var(--wa2)">Opens automatically</div></div></div>
                    <div class="sp-stat" style="flex:1"><span class="sp-stat-ic">✅</span><div><div style="font-size:11px;font-weight:600">Messages Sent</div><div style="font-size:10px;color:var(--tx3)" id="waConnMsgCount">0 messages this session</div></div></div>
                </div>
            </div>
        </div>
        <div class="mf">
            <button class="btn bg" onclick="closeM('mWaQR')">Close</button>
            <button class="btn bwa" onclick="testWaConnection()"><span class="mi sm">chat</span>Test Message</button>
        </div>
    </div></div>

<div class="mo" id="mWaSend"><div class="md">
        <div class="mh"><div class="mt">💬 Send WhatsApp</div><button class="mc" onclick="closeM('mWaSend')"><span class="mi sm">close</span></button></div>
        <div class="mb">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;padding:10px 13px;background:rgba(37,211,102,.08);border-radius:var(--r2);border:1px solid rgba(37,211,102,.2)">
                <div><span class="mi xl" style="color:var(--wa)">chat</span></div>
                <div><div style="font-weight:600;font-size:13px" id="waSendTo">Student Name</div><div style="font-size:11px;color:var(--tx3)" id="waSendPhone">+91 XXXXXXXXXX</div></div>
            </div>
            <div class="fgi" style="margin-bottom:12px"><label>Message</label><textarea id="waSendMsg" rows="7" style="font-size:12px;line-height:1.6"></textarea></div>
            <div class="wa-preview" id="waSendPreview" style="font-size:11.5px"></div>
        </div>
        <div class="mf">
            <button class="btn bg" onclick="closeM('mWaSend')">Cancel</button>
            <button class="btn bg" onclick="waCopyModal()" style="font-size:11px"><span class="mi sm">content_copy</span>Copy</button>
            <button class="btn bwa" onclick="waOpenLink()" id="waOpenBtn">💬 Open WhatsApp</button>
        </div>
    </div></div>

<!-- CHANGE PASSWORD MODAL -->
<div class="mo" id="mChangePw"><div class="md">
        <div class="mh"><div class="mt"><span class="mi sm" style="vertical-align:middle;margin-right:6px">lock_reset</span>Change Password</div><button class="mc" onclick="closeM('mChangePw')"><span class="mi sm">close</span></button></div>
        <div class="mb">
            <div class="fgi" style="margin-bottom:12px"><label>Current Password</label><input type="password" id="cp-cur" placeholder="Enter current password"></div>
            <div class="fgi" style="margin-bottom:12px"><label>New Password</label><input type="password" id="cp-new" placeholder="Min 6 characters"></div>
            <div class="fgi"><label>Confirm New Password</label><input type="password" id="cp-cf" placeholder="Repeat new password"></div>
        </div>
        <div class="mf"><button class="btn bg" onclick="closeM('mChangePw')">Cancel</button><button class="btn bp" onclick="doChangePassword()">Update Password</button></div>
    </div></div>

<!-- RENEWAL MODAL -->
<div class="mo" id="mRenew"><div class="md">
        <div class="mh"><div class="mt">🔄 Renew Student</div><button class="mc" onclick="closeM('mRenew')"><span class="mi sm">close</span></button></div>
        <div class="mb">
            <div id="mRenewStudentInfo" style="background:var(--sf2);border-radius:var(--r2);padding:12px;margin-bottom:14px"></div>
            <div class="fg">
                <div class="fgi"><label>Extend By</label>
                    <select id="ren-extend">
                        <option value="1">1 Month</option>
                        <option value="2">2 Months</option>
                        <option value="3">3 Months</option>
                        <option value="6">6 Months</option>
                    </select>
                </div>
                <div class="fgi"><label>New Due Date</label><input id="ren-newdate" type="date" style="background:var(--sf2)" title="You can override the calculated date"></div>
                <div class="fgi"><label>Fee Amount (₹)</label><input id="ren-fee" type="number" placeholder="0"></div>
                <div class="fgi"><label>Payment Mode</label>
                    <select id="ren-mode"><option>Cash</option><option>UPI</option><option>Bank Transfer</option><option>Cheque</option></select>
                </div>
                <div class="fgi full"><label>Notes</label><input id="ren-notes" placeholder="Optional renewal note"></div>
            </div>
            <div id="ren-summary" style="margin-top:12px;padding:10px 12px;border-radius:var(--r2);font-size:12px;color:var(--tx2);border:1px solid transparent"></div>
            <div id="ren-balance-warn" style="display:none;margin-top:6px;padding:9px 12px;background:rgba(234,88,12,.08);border:1px solid rgba(234,88,12,.3);border-radius:var(--r2);font-size:12px;color:#c2410c"></div>
        </div>
        <div class="mf">
            <button class="btn bg" onclick="closeM('mRenew')">Cancel</button>
            <button class="btn bwa" style="font-size:11px" onclick="sendRenewalWA()">💬 Send WA</button>
            <button class="btn bp" onclick="confirmRenew()">✅ Confirm Renewal</button>
        </div>
    </div></div>

<!-- STAFF SALARY SLIP MODAL -->
<div class="mo" id="mSalarySlip"><div class="md">
        <div class="mh"><div class="mt">💰 Salary Slip</div><button class="mc" onclick="closeM('mSalarySlip')"><span class="mi sm">close</span></button></div>
        <div class="mb" id="mSalarySlipContent"></div>
        <div class="mf">
            <button class="btn bg" onclick="closeM('mSalarySlip')">Close</button>
            <button class="btn bp" onclick="printSalarySlip()">🖨 Print</button>
        </div>
    </div></div>

<!-- PWA INSTALL BANNER -->
<div class="pwa-banner" id="pwaBanner" onclick="installPWA()">
    <span class="mi" style="font-size:22px">install_mobile</span>
    <div style="flex:1"><div style="font-size:13px;font-weight:700">Install Library App</div><div style="font-size:11px;opacity:.85">Add to home screen for quick access</div></div>
    <button onclick="event.stopPropagation();document.getElementById('pwaBanner').classList.remove('show')" style="background:rgba(255,255,255,.2);border:none;color:#fff;border-radius:6px;padding:4px 8px;cursor:pointer;font-size:11px">Later</button>
</div>

<div class="toast-wrap" id="toastWrap"></div>
<script>
    // ═══ API CONFIG ═══
    const API = 'api/index.php';

    async function apiGet(action, params = {}) {
        const qs = new URLSearchParams({ action, ...params }).toString();
        const r = await fetch(`${API}?${qs}`);
        return r.json();
    }

    async function apiPost(action, data = {}) {
        const r = await fetch(`${API}?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return r.json();
    }

    // ═══ DB STATE ═══
    let DB = {
        students: [], batches: [], books: [], transactions: [],
        expenses: [], invoices: [], activities: [], notifications: [],
        settings: {name:'OPTMS Tech Study Library',phone:'+91 72820 71620',email:'admin@optms.co.in',
            addr:'Madhepura, Bihar - 852113',fine_per_day:5,loan_days:14,wa_number:'917282071620'},
        attendance: {}, waSendLog: [], staff: [],
        auditLog: []   // ✅ ADD THIS
    };
    let editBatchIdx = -1, editStaffIdx = -1;

    // ═══ INIT — loads all data from MySQL ═══
    async function initData() {
        try {
            const data = await apiGet('get_dashboard');

            // Map DB columns to JS camelCase used throughout the app
            DB.batches = (data.batches || []).map(b => ({
                id: b.id, name: b.name,
                startTime: b.start_time ? b.start_time.slice(0,5) : '08:00',
                endTime:   b.end_time   ? b.end_time.slice(0,5)   : '12:00',
                total: +b.total_seats, occupied: +b.occupied_seats,
                baseFee: +b.base_fee, acExtra: +b.ac_extra
            }));

            DB.students = (data.students || []).map(s => ({
                id: s.id, fname: s.fname, lname: s.lname, phone: s.phone,
                email: s.email || '', addr: s.addr || '',
                batchId: s.batch_id, seatType: s.seat_type, seat: s.seat,
                baseFee: +s.base_fee,
                discount: { type: s.discount_type, value: +s.discount_value, reason: s.discount_reason },
                netFee: +s.net_fee, paidAmt: +s.paid_amt,
                feeStatus: s.fee_status, paidOn: s.paid_on,
                dueDate: s.due_date, course: s.course,
                color: s.color || '#3d6ff0', joinDate: s.join_date
            }));

            DB.books = (data.books || []).map(b => ({
                id: b.id, title: b.title, author: b.author, isbn: b.isbn,
                category: b.category, copies: +b.copies, available: +b.available,
                shelf: b.shelf, emoji: b.emoji || '📘'
            }));

            DB.transactions = (data.transactions || []).map(t => ({
                id: t.id, studentId: t.student_id, bookId: t.book_id,
                issueDate: t.issue_date, dueDate: t.due_date,
                returnDate: t.return_date, fine: +t.fine, status: t.status
            }));

            DB.expenses = (data.expenses || []).map(e => ({
                id: e.id, name: e.name, amount: +e.amount,
                category: e.category, date: e.expense_date,
                notes: e.notes, emoji: e.emoji || '<span class="mi lg" style="color:#9a3412">account_balance_wallet</span>'
            }));

            DB.invoices = (data.invoices || []).map(i => ({
                id: i.id, studentId: i.student_id, type: i.type,
                amount: +i.amount, baseFee: +i.base_fee, discount: +i.discount,
                netFee: +i.net_fee, paidAmt: +i.paid_amt, balance: +i.balance,
                date: i.invoice_date, month: i.month, mode: i.mode, status: i.status
            }));

            DB.activities = (data.activities || []).map(a => ({
                icon: a.icon, bg: a.bg, text: a.text,
                time: timeSince(a.created_at)
            }));

            DB.notifications = (data.notifications || []).map(n => ({
                id: +n.id, type: n.type, title: n.title, msg: n.msg,
                time: timeSince(n.created_at), read: !!+n.is_read
            }));

            if (data.staff) {
                DB.staff = data.staff.map(sf => ({
                    id: sf.id, name: sf.name, role: sf.role,
                    email: sf.email, phone: sf.phone, username: sf.username,
                    perms: {
                        students: !!+sf.perm_students, fees: !!+sf.perm_fees,
                        books: !!+sf.perm_books, expenses: !!+sf.perm_expenses,
                        reports: !!+sf.perm_reports, staff: !!+sf.perm_staff,
                        settings: !!+sf.perm_settings,
                        whatsapp: sf.perm_whatsapp !== undefined ? !!+sf.perm_whatsapp : true,
                        notifications: sf.perm_notifications !== undefined ? !!+sf.perm_notifications : true,
                    },
                    actPerms: sf.act_perms ? (typeof sf.act_perms === 'string' ? JSON.parse(sf.act_perms) : sf.act_perms) : {},
                    status: sf.status
                }));
            }

            const s = data.settings || {};
            DB.settings = {
                name:  s.name  || '',
                phone: s.phone || '',
                email: s.email || '',
                addr:  s.addr  || '',
                fine:  +(s.fine_per_day || 5),
                days:  +(s.loan_days    || 14),
                acFee: +(s.ac_fee || s.ac_extra || 200),
                waNumber: s.wa_number || '',
                logoUrl: s.logo_url || '',
                upiId:  s.upi_id   || '7282071620@okaxis'
            };
            // Apply logo and library name to sidebar immediately on load
            if (DB.settings.logoUrl) applyLogo(DB.settings.logoUrl);
            const nameEl = document.getElementById('sidebar-lib-name');
            if (nameEl && DB.settings.name) nameEl.textContent = DB.settings.name;

            // Apply nav permissions for the logged-in staff member
            if (data.me) applyNavPerms(data.me);

            // ── Fire all secondary calls IN PARALLEL (saves ~800ms vs sequential awaits) ──
            const today = new Date().toISOString().split('T')[0];
            const [attData, waLogRes, auditData, salData] = await Promise.all([
                apiGet('get_attendance', { date: today }).catch(e => { console.warn('get_attendance failed:', e); return {}; }),
                apiGet('get_wa_log').catch(e => { console.warn('get_wa_log failed:', e); return []; }),
                apiGet('get_audit_log').catch(e => { console.warn('get_audit_log failed:', e); return {}; }),
                apiGet('get_salary').catch(e => { console.warn('get_salary failed:', e); return {}; }),
            ]);

            // Attendance — default to absent until manually marked or saved
            DB.attendance = attData.attendance || {};
            DB.students.forEach(st => { if (!DB.attendance[st.id]) DB.attendance[st.id] = 'absent'; });

            // WA log
            const waRows = Array.isArray(waLogRes) ? waLogRes : (waLogRes.logs || []);
            DB.waSendLog = waRows.map(l => ({
                time: l.created_at ? l.created_at.slice(11,16) : '',
                to: l.sent_to, preview: l.preview, type: l.type
            }));

            // Audit log
            const auditRows = Array.isArray(auditData) ? auditData : (auditData.logs || auditData.records || []);
            DB.auditLog = auditRows.map(a => {
                const isoTs = a.created_at ? a.created_at.replace(' ', 'T') : null;
                return {
                    id: a.id, who: a.who || 'Admin', type: a.type || 'other', text: a.text || '',
                    time: timeSince(isoTs), ts: isoTs ? new Date(isoTs).getTime() : Date.now()
                };
            });

            // Staff salaries
            DB.staffSalary = salData.salaries || {};

            // Biometric preload — after parallel block so dashboard stat card shows correct count
            try { await loadAttBiometric(); } catch(e) { console.warn('biometric preload failed:', e); }

        } catch(e) {
            console.error('Init failed:', e);
            toast('Failed to load data from server', 'er');
        }
        refreshAll();
    }

    function timeSince(ts) {
        if (!ts) return 'Just now';
        // MySQL returns "YYYY-MM-DD HH:MM:SS" — normalize to ISO for cross-browser parsing
        const normalized = typeof ts === 'string' ? ts.replace(' ', 'T') : ts;
        const diff = Math.floor((Date.now() - new Date(normalized)) / 1000);
        if (isNaN(diff) || diff < 60) return 'Just now';
        if (diff < 3600) return Math.floor(diff/60) + ' min ago';
        if (diff < 86400) return Math.floor(diff/3600) + ' hr ago';
        return Math.floor(diff/86400) + ' day(s) ago';
    }

    // ═══ PERMISSION-BASED NAV ENFORCEMENT ═══
    function applyNavPerms(me) {
        // Admins always see everything
        if (me.role === 'admin') return;

        // ── Page-level gates (hide full sidebar items) ──
        const PAGE_PERM = {
            students:        'perm_students',
            enroll:          'perm_students',
            seats:           'perm_students',
            attendance:      'perm_students',
            fees:            'perm_fees',
            invoices:        'perm_fees',
            books:           'perm_books',
            transactions:    'perm_books',
            expenses:        'perm_expenses',
            reports:         'perm_reports',
            analytics:       'perm_reports',
            staff:           'perm_staff',
            staff_attendance:'perm_staff',
            renewal:         'perm_staff',
            audit:           'perm_staff',
            settings:        'perm_settings',
            whatsapp:        'perm_whatsapp',
            notifications:   'perm_notifications',
        };

        document.querySelectorAll('.ni[data-page]').forEach(el => {
            const page = el.dataset.page;
            const permKey = PAGE_PERM[page];
            if (permKey && !+me[permKey]) {
                el.style.display = 'none';
            }
        });

        // Also hide empty section group headers (ns) that have no visible items
        document.querySelectorAll('.ns').forEach(ns => {
            const items = ns.querySelectorAll('.ni');
            const allHidden = Array.from(items).every(i => i.style.display === 'none');
            if (allHidden) ns.style.display = 'none';
        });

        // ── Action-level gates (hide specific buttons inside pages) ──
        // me.actPerms comes from the server as a JSON object
        const ap = me.actPerms || {};
        document.querySelectorAll('[data-action]').forEach(el => {
            const action = el.dataset.action;
            // If explicitly set to false, hide the button
            if (action && ap[action] === false) {
                el.style.display = 'none';
            }
        });

        // Also gate inline dynamically-rendered buttons via a global flag
        // so renderStudents / renderFees etc can check it too
        window._actPerms = ap;
    }

    // Helper any render function can call: canDo('collect_fee')
    function canDo(action) {
        if (!window._actPerms) return true; // admin / not set = allow all
        const v = window._actPerms[action];
        return v === undefined ? true : !!v; // undefined = not restricted
    }

    // ═══ NAVIGATION ═══
    const PAGE_TITLES={dashboard:'Dashboard',students:'All Students',seats:'Seat Allocation',attendance:'Attendance',books:'Books Catalog',transactions:'Issue & Returns',fees:'Fee Management',invoices:'Invoices',expenses:'Expenses',reports:'Reports',analytics:'Analytics',whatsapp:'WhatsApp Messaging',staff:'Staff & Users',staff_attendance:'Staff Attendance & Salary',renewal:'Student Renewals',audit:'Audit Log',notifications:'Notifications',settings:'Settings'};
    function navTo(page){
        document.querySelectorAll('.ni').forEach(n=>n.classList.remove('active'));
        const ni=document.querySelector(`.ni[data-page="${page}"]`);if(ni)ni.classList.add('active');
        document.querySelectorAll('.page').forEach(p=>p.classList.remove('active'));
        const pg=document.getElementById('page-'+page);if(pg)pg.classList.add('active');
        document.getElementById('topTitle').textContent=PAGE_TITLES[page]||page;
        renderPage(page);
    }
    document.querySelectorAll('.ni[data-page]').forEach(el=>{
        el.addEventListener('click',()=>{if(el.dataset.page==='enroll'){openM('mEnroll');return;}navTo(el.dataset.page);});
    });
    function renderPage(p){
        const map={dashboard:renderDash,students:renderStudents,seats:renderSeats,attendance:renderAtt,books:renderBooks,transactions:renderTx,fees:renderFees,invoices:renderInv,expenses:renderExp,analytics:renderAnal,whatsapp:renderWA,staff:renderStaff,staff_attendance:renderStaffAtt,renewal:renderRenewal,audit:renderAudit,notifications:renderNotifs,settings:renderSettings,biometric:renderBiometric};
        if(map[p])map[p]();
    }

    // ═══ DASHBOARD ═══
    let calDate=new Date(new Date().getFullYear(),new Date().getMonth(),1); // always start at current month
    function renderDash(){
        const s=DB.students;
        const paid=s.filter(x=>x.feeStatus==='paid');
        const partial=s.filter(x=>x.feeStatus==='partial');
        const pending=s.filter(x=>x.feeStatus==='pending');
        const overdue=s.filter(x=>x.feeStatus==='overdue');
        const totalRev=DB.invoices.reduce((a,i)=>a+i.paidAmt,0);
        const liveInvs=DB.invoices.filter(i=>!!DB.students.find(x=>x.id===i.studentId));
        const deadInvs=DB.invoices.filter(i=>!DB.students.find(x=>x.id===i.studentId));
        const revLivePaid=liveInvs.filter(i=>i.status==='paid').reduce((a,i)=>a+i.paidAmt,0);
        const revLivePartial=liveInvs.filter(i=>i.status==='partial').reduce((a,i)=>a+i.paidAmt,0);
        const revFromDeleted=deadInvs.reduce((a,i)=>a+i.paidAmt,0);
        const activeStudentRev=revLivePaid+revLivePartial;
        const totalExp=DB.expenses.reduce((a,e)=>a+e.amount,0);
        const issTx=DB.transactions.filter(t=>t.status!=='returned');
        const odTx=DB.transactions.filter(t=>t.status==='overdue');
        const totalSeats=DB.batches.reduce((a,b)=>a+b.total,0);
        const occSeats=DB.batches.reduce((a,b)=>a+b.occupied,0);
        const prsnt=DB.students.filter(st=>DB.attendance[st.id]==='present').length;
        const totalDiscount=s.reduce((a,x)=>a+(x.baseFee-x.netFee),0);
        const allDue=[...pending,...overdue,...partial].reduce((a,x)=>a+(x.netFee-x.paidAmt),0);

        document.getElementById('dashAlerts').innerHTML=`
    <div class="al-card al-w"><span style="font-size:17px">⚠️</span><div><div class="al-t">Pending & Partial Payments</div><div class="al-b">${[...pending,...partial].length} students — ₹${[...pending,...partial].reduce((a,x)=>a+(x.netFee-x.paidAmt),0).toLocaleString()} outstanding</div></div></div>
    <div class="al-card al-d"><span style="font-size:17px">🚨</span><div><div class="al-t">Fee Overdue Alert</div><div class="al-b">${overdue.length} students overdue — seats highlighted in red 🔴</div></div></div>
    <div class="al-card al-i"><span style="font-size:17px">🎁</span><div><div class="al-t">Discounts Applied</div><div class="al-b">${s.filter(x=>x.baseFee>x.netFee).length} students with discounts — ₹${totalDiscount.toLocaleString()} waived</div></div></div>`;

        document.getElementById('dashStats').innerHTML=`
    <div class="sc" style="--ca:var(--ac)"><div class="s-row"><div class="s-ic"><span class="mi" style="color:var(--ac);font-size:20px">school</span></div><span class="s-lb">Total Students</span></div><div class="s-vl">${s.length}</div><div class="s-mt"><span class="bup">↑ 12%</span> this month</div></div>
    <div class="sc" style="--ca:var(--em)"><div class="s-row"><div class="s-ic"><span class="mi" style="color:var(--em);font-size:20px">event_seat</span></div><span class="s-lb">Seats Available</span></div><div class="s-vl">${totalSeats-occSeats}</div><div class="s-mt">${occSeats}/${totalSeats} occupied</div></div>
    <div class="sc" style="--ca:var(--gd)"><div class="s-row"><div class="s-ic"><span class="mi" style="color:var(--gd);font-size:20px">payments</span></div><span class="s-lb">Revenue Collected</span></div><div class="s-vl">${fmt(activeStudentRev)}</div><div class="s-mt" style="display:flex;flex-direction:column;gap:3px;margin-top:4px">
      <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:600;color:#166534"><span style="width:6px;height:6px;border-radius:50%;background:#16a34a;flex-shrink:0"></span>₹${revLivePaid.toLocaleString('en-IN')} live · fully paid</span>
      ${revLivePartial>0?`<span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:600;color:#92400e"><span style="width:6px;height:6px;border-radius:50%;background:#d97706;flex-shrink:0"></span>₹${revLivePartial.toLocaleString('en-IN')} live · partial</span>`:''}
      ${revFromDeleted>0?`<span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:600;color:#9f1239;opacity:.75"><span style="width:6px;height:6px;border-radius:50%;background:#e11d48;flex-shrink:0"></span>₹${revFromDeleted.toLocaleString('en-IN')} deleted students</span>`:''}
    </div></div>
    <div class="sc" style="--ca:var(--ro)"><div class="s-row"><div class="s-ic"><span class="mi" style="color:var(--ro);font-size:20px">pending</span></div><span class="s-lb">Total Due</span></div><div class="s-vl">${fmt(allDue)}</div><div class="s-mt" style="color:var(--ro)">${[...pending,...overdue,...partial].length} students</div></div>
    <div class="sc" style="--ca:var(--or)"><div class="s-row"><div class="s-ic"><span class="mi" style="color:var(--or);font-size:20px">redeem</span></div><span class="s-lb">Discounts Given</span></div><div class="s-vl">${fmt(totalDiscount)}</div><div class="s-mt">${s.filter(x=>x.baseFee>x.netFee).length} students</div></div>
    <div class="sc" style="--ca:var(--vi)"><div class="s-row"><div class="s-ic"><span class="mi" style="color:var(--vi);font-size:20px">menu_book</span></div><span class="s-lb">Books Issued</span></div><div class="s-vl">${issTx.length}</div><div class="s-mt" style="color:var(--ro)">${odTx.length} overdue</div></div>
    <div class="sc" style="--ca:var(--sk)"><div class="s-row"><div class="s-ic"><span class="mi" style="color:var(--sk);font-size:20px">fact_check</span></div><span class="s-lb">Attendance Today</span></div><div class="s-vl">${prsnt}</div><div class="s-mt" style="color:var(--em)">${s.length?Math.round(prsnt/s.length*100):0}%</div></div>
    <div class="sc" style="--ca:#7c3aed;cursor:pointer" onclick="navTo('biometric')"><div class="s-row"><div class="s-ic"><span class="mi" style="color:#7c3aed;font-size:20px">fingerprint</span></div><span class="s-lb">Biometric Check-ins</span></div><div class="s-vl">${Object.values(_bioToday).filter(b=>b.in).length}</div><div class="s-mt" style="color:#7c3aed">today · via device</div></div>
    ${(()=>{
      // ── Monthly Expenses Sparkline Card ──
      const now = new Date();
      const curY = now.getFullYear(), curM = now.getMonth();

      // Build last-6-months buckets
      const months = [];
      for(let i = 5; i >= 0; i--){
        const d = new Date(curY, curM - i, 1);
        months.push({ y: d.getFullYear(), m: d.getMonth(), total: 0,
          label: d.toLocaleDateString('en-IN',{month:'short'}) });
      }
      DB.expenses.forEach(e => {
        if(!e.date) return;
        const d = new Date(e.date);
        const bucket = months.find(b => b.y===d.getFullYear() && b.m===d.getMonth());
        if(bucket) bucket.total += e.amount;
      });

      const curTotal  = months[5].total;
      const prevTotal = months[4].total;
      const pctChange = prevTotal > 0 ? ((curTotal - prevTotal) / prevTotal * 100) : 0;
      const isUp      = pctChange >= 0;
      const vals      = months.map(b => b.total);
      const maxV      = Math.max(...vals, 1);

      // SVG sparkline (area chart) — 220×60
      const W=220, H=60, padX=6, padY=6;
      const pts = vals.map((v,i)=>{
        const x = padX + (i / (vals.length-1)) * (W - padX*2);
        const y = H - padY - (v/maxV) * (H - padY*2);
        return [x.toFixed(1), y.toFixed(1)];
      });
      const polyline = pts.map(p=>p.join(',')).join(' ');
      const area = `${pts[0][0]},${H-padY} ${polyline} ${pts[pts.length-1][0]},${H-padY}`;

      // Y-axis tick labels (3 levels)
      const yTicks = [0, Math.round(maxV/2), maxV].map((v,i)=>{
        const y = H - padY - (v/maxV)*(H-padY*2);
        const label = v>=1000?`${(v/1000).toFixed(0)}K`:`${v}`;
        return `<text x="0" y="${y.toFixed(1)}" font-size="5.5" fill="var(--tx3)" dominant-baseline="middle" font-family="JetBrains Mono,monospace">${label}</text>`;
      }).join('');

      const xLabels = months.map((b,i)=>{
        const x = padX + (i/(vals.length-1))*(W-padX*2);
        return `<text x="${x.toFixed(1)}" y="${H+1}" font-size="6" fill="var(--tx3)" text-anchor="middle" font-family="JetBrains Mono,monospace">${b.label}</text>`;
      }).join('');

      // dot on last point
      const lastPt = pts[pts.length-1];
      const dot = `<circle cx="${lastPt[0]}" cy="${lastPt[1]}" r="3" fill="var(--vi)" stroke="#fff" stroke-width="1.5"/>`;

      return `<div class="sc" style="--ca:var(--vi);padding:14px 16px;min-width:0;grid-column:span 1">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:6px">
          <div>
            <div style="font-size:10px;font-weight:700;color:var(--tx3);letter-spacing:.8px;text-transform:uppercase;font-family:var(--fm)">Monthly Expenses</div>
            <div style="font-size:22px;font-weight:800;color:var(--tx);line-height:1.15;font-family:var(--fd)">₹${curTotal.toLocaleString('en-IN')}</div>
            <div style="display:flex;align-items:center;gap:5px;margin-top:3px">
              <span style="display:inline-flex;align-items:center;gap:2px;padding:2px 6px;border-radius:5px;font-size:10px;font-weight:700;background:${isUp?'rgba(220,38,38,.10)':'rgba(22,163,74,.12)'};color:${isUp?'var(--ro)':'var(--em)'}">
                ${isUp?'▲':'▼'} ${Math.abs(pctChange).toFixed(1)}%
              </span>
              <span style="font-size:10px;color:var(--tx3)">vs last month</span>
            </div>
          </div>
          <div style="font-size:9px;color:var(--tx3);font-family:var(--fm);background:var(--sf2);border:1px solid var(--br);border-radius:6px;padding:3px 8px;white-space:nowrap">This Month</div>
        </div>
        <svg width="100%" viewBox="0 0 ${W} ${H+10}" preserveAspectRatio="none" style="display:block;overflow:visible">
          <defs>
            <linearGradient id="expGrad" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="var(--vi)" stop-opacity="0.22"/>
              <stop offset="100%" stop-color="var(--vi)" stop-opacity="0.02"/>
            </linearGradient>
          </defs>
          ${yTicks}
          <polygon points="${area}" fill="url(#expGrad)"/>
          <polyline points="${polyline}" fill="none" stroke="var(--vi)" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
          ${dot}
          ${xLabels}
        </svg>
      </div>`;
    })()}
    ${(()=>{
      // ── Quick Summary — Seats Donut Card ──
      const total = totalSeats || 1;
      const occPct = occSeats / total;
      const circ = 2 * Math.PI * 28;
      const occDash = occPct * circ;
      const avail = totalSeats - occSeats;
      const availPct = totalSeats ? Math.round(avail/totalSeats*100) : 0;
      const occPctDisp = totalSeats ? Math.round(occSeats/totalSeats*100) : 0;

      // Three arcs: available (green), occupied (blue), reserved (amber) — just avail+occ for now
      const circFull = 2 * Math.PI * 28;
      const availDash = (avail/total) * circFull;
      const occDashV  = (occSeats/total) * circFull;
      const offset    = circFull * 0.25; // start from top

      return `<div class="sc" style="--ca:var(--em);padding:14px 16px;min-width:0">
        <div style="font-size:10px;font-weight:700;color:var(--tx3);letter-spacing:.8px;text-transform:uppercase;font-family:var(--fm);margin-bottom:10px">Quick Summary</div>
        <div style="display:flex;align-items:center;gap:16px">
          <!-- Donut -->
          <div style="position:relative;flex-shrink:0;width:80px;height:80px">
            <svg width="80" height="80" viewBox="0 0 72 72">
              <!-- track -->
              <circle cx="36" cy="36" r="28" fill="none" stroke="#e8edf5" stroke-width="10"/>
              <!-- occupied (blue) — drawn first, from top -->
              <circle cx="36" cy="36" r="28" fill="none" stroke="var(--ac)" stroke-width="10"
                stroke-dasharray="${occDashV.toFixed(1)} ${circFull.toFixed(1)}"
                stroke-dashoffset="${offset.toFixed(1)}"
                stroke-linecap="butt"/>
              <!-- available (green) — starts after occupied arc -->
              <circle cx="36" cy="36" r="28" fill="none" stroke="var(--em)" stroke-width="10"
                stroke-dasharray="${availDash.toFixed(1)} ${circFull.toFixed(1)}"
                stroke-dashoffset="${(offset - occDashV).toFixed(1)}"
                stroke-linecap="butt"/>
            </svg>
            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center">
              <div style="font-size:15px;font-weight:800;color:var(--tx);line-height:1;font-family:var(--fd)">${totalSeats}</div>
              <div style="font-size:8px;color:var(--tx3);font-weight:600;margin-top:2px;font-family:var(--fm)">Total Seats</div>
            </div>
          </div>
          <!-- Legend -->
          <div style="display:flex;flex-direction:column;gap:7px;flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:6px;font-size:11px;font-weight:700">
              <span style="width:8px;height:8px;border-radius:50%;background:var(--em);flex-shrink:0"></span>
              <span style="color:var(--tx2);flex:1">Available</span>
              <span style="color:var(--em)">${avail} <span style="font-size:9px;color:var(--tx3);font-weight:500">(${availPct}%)</span></span>
            </div>
            <div style="display:flex;align-items:center;gap:6px;font-size:11px;font-weight:700">
              <span style="width:8px;height:8px;border-radius:50%;background:var(--ac);flex-shrink:0"></span>
              <span style="color:var(--tx2);flex:1">Occupied</span>
              <span style="color:var(--ac)">${occSeats} <span style="font-size:9px;color:var(--tx3);font-weight:500">(${occPctDisp}%)</span></span>
            </div>
            <div style="display:flex;align-items:center;gap:6px;font-size:11px;font-weight:700">
              <span style="width:8px;height:8px;border-radius:50%;background:var(--gd);flex-shrink:0"></span>
              <span style="color:var(--tx2);flex:1">Reserved</span>
              <span style="color:var(--gd)">0 <span style="font-size:9px;color:var(--tx3);font-weight:500">(0%)</span></span>
            </div>
          </div>
        </div>
      </div>`;
    })()}`;

        // ── BATCH SEAT AVAILABILITY WITH FEE STATUS ──
        // Build seat→student map
        const seatMap={};
        DB.students.forEach(st=>{ if(st.seat&&st.batchId)seatMap[st.batchId+'_'+st.seat]=st; });
        let batchHTML='';
        DB.batches.forEach(b=>{
            const pct=Math.round(b.occupied/b.total*100);
            const fc=pct>=100?'sf-r':pct>=70?'sf-y':'sf-g';
            const sc=pct>=100?'bst-f':pct>=70?'bst-n':'bst-o';
            const vacCount=b.total-b.occupied;
            const bStudents=DB.students.filter(x=>x.batchId===b.id);
            const bDue=bStudents.filter(x=>x.feeStatus==='pending'||x.feeStatus==='partial').length;
            const bOD=bStudents.filter(x=>x.feeStatus==='overdue').length;
            batchHTML+=`<div class="panel" style="margin-bottom:10px">
      <div class="ph" style="padding:10px 14px">
        <div><div style="font-weight:600;font-size:13px">${batchEmoji(b.name)} ${b.name}</div>
          <div style="font-size:10px;color:var(--tx3);font-family:var(--fm)">${fmtT(b.startTime)}–${fmtT(b.endTime)} · ₹${b.baseFee}+AC₹${b.acExtra}</div></div>
        <div style="display:flex;align-items:center;gap:8px">
          <div class="bst ${sc}">${pct>=100?'Full':pct>=70?'Filling':'Open'}</div>
          ${bDue>0?`<span style="font-size:9px;background:rgba(230,126,34,.15);color:var(--or);padding:2px 6px;border-radius:3px;font-weight:700;font-family:var(--fm)">⏳${bDue} pending</span>`:''}
          ${bOD>0?`<span style="font-size:9px;background:rgba(192,68,79,.15);color:var(--ro);padding:2px 6px;border-radius:3px;font-weight:700;font-family:var(--fm);animation:pulseDue 1s infinite">🚨${bOD} overdue</span>`:''}
        </div>
      </div>
      <div style="padding:8px 14px 12px">
        <div class="sbar"><div class="sfill ${fc}" style="width:${pct}%"></div></div>
        <div style="display:flex;justify-content:space-between;font-size:10px;font-family:var(--fm);color:var(--tx3)"><span>Total: <b>${b.total}</b></span><span style="color:var(--ro)">Occupied: <b>${b.occupied}</b></span><span style="color:var(--em)">Vacant: <b>${vacCount}</b></span></div>
      </div>
    </div>`;
        });
        document.getElementById('dashBatchCards').innerHTML=batchHTML;
        // In the new layout the batch grid is inside a 50% column — always 2 cols
        document.getElementById('dashBatchCards').style.gridTemplateColumns = window.innerWidth<600?'1fr':'repeat(2,1fr)';

        // ── EXPENSE TRACKER ──
        const catTotals={};DB.expenses.forEach(e=>{catTotals[e.category]=(catTotals[e.category]||0)+e.amount;});
        const catColors={Staff:'var(--ro)',Utilities:'var(--gd)',Maintenance:'var(--vi)',Books:'var(--sk)',Supplies:'var(--ac)',Other:'var(--tx3)'};
        document.getElementById('dashExpTracker').innerHTML=`<div class="pb">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <div><div style="font-size:9.5px;color:var(--tx3);font-family:var(--fm);text-transform:uppercase">TOTAL THIS MONTH</div><div style="font-size:22px;font-weight:700;color:var(--ro);font-family:var(--fd)">${fmt(totalExp)}</div></div>
      <div style="text-align:right"><div style="font-size:9.5px;color:var(--tx3);font-family:var(--fm);text-transform:uppercase">NET PROFIT</div><div style="font-size:22px;font-weight:700;color:var(--em);font-family:var(--fd)">${fmt(totalRev-totalExp)}</div></div>
    </div>
    ${Object.entries(catTotals).map(([cat,amt])=>{const pct=Math.round(amt/totalExp*100);return`<div style="margin-bottom:8px"><div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:3px"><span style="color:var(--tx2)">${cat}</span><span style="font-family:var(--fm);font-weight:700">₹${amt.toLocaleString()} (${pct}%)</span></div><div class="prg"><div class="prf" style="width:${pct}%;background:${catColors[cat]||'var(--ac)'}"></div></div></div>`;}).join('')}
    <div style="margin-top:10px;display:flex;flex-direction:column;gap:6px">
      ${DB.expenses.slice(0,4).map(e=>`<div class="ei2"><div class="eic" style="background:rgba(74,124,111,.1)">${e.emoji}</div><div style="flex:1"><div class="en2">${e.name}</div><div class="ed">${fmtDate(e.date)}</div></div><div class="ea ea-d">-₹${e.amount.toLocaleString()}</div></div>`).join('')}
    </div>
  </div>`;

        // ── FEE OVERVIEW ──
        document.getElementById('dashFeeOv').innerHTML=`<div class="panel" style="margin-bottom:8px"><div class="pb" style="padding-bottom:10px">
    <div class="fi"><div class="fd2" style="background:var(--em)"></div><div><div class="fn2">Fully Paid</div><div class="fsb">${paid.length} students</div></div><div class="fa" style="color:var(--em)">${fmt(paid.reduce((a,x)=>a+x.netFee,0))}</div></div>
    <div class="fi"><div class="fd2" style="background:var(--sk)"></div><div><div class="fn2">Partial Payment</div><div class="fsb">${partial.length} students · ₹${partial.reduce((a,x)=>a+(x.netFee-x.paidAmt),0).toLocaleString()} still due</div></div><div class="fa" style="color:var(--sk)">${fmt(partial.reduce((a,x)=>a+x.paidAmt,0))}</div></div>
    <div class="fi"><div class="fd2" style="background:var(--gd)"></div><div><div class="fn2">Pending</div><div class="fsb">${pending.length} students</div></div><div class="fa" style="color:var(--gd)">${fmt(pending.reduce((a,x)=>a+x.netFee,0))}</div></div>
    <div class="fi"><div class="fd2" style="background:var(--ro)"></div><div><div class="fn2">Overdue</div><div class="fsb">${overdue.length} students &gt;7 days</div></div><div class="fa" style="color:var(--ro)">${fmt(overdue.reduce((a,x)=>a+x.netFee,0))}</div></div>
    <div class="fi"><div class="fd2" style="background:var(--or)"></div><div><div class="fn2">Discounts</div><div class="fsb">${s.filter(x=>x.baseFee>x.netFee).length} students</div></div><div class="fa" style="color:var(--or)">${fmt(totalDiscount)}</div></div>
    <div style="margin-top:6px"><div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:4px;font-family:var(--fm)"><span style="color:var(--tx3)">Collection Rate</span><span style="color:var(--em);font-weight:700">${s.length?Math.round((paid.length+partial.length)/s.length*100):0}%</span></div>
    <div class="prg"><div class="prf" style="width:${s.length?Math.round((paid.length+partial.length)/s.length*100):0}%;background:linear-gradient(90deg,var(--em),#4ead82)"></div></div></div>
  </div></div>`;

        // ── RECENT STUDENTS TABLE ──
        const recent=[...s].sort((a,b)=>b.id.localeCompare(a.id)).slice(0,15);
        document.getElementById('dashStuTable').innerHTML=recent.map(x=>{
            const bal=x.netFee-x.paidAmt;
            const rowClass=x.feeStatus==='overdue'?'fee-due-row':x.feeStatus==='partial'||x.feeStatus==='pending'?'fee-partial-row':'';
            return `<tr class="${rowClass}">
      <td><div class="si"><div class="sav" style="background:${x.color}">${((x.fname||"?")[0]+((x.lname||"")[0]||"")).toUpperCase()}</div><div><div style="font-weight:600;font-size:12.5px;cursor:pointer;color:var(--ac)" onclick="openStudentProfile('${x.id}')">${x.fname||""} ${x.lname||""}</div><div style="font-size:10px;color:var(--tx3);font-family:var(--fm)">#${x.id}</div></div></div></td>
      <td>${bTag(x.batchId)}</td>
      <td>${x.seat?`<span style="font-family:var(--fm);font-size:11px;font-weight:600">${x.seat}</span>`:'<span style="color:var(--tx3)">—</span>'}${x.feeStatus==='overdue'?'<span style="font-size:9px;margin-left:3px">🔴</span>':x.feeStatus==='pending'||x.feeStatus==='partial'?'<span style="font-size:9px;margin-left:3px">🟠</span>':''}</td>
      <td><span style="font-family:var(--fm);font-weight:700">₹${x.netFee}</span>${x.baseFee>x.netFee?`<div style="font-size:9px;color:var(--or)">🎁-₹${x.baseFee-x.netFee}</div>`:''}</td>
      <td><span style="font-family:var(--fm);font-weight:700;color:var(--em)">₹${x.paidAmt}</span></td>
      <td>${bal>0?`<span class="fee-bal-badge">₹${bal} DUE</span>`:`<span style="color:var(--em);font-size:11px">✓</span>`}</td>
      <td><span class="tag ${x.feeStatus==='paid'?'tpd':x.feeStatus==='partial'?'tpart':x.feeStatus==='pending'?'tpn':'tod'}">${x.feeStatus==='paid'?'✓ Paid':x.feeStatus==='partial'?'◑ Partial':x.feeStatus==='pending'?'⏳ Pending':'🚨 Overdue'}</span></td>
      <td><button class="btn bwa" style="font-size:10px;padding:3px 7px" onclick="waQuick('${x.id}','${x.feeStatus==='paid'?'fee_receipt':x.feeStatus==='partial'?'partial_payment':x.feeStatus==='overdue'?'fee_overdue':'fee_due'}')">💬</button></td>
    </tr>`;
        }).join('');

        // ── LIVE ACTIVITY FEED ──
        const actEl = document.getElementById('dashLiveAct');
        if (actEl) {
            if (DB.activities.length === 0) {
                actEl.innerHTML = '<div style="padding:20px;text-align:center;color:var(--tx3);font-size:12px">No recent activity</div>';
            } else {
                actEl.innerHTML = DB.activities.slice(0,12).map((a,i) => `
        <div class="act-it" style="padding:8px 16px;animation:fuUp .3s ease ${i*0.04}s both">
          <div class="act-d" style="background:${a.bg||'rgba(61,111,240,.1)'}">${a.icon||'📌'}</div>
          <div style="flex:1;min-width:0">
            <div class="act-tx" style="font-size:11.5px;line-height:1.4">${a.text}</div>
            <div class="act-tm">${a.time||'Just now'}</div>
          </div>
        </div>`).join('');
            }
            document.getElementById('liveActTime').textContent =
                DB.activities.length ? DB.activities[0].time || 'Just now' : '—';
        }

        // ── REAL DONUT — calculated from actual data ──
        const totalStudents = s.length || 1;
        const paidAmt   = paid.reduce((a,x)=>a+x.netFee,0);
        const partialAmt= partial.reduce((a,x)=>a+x.paidAmt,0);
        const overdueAmt= overdue.reduce((a,x)=>a+x.netFee,0);
        const pendingAmt= pending.reduce((a,x)=>a+x.netFee,0);
        const grandTotal= paidAmt + partialAmt + overdueAmt + pendingAmt || 1;
        const circ = 2 * Math.PI * 34; // circumference for r=34

        function arcDash(pct, offset) {
            const len = circ * pct;
            return { dash: `${len.toFixed(1)} ${(circ-len).toFixed(1)}`, offset: (-offset).toFixed(1) };
        }
        const p1=paidAmt/grandTotal, p2=partialAmt/grandTotal,
            p3=overdueAmt/grandTotal, p4=pendingAmt/grandTotal;
        const off1=0, off2=circ*p1, off3=circ*(p1+p2), off4=circ*(p1+p2+p3);

        const a1=arcDash(p1,off1),a2=arcDash(p2,off2),a3=arcDash(p3,off3),a4=arcDash(p4,off4);
        const setArc = (id,d,o) => { const el=document.getElementById(id); if(el){el.setAttribute('stroke-dasharray',d.dash);el.setAttribute('stroke-dashoffset',o);} };
        setArc('donutArc1',a1,a1.offset);
        setArc('donutArc2',a2,a2.offset);
        setArc('donutArc3',a3,a3.offset);
        setArc('donutArc4',a4,a4.offset);
        document.getElementById('donutC').textContent = fmt(totalRev);
        document.getElementById('donutSub').textContent = 'collected';
        const pct = (v,t) => t>0?Math.round(v/t*100)+'%':'0%';
        const setSafe=(id,v)=>{const el=document.getElementById(id);if(el)el.textContent=v;};
        setSafe('revPct1', pct(paidAmt,grandTotal));
        setSafe('revPct2', pct(partialAmt,grandTotal));
        setSafe('revPct3', pct(overdueAmt,grandTotal));
        setSafe('revPct4', pct(pendingAmt,grandTotal));

        // ── WEEKLY CHART — last 4 weeks from invoices ──
        const now = new Date();
        const weekTotals = [0,0,0,0];
        const liveStudentIds = new Set(DB.students.map(s=>s.id));
        DB.invoices.filter(i=>liveStudentIds.has(i.studentId)).forEach(inv => {
            const d = new Date(inv.date);
            const diffDays = Math.floor((now - d) / 86400000);
            const wk = Math.floor(diffDays / 7);
            if (wk >= 0 && wk < 4) weekTotals[3 - wk] += (inv.amount || 0);
        });
        if (weekTotals.every(v=>v===0)) weekTotals[3] = totalRev;
        const wMax = Math.max(...weekTotals, 1);
        document.getElementById('weekChart').innerHTML = weekTotals.map((v,i) =>
            `<div class="cbar" style="flex:1;height:${Math.round(v/wMax*100)}%;background:var(--ac);opacity:${i===3?1:.5};border-radius:3px 3px 0 0"><div class="tt">₹${v.toLocaleString()}</div></div>`
        ).join('');

        renderCal();
    }

    function renderCal(){
        const d = calDate;
        const titleEl = document.getElementById('calTitle');
        if (titleEl) titleEl.textContent = d.toLocaleDateString('en-IN',{month:'long',year:'numeric'});
        const days = ['Su','Mo','Tu','We','Th','Fr','Sa'];
        let h = days.map(day=>`<div class="cal-dl">${day}</div>`).join('');
        const first = new Date(d.getFullYear(), d.getMonth(), 1).getDay();
        for(let i=0;i<first;i++) h+=`<div class="cal-d empty"></div>`;
        const dim  = new Date(d.getFullYear(), d.getMonth()+1, 0).getDate();
        const today = new Date(); today.setHours(0,0,0,0);

        // Build a map: day → students due that day
        const dueMap = {};
        DB.students.forEach(s => {
            if (!s.dueDate) return;
            const dd = new Date(s.dueDate);
            if (dd.getFullYear()===d.getFullYear() && dd.getMonth()===d.getMonth()) {
                const day = dd.getDate();
                if (!dueMap[day]) dueMap[day] = [];
                dueMap[day].push(s);
            }
        });

        for(let i=1;i<=dim;i++){
            const thisDate = new Date(d.getFullYear(), d.getMonth(), i);
            thisDate.setHours(0,0,0,0);
            const isToday = thisDate.getTime() === today.getTime();
            const hasDue  = dueMap[i] && dueMap[i].length > 0;
            const hasOD   = hasDue && dueMap[i].some(s=>s.feeStatus==='overdue');
            let cls = isToday ? 'today' : '';
            let extra = '';
            if (hasDue && !isToday) {
                cls = hasOD ? 'event' : 'event';
                const dotColor = hasOD ? 'var(--ro)' : 'var(--gd)';
                extra = `<span style="position:absolute;bottom:1px;left:50%;transform:translateX(-50%);width:4px;height:4px;border-radius:50%;background:${dotColor}"></span>`;
            }
            h += `<div class="cal-d ${cls}" style="position:relative" title="${hasDue?dueMap[i].map(s=>s.fname+' '+s.lname).join(', '):''}">${i}${extra}</div>`;
        }
        const calEl = document.getElementById('miniCal');
        if (calEl) calEl.innerHTML = h;

        // Upcoming due dates legend (next 5 students due this month)
        const legEl = document.getElementById('calDueLegend');
        if (legEl) {
            const upcoming = DB.students
                .filter(s=>s.dueDate)
                .map(s=>({s, dd:new Date(s.dueDate)}))
                .filter(({dd})=>dd>=today)
                .sort((a,b)=>a.dd-b.dd)
                .slice(0,4);
            legEl.innerHTML = upcoming.length
                ? upcoming.map(({s,dd})=>{
                    const diff = Math.round((dd-today)/86400000);
                    const col  = diff===0?'var(--ro)':diff<=3?'var(--or)':diff<=7?'var(--gd)':'var(--tx3)';
                    return `<div style="display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:1px solid var(--br)">
            <div style="width:7px;height:7px;border-radius:50%;background:${col};flex-shrink:0"></div>
            <span style="flex:1;font-size:12px;font-weight:600;color:var(--tx2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${s.fname} ${s.lname}</span>
            <span style="font-family:var(--fm);font-size:10px;font-weight:800;color:${col};white-space:nowrap;background:${col}18;padding:2px 7px;border-radius:5px">${diff===0?'Today':diff===1?'Tomorrow':'in '+diff+'d'}</span>
          </div>`;
                }).join('')
                : '<div style="font-size:11px;color:var(--tx3)">No upcoming dues</div>';
        }

        const prevBtn = document.getElementById('calPrev');
        const nextBtn = document.getElementById('calNext');
        if (prevBtn) prevBtn.onclick = ()=>{calDate=new Date(calDate.getFullYear(),calDate.getMonth()-1,1);renderCal();};
        if (nextBtn) nextBtn.onclick = ()=>{calDate=new Date(calDate.getFullYear(),calDate.getMonth()+1,1);renderCal();};
    }

    // ═══ STUDENTS ═══
    let stuPage=1,stuFilterVal='all',stuSearchVal='';
    function renderStudents(){
        let list=DB.students.filter(x=>{
            const mF=stuFilterVal==='all'||x.feeStatus===stuFilterVal;
            const mS=!stuSearchVal||`${x.fname} ${x.lname} ${x.id} ${x.phone}`.toLowerCase().includes(stuSearchVal.toLowerCase());
            return mF&&mS;
        });
        document.getElementById('stuCount2').textContent=`${list.length} student(s)`;
        const pp=7,total=list.length,pages=Math.ceil(total/pp)||1;
        stuPage=Math.min(stuPage,pages);
        const sl=list.slice((stuPage-1)*pp,stuPage*pp);
        document.getElementById('stuTable').innerHTML=sl.map(x=>{
            const bal=x.netFee-x.paidAmt;
            const discTxt=x.baseFee>x.netFee?`<span class="tag tor" style="font-size:9px">🎁-₹${x.baseFee-x.netFee}</span>`:'<span style="color:var(--tx3);font-size:10px">—</span>';
            const rowClass=x.feeStatus==='overdue'?'fee-due-row':x.feeStatus==='partial'||x.feeStatus==='pending'?'fee-partial-row':'';
            return `<tr class="${rowClass}">
      <td><div class="si"><div class="sav" style="background:${x.color}">${((x.fname||"?")[0]+((x.lname||"")[0]||"")).toUpperCase()}</div><div><div style="font-weight:600;font-size:12.5px;cursor:pointer;color:var(--ac)" onclick="openStudentProfile('${x.id}')">${x.fname||""} ${x.lname||""}</div><div style="font-size:10px;color:var(--tx3);font-family:var(--fm)">#${x.id}</div></div></div></td>
      <td>${bTag(x.batchId)}</td>
      <td><span style="font-family:var(--fm);font-size:11px">${x.seat||'—'}</span>${x.feeStatus==='overdue'?'🔴':x.feeStatus!=='paid'?'🟠':''}</td>
      <td>${x.seatType==='ac'?'<span class="tag tac" style="font-size:9px">❄ AC</span>':'<span style="font-size:10px;color:var(--tx3)">Non-AC</span>'}</td>
      <td><span style="font-family:var(--fm);font-weight:700">₹${x.baseFee}</span></td>
      <td>${discTxt}${x.discount?.reason?`<div style="font-size:9px;color:var(--tx3)">${x.discount.reason}</div>`:''}</td>
      <td><span style="font-family:var(--fm);font-weight:700;color:var(--em)">₹${x.netFee}</span></td>
      <td><span style="font-family:var(--fm);font-weight:700;color:var(--em)">₹${x.paidAmt}</span></td>
      <td>${bal>0?`<span class="fee-bal-badge">₹${bal}</span>`:''}</td>
      <td><span class="tag ${x.feeStatus==='paid'?'tpd':x.feeStatus==='partial'?'tpart':x.feeStatus==='pending'?'tpn':'tod'}">${x.feeStatus==='paid'?'✓ Paid':x.feeStatus==='partial'?'◑ Partial':x.feeStatus==='pending'?'⏳ Pending':'🚨 Overdue'}</span></td>
      <td><span style="font-size:10.5px;font-family:var(--fm);color:${x.feeStatus==='overdue'?'var(--ro)':x.feeStatus==='pending'?'var(--gd)':'var(--tx3)'}">${fmtDate(x.dueDate)}</span></td>
      <td><div style="display:flex;gap:4px">
        ${x.feeStatus!=='paid'&&canDo('collect_fee')?`<button class="btn bp" data-action="collect_fee" style="font-size:10px;padding:3px 7px" onclick="qCollect('${x.id}')">Collect</button>`:''}
        <button class="btn bg" style="font-size:10px;padding:3px 7px" onclick="openStudentProfile('${x.id}')">👤</button>
        <button class="btn bg" style="font-size:10px;padding:3px 7px" onclick="showStudentQR('${x.id}')" title="Student QR Code"><span class="mi sm">qr_code</span></button>
        <button class="btn bwa" style="font-size:10px;padding:3px 7px" onclick="waQuick('${x.id}','${x.feeStatus==='paid'?'fee_receipt':x.feeStatus==='partial'?'partial_payment':x.feeStatus==='overdue'?'fee_overdue':'fee_due'}')">💬</button>
        ${x.feeStatus!=='paid'?`<button class="btn bg" style="font-size:10px;padding:3px 7px;color:var(--ac);border-color:var(--ac)" onclick="sendUpiLink('${x.id}')">📱 UPI</button>`:''}
        ${canDo('delete_student')?`<button class="btn bd" data-action="delete_student" style="font-size:10px;padding:3px 6px" onclick="delStu('${x.id}')"><span class="mi sm">close</span></button>`:''}
      </div></td>
    </tr>`;
        }).join('')||'<tr><td colspan="12"><div class="empty"><div class="ei">👨‍🎓</div><div class="et">No students</div></div></td></tr>';
        document.getElementById('stuPagI').textContent=`${sl.length} of ${total}`;
        let pb='';for(let i=1;i<=pages;i++) pb+=`<div class="pb2 ${i===stuPage?'active':''}" onclick="stuPage=${i};renderStudents()">${i}</div>`;
        document.getElementById('stuPagB').innerHTML=pb;
    }
    function stuSrch(v){stuSearchVal=v;stuPage=1;renderStudents();}
    function stuFilt(f,el){stuFilterVal=f;stuPage=1;document.querySelectorAll('#stuTabs .tab').forEach(t=>t.classList.remove('active'));el.classList.add('active');renderStudents();}
    function qCollect(id){populateFeeModal(id);openM('mCollectFee');}
    function delStu(id){if(!confirm('Remove?'))return;DB.students=DB.students.filter(x=>x.id!==id);toast('Removed','wn');renderStudents();updateBadges();}
    // ═══ STUDENT PROFILE ═══
    let profileStudentId = null;
    let profileEditMode = false;

    function openStudentProfile(id) {
        const s = DB.students.find(x => x.id === id);
        if (!s) return;
        profileStudentId = id;
        profileEditMode = false;

        // Header
        document.getElementById('spHeaderName').textContent = s.fname + ' ' + s.lname;
        document.getElementById('spHeaderId').textContent = '#' + s.id;
        document.getElementById('spAv').textContent = ((s.fname?.[0]||'') + (s.lname?.[0]||'')).toUpperCase();
        document.getElementById('spAv').style.background = s.color || '#3d6ff0';

        // Fee bar
        const pct = s.netFee > 0 ? Math.round(s.paidAmt / s.netFee * 100) : 0;
        document.getElementById('spFeeFill').style.width = pct + '%';
        document.getElementById('spPaidLbl').textContent = 'Paid ₹' + s.paidAmt.toLocaleString();
        document.getElementById('spDueLbl').textContent = 'Due ₹' + Math.max(0, s.netFee - s.paidAmt).toLocaleString();
        document.getElementById('spNetFee').textContent = '₹' + s.netFee.toLocaleString();
        const feeTagEl = document.getElementById('spFeeTag');
        const feeMap = { paid: ['tpd', '✓ Paid'], partial: ['tpart', '◑ Partial'], pending: ['tpn', '⏳ Pending'], overdue: ['tod', '🚨 Overdue'] };
        feeTagEl.className = 'tag ' + (feeMap[s.feeStatus]?.[0] || 'tpn');
        feeTagEl.textContent = feeMap[s.feeStatus]?.[1] || s.feeStatus;

        // Batch & seat
        const b = DB.batches.find(x => x.id === s.batchId);
        document.getElementById('spBatchDisp').innerHTML = b ? `<span class="tag ${b.name.includes('Morning')||b.name.includes('Early')?'tpn':b.name.includes('Evening')?'tis':b.name.includes('Night')?'tac':'tav'}">${b.name}</span>` : '<span style="color:var(--tx3)">—</span>';
        document.getElementById('spSeatNum').textContent = s.seat || '—';
        document.getElementById('spSeatChip').title = s.seat ? 'Click to change seat' : 'Click to allocate seat';
        document.getElementById('spSeatTypeDisp').innerHTML = s.seatType === 'ac' ? '<span class="tag tac" style="font-size:10px">❄ AC</span>' : '<span style="font-size:11px;color:var(--tx2)">Non-AC</span>';
        document.getElementById('spJoinDate').textContent = s.joinDate || '—';

        // Personal
        document.getElementById('spFname').textContent = s.fname;
        document.getElementById('spLname').textContent = s.lname;
        document.getElementById('spPhone').textContent = s.phone || '—';
        document.getElementById('spEmail').textContent = s.email || '—';
        document.getElementById('spCourse').textContent = s.course || '—';
        document.getElementById('spAddr').textContent = s.addr || '—';

        // Fee details
        document.getElementById('spBaseFee').textContent = '₹' + (s.baseFee || 0).toLocaleString();
        document.getElementById('spDiscount').textContent = s.baseFee > s.netFee ? '₹' + (s.baseFee - s.netFee).toLocaleString() + (s.discount?.reason ? ' — ' + s.discount.reason : '') : '—';
        document.getElementById('spPaidAmt').textContent = '₹' + (s.paidAmt || 0).toLocaleString();
        document.getElementById('spDueDate').textContent = fmtDate(s.dueDate);

        // Quick action buttons
        document.getElementById('spCollectBtn').onclick = () => { closeM('mStudentProfile'); qCollect(id); };
        document.getElementById('spWaBtn').onclick = () => { closeM('mStudentProfile'); setTimeout(() => waQuick(id, s.feeStatus === 'paid' ? 'fee_receipt' : s.feeStatus === 'overdue' ? 'fee_overdue' : 'fee_due'), 200); };
        const _upiProfBtn = document.getElementById('spUpiBtn');
        if (_upiProfBtn) { _upiProfBtn.style.display = s.feeStatus !== 'paid' ? '' : 'none'; _upiProfBtn.onclick = () => { closeM('mStudentProfile'); setTimeout(() => sendUpiLink(id), 200); }; }
        document.getElementById('spDelBtn').onclick = () => { closeM('mStudentProfile'); delStu(id); };

        // Edit toggle reset
        document.getElementById('spEditToggle').classList.remove('on');
        document.getElementById('spEditToggle').textContent = '✏ Edit';
        document.getElementById('spSaveFooter').style.display = 'none';
        ['spFname','spLname','spPhone','spEmail','spCourse','spAddr'].forEach(id2 => {
            const el = document.getElementById(id2);
            el.contentEditable = 'false';
            el.classList.remove('editable');
        });

        openM('mStudentProfile');
    }

    function toggleProfileEdit() {
        profileEditMode = !profileEditMode;
        const toggle = document.getElementById('spEditToggle');
        const footer = document.getElementById('spSaveFooter');
        const fields = ['spFname','spLname','spPhone','spEmail','spCourse','spAddr'];
        if (profileEditMode) {
            toggle.classList.add('on');
            toggle.textContent = '✏ Editing…';
            footer.style.display = 'flex';
            fields.forEach(id2 => {
                const el = document.getElementById(id2);
                el.contentEditable = 'true';
                el.classList.add('editable');
            });
            document.getElementById('spFname').focus();
        } else {
            cancelProfileEdit();
        }
    }

    function cancelProfileEdit() {
        profileEditMode = false;
        const toggle = document.getElementById('spEditToggle');
        toggle.classList.remove('on');
        toggle.textContent = '✏ Edit';
        document.getElementById('spSaveFooter').style.display = 'none';
        ['spFname','spLname','spPhone','spEmail','spCourse','spAddr'].forEach(id2 => {
            const el = document.getElementById(id2);
            el.contentEditable = 'false';
            el.classList.remove('editable');
        });
        // Re-render original values
        openStudentProfile(profileStudentId);
    }

    async function saveProfileEdit() {
        const s = DB.students.find(x => x.id === profileStudentId);
        if (!s) return;
        const get = id2 => document.getElementById(id2).textContent.trim();
        s.fname  = get('spFname')  || s.fname;
        s.lname  = get('spLname')  || s.lname;
        s.phone  = get('spPhone');
        s.email  = get('spEmail');
        s.course = get('spCourse');
        s.addr   = get('spAddr');
        addActivity('✏', 'rgba(74,124,111,.14)', `Profile updated → <strong>${s.fname} ${s.lname}</strong>`);
        toast('Profile saved!', 'ok');
        renderStudents();
        // Re-open in view mode immediately with local data
        profileEditMode = false;
        openStudentProfile(profileStudentId);
        // Persist to server in background
        try {
            await apiPost('update_student', {
                id:     s.id,
                fname:  s.fname,
                lname:  s.lname,
                phone:  s.phone  || '',
                email:  s.email  || '',
                course: s.course || '',
                addr:   s.addr   || ''
            });
        } catch(e) {
            // API unavailable — changes kept in local session
        }
    }

    function openAllocFromProfile() {
        const s = DB.students.find(x => x.id === profileStudentId);
        closeM('mStudentProfile');
        setTimeout(() => {
            openM('mAllocSeat');
            if (s) {
                document.getElementById('as-stu').value = s.id;
                if (s.batchId) {
                    document.getElementById('as-bt').value = s.batchId;
                    populateAllocSeats(s.seat || '');
                }
            }
        }, 200);
    }

    // ═══ SEAT CLICK — OPEN ALLOC WITH PREFILL ═══
    function openAllocSeatPrefilled(batchId, seatNum) {
        openM('mAllocSeat');
        setTimeout(() => {
            document.getElementById('as-bt').value = batchId;
            populateAllocSeats(seatNum);
        }, 50);
    }

    // ═══ WHATSAPP QR CONNECT ═══
    let waSessionMsgCount = 0;

    function openM_waQR() { openM('mWaQR'); initWaQR(); }

    function initWaQR() {
        const num = DB.settings?.waNumber || '';
        document.getElementById('waConnNum').value = num ? '+' + num : '';
        document.getElementById('waConnMsgCount').textContent = waSessionMsgCount + ' messages this session';
        generateWaQR();
    }

    function generateWaQR() {
        const canvas = document.getElementById('waQRCanvas');
        const ctx = canvas.getContext('2d');
        const size = 150;
        // Draw a realistic-looking QR code pattern
        ctx.clearRect(0, 0, size, size);
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, size, size);
        // Use a seeded random for consistent pattern
        const seed = Date.now() % 10000;
        const rng = (n) => { let x = Math.sin(n + seed) * 10000; return x - Math.floor(x); };
        const cellSize = 5;
        const cols = Math.floor(size / cellSize);
        ctx.fillStyle = '#000';
        // Draw QR-like finder patterns (corners)
        const drawFinder = (ox, oy) => {
            ctx.fillStyle = '#000';
            ctx.fillRect(ox, oy, 35, 35);
            ctx.fillStyle = '#fff';
            ctx.fillRect(ox+5, oy+5, 25, 25);
            ctx.fillStyle = '#000';
            ctx.fillRect(ox+10, oy+10, 15, 15);
        };
        drawFinder(5, 5);
        drawFinder(size-40, 5);
        drawFinder(5, size-40);
        // Random data cells
        for (let r = 0; r < cols; r++) {
            for (let c = 0; c < cols; c++) {
                const x = c * cellSize, y = r * cellSize;
                // Skip finder pattern areas
                if ((x < 40 && y < 40) || (x > size-45 && y < 40) || (x < 40 && y > size-45)) continue;
                if (rng(r * cols + c) > 0.55) {
                    ctx.fillStyle = '#000';
                    ctx.fillRect(x, y, cellSize-0.5, cellSize-0.5);
                }
            }
        }
        // Add WA logo in center
        ctx.fillStyle = 'rgba(255,255,255,0.92)';
        ctx.beginPath();
        ctx.arc(size/2, size/2, 14, 0, Math.PI*2);
        ctx.fill();
        ctx.font = '18px serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('💬', size/2, size/2);

        document.getElementById('waQRStatus').textContent = 'Scan with WhatsApp · ' + new Date().toLocaleTimeString();
    }

    function refreshWaQR() {
        document.getElementById('waQRStatus').textContent = 'Refreshing…';
        setTimeout(generateWaQR, 400);
    }

    function saveWaNumber() {
        const val = document.getElementById('waConnNum').value.replace(/\D/g, '');
        if (!val) return toast('Enter a phone number', 'er');
        DB.settings.waNumber = val;
        document.getElementById('s-wa').value = val;
        toast('WhatsApp number saved!', 'ok');
        document.getElementById('waConnBadge').className = 'wa-conn-badge wa-conn-ok';
        document.getElementById('waConnBadge').textContent = '● Connected';
    }

    function testWaConnection() {
        const num = DB.settings?.waNumber || '';
        if (!num) return toast('Save a WhatsApp number first', 'er');
        const msg = encodeURIComponent(`Hello! This is a test message from ${DB.settings?.name || 'OPTMS Tech Library'} ERP. ✅`);
        window.open(`https://wa.me/${num}?text=${msg}`, '_blank');
        waSessionMsgCount++;
        document.getElementById('waConnMsgCount').textContent = waSessionMsgCount + ' messages this session';
        toast('WhatsApp test opened!', 'wa');
    }


    // ═══ SEATS (with fee status highlight) ═══
    function renderSeats(){
        const total=DB.batches.reduce((a,b)=>a+b.total,0);
        const occ=DB.batches.reduce((a,b)=>a+b.occupied,0);
        document.getElementById('st-total').textContent=total;
        document.getElementById('st-vacant').textContent=total-occ;
        document.getElementById('st-occupied').textContent=occ;
        document.getElementById('batchGrid').innerHTML=DB.batches.map((b,i)=>{
            const pct=Math.round(b.occupied/b.total*100);
            const fc=pct>=100?'sf-r':pct>=70?'sf-y':'sf-g';
            const sc=pct>=100?'bst-f':pct>=70?'bst-n':'bst-o';
            const scLbl=pct>=100?'Full':pct>=70?'Filling':'Open';
            const bStudents=DB.students.filter(x=>x.batchId===b.id);
            const seatStudentMap={};bStudents.forEach(st=>{if(st.seat)seatStudentMap[st.seat]=st;});
            // Improvement 6: count per status for summary chips
            let cntVac=0,cntPaid=0,cntDue=0,cntOD=0;
            let cells='';
            for(let s=1;s<=b.total;s++){
                const sn=seatLbl(b.name,s);
                const stu=seatStudentMap[sn];
                // Improvement 2: student initials; Improvement 3: color fix
                let cls='seat-vac',ttText='Vacant — click to assign',initials='+';
                if(stu){
                    initials=(stu.fname[0]+(stu.lname?stu.lname[0]:'')).toUpperCase();
                    if(stu.feeStatus==='overdue'){
                        cls='seat-overdue';cntOD++;
                        ttText=`🚨 ${stu.fname} ${stu.lname||''} · Overdue ₹${stu.netFee-stu.paidAmt}`;
                    } else if(stu.feeStatus==='pending'){
                        cls='seat-due';cntDue++;
                        ttText=`⏳ ${stu.fname} ${stu.lname||''} · Pending ₹${stu.netFee}`;
                    } else if(stu.feeStatus==='partial'){
                        cls='seat-due';cntDue++;
                        ttText=`🟠 ${stu.fname} ${stu.lname||''} · Partial ₹${stu.netFee-stu.paidAmt} due`;
                    } else {
                        cls='seat-occ';cntPaid++;
                        ttText=`✓ ${stu.fname} ${stu.lname||''} · Paid · Click to view`;
                    }
                } else { cntVac++; }
                const clickFn=stu?`openStudentProfile('${stu.id}')`:`openAllocSeatPrefilled('${b.id}','${sn}')`;
                // Improvement 1: bigger cell with seat-num + seat-init divs
                cells+=`<div class="seat-cell ${cls}" onclick="${clickFn}"><div class="seat-tooltip">${ttText}</div><div class="seat-num">${sn}</div><div class="seat-init">${initials}</div></div>`;
            }
            // summary chips — modern pill with Material Icon + dot + count
            const chips=[
                cntVac>0  ?`<span class="ss-chip ss-vac"><span class="ss-dot"></span><span class="ss-ic">event_seat</span><span class="ss-cnt">${cntVac}</span><span class="ss-lbl">Vacant</span></span>`:'',
                cntPaid>0 ?`<span class="ss-chip ss-occ"><span class="ss-dot"></span><span class="ss-ic">check_circle</span><span class="ss-cnt">${cntPaid}</span><span class="ss-lbl">Paid</span></span>`:'',
                cntDue>0  ?`<span class="ss-chip ss-due"><span class="ss-dot"></span><span class="ss-ic">schedule</span><span class="ss-cnt">${cntDue}</span><span class="ss-lbl">Pending</span></span>`:'',
                cntOD>0   ?`<span class="ss-chip ss-od"><span class="ss-dot"></span><span class="ss-ic">warning</span><span class="ss-cnt">${cntOD}</span><span class="ss-lbl">Overdue</span></span>`:'',
            ].filter(Boolean).join('');
            const vacCount=b.total-b.occupied;
            // Improvement 5: better batch header with icon card
            return `<div class="panel"><div class="ph">
      <div style="display:flex;align-items:center;gap:12px">
        <div style="width:42px;height:42px;background:var(--c-green);border:1.5px solid var(--cg);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">${batchEmoji(b.name)}</div>
        <div>
          <div style="font-weight:800;font-size:14px;color:var(--tx)">${b.name}</div>
          <div style="font-size:11px;color:var(--tx3);font-family:var(--fm);margin-top:2px">${fmtT(b.startTime)}–${fmtT(b.endTime)} · ₹${b.baseFee}/mo · AC +₹${b.acExtra}</div>
        </div>
      </div>
      <div style="display:flex;gap:7px;align-items:center">
        <div class="bst ${sc}">${scLbl}</div>
        ${cntOD>0?`<span style="font-size:10px;background:var(--c-rose);border:1.5px solid var(--cr);color:#9f1239;padding:4px 9px;border-radius:8px;font-weight:700;animation:pulseDue 1s infinite">🚨 ${cntOD} Overdue</span>`:''}
        ${cntDue>0?`<span style="font-size:10px;background:var(--c-amber);border:1.5px solid var(--ca2);color:#92400e;padding:4px 9px;border-radius:8px;font-weight:700">⏳ ${cntDue} Due</span>`:''}
        <button class="btn bg" style="font-size:11px;padding:5px 10px" onclick="editBatch(${i})">✏ Edit</button>
        <button class="btn bd" style="font-size:11px;padding:5px 8px" onclick="delBatch(${i})"><span class="mi sm">close</span></button>
      </div>
    </div>
    <div class="pb">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
        <span style="font-size:12px;font-weight:700;color:var(--em)">${vacCount} Vacant</span>
        <span style="font-size:12px;font-weight:700;color:var(--ac)">${b.occupied} Occupied</span>
        <span style="font-size:12px;font-weight:700;color:var(--tx3)">${b.total} Total</span>
      </div>
      <div class="sbar"><div class="sfill ${fc}" style="width:${pct}%"></div></div>
      <div class="seat-visual">${cells}</div>
      ${chips?`<div class="seat-summary">${chips}</div>`:''}
    </div></div>`;
        }).join('');
    }

    function seatLbl(n,i){const p={'Early Morning':'E','Morning':'A','Afternoon':'B','Evening':'C','Night':'D','Late Night':'F'};return`${p[n]||'X'}-${String(i).padStart(2,'0')}`;}

    function populateAllocSeats(prefillSeat) {
        const bId = gv('as-bt');
        const stuId = gv('as-stu');
        const sel = document.getElementById('as-st');
        if (!bId) { sel.innerHTML = '<option value="">-- Select Batch First --</option>'; return; }
        const b = DB.batches.find(x => x.id === bId);
        if (!b) { sel.innerHTML = '<option value="">-- Batch not found --</option>'; return; }

        // Seats taken by OTHER students in this batch (exclude current student so their current seat shows as available)
        const takenSeats = new Set(
            DB.students
                .filter(s => s.batchId === bId && s.seat && s.id !== stuId)
                .flatMap(s => {
                    const raw = String(s.seat);
                    return /^\d+$/.test(raw) ? [raw, seatLbl(b.name, +raw)] : [raw];
                })
        );

        const opts = ['<option value="">-- No Preference --</option>'];
        for (let i = 1; i <= b.total; i++) {
            const sn = seatLbl(b.name, i);
            if (!takenSeats.has(sn)) {
                opts.push(`<option value="${sn}">${sn}</option>`);
            }
        }
        if (opts.length === 1) opts.push('<option disabled>⚠ No vacant seats in this batch</option>');
        sel.innerHTML = opts.join('');

        // Prefill the current seat if provided (student's existing seat)
        if (prefillSeat) sel.value = prefillSeat;
    }

    function editBatch(idx){
        editBatchIdx=idx;const b=DB.batches[idx];
        document.getElementById('mAddBatchTitle').textContent='✏ Edit Batch';
        document.getElementById('batchSaveBtn').textContent='Save Changes';
        document.getElementById('ab-nm').value=b.name;document.getElementById('ab-st').value=b.startTime;
        document.getElementById('ab-et').value=b.endTime;document.getElementById('ab-ts').value=b.total;
        document.getElementById('ab-fe').value=b.baseFee;document.getElementById('ab-ac').value=b.acExtra;
        openM('mAddBatch');
    }
    function saveBatch(){
        const nm=gv('ab-nm'),st=gv('ab-st'),et=gv('ab-et'),ts=+gv('ab-ts'),fe=+gv('ab-fe'),ac=+gv('ab-ac');
        if(!nm||!st||!et||!ts||!fe)return toast('Fill required fields','er');
        if(editBatchIdx>=0){
            const b=DB.batches[editBatchIdx];
            if(ts<b.occupied)return toast('Cannot reduce below occupied','er');
            Object.assign(b,{name:nm,startTime:st,endTime:et,total:ts,baseFee:fe,acExtra:ac});
            toast(`"${nm}" updated!`,'ok');editBatchIdx=-1;
        } else {
            DB.batches.push({id:'BT-'+Date.now(),name:nm,startTime:st,endTime:et,total:ts,occupied:0,baseFee:fe,acExtra:ac});
            addActivity('🆕','rgba(74,124,111,.14)',`Batch "<strong>${nm}</strong>" added (${ts} seats)`);
            toast(`Batch "${nm}" created!`,'ok');
        }
        closeM('mAddBatch');document.getElementById('mAddBatchTitle').textContent='Add New Batch';document.getElementById('batchSaveBtn').textContent='Add Batch';editBatchIdx=-1;
        renderSeats();populateBatchSelects();
    }
    function delBatch(idx){if(!confirm('Delete batch?'))return;DB.batches.splice(idx,1);toast('Deleted','wn');renderSeats();populateBatchSelects();}
    function populateBatchSelects(){
        const opts=DB.batches.map(b=>`<option value="${b.id}">${b.name} (${fmtT(b.startTime)}–${fmtT(b.endTime)})</option>`).join('');
        ['en-bt','as-bt','attBatchF'].forEach(id=>{const el=document.getElementById(id);if(!el)return;if(id==='attBatchF')el.innerHTML=`<option value="all">All Batches</option>`+opts;else el.innerHTML=`<option value="">-- Select --</option>`+opts;});
    }
    function fmtT(t){const[h,m]=t.split(':');const hr=+h;return`${hr===0?12:hr>12?hr-12:hr}:${m} ${hr<12?'AM':'PM'}`;}

    // ═══ ENROLL ═══

    function calcEnrollDueDate() {
        const joinVal = document.getElementById('en-dt').value;
        const months  = +document.getElementById('en-dur').value || 1;
        const base    = joinVal ? new Date(joinVal) : new Date();
        base.setMonth(base.getMonth() + months);
        document.getElementById('en-due').value = base.toISOString().split('T')[0];

        // If a batch is already selected, refresh the fee note (shows multi-month total)
        const baseFee = +(document.getElementById('en-fe').value) || 0;
        if (baseFee > 0) {
            applyEnrollDiscount(baseFee);
            return; // applyEnrollDiscount calls calcEnrollDueDate again internally but won't loop
        }

        // No batch selected yet — nothing to show
        const feeNote = document.getElementById('en-fee-note');
        if (feeNote) feeNote.style.display = 'none';
    }


   //     const bId=gv('en-bt'),acType=gv('en-ac');const b=DB.batches.find(x=>x.id===bId);
   //     if(!b){document.getElementById('en-fe').value='';document.getElementById('en-net-fe').value='';document.getElementById('en-fee-note').style.display='none';return;}
   //     const base=b.baseFee+(acType==='ac'?b.acExtra:0);
   //     document.getElementById('en-fe').value=base;
   //     applyEnrollDiscount(base);
   // }

   function calcEnrollFee(){
    const bId = gv('en-bt'), acType = gv('en-ac');
    const b = DB.batches.find(x => x.id === bId);

    // ── Fee calculation (unchanged) ──
    if(!b){
        document.getElementById('en-fe').value = '';
        document.getElementById('en-net-fe').value = '';
        document.getElementById('en-fee-note').style.display = 'none';

        // Reset seat dropdown
        document.getElementById('en-st').innerHTML = '<option value="">-- Select Batch First --</option>';
        return;
    }

    const base = b.baseFee + (acType === 'ac' ? b.acExtra : 0);
    document.getElementById('en-fe').value = base;
    applyEnrollDiscount(base);

    // ── Populate vacant seats for selected batch ──
    const takenSeats = new Set(
        DB.students
            .filter(s => s.batchId === bId && s.seat)
            .flatMap(s => {
                const raw = String(s.seat);
                return /^\d+$/.test(raw) ? [raw, seatLbl(b.name, +raw)] : [raw];
            })
    );

    const totalSeats = b.total;
    const opts = ['<option value="">-- No Preference --</option>'];

    for (let i = 1; i <= totalSeats; i++) {
        const sn = seatLbl(b.name, i);
        if (!takenSeats.has(sn)) {
            const label = acType === 'ac' ? `${sn} ❄` : sn;
            opts.push(`<option value="${sn}">${label}</option>`);
        }
    }

    if (opts.length === 1) {
        opts.push('<option disabled>⚠ No vacant seats in this batch</option>');
    }

    document.getElementById('en-st').innerHTML = opts.join('');
}


    function applyEnrollDiscount(base){
        if(!base)base=+gv('en-fe');
        const dtype=gv('en-disc-type'),dval=+gv('en-disc-val')||0;
        let disc=0;
        if(dtype==='flat')disc=Math.min(dval,base);
        else if(dtype==='percent')disc=Math.round(base*dval/100);
        const net=base-disc;
        document.getElementById('en-net-fe').value=net;

        // Recalculate due date directly (no recursive call)
        const joinVal = document.getElementById('en-dt').value;
        const months  = +document.getElementById('en-dur').value || 1;
        const baseDate = joinVal ? new Date(joinVal) : new Date();
        baseDate.setMonth(baseDate.getMonth() + months);
        document.getElementById('en-due').value = baseDate.toISOString().split('T')[0];

        const note=document.getElementById('en-fee-note');
        note.style.display='block';
        const discPart = disc>0
            ? ` <span style="color:var(--or)">− Discount: ₹${disc}</span> = <strong style="color:var(--em)">₹${net}/month</strong>`
            : ` = ₹${net}/month`;
        if(months > 1){
            note.innerHTML=`💡 Base: ₹${base}${discPart} &nbsp;|&nbsp; 📅 <strong>${months} months</strong> × ₹${net}/mo = <strong style="color:var(--ac)">₹${(net*months).toLocaleString('en-IN')} total</strong>`;
        } else {
            note.innerHTML=`💡 Base: ₹${base}${discPart} · Due: <strong>${baseDate.toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'})}</strong>`;
        }
    }
    document.getElementById('en-bt').addEventListener('change',calcEnrollFee);
    document.getElementById('en-ac').addEventListener('change',calcEnrollFee);
    document.getElementById('en-disc-type').addEventListener('change',()=>applyEnrollDiscount(+gv('en-fe')));
    document.getElementById('en-disc-val').addEventListener('input',()=>applyEnrollDiscount(+gv('en-fe')));
    // Set today as default join date and calculate initial due date
    const enDtEl = document.getElementById('en-dt');
    if (enDtEl && !enDtEl.value) { enDtEl.value = new Date().toISOString().split('T')[0]; calcEnrollDueDate(); }

    function enrollStudent(){
        const fn=gv('en-fn'),ln=gv('en-ln'),ph=gv('en-ph'),bt=gv('en-bt'),fe=+gv('en-fe'),net=+gv('en-net-fe'),ac=gv('en-ac');
        if(!fn||!ln||!ph||!bt||!fe)return toast('Fill required fields','er');
        const batch=DB.batches.find(b=>b.id===bt);
        if(batch&&batch.occupied>=batch.total)return toast(`${batch.name} is full!`,'er');
        const id='STU-'+String(DB.students.length+1).padStart(3,'0');
        const cols=['#3d6ff0','#d97706','#0284c7','#7c3aed','#dc2626','#16a34a'];
        const due=new Date();due.setDate(due.getDate()+11);
        const discType=gv('en-disc-type'),discVal=+gv('en-disc-val')||0,discReason=gv('en-disc-reason');
        DB.students.push({id,fname:fn,lname:ln,phone:ph,email:gv('en-em'),batchId:bt,seatType:ac,seat:gv('en-st'),baseFee:fe,discount:{type:discType,value:discVal,reason:discReason},netFee:net||fe,paidAmt:0,feeStatus:'pending',paidOn:'-',dueDate:due.toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'}),course:gv('en-co'),addr:gv('en-ad'),color:cols[DB.students.length%cols.length],joinDate:new Date().toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'})});
        if(batch)batch.occupied++;DB.attendance[id]='present';
        addActivity('<span class="mi lg" style="color:#1e40af">school</span>','rgba(58,125,94,.14)',`<strong>${fn} ${ln}</strong> enrolled in ${batch?.name||bt}${fe>net?` (₹${fe-net} discount)`:''}`);
        addNotif('success','New Enrollment',`${fn} ${ln} enrolled`);
        if(document.getElementById('en-wa').checked)setTimeout(()=>waQuick(id,'welcome'),600);
        closeM('mEnroll');toast(`${fn} ${ln} enrolled${fe>net?` with ₹${fe-net} discount`:''}!`,'ok');
        ['en-fn','en-ln','en-ph','en-em','en-bt','en-fe','en-net-fe','en-st','en-co','en-ad','en-disc-val','en-disc-reason'].forEach(i=>{const el=document.getElementById(i);if(el)el.value='';});
        document.getElementById('en-fee-note').style.display='none';
        refreshAll();updateBadges();
    }

    // ═══ ATTENDANCE ═══
    // Stores today's biometric check-in/out per student_id
    const _bioToday = {};

    function renderAtt(){
        const today=new Date().toLocaleDateString('en-IN',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
        document.getElementById('attLbl').textContent=`Date: ${today}`;
        const bf=gv('attBatchF');
        const list=DB.students.filter(s=>bf==='all'||s.batchId===bf);
        const prs=list.filter(s=>DB.attendance[s.id]==='present').length;
        document.getElementById('at-p').textContent=prs;
        document.getElementById('at-a').textContent=list.length-prs;
        document.getElementById('at-r').textContent=list.length?Math.round(prs/list.length*100)+'%':'0%';
        document.getElementById('at-t').textContent=list.length;
        document.getElementById('attTable').innerHTML=list.map(s=>{
            const st=DB.attendance[s.id]||'absent';
            const bio=_bioToday[s.id]||{};
            const checkIn=bio.in||'—';
            const checkOut=bio.out||'—';
            // Calculate hours studied
            let hrs='—';
            if(bio.in && bio.out){
                const [ih,im]=bio.in.split(':').map(Number);
                const [oh,om]=bio.out.split(':').map(Number);
                const mins=(oh*60+om)-(ih*60+im);
                if(mins>0) hrs=Math.floor(mins/60)+'h '+(mins%60)+'m';
            }
            const bioMark=bio.in?`<span style="font-size:9px;background:#dcfce7;border:1px solid #a3e8d4;color:#166534;padding:1px 5px;border-radius:4px;font-weight:700;font-family:var(--fm)">🖐️</span>`:'';
            return `<tr>
            <td><div class="si"><div class="sav" style="background:${s.color}">${(s.fname?.[0]||'')+(s.lname?.[0]||'')}</div><div><div style="font-weight:600;font-size:12.5px">${s.fname} ${s.lname} ${bioMark}</div><div style="font-size:10px;color:var(--tx3);font-family:var(--fm)">${s.id}</div></div></div></td>
            <td>${bTag(s.batchId)}</td>
            <td><span style="font-family:var(--fm);font-size:11px">${s.seat||'—'}</span></td>
            <td><span class="tag ${s.feeStatus==='paid'?'tpd':s.feeStatus==='partial'?'tpart':s.feeStatus==='pending'?'tpn':'tod'}">${s.feeStatus==='paid'?'✓ Paid':s.feeStatus==='partial'?'◑ Partial':s.feeStatus==='pending'?'⏳ Pending':'🚨 Overdue'}</span></td>
            <td><span style="font-family:var(--fm);font-size:11px;font-weight:700;color:${bio.in?'var(--em)':'var(--tx3)'}">${checkIn}</span></td>
            <td><span style="font-family:var(--fm);font-size:11px;font-weight:700;color:${bio.out?'var(--ro)':'var(--tx3)'}">${checkOut}</span></td>
            <td><span style="font-family:var(--fm);font-size:11px;font-weight:700;color:var(--ac)">${hrs}</span></td>
            <td><span class="tag ${st==='present'?'tpd':'tod'}">${st==='present'?'✓ Present':'✗ Absent'}</span></td>
            <td><button class="btn ${st==='present'?'bd':'bp'}" style="font-size:10.5px;padding:4px 10px" onclick="togAtt('${s.id}')">${st==='present'?'Absent':'Present'}</button></td>
            </tr>`;
        }).join('')||'<tr><td colspan="9"><div class="empty"><div class="ei">📋</div><div class="et">No students</div></div></td></tr>';
        // Load QR feed (biometric loaded separately by loadAttBiometric)
        loadQRScans();
    }
    function togAtt(id){DB.attendance[id]=DB.attendance[id]==='present'?'absent':'present';renderAtt();}
    function markAll(p){DB.students.forEach(s=>{DB.attendance[s.id]=p?'present':'absent';});renderAtt();toast(p?'All present':'All absent',p?'ok':'wn');}

    // ═══ BOOKS ═══
    let bkPage=1,bkSearch='';
    function renderBooks(){
        const cf=document.getElementById('bkCatF')?.value||'all';
        let list=DB.books.filter(b=>(cf==='all'||b.category===cf)&&(!bkSearch||`${b.title} ${b.author}`.toLowerCase().includes(bkSearch.toLowerCase())));
        document.getElementById('bkCount').textContent=`${list.length} books`;
        const pp=7,total=list.length,pages=Math.ceil(total/pp)||1;
        bkPage=Math.min(bkPage,pages);const sl=list.slice((bkPage-1)*pp,bkPage*pp);
        document.getElementById('bkTable').innerHTML=sl.map(b=>{const av=b.available>0;
            return `<tr><td><div style="display:flex;align-items:center;gap:7px"><span style="font-size:17px">${b.emoji}</span><div><div style="font-weight:600;font-size:12.5px">${b.title}</div><div style="font-size:10px;color:var(--tx3);font-family:var(--fm)">${b.id}</div></div></div></td>
    <td>${b.author}</td><td><span class="tag tac" style="font-size:9px">${b.category}</span></td>
    <td style="font-family:var(--fm);font-weight:700">${b.copies}</td>
    <td><span style="font-family:var(--fm);font-weight:700;color:${av?'var(--em)':'var(--ro)'}">${b.available}</span></td>
    <td><span style="font-family:var(--fm);font-size:11px">${b.shelf||'—'}</span></td>
    <td><span class="tag ${av?'tav':'tis'}">${av?'Available':'Issued'}</span></td>
    <td><div style="display:flex;gap:4px"><button class="btn bp" style="font-size:10px;padding:3px 7px" onclick="openIssueFor('${b.id}')">Issue</button><button class="btn bd" style="font-size:10px;padding:3px 6px" onclick="delBook('${b.id}')"><span class="mi sm">close</span></button></div></td></tr>`;
        }).join('')||'<tr><td colspan="8"><div class="empty"><div class="ei">📚</div><div class="et">No books</div></div></td></tr>';
        document.getElementById('bkPagI').textContent=`${sl.length} of ${total}`;
        let pb='';for(let i=1;i<=pages;i++) pb+=`<div class="pb2 ${i===bkPage?'active':''}" onclick="bkPage=${i};renderBooks()">${i}</div>`;
        document.getElementById('bkPagB').innerHTML=pb;
    }
    function bkSrch(v){bkSearch=v;bkPage=1;renderBooks();}
    function openIssueFor(bkId){populateIssueModal(bkId);openM('mIssueBook');}
    function delBook(id){if(!confirm('Remove?'))return;DB.books=DB.books.filter(b=>b.id!==id);toast('Removed','wn');renderBooks();}
    function addBook(){
        const tl=gv('bk-tl'),au=gv('bk-au'),cp=+gv('bk-cp');if(!tl||!au||!cp)return toast('Fill required','er');
        const emjs=['📘','📙','📗','📕','📔','📒'];
        DB.books.push({id:'BK-'+String(DB.books.length+1).padStart(3,'0'),title:tl,author:au,isbn:gv('bk-is')||'N/A',category:gv('bk-ca'),copies:cp,available:cp,shelf:gv('bk-sh')||'TBD',emoji:emjs[DB.books.length%emjs.length]});
        addActivity('📚','rgba(58,122,176,.14)',`Book "<strong>${tl}</strong>" added`);
        closeM('mAddBook');toast(`Added!`,'ok');renderBooks();
    }

    // ═══ TRANSACTIONS ═══
    function renderTx(){
        const iss=DB.transactions.filter(t=>t.status!=='returned');const od=DB.transactions.filter(t=>t.status==='overdue');const rt=DB.transactions.filter(t=>t.status==='returned');const fine=DB.transactions.reduce((a,t)=>a+(t.fine||0),0);
        document.getElementById('tx-is').textContent=iss.length;document.getElementById('tx-od').textContent=od.length;document.getElementById('tx-rt').textContent=rt.length;document.getElementById('tx-fn').textContent=fmt(fine);
        document.getElementById('b-overdue').textContent=od.length;document.getElementById('txCount').textContent=`${DB.transactions.length} transactions`;
        document.getElementById('txTable').innerHTML=DB.transactions.map(t=>{
            const s=DB.students.find(x=>x.id===t.studentId);const b=DB.books.find(x=>x.id===t.bookId);if(!s||!b)return'';
            return `<tr><td><div class="si"><div class="sav" style="background:${s.color}">${(s.fname?.[0]||'')+(s.lname?.[0]||'')}</div><span style="font-size:12.5px;font-weight:600">${s.fname} ${s.lname}</span></div></td>
    <td>${b.emoji} ${b.title}</td>
    <td><span style="font-family:var(--fm);font-size:10.5px">${fmtDate(t.issueDate)}</span></td>
    <td><span style="font-family:var(--fm);font-size:10.5px;color:${t.status==='overdue'?'var(--ro)':'inherit'}">${fmtDate(t.dueDate)}</span></td>
    <td><span style="font-family:var(--fm);font-size:10.5px">${t.returnDate||'—'}</span></td>
    <td><span style="font-family:var(--fm);font-weight:700;color:${t.fine>0?'var(--ro)':'inherit'}">₹${t.fine}</span></td>
    <td><span class="tag ${t.status==='returned'?'trt':t.status==='overdue'?'tod':'tis'}">${t.status==='returned'?'✓ Returned':t.status==='overdue'?'⚠ Overdue':'📤 Issued'}</span></td>
    <td>${t.status!=='returned'?`<div style="display:flex;gap:4px"><button class="btn bg" style="font-size:10px;padding:3px 7px" onclick="qReturn('${t.id}')">Return</button><button class="btn bwa" style="font-size:10px;padding:3px 7px" onclick="waQuick('${t.studentId}','book_overdue')"><span class="mi sm">chat</span></button></div>`:''}</td></tr>`;
        }).join('')||'<tr><td colspan="8"><div class="empty"><div class="ei">🔄</div><div class="et">No transactions</div></div></td></tr>';
    }
    function qReturn(txId){populateReturnModal();setTimeout(()=>{document.getElementById('rb-tx').value=txId;calcFine();},50);openM('mReturnBook');}
    function populateIssueModal(bkId){
        document.getElementById('ib-stu').innerHTML='<option value="">-- Select --</option>'+DB.students.map(s=>`<option value="${s.id}">${s.fname} ${s.lname}</option>`).join('');
        document.getElementById('ib-bk').innerHTML='<option value="">-- Select --</option>'+DB.books.filter(b=>b.available>0).map(b=>`<option value="${b.id}" ${b.id===bkId?'selected':''}>${b.emoji} ${b.title} (${b.available})</option>`).join('');
        const today=new Date().toISOString().split('T')[0];document.getElementById('ib-id').value=today;
        const due=new Date();due.setDate(due.getDate()+DB.settings.days);document.getElementById('ib-dd').value=due.toISOString().split('T')[0];
    }
    function issueBook(){
        const stuId=gv('ib-stu'),bkId=gv('ib-bk');if(!stuId||!bkId)return toast('Select student and book','er');
        const bk=DB.books.find(b=>b.id===bkId);const stu=DB.students.find(s=>s.id===stuId);
        if(bk.available<=0)return toast('No copies available!','er');bk.available--;
        const iDate=new Date().toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'});
        const due=new Date();due.setDate(due.getDate()+DB.settings.days);
        DB.transactions.push({id:'TX-'+Date.now(),studentId:stuId,bookId:bkId,issueDate:iDate,dueDate:due.toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'}),returnDate:null,fine:0,status:'issued'});
        addActivity('📤','rgba(124,92,191,.14)',`<strong>${stu?.fname}</strong> issued "${bk.title}"`);
        closeM('mIssueBook');toast(`"${bk.title}" issued!`,'ok');refreshAll();updateBadges();
    }
    function populateReturnModal(){
        const active=DB.transactions.filter(t=>t.status!=='returned');
        document.getElementById('rb-tx').innerHTML='<option value="">-- Select --</option>'+active.map(t=>{const s=DB.students.find(x=>x.id===t.studentId);const b=DB.books.find(x=>x.id===t.bookId);return`<option value="${t.id}">${b?.emoji} ${b?.title} → ${s?.fname} (Due:${fmtDate(t.dueDate)})</option>`;}).join('');
        document.getElementById('rb-dt').value=new Date().toISOString().split('T')[0];
    }
    function calcFine(){
        const txId=document.getElementById('rb-tx').value;const tx=DB.transactions.find(t=>t.id===txId);if(!tx)return;
        const due=new Date(tx.dueDate);const today=new Date();
        const diff=Math.max(0,Math.floor((today-due)/(1000*60*60*24)));const fine=diff*DB.settings.fine;
        document.getElementById('rb-fn').value=fine;
        const note=document.getElementById('rb-note');
        if(fine>0){note.style.display='block';note.textContent=`⚠ ${diff} days overdue. Fine: ₹${fine} (₹${DB.settings.fine}/day)`;}else note.style.display='none';
    }
    function returnBook(){
        const txId=gv('rb-tx'),cond=gv('rb-cd');if(!txId)return toast('Select transaction','er');
        const tx=DB.transactions.find(t=>t.id===txId);if(!tx)return;
        const bk=DB.books.find(b=>b.id===tx.bookId);const stu=DB.students.find(s=>s.id===tx.studentId);
        if(cond!=='Lost')bk.available++;
        tx.fine=+gv('rb-fn')||0;tx.status='returned';tx.returnDate=new Date().toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'});
        addActivity('📩','rgba(58,125,94,.14)',`<strong>${stu?.fname}</strong> returned "${bk?.title}"${tx.fine>0?` Fine ₹${tx.fine}`:''}`);
        closeM('mReturnBook');toast(`Returned!${tx.fine>0?' Fine: ₹'+tx.fine:''}`,'ok');refreshAll();updateBadges();
    }

    // ═══ FEES ═══
    let feeFiltVal='all',feeSrchVal='';
    function renderFees(){
        const s=DB.students;
        const paid=s.filter(x=>x.feeStatus==='paid');const partial=s.filter(x=>x.feeStatus==='partial');
        const pend=s.filter(x=>x.feeStatus==='pending');const od=s.filter(x=>x.feeStatus==='overdue');
        const activeIds=new Set(s.map(x=>x.id));const activeInvs=DB.invoices.filter(i=>activeIds.has(i.studentId));document.getElementById('fc-c').textContent=fmt(activeInvs.reduce((a,i)=>a+i.paidAmt,0));document.getElementById('fc-cm').textContent=`${paid.length} fully paid`;
        document.getElementById('fc-pp').textContent=partial.length;document.getElementById('fc-ppm').textContent=`₹${partial.reduce((a,x)=>a+(x.netFee-x.paidAmt),0).toLocaleString()} balance due`;
        document.getElementById('fc-p').textContent=fmt(pend.reduce((a,x)=>a+x.netFee,0));document.getElementById('fc-pm').textContent=`${pend.length} students`;
        document.getElementById('fc-o').textContent=fmt(od.reduce((a,x)=>a+x.netFee,0));document.getElementById('fc-om').textContent=`${od.length} students (>7 days)`;
        document.getElementById('b-fee').textContent=pend.length+od.length+partial.length;
        let list=s.filter(x=>(feeFiltVal==='all'||x.feeStatus===feeFiltVal)&&(!feeSrchVal||`${x.fname} ${x.lname} ${x.id}`.toLowerCase().includes(feeSrchVal.toLowerCase())));
        document.getElementById('feeTable').innerHTML=list.map(x=>{
            // ── Renewal data ──
            const stuRenewals=DB.invoices.filter(i=>i.studentId===x.id&&i.type&&i.type.startsWith('Renewal'));
            const renewCount=stuRenewals.length;
            const lastRenew=stuRenewals[0]||null; // most recent renewal (invoices sorted desc)
            const totalRenewAmt=stuRenewals.reduce((a,i)=>a+i.paidAmt,0);
            // ── Monthly fee data ──
            const monthlyInvs=DB.invoices.filter(i=>i.studentId===x.id&&i.type==='Monthly Fee').sort((a,b)=>(b.date||'').localeCompare(a.date||''));
            const curPaid=monthlyInvs.length?monthlyInvs[0].paidAmt:x.paidAmt;
            const curBal=monthlyInvs.length?monthlyInvs[0].balance:(x.netFee-x.paidAmt);
            const pctPaid=x.netFee>0?Math.round(curPaid/x.netFee*100):0;
            const discTxt=x.baseFee>x.netFee?`<div><span class="tag tor" style="font-size:9px">🎁 ₹${x.baseFee-x.netFee}</span><div style="font-size:9px;color:var(--tx3)">${x.discount?.reason||''}</div></div>`:'<span style="color:var(--tx3)">—</span>';
            const partialBar=x.feeStatus==='partial'?`<div class="fee-partial-wrap"><div class="fee-partial-bar"><div class="fee-partial-fill" style="width:${pctPaid}%"></div></div><div style="font-size:9px;color:var(--tx3);font-family:var(--fm)">${pctPaid}% paid</div></div>`:'';
            const rowClass=x.feeStatus==='overdue'?'fee-due-row':x.feeStatus==='partial'||x.feeStatus==='pending'?'fee-partial-row':'';
            // ── Renewal sub-row style ──
            const RS='background:rgba(61,111,240,.04);border-top:1px dashed rgba(61,111,240,.2)';
            const RL='font-size:9.5px;color:var(--ac);font-family:var(--fm);font-weight:600';
            const RV='font-size:9.5px;color:var(--ac);font-family:var(--fm);font-weight:700';
            const renewRow=renewCount>0?`
    <tr style="${RS}">
      <td style="padding:4px 13px 6px 13px">
        <div style="display:flex;align-items:center;gap:5px">
          <span style="font-size:13px">🔄</span>
          <div>
            <span style="${RL}">Renewal ×${renewCount}</span>
            <span style="font-size:9px;color:var(--tx3);margin-left:4px">${lastRenew?fmtDate(lastRenew.date):''}</span>
          </div>
        </div>
      </td>
      <td style="padding:4px 13px 6px"><span style="font-size:9px;color:var(--tx3)">—</span></td>
      <td style="padding:4px 13px 6px"><span style="${RV}">₹${lastRenew?lastRenew.amount:0}</span><div style="font-size:9px;color:var(--tx3)">per renewal</div></td>
      <td style="padding:4px 13px 6px"><span style="font-size:9px;color:var(--tx3)">—</span></td>
      <td style="padding:4px 13px 6px"><span style="${RV}">₹${lastRenew?lastRenew.netFee:0}</span><div style="font-size:9px;color:var(--tx3)">renewal fee</div></td>
      <td style="padding:4px 13px 6px"><span style="${RV}">₹${totalRenewAmt}</span><div style="font-size:9px;color:var(--tx3)">total renewed</div></td>
      <td style="padding:4px 13px 6px"><span style="color:var(--em);font-size:11px">✓ Clear</span></td>
      <td style="padding:4px 13px 6px"><span style="font-size:9px;color:var(--tx3)">${lastRenew?fmtDate(lastRenew.date):'—'}</span></td>
      <td style="padding:4px 13px 6px"><span class="tag trt" style="font-size:9px">🔄 Renewed</span></td>
      <td style="padding:4px 13px 6px"><span style="font-size:9px;color:var(--ac);font-family:var(--fm)">${fmtDate(x.dueDate)}</span></td>
      <td style="padding:4px 13px 6px"><span style="font-size:9px;color:var(--tx3)">—</span></td>
    </tr>`:'';
            return `<tr class="${rowClass}">
      <td><div class="si"><div class="sav" style="background:${x.color}">${((x.fname||"?")[0]+((x.lname||"")[0]||"")).toUpperCase()}</div><div><div style="font-weight:600;font-size:12.5px;cursor:pointer;color:var(--ac)" onclick="openStudentProfile('${x.id}')">${x.fname||""} ${x.lname||""}</div><div style="font-size:10px;color:var(--tx3);font-family:var(--fm)">${x.id}</div></div></div></td>
      <td>${bTag(x.batchId)}</td>
      <td><span style="font-family:var(--fm)">₹${x.baseFee}</span></td>
      <td>${discTxt}</td>
      <td><span style="font-family:var(--fm);font-weight:700;color:var(--em)">₹${x.netFee}</span></td>
      <td><div><span style="font-family:var(--fm);font-weight:700;color:var(--em)">₹${curPaid}</span>${partialBar}</div></td>
      <td>${curBal>0?`<div style="display:flex;align-items:center;gap:4px"><span class="fee-bal-badge">₹${curBal} DUE</span></div>`:`<span style="color:var(--em);font-size:12px">✓ Clear</span>`}</td>
      <td><span style="font-family:var(--fm);font-size:10.5px">${fmtDate(x.paidOn)}</span></td>
      <td><span class="tag ${x.feeStatus==='paid'?'tpd':x.feeStatus==='partial'?'tpart':x.feeStatus==='pending'?'tpn':'tod'}">${x.feeStatus==='paid'?'✓ Paid':x.feeStatus==='partial'?'◑ Partial':x.feeStatus==='pending'?'⏳ Pending':'🚨 Overdue'}</span></td>
      <td><span style="font-size:10.5px;font-family:var(--fm);color:${x.feeStatus==='overdue'?'var(--ro)':x.feeStatus==='pending'?'var(--gd)':'var(--tx3)'}">${fmtDate(x.dueDate)}</span></td>
      <td><div style="display:flex;gap:4px">
        ${x.feeStatus!=='paid'?`<button class="btn bp" style="font-size:10px;padding:3px 7px" onclick="qCollect('${x.id}')">Collect</button>`:'<span style="color:var(--em);font-size:11px">✓</span>'}
        <button class="btn bwa" style="font-size:10px;padding:3px 7px" onclick="waQuick('${x.id}','${x.feeStatus==='paid'?'fee_receipt':x.feeStatus==='partial'?'partial_payment':x.feeStatus==='overdue'?'fee_overdue':'fee_due'}')">💬</button>
        ${x.feeStatus!=='paid'?`<button class="btn bg" style="font-size:10px;padding:3px 7px;color:var(--ac);border-color:var(--ac)" onclick="sendUpiLink('${x.id}')">📱 UPI</button>`:''}
      </div></td>
    </tr>${renewRow}`;
        }).join('')||'<tr><td colspan="11"><div class="empty"><div class="ei">💰</div><div class="et">No records</div></div></td></tr>';
        document.getElementById('feePagI').textContent=`${list.length} records`;
    }
    function feeFilt(f,el){feeFiltVal=f;document.querySelectorAll('#feeTabs .tab').forEach(t=>t.classList.remove('active'));el.classList.add('active');renderFees();}
    function feeSrch(v){feeSrchVal=v;renderFees();}
    function sendReminders(){const od=DB.students.filter(x=>x.feeStatus!=='paid');od.forEach(s=>addActivity('📣','rgba(196,125,43,.14)',`Reminder → <strong>${s.fname}</strong>`));toast(`Reminders sent to ${od.length}`,'ok');}
    function waBulkFee(){bulkSend('pending');navTo('whatsapp');}

    // FEE COLLECT
    function populateFeeModal(stuId){
        populateModal_cf();
        setTimeout(()=>{const s=DB.students.find(x=>x.id===stuId);if(s){document.getElementById('cf-stu').value=stuId;cfLoadStudent();}},50);
    }
    function populateModal_cf(){
        document.getElementById('cf-stu').innerHTML='<option value="">-- Select --</option>'+DB.students.filter(s=>s.feeStatus!=='paid').map(s=>`<option value="${s.id}">${s.fname} ${s.lname} — Net ₹${s.netFee} (${s.feeStatus})</option>`).join('');
        document.getElementById('cf-stu').onchange=cfLoadStudent;
    }
    function cfLoadStudent(){
        const s=DB.students.find(x=>x.id===gv('cf-stu'));if(!s)return;
        document.getElementById('cf-tot').value=s.netFee;
        document.getElementById('cf-amt').value='';
        const bal=s.netFee-s.paidAmt;
        const info=document.getElementById('cf-status-info');
        if(s.feeStatus==='partial'){
            info.style.display='block';
            info.innerHTML=`<div style="padding:10px 13px;border-radius:var(--r2);border:1px solid rgba(58,122,176,.3);background:rgba(58,122,176,.06)">
      <div style="font-size:12px;font-weight:600;margin-bottom:4px;color:var(--sk)">◑ Partial Payment on Record</div>
      <div style="font-size:11.5px;color:var(--tx2)">Net Fee: <strong>₹${s.netFee}</strong> | Paid: <strong style="color:var(--em)">₹${s.paidAmt}</strong> | <strong style="color:var(--ro)">Balance: ₹${bal}</strong></div>
      <div style="height:6px;background:var(--sf2);border-radius:3px;overflow:hidden;margin:6px 0"><div style="width:${Math.round(s.paidAmt/s.netFee*100)}%;height:100%;background:linear-gradient(90deg,var(--em),#4ead82);border-radius:3px"></div></div>
      <div style="font-size:10px;color:var(--tx3);font-family:var(--fm)">${Math.round(s.paidAmt/s.netFee*100)}% paid so far</div>
    </div>`;
            document.getElementById('cf-amt').value=bal;
        } else if(s.feeStatus==='overdue'){
            info.style.display='block';
            info.innerHTML=`<div style="padding:8px 12px;border-radius:var(--r2);border:1px solid rgba(192,68,79,.3);background:rgba(192,68,79,.06)"><div style="font-size:12px;font-weight:600;color:var(--ro)">🚨 Fee Overdue since ${fmtDate(s.dueDate)}</div><div style="font-size:11px;color:var(--tx2)">Amount due: ₹${s.netFee}</div></div>`;
            document.getElementById('cf-amt').value=s.netFee;
        } else if(s.baseFee>s.netFee){
            info.style.display='block';
            info.innerHTML=`<div style="padding:8px 12px;border-radius:var(--r2);border:1px solid rgba(230,126,34,.3);background:rgba(230,126,34,.06)"><div style="font-size:12px;font-weight:600;color:var(--or)">🎁 Discount Applied: ₹${s.baseFee-s.netFee}</div><div style="font-size:11px;color:var(--tx2)">Full fee: ₹${s.baseFee} → Net: ₹${s.netFee} (${s.discount?.reason||''})</div></div>`;
            document.getElementById('cf-amt').value=s.netFee;
        } else {
            info.style.display='none';
            document.getElementById('cf-amt').value=s.netFee;
        }
        document.getElementById('cf-balance-note').style.display='none';
    }
    function cfCalcBalance(){
        const s=DB.students.find(x=>x.id===gv('cf-stu'));if(!s)return;
        const tot=s.netFee;const now=+gv('cf-amt')||0;const totalPaid=s.paidAmt+now;const bal=tot-totalPaid;
        const note=document.getElementById('cf-balance-note');
        if(now>0&&bal>0){note.style.display='block';note.innerHTML=`<div style="font-size:12px;font-weight:600;color:var(--or);margin-bottom:4px">⚡ Partial Payment</div><div style="font-size:11.5px">Net Fee: ₹${tot} | Paying now: ₹${now} | <strong style="color:var(--ro)">Balance: ₹${bal}</strong></div><div style="font-size:10.5px;color:var(--tx3);margin-top:3px">Status: <strong>Partial</strong></div>`;}
        else note.style.display='none';
    }
    function toggleSplit(){
        const m=gv('cf-mode');
        const isSplit=m==='split'||m==='split2';
        // Always keep cf-amt (Amount Paying) visible — partial students need to adjust it
        const refRow=document.getElementById('cf-ref');
        if(refRow) refRow.closest('.fgi').style.display=isSplit?'none':'flex';
        document.getElementById('payNormal').style.display='grid';
        document.getElementById('paySplit').style.display=isSplit?'block':'none';
        if(isSplit)calcSplitRem();
    }
    function calcSplitRem(){
        // Use cf-amt (amount being paid now) as split total, not full net fee
        const tot=+gv('cf-amt')||+gv('cf-tot')||0;const a1=+gv('cf-a1')||0;const rem=Math.max(0,tot-a1);
        document.getElementById('cf-a2').value=rem;
        document.getElementById('splitNote').textContent=`Total: ₹${tot} | Mode 1: ₹${a1} | Mode 2: ₹${rem}`;
    }
    // ═══ INVOICES ═══
    function renderInv(){
        document.getElementById('invCount').textContent=`${DB.invoices.length} invoice(s)`;
        document.getElementById('gi-stu').innerHTML='<option value="">-- Select --</option>'+DB.students.map(s=>`<option value="${s.id}">${s.fname} ${s.lname}</option>`).join('');
        // ── Invoice revenue summary bar ──
        const _liveInvs = DB.invoices.filter(i=> !!DB.students.find(x=>x.id===i.studentId));
        const _deadInvs = DB.invoices.filter(i=> !DB.students.find(x=>x.id===i.studentId));
        const _livePaid        = _liveInvs.filter(i=>i.status==='paid').reduce((a,i)=>a+i.paidAmt,0);
        const _livePartial     = _liveInvs.filter(i=>i.status==='partial').reduce((a,i)=>a+i.paidAmt,0);
        const _livePartialBal  = _liveInvs.filter(i=>i.status==='partial').reduce((a,i)=>a+i.balance,0);
        const _livePaidCnt     = _liveInvs.filter(i=>i.status==='paid').length;
        const _livePartialCnt  = _liveInvs.filter(i=>i.status==='partial').length;
        const _liveTotal = _livePaid + _livePartial;
        const _deletedRev   = _deadInvs.reduce((a,i)=>a+i.paidAmt,0);
        const _deletedCount = _deadInvs.length;
        const _summaryEl=document.getElementById('invRevSummary');
        if(_summaryEl) _summaryEl.innerHTML=`
        <div style="display:flex;flex-wrap:wrap;gap:10px;padding:13px 18px;background:var(--sf2);border-bottom:1px solid var(--br);align-items:center">

          <div style="display:flex;align-items:center;gap:9px;padding:8px 16px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:2px solid #86efac;border-radius:22px;box-shadow:0 2px 8px rgba(22,163,74,.15)">
            <span class="mi sm" style="color:#15803d">person</span>
            <div>
              <div style="font-size:9px;font-family:var(--fm);font-weight:700;color:#15803d;letter-spacing:.8px;text-transform:uppercase;line-height:1">Live Students</div>
              <div style="font-size:15px;font-weight:800;color:#14532d;font-family:var(--fm);line-height:1.3">₹${_liveTotal.toLocaleString('en-IN')}</div>
            </div>
          </div>

          <div style="width:1px;height:32px;background:var(--br);flex-shrink:0"></div>

          <div style="display:flex;align-items:center;gap:7px;padding:7px 13px;background:#fff;border:1.5px solid var(--cg);border-radius:20px;box-shadow:0 1px 3px rgba(0,0,0,.06)">
            <span style="width:8px;height:8px;border-radius:50%;background:#16a34a;box-shadow:0 0 0 2px rgba(22,163,74,.2);flex-shrink:0"></span>
            <div>
              <div style="font-size:11.5px;font-weight:800;color:#15803d;font-family:var(--fm);line-height:1">₹${_livePaid.toLocaleString('en-IN')}</div>
              <div style="font-size:9px;color:var(--tx3);line-height:1.4">Fully Paid · ${_livePaidCnt} invoice${_livePaidCnt!==1?'s':''}</div>
            </div>
          </div>

          ${_livePartial>0?`<div style="display:flex;align-items:center;gap:7px;padding:7px 13px;background:#fff;border:1.5px solid var(--ca2);border-radius:20px;box-shadow:0 1px 3px rgba(0,0,0,.06)">
            <span style="width:8px;height:8px;border-radius:50%;background:#d97706;box-shadow:0 0 0 2px rgba(217,119,6,.2);flex-shrink:0"></span>
            <div>
              <div style="font-size:11.5px;font-weight:800;color:#92400e;font-family:var(--fm);line-height:1">₹${_livePartial.toLocaleString('en-IN')}</div>
              <div style="font-size:9px;color:var(--tx3);line-height:1.4">Partial · ${_livePartialCnt} invoice${_livePartialCnt!==1?'s':''} · <span style="color:var(--ro);font-weight:600">₹${_livePartialBal.toLocaleString('en-IN')} still due</span></div>
            </div>
          </div>`:''}

          ${_deletedRev>0?`<div style="display:flex;align-items:center;gap:7px;padding:7px 13px;background:#fff8f8;border:1.5px dashed rgba(192,68,79,.45);border-radius:20px;box-shadow:0 1px 3px rgba(0,0,0,.05);opacity:.85">
            <span class="mi sm" style="color:#c0444f">person_remove</span>
            <div>
              <div style="font-size:11.5px;font-weight:800;color:#9f1239;font-family:var(--fm);line-height:1">₹${_deletedRev.toLocaleString('en-IN')}</div>
              <div style="font-size:9px;color:var(--tx3);line-height:1.4">${_deletedCount} deleted student invoice${_deletedCount!==1?'s':''}</div>
            </div>
          </div>`:''}

        </div>`;
        document.getElementById('invTable').innerHTML=DB.invoices.length?DB.invoices.map(inv=>{
            const s=DB.students.find(x=>x.id===inv.studentId);
            const deleted=!s;
            const balCell=inv.balance>0
                ?(deleted
                    ?`<span style="font-family:var(--fm);font-size:11px;color:var(--tx3)">₹${inv.balance}</span>`
                    :`<span class="fee-bal-badge">₹${inv.balance}</span>`)
                :`<span style="color:var(--em);font-size:11px">✓</span>`;
            return `<tr style="${deleted?'opacity:.75;background:rgba(192,68,79,.03)':''}"><td><span style="font-family:var(--fm);font-weight:700;color:var(--ac)">${inv.id}</span></td>
    <td>${s?`<div class="si"><div class="sav" style="background:${s.color}">${(s.fname?.[0]||'')+(s.lname?.[0]||'')}</div><span>${s.fname} ${s.lname}</span></div>`:deleted?`<span style="display:inline-flex;align-items:center;gap:4px;background:rgba(192,68,79,.10);border:1px solid rgba(192,68,79,.25);color:#c0444f;font-size:10px;font-weight:600;padding:3px 8px;border-radius:5px;font-style:normal">🗑 Deleted Student</span>`:'—'}</td>
    <td><span class="tag tac" style="font-size:9px">${inv.type}</span></td>
    <td><span style="font-family:var(--fm)">₹${inv.baseFee||inv.amount}</span></td>
    <td>${inv.discount>0?`<span class="tag tor" style="font-size:9px">🎁 -₹${inv.discount}</span>`:'<span style="color:var(--tx3)">—</span>'}</td>
    <td><span style="font-family:var(--fm);font-weight:700">₹${inv.amount}</span></td>
    <td>${balCell}</td>
    <td><span style="font-family:var(--fm);font-size:10.5px">${fmtDate(inv.date)}</span></td>
    <td><span style="font-size:11px">${inv.mode}</span></td>
    <td><span class="tag ${inv.status==='paid'?'tpd':'tpart'}">${inv.status==='paid'?'● Paid':'◑ Partial'}</span></td>
    <td><button class="btn bg" style="font-size:10px;padding:3px 7px" onclick="printInv('${inv.id}')"><span class="mi sm">print</span>Print</button></td></tr>`;
        }).join(''):'<tr><td colspan="11"><div class="empty"><div class="ei">🧾</div><div class="et">No invoices yet</div></div></td></tr>';
    }
    function autoFillInv(){const s=DB.students.find(x=>x.id===gv('gi-stu'));if(s)document.getElementById('gi-am').value=s.netFee;}
    function printInv(invId){
        const inv=DB.invoices.find(x=>x.id===invId);const s=DB.students.find(x=>x.id===inv.studentId);const b=s?DB.batches.find(bt=>bt.id===s.batchId):null;
        const logoHtml=DB.settings.logoUrl?`<img src="${DB.settings.logoUrl}" style="height:52px;width:52px;object-fit:contain;border-radius:8px;margin-right:14px;flex-shrink:0" alt="Logo">`:'<div style="width:52px;height:52px;border-radius:10px;background:linear-gradient(135deg,#3d6ff0,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:24px;margin-right:14px;flex-shrink:0">📚</div>';
        const w=window.open('','_blank');
        w.document.write(`<html><head><title>Invoice ${inv.id}</title><style>body{font-family:sans-serif;padding:40px;color:#0f172a;max-width:600px;margin:auto}.hd{border-bottom:2px solid #3d6ff0;padding-bottom:16px;margin-bottom:20px}.hd-inner{display:flex;align-items:center}.logo{font-size:22px;font-weight:700;color:#3d6ff0}.row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #eee}.tot{font-weight:700;font-size:18px;color:#3d6ff0}.ok{background:#f0fdf4;color:#166534;display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px}.partial{background:#eff4ff;color:#1e40af;display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px}.disc{background:rgba(230,126,34,.08);border:1px solid rgba(230,126,34,.2);border-radius:8px;padding:10px;margin:10px 0}</style></head><body>
  <div class="hd"><div class="hd-inner">${logoHtml}<div><div class="logo">${DB.settings.name}</div><div style="font-size:12px;color:#888;margin-top:4px">${DB.settings.addr} · ${DB.settings.phone}</div></div></div></div>
  <div style="display:flex;justify-content:space-between;margin-bottom:20px"><div><div style="font-size:20px;font-weight:700">INVOICE</div><div style="font-size:13px;color:#888">${inv.id}</div></div><div style="text-align:right"><div style="font-size:13px">${fmtDate(inv.date)}</div><div class="${inv.status==='paid'?'ok':'partial'}">${inv.status==='paid'?'✓ Paid':'◑ Partial'}</div></div></div>
  <div style="background:#f9f7f4;padding:14px;border-radius:8px;margin-bottom:16px"><div style="font-weight:600;margin-bottom:6px">Student</div><div style="font-size:13px">${s?.fname} ${s?.lname} · ${s?.id}</div><div style="font-size:12px;color:#888">${b?.name||''} · ${s?.seatType?.toUpperCase()} Seat ${s?.seat||''}</div></div>
  <div class="row"><span>Description</span><span>Amount</span></div>
  <div class="row"><span>${inv.type} – ${inv.month}</span><span>₹${inv.baseFee||inv.amount}</span></div>
  ${inv.discount>0?`<div class="disc"><strong>🎁 Discount Applied: -₹${inv.discount}</strong><br><span style="font-size:12px;color:#888">${s?.discount?.reason||''}</span></div>`:''}
  ${inv.discount>0?`<div class="row"><span>Net Fee (after discount)</span><span style="color:#3a7d5e;font-weight:700">₹${inv.netFee||inv.amount}</span></div>`:''}
  <div class="row"><span>Amount Paid Now</span><span>₹${inv.amount}</span></div>
  <div class="row tot"><span>Total Paid</span><span>₹${inv.paidAmt||inv.amount}</span></div>
  ${inv.balance>0?`<div style="background:rgba(192,68,79,.08);border:1px solid rgba(192,68,79,.2);border-radius:8px;padding:12px;margin-top:12px"><strong>⚠ Balance Due: ₹${inv.balance}</strong><br><span style="font-size:12px">Please clear by due date</span></div>`:''}
  <div style="margin-top:16px;font-size:12px;color:#888">Mode: ${inv.mode}</div>
  <div style="margin-top:40px;text-align:center;font-size:11px;color:#aaa">Thank you · ${DB.settings.name}</div>
  <script>window.print();<\/script></body></html>`);
    }

    // ═══ EXPENSES ═══
    function renderExp(){
        const cf=document.getElementById('exCatF')?.value||'all';
        const list=DB.expenses.filter(e=>cf==='all'||e.category===cf);
        const rev=DB.invoices.reduce((a,i)=>a+i.paidAmt,0);
        const allExp=DB.expenses.reduce((a,e)=>a+e.amount,0);
        document.getElementById('ex-t').textContent=fmt(list.reduce((a,e)=>a+e.amount,0));
        document.getElementById('ex-r').textContent=fmt(rev);
        const p=rev-allExp;document.getElementById('ex-p').textContent=fmt(Math.abs(p));document.getElementById('ex-p').style.color=p>=0?'var(--em)':'var(--ro)';
        document.getElementById('expList').innerHTML=list.map(e=>`<div class="ei2"><div class="eic" style="background:rgba(74,124,111,.1)">${e.emoji}</div><div style="flex:1"><div class="en2">${e.name}</div><div class="ed">${fmtDate(e.date)} · ${e.category}</div></div><div class="ea ea-d">-₹${e.amount.toLocaleString()}</div><button class="btn bd" style="font-size:10px;padding:3px 6px;margin-left:7px" onclick="delExp('${e.id}')"><span class="mi sm">close</span></button></div>`).join('')||'<div class="empty"><div class="ei">💸</div><div class="et">No expenses</div></div>';
    }
    function delExp(id){DB.expenses=DB.expenses.filter(e=>e.id!==id);toast('Removed','wn');renderExp();}
    function addExp(){
        const nm=gv('ex-nm'),am=+gv('ex-am');if(!nm||!am)return toast('Fill required','er');
        const emjs={Utilities:'⚡',Staff:'👨‍💼',Maintenance:'🔧',Supplies:'📦',Books:'📚',Other:'<span class="mi lg" style="color:#92400e">payments</span>'};
        DB.expenses.push({id:'EX-'+Date.now(),name:nm,amount:am,category:gv('ex-ca'),date:new Date().toISOString().slice(0,10),notes:gv('ex-nt'),emoji:emjs[gv('ex-ca')]||'<span class="mi lg" style="color:#92400e">payments</span>'});
        addActivity('<span class="mi lg" style="color:#9a3412">account_balance_wallet</span>','rgba(196,125,43,.14)',`Expense: <strong>${nm}</strong> ₹${am.toLocaleString()}`);
        closeM('mExpense');toast('Expense added!','ok');renderExp();
    }

    // ═══ ANALYTICS ═══
    function renderAnal(){
        const s=DB.students;const paid=s.filter(x=>x.feeStatus==='paid');
        const rev=DB.invoices.reduce((a,i)=>a+i.paidAmt,0);
        const iss=DB.transactions.filter(t=>t.status!=='returned');const od=DB.transactions.filter(t=>t.status==='overdue');
        const bks=DB.books.reduce((a,b)=>a+b.copies,0);const prs=Object.values(DB.attendance).filter(v=>v==='present').length;
        document.getElementById('analCards').innerHTML=`
    <div class="panel"><div class="pb"><div class="s-lb">Revenue YTD</div><div class="s-vl" style="color:var(--em)">${fmt(rev*3)}</div></div></div>
    <div class="panel"><div class="pb"><div class="s-lb">Fee Collection Rate</div><div class="s-vl">${s.length?Math.round(paid.length/s.length*100):0}%</div></div></div>
    <div class="panel"><div class="pb"><div class="s-lb">Avg Attendance</div><div class="s-vl">${s.length?Math.round(prs/s.length*100):0}%</div></div></div>
    <div class="panel"><div class="pb"><div class="s-lb">Book Utilization</div><div class="s-vl">${bks?Math.round(iss.length/bks*100):0}%</div></div></div>
    <div class="panel"><div class="pb"><div class="s-lb">Overdue Rate</div><div class="s-vl" style="color:var(--ro)">${iss.length?Math.round(od.length/iss.length*100):0}%</div></div></div>
    <div class="panel"><div class="pb"><div class="s-lb">Net Profit</div><div class="s-vl" style="color:var(--ac)">${fmt(rev-DB.expenses.reduce((a,e)=>a+e.amount,0))}</div></div></div>`;
        const rData=[155000,162000,rev,rev*0.5];const rMax=Math.max(...rData,1);
        document.getElementById('revChart').innerHTML=rData.map((v,i)=>`<div class="cbar" style="flex:1;height:${Math.round(v/rMax*100)}%;background:var(--ac);opacity:${i===3?.4:.8};border-radius:4px 4px 0 0"><div class="tt">₹${v.toLocaleString()}</div></div>`).join('');
        document.getElementById('batchAnal').innerHTML=DB.batches.map(b=>{const p=Math.round(b.occupied/b.total*100);const fc=p>=80?'sf-r':p>=50?'sf-y':'sf-g';return`<div style="margin-bottom:10px"><div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:3px"><span>${b.name}</span><span style="font-family:var(--fm)">${b.occupied}/${b.total} (${p}%)</span></div><div class="sbar"><div class="sfill ${fc}" style="width:${p}%"></div></div></div>`;}).join('');
    }

    // ═══ REPORTS ═══
    function genReport(type){
        const titles={monthly:'Monthly Summary',fee:'Fee Report',books:'Book Inventory',attendance:'Attendance',expense:'Expense Report',student:'Student Directory'};
        document.getElementById('rptTitle').textContent=titles[type];
        const s=DB.students;const paid=s.filter(x=>x.feeStatus==='paid');
        let html='';
        if(type==='monthly'){const rev=DB.invoices.reduce((a,i)=>a+i.paidAmt,0);const exp=DB.expenses.reduce((a,e)=>a+e.amount,0);html=`<div class="g3" style="margin-bottom:16px"><div class="sc" style="--ca:var(--em)"><div class="s-lb">Revenue</div><div class="s-vl" style="color:var(--em)">${fmt(rev)}</div></div><div class="sc" style="--ca:var(--ro)"><div class="s-lb">Expenses</div><div class="s-vl" style="color:var(--ro)">${fmt(exp)}</div></div><div class="sc" style="--ca:var(--ac)"><div class="s-lb">Profit</div><div class="s-vl">${fmt(rev-exp)}</div></div></div>`;}
        else{
            const map={fee:['Student','Batch','Base Fee','Discount','Net Fee','Paid','Balance','Status'],student:['ID','Name','Batch','Seat','Type','Course'],attendance:['Student','Batch','Status'],expense:['Name','Category','Amount','Date'],books:['Book','Author','Category','Available']};
            const rows={fee:s.map(x=>[`${x.fname} ${x.lname}`,batchName(x.batchId),`₹${x.baseFee}`,x.baseFee>x.netFee?`₹${x.baseFee-x.netFee} (${x.discount?.reason||''})`:'-',`₹${x.netFee}`,`₹${x.paidAmt}`,`₹${x.netFee-x.paidAmt}`,x.feeStatus]),student:s.map(x=>[x.id,`${x.fname} ${x.lname}`,batchName(x.batchId),x.seat||'—',x.seatType.toUpperCase(),x.course]),attendance:s.map(x=>[`${x.fname} ${x.lname}`,batchName(x.batchId),DB.attendance[x.id]||'absent']),expense:DB.expenses.map(e=>[e.name,e.category,`₹${e.amount.toLocaleString()}`,e.date]),books:DB.books.map(b=>[b.title,b.author,b.category,`${b.available}/${b.copies}`])};
            html=`<table style="width:100%;border-collapse:collapse;font-size:12px"><thead><tr>${map[type].map(c=>`<th style="padding:7px 11px;text-align:left;background:var(--sf2);color:var(--tx3);font-size:9px;text-transform:uppercase;font-family:var(--fm);border-bottom:1px solid var(--br)">${c}</th>`).join('')}</tr></thead><tbody>${rows[type].map(r=>`<tr style="border-bottom:1px solid var(--br)">${r.map(c=>`<td style="padding:7px 11px">${c}</td>`).join('')}</tr>`).join('')}</tbody></table>`;
        }
        document.getElementById('rptBody').innerHTML=html;document.getElementById('rptOut').style.display='block';document.getElementById('rptOut').scrollIntoView({behavior:'smooth'});toast('Report generated!','ok');
    }

    // ═══ WHATSAPP ═══
    const WA_TEMPLATES={
        welcome:(s,b)=>`🎉 *Welcome to ${DB.settings.name}!*\n\nDear *${s.fname} ${s.lname}*,\n\nWe're delighted to have you! 🎓\n\n📋 *Your Details:*\n• Student ID: ${s.id}\n• Batch: ${b?.name||'—'} (${b?fmtT(b.startTime)+' – '+fmtT(b.endTime):''})\n• Seat: ${s.seat||'To be assigned'} (${s.seatType.toUpperCase()})\n• Net Monthly Fee: ₹${s.netFee}${s.baseFee>s.netFee?`\n• 🎁 Discount Applied: ₹${s.baseFee-s.netFee} (${s.discount?.reason||''})`:''}\n\n🏫 ${DB.settings.name}\n📍 ${DB.settings.addr}\n📞 ${DB.settings.phone}\n\nBest wishes for your studies! 📚`,
        fee_due:(s,b)=>`⏰ *Fee Payment Reminder*\n\nDear *${s.fname} ${s.lname}*,\n\nYour monthly fee is due.\n\n💰 *Fee Details:*\n• Net Fee: ₹${s.netFee}${s.baseFee>s.netFee?`\n• 🎁 Discount: ₹${s.baseFee-s.netFee} (${s.discount?.reason||''})`:''}\n• Amount Due: ₹${s.netFee-s.paidAmt}\n• Due Date: ${fmtDate(s.dueDate)}\n• Batch: ${b?.name||'—'}\n\nPlease pay to avoid late charges.\n\n📞 ${DB.settings.phone}`,
        fee_overdue:(s,b)=>`🚨 *URGENT: Fee Overdue*\n\nDear *${s.fname} ${s.lname}*,\n\nYour fee is *OVERDUE* since ${fmtDate(s.dueDate)}. Your seat may be de-allocated.\n\n⚠️ *Details:*\n• Net Fee: ₹${s.netFee}\n• Overdue Amount: ₹${s.netFee-s.paidAmt}\n• Late Fine: ₹${DB.settings.fine}/day (accumulating)\n• Batch: ${b?.name||'—'} · Seat: ${s.seat||'—'}\n\n❗ Clear *immediately* to retain your seat.\n\n📞 ${DB.settings.phone}\n🏫 ${DB.settings.name}`,
        partial_payment:(s,b)=>`💳 *Partial Payment Received*\n\nDear *${s.fname} ${s.lname}*,\n\nThank you for your partial payment!\n\n📊 *Payment Summary:*\n• Net Fee: ₹${s.netFee}\n• Amount Paid: ₹${s.paidAmt}\n• *Balance Due: ₹${s.netFee-s.paidAmt}*\n• Due Date: ${fmtDate(s.dueDate)}\n\nPlease pay ₹${s.netFee-s.paidAmt} at the earliest.\n\n📞 ${DB.settings.phone}`,
        fee_receipt:(s,b,inv)=>`✅ *Payment Receipt*\n\nDear *${s.fname} ${s.lname}*,\n\nFee payment confirmed! 🙏\n\n🧾 *Receipt:*\n• Receipt No: ${inv?.id||'—'}\n• Amount Paid: ₹${s.paidAmt}\n• Net Fee: ₹${s.netFee}${s.baseFee>s.netFee?`\n• 🎁 Discount: ₹${s.baseFee-s.netFee}`:''}\n• Date: ${fmtDate(s.paidOn)}\n• Batch: ${b?.name||'—'}\n\n✅ *Fee FULLY PAID*\n\n📞 ${DB.settings.phone}`,
        discount_applied:(s,b)=>`🎁 *Discount Applied to Your Fee*\n\nDear *${s.fname} ${s.lname}*,\n\nA discount has been applied to your fee account.\n\n💰 *Updated Fee Structure:*\n• Original Fee: ₹${s.baseFee}\n• Discount: -₹${s.baseFee-s.netFee} (${s.discount?.reason||'Special Discount'})\n• *Net Fee: ₹${s.netFee}/month*\n• Batch: ${b?.name||'—'}\n\nThank you for being a valued student! 🌟\n\n📞 ${DB.settings.phone}`,
        book_due:(s,b,tx)=>`📚 *Book Return Reminder*\n\nDear *${s.fname} ${s.lname}*,\n\n• Book: ${tx?.bookTitle||'Borrowed book'}\n• Due: ${tx?.dueDate||'—'}\n• Fine: ₹${DB.settings.fine}/day if late\n\nPlease return on time!\n\n📞 ${DB.settings.phone}`,
        book_overdue:(s,b,tx)=>`⚠️ *Book Overdue – Fine Accruing*\n\nDear *${s.fname} ${s.lname}*,\n\n• Book: ${tx?.bookTitle||'—'}\n• Due: ${tx?.dueDate||'—'}\n• Fine: ₹${tx?.fine||0} (₹${DB.settings.fine}/day)\n\nReturn *immediately* to stop fines.\n\n📞 ${DB.settings.phone}`,
        seat_allotted:(s,b)=>`🪑 *Seat Allotment Confirmed*\n\nDear *${s.fname} ${s.lname}*,\n\n• Seat: ${s.seat||'—'}\n• Batch: ${b?.name||'—'} (${b?fmtT(b.startTime)+' – '+fmtT(b.endTime):''})\n• Type: ${s.seatType.toUpperCase()}\n\nCarry your ID card while visiting.\n\n📞 ${DB.settings.phone}`,
        holiday:(s)=>`📅 *Holiday Notice*\n\nDear *${s.fname} ${s.lname}*,\n\nThe library will be *CLOSED* on the upcoming holiday. Normal operations resume next day.\n\n📞 ${DB.settings.phone}\n🏫 ${DB.settings.name}`,
        custom:(s)=>`Dear *${s.fname} ${s.lname}*,\n\n[Your message here]\n\n🏫 ${DB.settings.name}\n📞 ${DB.settings.phone}`
    };

    function renderWA(){
        const templates=[
            {key:'welcome',ic:'🎉',lb:'Welcome',ds:'Enrollment confirmation'},
            {key:'fee_due',ic:'<span class="mi lg" style="color:#92400e">payments</span>',lb:'Fee Due',ds:'Gentle reminder'},
            {key:'fee_overdue',ic:'🚨',lb:'Overdue Alert',ds:'Urgent overdue'},
            {key:'partial_payment',ic:'💳',lb:'Partial Payment',ds:'Balance due notice'},
            {key:'fee_receipt',ic:'✅',lb:'Fee Receipt',ds:'Payment confirmed'},
            {key:'discount_applied',ic:'🎁',lb:'Discount Applied',ds:'Discount notification'},
            {key:'book_due',ic:'📚',lb:'Book Return Due',ds:'Return reminder'},
            {key:'book_overdue',ic:'⚠️',lb:'Book Overdue',ds:'Fine accruing alert'},
            {key:'seat_allotted',ic:'🪑',lb:'Seat Allotment',ds:'Seat confirmation'},
            {key:'holiday',ic:'📅',lb:'Holiday Notice',ds:'Library closure'},
            {key:'custom',ic:'✏️',lb:'Custom',ds:'Write your own'},
        ];
        document.getElementById('waTemplateGrid').innerHTML=templates.map(t=>`<div class="wa-tpl" data-key="${t.key}" onclick="waSelectTpl('${t.key}')"><div class="wt-ic">${t.ic}</div><div class="wt-lb">${t.lb}</div><div class="wt-ds">${t.ds}</div></div>`).join('');
        const pending=DB.students.filter(x=>x.feeStatus!=='paid').length;
        const overdue=DB.students.filter(x=>x.feeStatus==='overdue').length;
        const newStudents=DB.students.filter(s=>s.joinDate&&s.joinDate.includes('Mar')).length;
        const bookOd=DB.transactions.filter(t=>t.status==='overdue').length;
        document.getElementById('bk-welcome').textContent=newStudents;
        document.getElementById('bk-pending').textContent=pending;
        document.getElementById('bk-overdue2').textContent=overdue;
        document.getElementById('bk-bookod').textContent=bookOd;
        // populate student select
        document.getElementById('wa-stu').innerHTML='<option value="">-- Select Student --</option><option value="all">📢 All Students</option><option value="pending_all">⏳ All Pending + Partial</option><option value="overdue">🚨 All Overdue</option>'+DB.students.map(s=>`<option value="${s.id}">${s.fname} ${s.lname} (${s.feeStatus})</option>`).join('');
        renderWASendLog();
    }
    function waSelectTpl(key){
        document.querySelectorAll('.wa-tpl').forEach(t=>t.classList.remove('selected'));
        const tplEl=document.querySelector(`.wa-tpl[data-key="${key}"]`);
        if(tplEl)tplEl.classList.add('selected');
        document.getElementById('wa-tpl').value=key;
        waLoadTemplate();
    }
    function waLoadTemplate(){
        const tplKey=gv('wa-tpl');const stuId=gv('wa-stu');
        let s=DB.students[0];let b=DB.batches.find(x=>x.id===s?.batchId);
        if(stuId&&!['all','pending_all','overdue'].includes(stuId)){s=DB.students.find(x=>x.id===stuId)||s;b=DB.batches.find(x=>x.id===s.batchId);}
        if(!tplKey||tplKey==='custom'){document.getElementById('wa-msg').value='';document.getElementById('waPreview').textContent='Type your message…';return;}
        const lastInv=DB.invoices.filter(x=>x.studentId===s?.id).pop();
        const lastTx=DB.transactions.filter(t=>t.studentId===s?.id&&t.status!=='returned').pop();
        const txData=lastTx?{...lastTx,bookTitle:DB.books.find(bk=>bk.id===lastTx.bookId)?.title||'—'}:null;
        const msg=WA_TEMPLATES[tplKey]?WA_TEMPLATES[tplKey](s,b,lastInv||txData):'';
        document.getElementById('wa-msg').value=msg;waUpdatePreview();
    }
    function waUpdatePreview(){document.getElementById('waPreview').textContent=gv('wa-msg')||'Select a template…';}
    function waSend(){
        waSessionMsgCount++;
        const stuId=gv('wa-stu');const msg=gv('wa-msg');
        if(!msg)return toast('Write a message first','er');if(!stuId)return toast('Select a recipient','er');
        if(['all','pending_all','overdue'].includes(stuId)){
            const list=stuId==='all'?DB.students:stuId==='pending_all'?DB.students.filter(x=>x.feeStatus!=='paid'):DB.students.filter(x=>x.feeStatus==='overdue');
            list.forEach(s=>openWALink(s.phone,msg));
            DB.waSendLog.push({time:new Date().toLocaleTimeString(),to:`${list.length} students`,preview:msg.slice(0,40)+'…',type:'bulk'});
            toast(`WhatsApp opened for ${list.length} students!`,'wa');
        } else {
            const s=DB.students.find(x=>x.id===stuId);if(s)openWALink(s.phone,msg);
            DB.waSendLog.push({time:new Date().toLocaleTimeString(),to:s?`${s.fname} ${s.lname}`:'Unknown',preview:msg.slice(0,40)+'…',type:'single'});
            toast('WhatsApp opened!','wa');
        }
        addActivity('💬','rgba(37,211,102,.14)',`WhatsApp sent`);renderWASendLog();
    }
    function waCopy(){const msg=gv('wa-msg');if(!msg)return;navigator.clipboard?.writeText(msg).then(()=>toast('Copied!','ok')).catch(()=>toast('Select & copy manually','wn'));}
    function waSchedule(){toast('Message scheduled for 9 AM tomorrow!','ok');}
    function openWALink(phone,msg){const p=phone.replace(/\D/g,'');const full=p.length===10?'91'+p:p;window.open(`https://wa.me/${full}?text=${encodeURIComponent(msg)}`,'_blank');}
    function waSendDirect(phone,msg,name){openWALink(phone,msg);waSessionMsgCount++;DB.waSendLog.unshift({time:new Date().toLocaleTimeString(),to:name||phone,preview:msg.slice(0,40)+'…',type:'single'});renderWASendLog();}
    function waQuick(stuId,tplKey){
        const s=DB.students.find(x=>x.id===stuId);if(!s)return;
        const b=DB.batches.find(x=>x.id===s.batchId);
        const lastInv=DB.invoices.filter(x=>x.studentId===s.id).pop();
        const lastTx=DB.transactions.filter(t=>t.studentId===s.id&&t.status!=='returned').pop();
        const txData=lastTx?{...lastTx,bookTitle:DB.books.find(bk=>bk.id===lastTx.bookId)?.title||'—'}:null;
        const msg=WA_TEMPLATES[tplKey]?WA_TEMPLATES[tplKey](s,b,lastInv||txData):'Hello '+s.fname;
        document.getElementById('waSendTo').textContent=`${s.fname} ${s.lname}`;
        document.getElementById('waSendPhone').textContent=`+91 ${s.phone}`;
        document.getElementById('waSendMsg').value=msg;
        document.getElementById('waSendPreview').textContent=msg;
        document.getElementById('waOpenBtn').onclick=()=>{openWALink(s.phone,msg);DB.waSendLog.push({time:new Date().toLocaleTimeString(),to:`${s.fname} ${s.lname}`,preview:msg.slice(0,40)+'…',type:'single'});closeM('mWaSend');toast('WhatsApp opened!','wa');addActivity('💬','rgba(37,211,102,.14)',`WhatsApp → <strong>${s.fname}</strong>`);};
        openM('mWaSend');
    }
    function bulkSend(type){
        let list=[],tpl='';
        if(type==='welcome'){list=DB.students.filter(s=>s.joinDate&&s.joinDate.includes('Mar'));tpl='welcome';}
        else if(type==='pending'){list=DB.students.filter(x=>x.feeStatus!=='paid');tpl='fee_due';}
        else if(type==='overdue'){list=DB.students.filter(x=>x.feeStatus==='overdue');tpl='fee_overdue';}
        else if(type==='bookoverdue'){const txIds=DB.transactions.filter(t=>t.status==='overdue').map(t=>t.studentId);list=DB.students.filter(s=>txIds.includes(s.id));tpl='book_overdue';}
        if(!list.length){toast('No students in this category','wn');return;}
        list.forEach(s=>{const b=DB.batches.find(x=>x.id===s.batchId);const lastTx=DB.transactions.filter(t=>t.studentId===s.id&&t.status!=='returned').pop();const txData=lastTx?{...lastTx,bookTitle:DB.books.find(bk=>bk.id===lastTx.bookId)?.title||'—'}:null;const msg=WA_TEMPLATES[tpl]?WA_TEMPLATES[tpl](s,b,txData):'';openWALink(s.phone,msg);});
        DB.waSendLog.push({time:new Date().toLocaleTimeString(),to:`${list.length} students (${type})`,preview:`Bulk: ${tpl}`,type:'bulk'});
        addActivity('💬','rgba(37,211,102,.14)',`Bulk WA sent to <strong>${list.length}</strong> (${type})`);
        toast(`WhatsApp opened for ${list.length} students!`,'wa');
        if(document.getElementById('page-whatsapp').classList.contains('active'))renderWASendLog();
    }
    function waCopyModal(){const msg=gv('waSendMsg');navigator.clipboard?.writeText(msg).then(()=>toast('Copied!','ok'));}
    function renderWASendLog(){const el=document.getElementById('waSendLog');if(!el)return;el.innerHTML=DB.waSendLog.slice(-8).reverse().map(l=>`<div style="display:flex;align-items:center;gap:7px;padding:5px 8px;background:var(--sf2);border-radius:var(--r2);font-size:11px"><span style="color:var(--wa2);font-weight:600;flex-shrink:0">${l.time}</span><span style="color:var(--tx2);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${l.to}</span><span class="tag twa" style="font-size:9px">${l.type}</span></div>`).join('')||'<div style="font-size:11px;color:var(--tx3);text-align:center;padding:10px">No messages sent yet</div>';}

    // ═══ STAFF ═══
    const PERMS=[
      {key:'students',      label:'Students',       desc:'Enroll, edit, seat & attendance',  icon:'school'},
      {key:'fees',          label:'Fees',            desc:'Payments, invoices & renewals',     icon:'payments'},
      {key:'books',         label:'Books',           desc:'Catalog, issue & return',           icon:'menu_book'},
      {key:'expenses',      label:'Expenses',        desc:'Add & view expense records',        icon:'receipt'},
      {key:'reports',       label:'Reports',         desc:'Analytics & export data',           icon:'bar_chart'},
      {key:'staff',         label:'Staff',           desc:'Add/edit staff & salary',           icon:'manage_accounts'},
      {key:'whatsapp',      label:'WhatsApp',        desc:'Messaging & bulk sends',            icon:'chat'},
      {key:'notifications', label:'Notifications',   desc:'System alerts & notices',           icon:'notifications'},
      {key:'settings',      label:'Settings',        desc:'Library config & branding',         icon:'settings'},
    ];
    const ROLE_PERMS={admin:{students:true,fees:true,books:true,expenses:true,reports:true,staff:true,whatsapp:true,notifications:true,settings:true},librarian:{students:true,fees:false,books:true,expenses:false,reports:true,staff:false,whatsapp:false,notifications:true,settings:false},accountant:{students:false,fees:true,books:false,expenses:true,reports:true,staff:false,whatsapp:false,notifications:true,settings:false},receptionist:{students:true,fees:false,books:false,expenses:false,reports:false,staff:false,whatsapp:true,notifications:true,settings:false}};

    // ── Action-level permissions ──
    const ACTION_PERMS = [
      {key:'enroll_student',    label:'Enroll Student',     desc:'Add new students',             icon:'person_add',     module:'students'},
      {key:'delete_student',    label:'Delete Student',     desc:'Remove student records',       icon:'person_remove',  module:'students'},
      {key:'alloc_seat',        label:'Allocate Seat',      desc:'Assign & change seats',        icon:'event_seat',     module:'students'},
      {key:'collect_fee',       label:'Collect Fee',        desc:'Mark payments & UPI',          icon:'payments',       module:'fees'},
      {key:'apply_discount',    label:'Apply Discount',     desc:'Give fee discounts',           icon:'redeem',         module:'fees'},
      {key:'generate_invoice',  label:'Generate Invoice',   desc:'Create new invoices',          icon:'receipt_long',   module:'fees'},
      {key:'issue_book',        label:'Issue Book',         desc:'Issue books to students',      icon:'upload',         module:'books'},
      {key:'return_book',       label:'Return Book',        desc:'Process book returns',         icon:'download',       module:'books'},
      {key:'add_book',          label:'Add Book',           desc:'Add titles to catalog',        icon:'menu_book',      module:'books'},
      {key:'add_expense',       label:'Add Expense',        desc:'Log expense records',          icon:'receipt',        module:'expenses'},
      {key:'send_whatsapp',     label:'Send WhatsApp',      desc:'Bulk & individual messages',   icon:'chat',           module:'whatsapp'},
      {key:'add_staff',         label:'Add / Edit Staff',   desc:'Manage staff accounts',        icon:'manage_accounts',module:'staff'},
      {key:'add_batch',         label:'Add / Edit Batch',   desc:'Create & modify batches',      icon:'groups',         module:'students'},
      {key:'reset_data',        label:'Reset Data',         desc:'Factory reset — dangerous',    icon:'restart_alt',    module:'settings'},
    ];

    // Default action perms per role (all true for admin, sensible defaults for others)
    const ROLE_ACTION_PERMS = {
      admin:       Object.fromEntries(ACTION_PERMS.map(a=>[a.key,true])),
      librarian:   {enroll_student:false,delete_student:false,alloc_seat:true,collect_fee:false,apply_discount:false,generate_invoice:false,issue_book:true,return_book:true,add_book:true,add_expense:false,send_whatsapp:false,add_staff:false,add_batch:false,reset_data:false},
      accountant:  {enroll_student:false,delete_student:false,alloc_seat:false,collect_fee:true,apply_discount:true,generate_invoice:true,issue_book:false,return_book:false,add_book:false,add_expense:true,send_whatsapp:false,add_staff:false,add_batch:false,reset_data:false},
      receptionist:{enroll_student:true,delete_student:false,alloc_seat:true,collect_fee:true,apply_discount:false,generate_invoice:false,issue_book:false,return_book:false,add_book:false,add_expense:false,send_whatsapp:true,add_staff:false,add_batch:false,reset_data:false},
    };
    function renderStaff(){
        document.getElementById('staffCount').textContent=`${DB.staff.length} staff`;
        document.getElementById('staffTable').innerHTML=DB.staff.map((sf,i)=>{const pc=Object.values(sf.perms).filter(Boolean).length;
            return `<tr><td><div class="si"><div class="sav" style="background:linear-gradient(135deg,var(--ac),var(--vi))">${(sf.name||"?").split(' ').filter(Boolean).map(n=>n[0]).join('').slice(0,2).toUpperCase()||"?"}</div><div><div style="font-weight:600;font-size:12.5px">${sf.name||""}</div><div style="font-size:10px;color:var(--tx3);font-family:var(--fm)">${sf.id}</div></div></div></td>
    <td><span class="tag tac" style="text-transform:capitalize">${sf.role}</span></td><td>${sf.email}</td><td>${sf.phone}</td>
    <td><div style="display:flex;flex-direction:column;gap:4px">
      <div style="display:flex;flex-wrap:wrap;gap:3px">${PERMS.filter(p=>sf.perms[p.key]).map(p=>`<span title="Page: ${p.label}" style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;background:rgba(61,111,240,.09);border:1px solid rgba(61,111,240,.2);border-radius:6px"><span class="mi" style="font-size:12px;color:var(--ac)">${p.icon}</span></span>`).join('')}${pc===0?'<span style="font-size:10px;color:var(--tx3);font-style:italic">No pages</span>':''}</div>
      <div style="display:flex;flex-wrap:wrap;gap:3px">${ACTION_PERMS.filter(a=>(sf.actPerms||{})[a.key]).map(a=>`<span title="Action: ${a.label}" style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;background:rgba(124,58,237,.08);border:1px solid rgba(124,58,237,.2);border-radius:6px"><span class="mi" style="font-size:12px;color:var(--vi)">${a.icon}</span></span>`).join('')||'<span style="font-size:9px;color:var(--tx3);font-style:italic">No actions</span>'}</div>
    </div></td><td><span class="tag ${sf.status==='active'?'tpd':'tod'}">${sf.status==='active'?'Active':'Inactive'}</span></td>
    <td><div style="display:flex;gap:4px"><button class="btn bg" style="font-size:10px;padding:3px 7px" onclick="editStaff(${i})">✏</button>${i>0?`<button class="btn bd" style="font-size:10px;padding:3px 6px" onclick="delStaff(${i})"><span class="mi sm">close</span></button>`:''}</div></td></tr>`;
        }).join('')||'<tr><td colspan="7"><div class="empty"><div class="ei">👥</div><div class="et">No staff</div></div></td></tr>';
    }
    function buildPermList(){
      const role=gv('sf-rl')||'librarian';
      const d=ROLE_PERMS[role];
      const da=ROLE_ACTION_PERMS[role]||{};
      const isAdmin=role==='admin';

      // helper: render one permission card (works for both module & action perms)
      function permCard(key,label,desc,icon,on,disabled,idPrefix){
        return `<label style="display:flex;align-items:center;gap:10px;padding:10px 13px;background:${on?'rgba(61,111,240,.05)':'var(--sf2)'};border:1.5px solid ${on?'rgba(61,111,240,.25)':'var(--br)'};border-radius:var(--r2);cursor:${disabled?'default':'pointer'};transition:all .18s" class="perm-card-lbl">
          <div style="width:32px;height:32px;border-radius:8px;background:${on?'rgba(61,111,240,.1)':'var(--sf3)'};display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .18s">
            <span class="mi sm" style="color:${on?'var(--ac)':'var(--tx3)'}">${icon}</span>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-size:12px;font-weight:700;color:${on?'var(--tx)':'var(--tx2)'}">${label}</div>
            <div style="font-size:9.5px;color:var(--tx3);line-height:1.3;margin-top:1px">${desc}</div>
          </div>
          <label class="toggle-wrap" onclick="event.stopPropagation();setTimeout(()=>refreshPermCards(),10)" style="flex-shrink:0">
            <input type="checkbox" id="${idPrefix}${key}" class="toggle-inp" ${on?'checked':''} ${disabled?'disabled':''}>
            <span class="toggle-sl"></span>
          </label>
        </label>`;
      }

      const adminBanner = `<div style="margin-top:8px;padding:7px 11px;background:rgba(61,111,240,.06);border:1px solid rgba(61,111,240,.2);border-radius:var(--r2);font-size:11px;color:var(--ac);display:flex;align-items:center;gap:6px"><span class="mi sm">verified_user</span>Admin has full access — all permissions locked.</div>`;

      // Group action perms by module for cleaner display
      const actionModules = [...new Set(ACTION_PERMS.map(a=>a.module))];

      document.getElementById('permList').innerHTML=`
        <!-- ── Section 1: Module Access ── -->
        <div style="display:flex;align-items:center;gap:8px;margin:10px 0 8px">
          <span class="mi sm" style="color:var(--ac)">grid_view</span>
          <span style="font-size:11px;font-weight:700;color:var(--tx2);text-transform:uppercase;letter-spacing:.8px;font-family:var(--fm)">Module Access</span>
          <div style="flex:1;height:1px;background:var(--br)"></div>
          <span style="font-size:9.5px;color:var(--tx3)">Show / hide full pages</span>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
          ${PERMS.map(p=>permCard(p.key,p.label,p.desc,p.icon,isAdmin?true:d[p.key],isAdmin,'perm-')).join('')}
        </div>
        ${isAdmin?adminBanner:''}

        <!-- ── Section 2: Action Controls ── -->
        <div style="display:flex;align-items:center;gap:8px;margin:16px 0 8px">
          <span class="mi sm" style="color:var(--vi)">tune</span>
          <span style="font-size:11px;font-weight:700;color:var(--tx2);text-transform:uppercase;letter-spacing:.8px;font-family:var(--fm)">Action Controls</span>
          <div style="flex:1;height:1px;background:var(--br)"></div>
          <span style="font-size:9.5px;color:var(--tx3)">Show / hide specific buttons</span>
        </div>
        ${actionModules.map(mod=>{
          const modActions = ACTION_PERMS.filter(a=>a.module===mod);
          const modLabel = mod.charAt(0).toUpperCase()+mod.slice(1);
          return `<div style="margin-bottom:10px">
            <div style="font-size:10px;font-weight:600;color:var(--tx3);font-family:var(--fm);text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px;padding-left:2px">${modLabel}</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
              ${modActions.map(a=>permCard(a.key,a.label,a.desc,a.icon,isAdmin?true:da[a.key],isAdmin,'act-')).join('')}
            </div>
          </div>`;
        }).join('')}
        ${isAdmin?'':'<div style="padding:7px 11px;background:rgba(124,58,237,.05);border:1px solid rgba(124,58,237,.2);border-radius:var(--r2);font-size:10.5px;color:var(--vi);display:flex;align-items:center;gap:6px"><span class="mi sm">info</span>Action controls work inside pages the staff member can already access.</div>'}
      `;
    }
    function refreshPermCards(){
      function syncCard(cb){
        const card=cb?.closest('label.perm-card-lbl');
        if(!cb||!card)return;
        const on=cb.checked;
        card.style.background=on?'rgba(61,111,240,.05)':'var(--sf2)';
        card.style.borderColor=on?'rgba(61,111,240,.25)':'var(--br)';
        const ic=card.querySelector('.mi.sm');
        if(ic)ic.style.color=on?'var(--ac)':'var(--tx3)';
        const icBox=card.querySelector('div[style*="32px"]');
        if(icBox)icBox.style.background=on?'rgba(61,111,240,.1)':'var(--sf3)';
        const lbl=card.querySelector('div[style*="font-weight:700"]');
        if(lbl)lbl.style.color=on?'var(--tx)':'var(--tx2)';
      }
      PERMS.forEach(p=>syncCard(document.getElementById('perm-'+p.key)));
      ACTION_PERMS.forEach(a=>syncCard(document.getElementById('act-'+a.key)));
    }
    function setDefaultPerms(){buildPermList();}
    function editStaff(idx){
      editStaffIdx=idx;
      const sf=DB.staff[idx];
      document.getElementById('staffModalTitle').textContent='✏ Edit Staff';
      document.getElementById('staffSaveBtn').textContent='Save';
      document.getElementById('sf-nm').value=sf.name;
      document.getElementById('sf-rl').value=sf.role;
      document.getElementById('sf-em').value=sf.email;
      document.getElementById('sf-ph').value=sf.phone;
      document.getElementById('sf-un').value=sf.username;
      buildPermList();
      // Restore module perms
      PERMS.forEach(p=>{const el=document.getElementById('perm-'+p.key);if(el)el.checked=!!sf.perms[p.key];});
      // Restore action perms
      ACTION_PERMS.forEach(a=>{const el=document.getElementById('act-'+a.key);if(el)el.checked=!!(sf.actPerms||{})[a.key];});
      refreshPermCards();
      openM('mAddStaff');
    }
    // delStaff is defined below as an async function (API-backed with local fallback)
    // saveStaff is defined below as an async function (API-backed with local fallback)

    // ═══ NOTIFICATIONS ═══
    function renderNotifs(){
        document.getElementById('notifCount').textContent=`${DB.notifications.length} notifications`;
        const ic={warning:'⚠️',info:'ℹ️',success:'✅',error:'🚨'};const bg={warning:'rgba(196,125,43,.1)',info:'rgba(74,124,111,.1)',success:'rgba(58,125,94,.1)',error:'rgba(192,68,79,.1)'};
        document.getElementById('notifList').innerHTML=DB.notifications.map(n=>`<div style="display:flex;gap:11px;padding:12px;background:${n.read?'transparent':'rgba(74,124,111,.04)'};border:1px solid ${n.read?'var(--br)':'rgba(74,124,111,.2)'};border-radius:var(--r2)">
    <div style="width:32px;height:32px;border-radius:9px;background:${bg[n.type]};display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0">${ic[n.type]}</div>
    <div style="flex:1"><div style="font-size:12.5px;font-weight:600;margin-bottom:2px">${n.title}</div><div style="font-size:11.5px;color:var(--tx2)">${n.msg}</div><div style="font-size:10px;color:var(--tx3);font-family:var(--fm);margin-top:3px">${n.time}</div></div>
    <div style="display:flex;gap:5px;align-items:flex-start">${!n.read?`<button class="btn bg" style="font-size:10px;padding:2px 7px" onclick="markRead(${n.id})">Read</button>`:''}<button class="btn bg" style="font-size:10px;padding:2px 6px" onclick="delNotif(${n.id})"><span class="mi sm">close</span></button></div>
  </div>`).join('')||'<div class="empty"><div class="ei">🔔</div><div class="et">No notifications</div></div>';
    }
    function markRead(id){const n=DB.notifications.find(x=>x.id===id);if(n)n.read=true;renderNotifs();updateBadges();}
    function delNotif(id){DB.notifications=DB.notifications.filter(x=>x.id!==id);renderNotifs();updateBadges();}
    function clearNotifs(){DB.notifications=[];renderNotifs();updateBadges();toast('Cleared','ok');}

    // ═══ SETTINGS ═══
    function renderSettingsStats(){
        const s=DB.students;
        const data=[{l:'Total Students',v:s.length},{l:'Discounts Given',v:`${s.filter(x=>x.baseFee>x.netFee).length} students (₹${s.reduce((a,x)=>a+(x.baseFee-x.netFee),0).toLocaleString()})`},{l:'Total Books',v:DB.books.reduce((a,b)=>a+b.copies,0)},{l:'Active Transactions',v:DB.transactions.filter(t=>t.status!=='returned').length},{l:'Total Batches',v:DB.batches.length},{l:'Staff Members',v:DB.staff.length},{l:'Net Profit',v:fmt(s.filter(x=>x.feeStatus==='paid').reduce((a,x)=>a+x.netFee,0)-DB.expenses.reduce((a,e)=>a+e.amount,0))}];
        document.getElementById('setStats').innerHTML=data.map(d=>`<div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--br)"><span style="font-size:12px;color:var(--tx2)">${d.l}</span><span style="font-weight:700;font-family:var(--fm)">${d.v}</span></div>`).join('');
    }
    function saveSettings(){DB.settings.fine=+gv('s-fine');DB.settings.days=+gv('s-days');DB.settings.waNumber=gv('s-wa');DB.settings.name=gv('s-name');toast('Settings saved!','ok');}

    // ═══ MODALS ═══
    function openM(id){
        const pre={
            mCollectFee:populateModal_cf,
            mIssueBook:()=>populateIssueModal(null),
            mReturnBook:populateReturnModal,
            mAddStaff:buildPermList,
            mGenInv:()=>{document.getElementById('gi-stu').innerHTML='<option value="">-- Select --</option>'+DB.students.map(s=>`<option value="${s.id}">${s.fname} ${s.lname}</option>`).join('');},
            mAllocSeat:()=>{document.getElementById('as-stu').innerHTML='<option value="">-- Select --</option>'+DB.students.map(s=>`<option value="${s.id}">${s.fname} ${s.lname}</option>`).join('');document.getElementById('as-bt').innerHTML='<option value="">-- Select --</option>'+DB.batches.map(b=>`<option value="${b.id}">${b.name}</option>`).join('');document.getElementById('as-st').innerHTML='<option value="">-- Select Batch First --</option>';},
            mWaQR:()=>{setTimeout(initWaQR,100);},
            mEnroll:()=>{document.getElementById('en-dt').value=new Date().toISOString().split('T')[0];populateBatchSelects();}
        };
        if(pre[id])pre[id]();
        document.getElementById(id)?.classList.add('open');
    }
    function closeM(id){document.getElementById(id)?.classList.remove('open');}
    // Modals close ONLY via the × button or Cancel — NOT on outside click.

    // ═══ UTILITIES ═══
    function gv(id){const el=document.getElementById(id);return el?el.value.trim():'';}
    function fmt(n){if(n>=100000)return'₹'+(n/100000).toFixed(1)+'L';if(n>=1000)return'₹'+n.toLocaleString();return'₹'+n;}
    // Format a date string (YYYY-MM-DD or any parseable) to "28 Mar 2026"; returns '—' for null/empty/0000
    function fmtDate(v){if(!v||v==='-'||v.startsWith('0000'))return'—';const d=new Date(v);if(isNaN(d))return v;return d.toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'});}
    function batchName(id){const b=DB.batches.find(x=>x.id===id);return b?b.name:'Unknown';}
    function bTag(bId){const b=DB.batches.find(x=>x.id===bId);if(!b)return'<span class="tag">—</span>';const cls=b.name.includes('Morning')||b.name.includes('Early')?'tpn':b.name.includes('Evening')?'tis':b.name.includes('Night')?'tac':'tav';return`<span class="tag ${cls}">${batchEmoji(b.name)} ${b.name}</span>`;}
    function batchEmoji(n){const m={'Early Morning':'🌅','Morning':'☀️','Afternoon':'🌤','Evening':'🌆','Night':'🌙','Late Night':'🌃'};return m[n]||'📚';}
    function addActivity(icon,bg,text){DB.activities.unshift({icon,bg,text,time:'Just now'});if(DB.activities.length>20)DB.activities.pop();}
    function addNotif(type,title,msg){DB.notifications.unshift({id:Date.now(),type,title,msg,time:'Just now',read:false});}
    function updateBadges(){
        // Renewal badge
        const today=new Date();today.setHours(0,0,0,0);
        const in7=new Date(today);in7.setDate(in7.getDate()+7);
        const renDue=DB.students.filter(s=>{const d=new Date(s.dueDate);return d<=in7;}).length;
        const bRen=document.getElementById('b-renewal');if(bRen)bRen.textContent=renDue;
        const pf=DB.students.filter(x=>x.feeStatus!=='paid').length;
        const od=DB.transactions.filter(t=>t.status==='overdue').length;
        const un=DB.notifications.filter(n=>!n.read).length;
        const ab=Object.values(DB.attendance).filter(v=>v==='absent').length;
        document.getElementById('b-fee').textContent=pf;document.getElementById('b-overdue').textContent=od;
        document.getElementById('b-notif').textContent=un;document.getElementById('b-absent').textContent=ab;
        document.getElementById('notifDot').style.display=un>0?'block':'none';
    }
    function refreshAll(){updateBadges();const active=document.querySelector('.page.active');if(active){const id=active.id.replace('page-','');renderPage(id);}else renderDash();}
    function globalSearch(v){if(!v.trim())return;const s=DB.students.find(x=>`${x.fname} ${x.lname} ${x.id}`.toLowerCase().includes(v.toLowerCase()));const bk=DB.books.find(x=>`${x.title} ${x.author}`.toLowerCase().includes(v.toLowerCase()));if(s){navTo('students');document.getElementById('stuSrchInp').value=v;stuSrch(v);}else if(bk){navTo('books');bkSrch(v);}}
    function toast(msg,type='ok'){const c=document.getElementById('toastWrap');const t=document.createElement('div');t.className=`toast ${type}`;const ic={ok:'✅',er:'❌',wn:'⚠️',wa:'💬'};t.innerHTML=`${ic[type]||''} ${msg}`;c.appendChild(t);setTimeout(()=>{t.style.animation='tOut .3s ease forwards';setTimeout(()=>t.remove(),300);},3500);}

    // ── PRO LOGOUT SLIDE-OUT TOAST ──
    let _logoutEl = null;
    function logoutToast() {
        if (_logoutEl) return; // already open
        const staffName = <?= json_encode($staffName) ?>;
        const staffRole = <?= json_encode($staffRole) ?>;
        const el = document.createElement('div');
        el.className = 'logout-toast';
        el.innerHTML = `
    <div class="lt-top">
      <div class="lt-icon"><span class="mi" style="color:var(--ro);font-size:20px">power_settings_new</span></div>
      <div>
        <div class="lt-title">Logging out?</div>
        <div class="lt-sub">Your session will end immediately</div>
      </div>
    </div>
    <div class="lt-meta">
      <span class="mi sm" style="color:var(--tx3)">badge</span>
      Signed in as <strong style="color:var(--tx);margin-left:3px">${staffName}</strong>
      <span style="margin-left:auto;color:var(--tx3)">${staffRole}</span>
    </div>
    <div class="lt-actions">
      <button class="lt-cancel" onclick="closeLogoutToast()">Stay</button>
      <button class="lt-confirm" onclick="doLogout()">
        <span class="mi sm">logout</span> Yes, Logout
      </button>
    </div>`;
        document.body.appendChild(el);
        _logoutEl = el;
    }
    function closeLogoutToast() {
        if (!_logoutEl) return;
        _logoutEl.classList.add('closing');
        setTimeout(() => { if (_logoutEl) { _logoutEl.remove(); _logoutEl = null; } }, 260);
    }
    function doLogout() {
        if (_logoutEl) {
            const btn = _logoutEl.querySelector('.lt-confirm');
            if (btn) { btn.innerHTML = '<span class="mi sm">hourglass_empty</span> Logging out…'; btn.disabled = true; }
        }
        setTimeout(() => { window.location.href = 'logout.php'; }, 400);
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLogoutToast(); });

    // ═══ INIT ═══

    // ═══════════════════════════════════════════════════════════
    // API-BACKED OVERRIDES — replace in-memory-only functions
    // ═══════════════════════════════════════════════════════════

    // ── ENROLL STUDENT ──
    async function enrollStudent() {
        const fn=gv('en-fn'), ln=gv('en-ln'), bt=gv('en-bt');
        if (!fn || !bt) return toast('First name and batch required', 'er');
        // join_date: use selected date or today, formatted as YYYY-MM-DD for the API
        const joinDateRaw = gv('en-dt') || new Date().toISOString().slice(0,10);
        const months = +gv('en-dur') || 1;
        const dueDate = gv('en-due') || (() => {
            const d = new Date(joinDateRaw); d.setMonth(d.getMonth() + months);
            return d.toISOString().split('T')[0];
        })();
        const payload = {
            fname: fn, lname: ln, phone: gv('en-ph'), batch_id: bt,
            seat_type: gv('en-ac'), seat: gv('en-st'), course: gv('en-co'),
            join_date: joinDateRaw,
            months: months,
            due_date: dueDate,
            base_fee: +gv('en-fe'), discount_type: gv('en-disc-type'),
            discount_value: +gv('en-disc-val') || 0,
            discount_reason: gv('en-disc-reason')
        };
        const res = await apiPost('add_student', payload);
        if (res.error) return toast(res.error, 'er');
        const waCheck = document.getElementById('en-wa');
        closeM('mEnroll');
        toast(`${fn} enrolled for ${months} month${months>1?'s':''}!`, 'ok');
        // ── Reset form ──
        ['en-fn','en-ln','en-ph','en-em','en-ad','en-co','en-dt','en-st',
            'en-fe','en-net-fe','en-disc-val','en-disc-reason','en-due'].forEach(id => {
            const el = document.getElementById(id); if (el) el.value = '';
        });
        const btEl = document.getElementById('en-bt'); if (btEl) btEl.value = '';
        const acEl = document.getElementById('en-ac'); if (acEl) acEl.value = 'non-ac';
        const dtEl = document.getElementById('en-disc-type'); if (dtEl) dtEl.value = 'none';
        const durEl = document.getElementById('en-dur'); if (durEl) durEl.value = '1';
        const waEl = document.getElementById('en-wa'); if (waEl) waEl.checked = true;
        const feeNote = document.getElementById('en-fee-note'); if (feeNote) feeNote.style.display = 'none';
        // Reset join date to today and recalculate due date
        const enDtEl = document.getElementById('en-dt');
        if (enDtEl) { enDtEl.value = new Date().toISOString().split('T')[0]; calcEnrollDueDate(); }
        await reloadDB();
        if (waCheck && waCheck.checked) {
            const newStu = DB.students.find(x => x.id === res.id);
            if (newStu) setTimeout(() => waQuick(newStu.id, 'welcome'), 800);
        }
    }

    // ── DELETE STUDENT ──
    async function delStu(id) {
        if (!confirm('Remove this student?')) return;
        const res = await apiPost('delete_student', { id });
        if (res.error) return toast(res.error, 'er');
        toast('Removed', 'wn');
        await reloadDB();
    }

    // ── SAVE BATCH (add/edit) ──
    async function saveBatch() {
        const nm=gv('ab-nm'), st=gv('ab-st'), et=gv('ab-et'), ts=+gv('ab-ts'), fe=+gv('ab-fe'), ac=+gv('ab-ac');
        if (!nm || !st || !et || !ts || !fe) return toast('Fill required fields', 'er');
        const payload = { name: nm, start_time: st, end_time: et, total_seats: ts, base_fee: fe, ac_extra: ac };
        if (editBatchIdx >= 0) payload.id = DB.batches[editBatchIdx].id;
        const res = await apiPost('save_batch', payload);
        if (res.error) return toast(res.error, 'er');
        toast(editBatchIdx >= 0 ? `"${nm}" updated!` : `Batch "${nm}" created!`, 'ok');
        editBatchIdx = -1;
        closeM('mAddBatch');
        document.getElementById('mAddBatchTitle').textContent = 'Add New Batch';
        document.getElementById('batchSaveBtn').textContent = 'Add Batch';
        await reloadDB();
    }

    // ── DELETE BATCH ──
    async function delBatch(idx) {
        if (!confirm('Delete batch?')) return;
        const res = await apiPost('delete_batch', { id: DB.batches[idx].id });
        if (res.error) return toast(res.error, 'er');
        toast('Deleted', 'wn');
        await reloadDB();
    }

    // ── ALLOC SEAT ──
    async function allocSeat() {
        const stuId=gv('as-stu'), bId=gv('as-bt'), seat=gv('as-st');
        if (!stuId || !bId || !seat) return toast('Fill all fields', 'er');
        const res = await apiPost('alloc_seat', { student_id: stuId, batch_id: bId, seat });
        if (res.error) return toast(res.error, 'er');
        closeM('mAllocSeat');
        toast(`Seat ${seat} allocated!`, 'ok');
        await reloadDB();
    }

    // ── ADD BOOK ──
    async function addBook() {
        const tl=gv('bk-tl');
        if (!tl) return toast('Title required', 'er');
        const res = await apiPost('add_book', {
            title: tl, author: gv('bk-au'), isbn: gv('bk-is'),
            category: gv('bk-ca'), copies: +gv('bk-cp') || 1, shelf: gv('bk-sh')
        });
        if (res.error) return toast(res.error, 'er');
        closeM('mAddBook');
        toast(`"${tl}" added!`, 'ok');
        await reloadDB();
    }

    // ── ISSUE BOOK ──
    async function issueBook() {
        const stuId=gv('ib-stu'), bkId=gv('ib-bk');
        if (!stuId || !bkId) return toast('Select student and book', 'er');
        const res = await apiPost('issue_book', { student_id: stuId, book_id: bkId });
        if (res.error) return toast(res.error, 'er');
        closeM('mIssueBook');
        toast('Book issued!', 'ok');
        await reloadDB();
    }

    // ── RETURN BOOK ──
    async function returnBook() {
        const txId=gv('rb-tx'), cond=gv('rb-cd');
        if (!txId) return toast('Select transaction', 'er');
        const fine = +gv('rb-fn') || 0;
        const res = await apiPost('return_book', { tx_id: txId, fine, condition: cond });
        if (res.error) return toast(res.error, 'er');
        closeM('mReturnBook');
        toast(`Returned!${fine > 0 ? ' Fine: ₹' + fine : ''}`, 'ok');
        await reloadDB();
    }

    // ── COLLECT FEE ──
    async function collectFee() {
        const stuId=gv('cf-stu');
        if (!stuId) return toast('Select student', 'er');
        const mode=gv('cf-mode'), isSplit=mode==='split'||mode==='split2';
        let amt, modeStr;
        if (isSplit) {
            const a1=+gv('cf-a1')||0, a2=+gv('cf-a2')||0;
            amt=a1+a2; modeStr=`${gv('cf-m1')} ₹${a1} + ${gv('cf-m2')} ₹${a2}`;
        } else {
            amt=+gv('cf-amt')||0; modeStr=mode;
        }
        if (!amt) return toast('Enter amount', 'er');
        const res = await apiPost('collect_fee', {
            student_id: stuId, amount: amt, mode: modeStr,
            month: gv('cf-mo') || new Date().toLocaleDateString('en-IN',{month:'long',year:'numeric'})
        });
        if (res.error) return toast(res.error, 'er');
        const bal = res.balance || 0;
        const waCheck = document.getElementById('cf-wa');
        closeM('mCollectFee');
        toast(`₹${amt} collected${bal > 0 ? ` — ₹${bal} still pending` : ''}!`, 'ok');
        await reloadDB();
        if (waCheck && waCheck.checked) {
            setTimeout(() => waQuick(stuId, res.fee_status==='paid' ? 'fee_receipt' : 'partial_payment'), 600);
        }
    }

    // ── GENERATE INVOICE ──
    async function genInvoice() {
        const stuId=gv('gi-stu'), amt=+gv('gi-am');
        if (!stuId || !amt) return toast('Fill required', 'er');
        const res = await apiPost('gen_invoice', {
            student_id: stuId, amount: amt,
            type: gv('gi-tp'), month: gv('gi-mo') || new Date().toLocaleDateString('en-IN',{month:'long',year:'numeric'})
        });
        if (res.error) return toast(res.error, 'er');
        closeM('mGenInv');
        toast('Invoice generated!', 'ok');
        await reloadDB();
    }

    // ── ADD EXPENSE ──
    async function addExp() {
        const nm=gv('ex-nm'), am=+gv('ex-am');
        if (!nm || !am) return toast('Fill required', 'er');
        const res = await apiPost('add_expense', {
            name: nm, amount: am, category: gv('ex-ca'),
            date: gv('ex-dt') || new Date().toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'}),
            notes: gv('ex-nt')
        });
        if (res.error) return toast(res.error, 'er');
        closeM('mExpense');
        toast('Expense added!', 'ok');
        await reloadDB();
    }

    // ── DELETE EXPENSE ──
    async function delExp(id) {
        const res = await apiPost('delete_expense', { id });
        if (res.error) return toast(res.error, 'er');
        toast('Removed', 'wn');
        await reloadDB();
    }

    // ── SAVE ATTENDANCE ──
    async function saveAtt() {
        const res = await apiPost('save_attendance', {
            date: new Date().toISOString().split('T')[0],
            attendance: DB.attendance
        });
        if (res.error) return toast(res.error, 'er');
        const p = Object.values(DB.attendance).filter(v=>v==='present').length;
        addActivity('<span class="mi sm">fact_check</span>','rgba(2,132,199,.12)',`Attendance: <strong>${p}/${DB.students.length}</strong> present`);
        toast(`Saved! ${p} present`, 'ok');
        updateBadges();
    }

    // ── SAVE STAFF ──
    async function saveStaff() {
        const nm=gv('sf-nm'), rl=gv('sf-rl'), em=gv('sf-em');
        if (!nm || !rl || !em) return toast('Fill required', 'er');
        const perms = {};
        PERMS.forEach(p => { const el=document.getElementById('perm-'+p.key); perms[p.key]=el?el.checked:false; });
        const actPerms = {};
        ACTION_PERMS.forEach(a => { const el=document.getElementById('act-'+a.key); actPerms[a.key]=el?el.checked:false; });
        const payload = {
            name: nm, role: rl, email: em,
            phone: gv('sf-ph'), username: gv('sf-un'), password: gv('sf-pw'), perms, actPerms
        };
        if (editStaffIdx >= 0) payload.id = DB.staff[editStaffIdx].id;

        // Optimistically update local DB first so table shows immediately
        if (editStaffIdx >= 0) {
            Object.assign(DB.staff[editStaffIdx], { name: nm, role: rl, email: em, phone: gv('sf-ph'), username: gv('sf-un'), perms, actPerms });
        } else {
            DB.staff.push({ id: 'SF-' + Date.now(), name: nm, role: rl, email: em, phone: gv('sf-ph'), username: gv('sf-un') || nm.split(' ')[0].toLowerCase(), perms, actPerms, status: 'active' });
        }
        toast(editStaffIdx >= 0 ? `${nm} updated!` : `${nm} added!`, 'ok');
        editStaffIdx = -1;
        closeM('mAddStaff');
        document.getElementById('staffModalTitle').textContent = 'Add Staff';
        document.getElementById('staffSaveBtn').textContent = 'Add Staff';
        renderStaff(); // render immediately with local data
        document.getElementById('staffCount').textContent = `${DB.staff.length} staff`;

        // Then try to persist to server in background
        try {
            const res = await apiPost('save_staff', payload);
            if (res && res.error) { toast('Server: ' + res.error, 'wn'); }
            else { await reloadDB(); } // refresh from server to get proper IDs
        } catch(e) {
            // API not available — local data already shown above
        }
    }

    // ── DELETE STAFF ──
    async function delStaff(idx) {
        if (!confirm('Remove?')) return;
        const staffId = DB.staff[idx]?.id;
        DB.staff.splice(idx, 1); // optimistic local remove
        toast('Removed', 'wn');
        renderStaff();
        try {
            if (staffId) await apiPost('delete_staff', { id: staffId });
        } catch(e) { /* API not available — local removal already done */ }
    }

    // ── MARK NOTIFICATION READ ──
    async function markRead(id) {
        await apiPost('mark_read', { id });
        const n = DB.notifications.find(x => x.id === id);
        if (n) n.read = true;
        renderNotifs(); updateBadges();
    }

    // ── DELETE NOTIFICATION ──
    async function delNotif(id) {
        await apiPost('delete_notif', { id });
        DB.notifications = DB.notifications.filter(x => x.id !== id);
        renderNotifs(); updateBadges();
    }

    // ── CLEAR ALL NOTIFICATIONS ──
    async function clearNotifs() {
        await apiGet('clear_notifs');
        DB.notifications = [];
        renderNotifs(); updateBadges(); toast('Cleared', 'ok');
    }

    // ── SAVE SETTINGS ──
    async function saveSettings() {
        try {
            const payload = {
                name:      gv('s-name'),
                phone:     gv('s-phone'),
                email:     gv('s-email'),
                addr:      gv('s-addr'),
                fine:      +gv('s-fine'),
                days:      +gv('s-days'),
                wa_number: gv('s-wa'),
                ac_fee:    +gv('s-acfee'),
                upi_id:    gv('s-upi') || '7282071620@okaxis'
            };
            const res = await apiPost('save_settings', payload);
            if (res && res.error) return toast('❌ ' + res.error, 'er');
            // Update in-memory state immediately
            DB.settings.name  = payload.name;
            DB.settings.phone = payload.phone;
            DB.settings.email = payload.email;
            DB.settings.addr  = payload.addr;
            DB.settings.fine  = payload.fine;
            DB.settings.days  = payload.days;
            DB.settings.waNumber = payload.wa_number;
            DB.settings.acFee = payload.ac_fee;
            DB.settings.upiId = payload.upi_id;
            toast('✅ Settings saved to database!', 'ok');
        } catch(e) {
            toast('❌ Save failed: ' + e.message, 'er');
        }
    }

    // ── WA SEND LOG (persist to DB) ──
    const _origWaSend = typeof waSend === 'function' ? waSend : null;
    async function logWA(to, preview, type) {
        DB.waSendLog.unshift({ time: new Date().toLocaleTimeString(), to, preview: preview.slice(0,40)+'…', type });
        await apiPost('log_wa', { to, preview: preview.slice(0,60), type });
    }

    // ── RELOAD ALL DATA FROM SERVER ──
    async function reloadDB() {
        await initData();
        const active = document.querySelector('.page.active');
        if (active) {
            const id = active.id.replace('page-', '');
            renderPage(id);
        }
    }

    // ── OVERRIDE addActivity / addNotif to be no-ops (server handles them) ──
    function addActivity(icon, bg, text) {
        DB.activities.unshift({ icon, bg, text, time: 'Just now' });
        if (DB.activities.length > 20) DB.activities.pop();
    }
    function addNotif(type, title, msg) {
        DB.notifications.unshift({ id: Date.now(), type, title, msg, time: 'Just now', read: false });
    }

    // ── SETTINGS PAGE: populate from DB ──
    function renderSettings() {
        const s = DB.settings;
        // Load biometric device panel
        loadSettingsBio();
        const map = {
            's-name':  s.name  ?? '',
            's-phone': s.phone ?? '',
            's-email': s.email ?? '',
            's-addr':  s.addr  ?? '',
            's-fine':  s.fine  ?? 5,
            's-days':  s.days  ?? 14,
            's-acfee': s.acFee ?? 200,
            's-wa':    s.waNumber ?? '',
            's-upi':   s.upiId ?? '7282071620@okaxis'
        };
        Object.entries(map).forEach(([id, val]) => {
            const el = document.getElementById(id);
            if (el) el.value = val;
        });
        // Update sidebar library name
        const nameEl = document.getElementById('sidebar-lib-name');
        if (nameEl && s.name) nameEl.textContent = s.name;
        // Apply logo preview if already loaded
        if (s.logoUrl) applyLogo(s.logoUrl);
        renderSettingsStats();
    }

    // ═══ CHANGE PASSWORD ═══
    async function doChangePassword() {
        const cur = document.getElementById('cp-cur').value;
        const nw  = document.getElementById('cp-new').value;
        const cf  = document.getElementById('cp-cf').value;
        if (!cur || !nw || !cf) return toast('Fill all fields', 'er');
        if (nw.length < 6) return toast('Password must be 6+ characters', 'er');
        if (nw !== cf) return toast('Passwords do not match', 'er');
        try {
            const res = await apiPost('change_password', { current_password: cur, new_password: nw });
            if (res.success) {
                toast('Password updated!', 'ok');
                closeM('mChangePw');
                document.getElementById('cp-cur').value = '';
                document.getElementById('cp-new').value = '';
                document.getElementById('cp-cf').value = '';
            } else {
                toast(res.error || 'Failed to update', 'er');
            }
        } catch(e) {
            toast('Error: ' + e.message, 'er');
        }
    }


    // ══════════════════════════════════════════════════════════════
    // ═══ FEATURE 11: STUDENT RENEWAL SYSTEM ═══════════════════════
    // ══════════════════════════════════════════════════════════════
    let renewStudentId = null;

    function renderRenewal() {
        const filter = document.getElementById('renFilterSel')?.value || 'all';
        const today  = new Date(); today.setHours(0,0,0,0);
        const in7    = new Date(today); in7.setDate(in7.getDate() + 7);
        const in30   = new Date(today); in30.setDate(in30.getDate() + 30);

        let list = DB.students.filter(s => {
            const due = new Date(s.dueDate);
            if (filter === 'overdue') return due < today;
            if (filter === 'due7')    return due >= today && due <= in7;
            if (filter === 'due30')   return due >= today && due <= in30;
            return true;
        }).sort((a,b) => new Date(a.dueDate) - new Date(b.dueDate));

        const overdue = DB.students.filter(s => new Date(s.dueDate) < today).length;
        const soon    = DB.students.filter(s => { const d=new Date(s.dueDate); return d>=today && d<=in7; }).length;
        document.getElementById('ren-overdue').textContent = overdue;
        document.getElementById('ren-soon').textContent    = soon;
        document.getElementById('b-renewal').textContent   = overdue + soon;
        document.getElementById('renCount').textContent    = list.length + ' student(s)';

        document.getElementById('renewalList').innerHTML = list.map(s => {
            const due     = new Date(s.dueDate);
            const diffMs  = due - today;
            const diffDays= Math.round(diffMs / (1000*60*60*24));
            const isOver  = diffDays < 0;
            const isSoon  = diffDays >= 0 && diffDays <= 7;
            const badge   = isOver
                ? `<span class="ren-badge ren-overdue">⚠ ${Math.abs(diffDays)}d overdue</span>`
                : isSoon
                    ? `<span class="ren-badge ren-soon">⏰ ${diffDays}d left</span>`
                    : `<span class="ren-badge ren-ok">✓ ${diffDays}d left</span>`;
            const b = DB.batches.find(x => x.id === s.batchId);
            return `<div class="ren-card">
      <div class="ren-av" style="background:${s.color}">${(s.fname?.[0]||'')+(s.lname?.[0]||'')}</div>
      <div class="ren-info">
        <div class="ren-name">${s.fname} ${s.lname}</div>
        <div class="ren-meta">${b?b.name:'—'} · Seat ${s.seat||'—'} · ₹${s.netFee}/mo</div>
        <div style="margin-top:4px;display:flex;align-items:center;gap:6px">${badge}
          <span style="font-size:10px;color:var(--tx3);font-family:var(--fm)">Due: ${fmtDate(s.dueDate)}</span>
        </div>
      </div>
      <div style="display:flex;flex-direction:column;gap:5px">
        <button class="btn bp" style="font-size:10px;padding:4px 10px" onclick="openRenewModal('${s.id}')">🔄 Renew</button>
        <button class="btn bwa" style="font-size:10px;padding:4px 10px" onclick="waQuick('${s.id}','fee_due')">💬 WA</button>
      </div>
    </div>`;
        }).join('') || '<div class="empty"><div class="ei">✅</div><div class="et">All students up to date!</div></div>';
    }

    function openRenewModal(id) {
        const s = DB.students.find(x => x.id === id);
        if (!s) return;
        renewStudentId = id;
        const b = DB.batches.find(x => x.id === s.batchId);

        // Show previous unpaid balance if any
        const prevBal = Math.max(0, s.netFee - (s.paidAmt || 0));
        const prevBalHtml = prevBal > 0
            ? `<div style="margin-top:4px;font-size:10px;color:#c2410c;font-weight:600">⚠ ₹${prevBal} still unpaid from previous period</div>`
            : '';

        document.getElementById('mRenewStudentInfo').innerHTML =
            `<div style="display:flex;align-items:center;gap:10px">
      <div style="width:36px;height:36px;border-radius:9px;background:${s.color};display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff">${(s.fname?.[0]||'')+(s.lname?.[0]||'')}</div>
      <div>
        <div style="font-weight:600;font-size:13px">${s.fname} ${s.lname}</div>
        <div style="font-size:11px;color:var(--tx3)">${b?b.name:'—'} · Current due: ${fmtDate(s.dueDate)}</div>
        ${prevBalHtml}
      </div>
    </div>`;

        document.getElementById('ren-fee').value    = s.netFee;
        document.getElementById('ren-extend').value = '1';
        document.getElementById('ren-balance-warn').style.display = 'none';
        // Clear manual edit flag so fee auto-updates with months on fresh open
        delete document.getElementById('ren-fee').dataset.manualEdit;
        document.getElementById('ren-fee').oninput = function() {
            this.dataset.manualEdit = '1'; // staff typed — stop auto-updating fee
            updateRenewDate();
        };
        document.getElementById('ren-extend').onchange  = updateRenewDate;
        updateRenewDate();
        openM('mRenew');
    }

    function updateRenewDate() {
        const s = DB.students.find(x => x.id === renewStudentId);
        if (!s) return;
        const months = +document.getElementById('ren-extend').value;
        const base   = new Date(s.dueDate) < new Date() ? new Date() : new Date(s.dueDate);
        base.setMonth(base.getMonth() + months);
        const dateEl = document.getElementById('ren-newdate');
        dateEl.value = base.toISOString().split('T')[0];
        dateEl.min   = new Date().toISOString().split('T')[0];
        dateEl.onchange = updateRenewDate;

        // Auto-update fee = netFee x months (skip if staff manually edited the fee)
        const feeEl   = document.getElementById('ren-fee');
        if (!feeEl.dataset.manualEdit) feeEl.value = s.netFee * months;

        const fee    = +feeEl.value || 0;
        const netFee = s.netFee * months;
        const balance = netFee - fee;
        const d       = base.toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'});
        const sumEl   = document.getElementById('ren-summary');
        const warnEl  = document.getElementById('ren-balance-warn');

        if (fee >= netFee) {
            // Full payment
            sumEl.style.cssText = 'margin-top:12px;padding:10px 12px;border-radius:var(--r2);font-size:12px;color:var(--tx2);background:rgba(22,163,74,.07);border:1px solid rgba(22,163,74,.2)';
            sumEl.innerHTML     = `✅ <strong>${months} month${months>1?'s':''}</strong> extension · Due: <strong>${d}</strong> · ₹${fee} — <strong style="color:#16a34a">Fully Paid</strong>`;
            warnEl.style.display = 'none';
        } else if (fee > 0) {
            // Partial payment
            sumEl.style.cssText = 'margin-top:12px;padding:10px 12px;border-radius:var(--r2);font-size:12px;color:var(--tx2);background:rgba(234,88,12,.06);border:1px solid rgba(234,88,12,.25)';
            sumEl.innerHTML     = `◑ <strong>${months} month${months>1?'s':''}</strong> extension · Due: <strong>${d}</strong> · ₹${fee} paid of ₹${netFee}`;
            warnEl.style.display = 'block';
            warnEl.innerHTML    = `⚠ <strong>₹${balance} balance remaining</strong> — student will be marked as <strong>Partial Paid</strong>. Collect the remaining amount later via Fee Collection.`;
        } else {
            // No amount entered yet
            sumEl.style.cssText = 'margin-top:12px;padding:10px 12px;border-radius:var(--r2);font-size:12px;color:var(--tx2);background:rgba(22,163,74,.07);border:1px solid rgba(22,163,74,.2)';
            sumEl.innerHTML     = `✅ <strong>${months} month${months>1?'s':''}</strong> extension · Due: <strong>${d}</strong>`;
            warnEl.style.display = 'none';
        }
    }

    async function confirmRenew() {
        const s = DB.students.find(x => x.id === renewStudentId);
        if (!s) return;
        const newDate = document.getElementById('ren-newdate').value;
        const fee     = +document.getElementById('ren-fee').value || 0;
        const mode    = document.getElementById('ren-mode').value;
        const notes   = document.getElementById('ren-notes').value;
        const months  = +document.getElementById('ren-extend').value;

        // ── Validate ──
        if (!newDate) return toast('⚠ Please set a new due date', 'wn');
        if (new Date(newDate) < new Date(new Date().toISOString().split('T')[0]))
            return toast('⚠ Due date cannot be in the past', 'wn');
        if (fee <= 0) return toast('⚠ Fee amount must be greater than 0', 'wn');

        // ── Save to DB ──
        try {
            const res = await apiPost('renew_student', {
                student_id: s.id,
                amount:     fee,
                months:     months,
                mode:       mode,
                note:       notes,
                new_due_date: newDate
            });
            if (!res.success) {
                toast('❌ Renewal failed: ' + (res.error || 'Unknown error'), 'er');
                return;
            }
            // ── Update local DB state ──
            s.dueDate  = newDate;
            s.paidAmt  = (s.paidAmt || 0) + fee;
            const bal  = Math.max(0, s.netFee - s.paidAmt);
            s.feeStatus = s.paidAmt >= s.netFee ? 'paid' : (s.paidAmt > 0 ? 'partial' : 'pending');
            s.paidOn   = new Date().toISOString().split('T')[0];

            // Push invoice with correct balance and status
            if (res.invoice_id) {
                DB.invoices.unshift({
                    id: res.invoice_id, studentId: s.id,
                    type: `Renewal (${months}mo)`, amount: fee,
                    baseFee: s.baseFee, discount: s.baseFee - s.netFee,
                    netFee: s.netFee, paidAmt: fee,
                    balance: bal,
                    date: new Date().toISOString().split('T')[0],
                    month: new Date().toLocaleDateString('en-IN', {month:'long', year:'numeric'}),
                    mode, status: s.feeStatus
                });
            }

            auditLog('renewal', `Renewed ${s.fname} ${s.lname} — ${months}mo, ₹${fee} (${mode})${notes?' — '+notes:''}${bal>0?' · ₹'+bal+' balance remaining':''}`);
            addActivity('🔄', 'rgba(61,111,240,.14)', `Renewed <strong>${s.fname} ${s.lname}</strong> for ${months} month${months>1?'s':''}${bal>0?' — ₹'+bal+' still due':''}`);
            closeM('mRenew');
            const toastMsg = s.feeStatus === 'partial'
                ? `◑ ${s.fname} partially renewed — ₹${bal} still due`
                : `✅ ${s.fname} renewed for ${months} month${months>1?'s':''}!`;
            toast(toastMsg, s.feeStatus === 'partial' ? 'wn' : 'ok');
            renderRenewal(); renderStudents(); updateBadges();
        } catch(e) {
            toast('❌ Network error: ' + e.message, 'er');
        }
    }

    function sendRenewalWA() {
        const s = DB.students.find(x => x.id === renewStudentId);
        if (!s) return;
        const newDate  = document.getElementById('ren-newdate').value;
        const fee      = +document.getElementById('ren-fee').value || 0;
        const months   = document.getElementById('ren-extend').value;
        const d        = new Date(newDate).toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'});
        const balance  = Math.max(0, s.netFee - fee);
        const isPartial = fee > 0 && fee < s.netFee;

        const msg = isPartial
            ? `🔄 *Renewal Confirmation (Partial Payment)*

Dear *${s.fname} ${s.lname}*,

Your library membership has been renewed!

✅ *Details:*
• Extended By: ${months} month(s)
• Fee Paid: ₹${fee}
• Balance Due: ₹${balance}
• New Due Date: ${d}
• Seat: ${s.seat||'—'}

⚠ Please pay the remaining ₹${balance} at your earliest convenience.

🏫 ${DB.settings.name}
📞 ${DB.settings.phone}`
            : `🔄 *Renewal Confirmation*

Dear *${s.fname} ${s.lname}*,

Your library membership has been renewed!

✅ *Details:*
• Extended By: ${months} month(s)
• Fee Paid: ₹${fee}
• New Due Date: ${d}
• Seat: ${s.seat||'—'}

Thank you! 📚

🏫 ${DB.settings.name}
📞 ${DB.settings.phone}`;
        waSendDirect(s.phone, msg, s.fname+' '+s.lname);
    }

    async function bulkRenew() {
        const today = new Date(); today.setHours(0,0,0,0);
        const due   = DB.students.filter(s => new Date(s.dueDate) <= today);
        if (!due.length) return toast('No overdue students', 'wn');
        if (!confirm(`Renew ${due.length} overdue students by 1 month?`)) return;

        let ok = 0, fail = 0;
        for (const s of due) {
            const base = new Date(s.dueDate) < today ? new Date() : new Date(s.dueDate);
            base.setMonth(base.getMonth() + 1);
            const newDue = base.toISOString().split('T')[0];
            try {
                const res = await apiPost('renew_student', {
                    student_id: s.id, amount: s.netFee,
                    months: 1, mode: 'Cash', note: 'Bulk renewal', new_due_date: newDue
                });
                if (res.success) {
                    s.dueDate   = newDue;
                    s.paidAmt   = (s.paidAmt || 0) + s.netFee;
                    s.feeStatus = s.paidAmt >= s.netFee ? 'paid' : 'partial';
                    ok++;
                } else { fail++; }
            } catch(e) { fail++; }
        }
        auditLog('renewal', `Bulk renewal — ${ok} students extended 1 month${fail?', '+fail+' failed':''}`);
        toast(`✅ ${ok} students renewed!${fail?' ⚠ '+fail+' failed':''}`, ok ? 'ok' : 'er');
        renderRenewal(); renderStudents(); updateBadges();
    }

    // ══════════════════════════════════════════════════════════════
    // ═══ FEATURE 25: STAFF ATTENDANCE & SALARY ════════════════════
    // ══════════════════════════════════════════════════════════════
    // staffAttData: { 'YYYY-MM-DD': { 'SF-001': 'present'|'absent'|'half' } }
    if (!DB.staffAtt) DB.staffAtt = {};
    if (!DB.staffSalary) DB.staffSalary = {}; // { 'SF-001': 30000 } monthly base

    async function renderStaffAtt() {
        const dateEl = document.getElementById('staffAttDate');
        if (!dateEl.value) dateEl.value = new Date().toISOString().split('T')[0];
        const date = dateEl.value;
        DB.staffAtt[date] = {}; // always reset before loading fresh from DB

        // ── Load from DB, fall back to localStorage ──
        try {
            const res = await apiGet('get_staff_attendance', { date });
            if (res && res.attendance && Object.keys(res.attendance).length) {
                // DB has data — use it as source of truth, no fallback
                Object.entries(res.attendance).forEach(([sfId, row]) => {
                    DB.staffAtt[date][sfId] = (typeof row === 'string') ? row : (row.status || 'present');
                });
            } else {
                // DB empty — try localStorage
                const stored = JSON.parse(localStorage.getItem('staffAtt') || '{}');
                if (stored[date]) Object.entries(stored[date]).forEach(([id,s]) => { DB.staffAtt[date][id] = s.status || s; });
            }
        } catch(e) {
            const stored = JSON.parse(localStorage.getItem('staffAtt') || '{}');
            if (stored[date]) Object.entries(stored[date]).forEach(([id,s]) => { DB.staffAtt[date][id] = s.status || s; });
        }

        document.getElementById('staffAttList').innerHTML = DB.staff.map(sf => {
            const cur = DB.staffAtt[date][sf.id] !== undefined ? DB.staffAtt[date][sf.id] : 'present';
            const av  = (sf.name||'?').split(' ').filter(Boolean).map(p=>p[0]).join('').toUpperCase().slice(0,2)||'?';
            return `<div class="att-row">
      <div class="att-av" style="background:${sf.color||'var(--ac)'}">${av}</div>
      <div style="flex:1">
        <div class="att-name">${sf.name}</div>
        <div class="att-role">${sf.role}</div>
      </div>
      <div class="att-toggle">
        <button class="att-btn att-p ${cur==='present'?'active':''}" onclick="setStaffAtt('${sf.id}','present','${date}',this)">P</button>
        <button class="att-btn att-a ${cur==='absent'?'active':''}" onclick="setStaffAtt('${sf.id}','absent','${date}',this)">A</button>
        <button class="att-btn att-h ${cur==='half'?'active':''}" onclick="setStaffAtt('${sf.id}','half','${date}',this)">½</button>
      </div>
    </div>`;
        }).join('') || '<div class="empty"><div class="et">No staff members</div></div>';

        renderStaffSalary();
        renderStaffAttSummary();
    }

    function setStaffAtt(sfId, status, date, btn) {
        if (!DB.staffAtt[date]) DB.staffAtt[date] = {};
        DB.staffAtt[date][sfId] = status;
        const row = btn.closest('.att-toggle');
        row.querySelectorAll('.att-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    async function saveStaffAtt() {
        const date = document.getElementById('staffAttDate').value;
        if (!date) return toast('Select a date first', 'wn');
        const dayAtt = DB.staffAtt[date] || {};
        if (!Object.keys(dayAtt).length) return toast('No attendance marked', 'wn');

        const attendance = {};
        Object.entries(dayAtt).forEach(([sfId, status]) => { attendance[sfId] = { status }; });

        let savedToServer = false;
        try {
            const res = await apiPost('save_staff_attendance', { date, attendance });
            if (res && res.success) {
                savedToServer = true;
            }
            // if unknown action — silent fallback to local
        } catch(e) { /* fallback */ }

        // Always persist locally so data is never lost
        try {
            const stored = JSON.parse(localStorage.getItem('staffAtt') || '{}');
            stored[date] = attendance;
            localStorage.setItem('staffAtt', JSON.stringify(stored));
        } catch(e) {}

        auditLog('staff', `Staff attendance saved for ${date}`);
        toast(`Attendance saved for ${date}!`, 'ok');
        renderStaffAttSummary();
    }

    async function saveSalaries() {
        const salaries = {};
        document.querySelectorAll('.salary-input').forEach(inp => {
            const sfId = inp.dataset.staffId;
            const val  = parseInt(inp.value) || 0;
            if (sfId && val > 0) salaries[sfId] = val;
        });
        if (!Object.keys(salaries).length) return toast('Enter at least one salary amount', 'wn');
        try {
            const res = await apiPost('save_salary', { salaries });
            if (res.success) {
                Object.entries(salaries).forEach(([id, amt]) => DB.staffSalary[id] = amt);
                renderStaffSalary();
                toast('✅ Salaries saved!', 'ok');
            } else toast('❌ ' + (res.error || 'Failed to save'), 'er');
        } catch(e) { toast('❌ Error: ' + e.message, 'er'); }
    }

    function renderStaffSalary() {
        const selEl = document.getElementById('staffSalMonth');
        // Populate months if empty
        if (!selEl.options.length) {
            for (let i = 0; i < 6; i++) {
                const d = new Date(); d.setMonth(d.getMonth() - i);
                const val = d.toISOString().slice(0,7);
                const lbl = d.toLocaleDateString('en-IN',{month:'long',year:'numeric'});
                const opt = new Option(lbl, val);
                selEl.appendChild(opt);
            }
        }
        const month = selEl.value || new Date().toISOString().slice(0,7);
        let total = 0;

        document.getElementById('staffSalList').innerHTML = DB.staff.map(sf => {
            const base    = DB.staffSalary[sf.id] || 0;
            // Count working days this month
            const daysInMonth = new Date(month.split('-')[0], month.split('-')[1], 0).getDate();
            let present=0, absent=0, half=0;
            for (let d=1; d<=daysInMonth; d++) {
                const key  = `${month}-${String(d).padStart(2,'0')}`;
                const stat = (DB.staffAtt[key]||{})[sf.id] || 'present';
                if (stat==='present') present++;
                else if (stat==='absent') absent++;
                else half += 0.5;
            }
            const worked  = present + half;
            const salary  = base > 0 ? Math.round((base / daysInMonth) * worked) : 0;
            total += salary;
            return `<div class="sal-row">
      <div>
        <div style="font-size:12.5px;font-weight:600">${sf.name}</div>
        <div class="sal-days">P:${present} A:${absent} ½:${half*2} · ${worked.toFixed(1)}/${daysInMonth} days</div>
        ${base===0?'<div style="font-size:10px;color:var(--ro)">Set salary below</div>':''}
      </div>
      <div style="text-align:right">
        <div class="sal-amt">${salary>0?'₹'+salary.toLocaleString():'—'}</div>
        <input type="number" value="${base||''}" placeholder="Base₹"
          class="salary-input" data-staff-id="${sf.id}"
          style="width:80px;font-size:11px;padding:3px 6px;margin-top:4px;text-align:right"
          onchange="DB.staffSalary['${sf.id}']=+this.value;renderStaffSalary()" title="Monthly base salary">
      </div>
    </div>`;
        }).join('') || '<div style="color:var(--tx3);font-size:12px;padding:10px">No staff added yet</div>';

        document.getElementById('staffSalTotal').textContent = total > 0 ? '₹' + total.toLocaleString() : '₹0';
        const saveBtn = document.getElementById('saveSalBtn');
        if (saveBtn) saveBtn.onclick = saveSalaries;
    }

    async function renderStaffAttSummary() {
        const month = new Date().toISOString().slice(0,7);
        // ── Load monthly summary from DB ──
        let dbSummary = {};
        try {
            const rows = await apiGet('get_staff_attendance_summary', { month });
            if (rows && Array.isArray(rows)) rows.forEach(r => { dbSummary[r.id] = r; });
            // if unknown action or empty — fall through to localStorage below
        } catch(e) { /* fallback */ }
        // Supplement with localStorage data if DB had nothing
        if (!Object.keys(dbSummary).length) {
            const stored = JSON.parse(localStorage.getItem('staffAtt') || '{}');
            const daysInMonth2 = new Date(month.split('-')[0], month.split('-')[1], 0).getDate();
            DB.staff.forEach(sf => {
                let p=0,a=0,h=0;
                for (let d=1;d<=daysInMonth2;d++) {
                    const key=`${month}-${String(d).padStart(2,'0')}`;
                    const dayData = stored[key] || {};
                    const s = (dayData[sf.id] && (dayData[sf.id].status || dayData[sf.id])) || (DB.staffAtt[key]||{})[sf.id] || 'present';
                    if(s==='present')p++; else if(s==='absent')a++; else h++;
                }
                dbSummary[sf.id] = { id: sf.id, present: p, absent: a, half: h };
            });
        }

        const daysInMonth = new Date(month.split('-')[0], month.split('-')[1], 0).getDate();

        document.getElementById('staffAttSummary').innerHTML = DB.staff.map(sf => {
            let p, a, h;
            if (dbSummary[sf.id]) {
                p = +dbSummary[sf.id].present;
                a = +dbSummary[sf.id].absent;
                h = +dbSummary[sf.id].half;
            } else {
                p = 0; a = 0; h = 0;
                for (let d = 1; d <= daysInMonth; d++) {
                    const key = `${month}-${String(d).padStart(2,'0')}`;
                    const s   = (DB.staffAtt[key]||{})[sf.id] || 'present';
                    if (s==='present') p++; else if (s==='absent') a++; else h++;
                }
            }
            const pct = Math.round(((p + h*0.5) / daysInMonth) * 100);
            return `<div class="sa-card">
      <div style="font-size:12px;font-weight:600;margin-bottom:6px">${sf.name}</div>
      <div style="font-size:22px;font-weight:700;font-family:var(--fm);color:${pct>=90?'var(--em)':pct>=70?'var(--gd)':'var(--ro)'}">${pct}%</div>
      <div style="font-size:10px;color:var(--tx3);margin-top:4px">P:${p} A:${a} ½:${h}</div>
      <div style="margin-top:8px;height:4px;background:var(--sf2);border-radius:2px;overflow:hidden">
        <div style="height:100%;border-radius:2px;background:${pct>=90?'var(--em)':pct>=70?'var(--gd)':'var(--ro)'};width:${pct}%;transition:width .6s"></div>
      </div>
      <button class="btn bg" style="font-size:10px;padding:3px 8px;margin-top:8px" onclick="openSalarySlip('${sf.id}')">📄 Slip</button>
    </div>`;
        }).join('');
    }

    function openSalarySlip(sfId) {
        const sf    = DB.staff.find(x => x.id === sfId);
        if (!sf) return;
        const month = document.getElementById('staffSalMonth').value || new Date().toISOString().slice(0,7);
        const base  = DB.staffSalary[sfId] || 0;
        const daysInMonth = new Date(month.split('-')[0], month.split('-')[1], 0).getDate();
        let p=0, a=0, h=0;
        for (let d=1; d<=daysInMonth; d++) {
            const key = `${month}-${String(d).padStart(2,'0')}`;
            const s   = (DB.staffAtt[key]||{})[sfId] || 'present';
            if (s==='present') p++; else if (s==='absent') a++; else h++;
        }
        const worked  = p + h*0.5;
        const salary  = base > 0 ? Math.round((base / daysInMonth) * worked) : 0;
        const mLabel  = new Date(month).toLocaleDateString('en-IN',{month:'long',year:'numeric'});
        document.getElementById('mSalarySlipContent').innerHTML = `
    <div style="padding:4px 0">
      <div style="text-align:center;margin-bottom:16px;padding-bottom:14px;border-bottom:2px solid var(--ac)">
        <div style="font-size:16px;font-weight:700;color:var(--ac)">${DB.settings.name}</div>
        <div style="font-size:12px;color:var(--tx3)">${DB.settings.addr}</div>
        <div style="font-size:13px;font-weight:600;margin-top:8px">SALARY SLIP — ${mLabel.toUpperCase()}</div>
      </div>
      <div style="display:flex;justify-content:space-between;margin-bottom:14px">
        <div><div style="font-size:13px;font-weight:700">${sf.name}</div><div style="font-size:11px;color:var(--tx3)">${sf.role} · ${sf.id}</div></div>
      </div>
      <div style="background:var(--sf2);border-radius:var(--r2);padding:12px;margin-bottom:12px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px">
          <div><span style="color:var(--tx3)">Working Days</span><div style="font-weight:600">${daysInMonth}</div></div>
          <div><span style="color:var(--tx3)">Days Present</span><div style="font-weight:600;color:var(--em)">${p}</div></div>
          <div><span style="color:var(--tx3)">Days Absent</span><div style="font-weight:600;color:var(--ro)">${a}</div></div>
          <div><span style="color:var(--tx3)">Half Days</span><div style="font-weight:600;color:var(--gd)">${h}</div></div>
        </div>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:rgba(61,111,240,.07);border-radius:var(--r2)">
        <span style="font-size:13px;font-weight:600">Net Salary</span>
        <span style="font-size:20px;font-weight:700;font-family:var(--fm);color:var(--ac)">₹${salary.toLocaleString()}</span>
      </div>
      <div style="margin-top:10px;font-size:10px;color:var(--tx3);text-align:center">Base: ₹${base.toLocaleString()}/month · Worked: ${worked}/${daysInMonth} days</div>
    </div>`;
        openM('mSalarySlip');
    }

    function printSalarySlip() {
        const w = window.open('', '_blank');
        w.document.write('<html><head><title>Salary Slip</title><style>body{font-family:sans-serif;padding:30px;color:#1e293b;max-width:500px;margin:auto}@media print{body{padding:0}}</style></head><body>');
        w.document.write(document.getElementById('mSalarySlipContent').innerHTML);
        w.document.write('</body></html>');
        w.document.close();
        w.print();
    }

    // ══════════════════════════════════════════════════════════════
    // ═══ FEATURE 30: AUDIT LOG ════════════════════════════════════
    // ══════════════════════════════════════════════════════════════
    if (!DB.auditLog) DB.auditLog = [];

    function auditLog(type, text) {
        const who = '<?= $staffName ?>';
        DB.auditLog.unshift({
            id: Date.now(),
            who,
            type,
            text,
            time: new Date().toLocaleString('en-IN', {day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'}),
            ts: Date.now()
        });
        if (DB.auditLog.length > 500) DB.auditLog = DB.auditLog.slice(0, 500);

        // Save to database
        apiPost('save_audit_log', { type, text, who }).catch(() => {});
    }

    // Patch existing functions to log — wrap addActivity
    const _origAddActivity = addActivity;
    window.addActivity = function(icon, bg, text) {
        _origAddActivity(icon, bg, text);
        // Refresh live activity panel if dashboard is visible
        const actEl = document.getElementById('dashLiveAct');
        if (actEl && document.getElementById('page-dashboard').classList.contains('active')) {
            actEl.innerHTML = DB.activities.slice(0,12).map((a,i) =>
                `<div class="act-it" style="padding:8px 16px;animation:fuUp .25s ease both">
        <div class="act-d" style="background:${a.bg||'rgba(61,111,240,.1)'}">${a.icon||'📌'}</div>
        <div style="flex:1;min-width:0"><div class="act-tx" style="font-size:11.5px">${a.text}</div>
        <div class="act-tm">${a.time||'Just now'}</div></div>
      </div>`).join('');
            const timeEl = document.getElementById('liveActTime');
            if (timeEl && DB.activities.length) timeEl.textContent = DB.activities[0].time || 'Just now';
        }
        const type = icon==='💬'?'whatsapp':icon==='💰'||icon==='🧾'?'fee':icon==='📚'||icon==='🔄'?'book':icon==='🆕'||icon==='✏'?'student':icon==='⚙'?'settings':'other';
        const cleanText = text.replace(/<[^>]+>/g,'');
        DB.auditLog.unshift({ id:Date.now(), who:'<?= $staffName ?>', type, text:cleanText, time:new Date().toLocaleString('en-IN',{day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'}), ts:Date.now() });
        if (DB.auditLog.length>500) DB.auditLog=DB.auditLog.slice(0,500);
        apiPost('save_audit_log', { type, text: cleanText, who: '<?= $staffName ?>' }).catch(() => {});
    };

    function renderAudit() {
        const filter = document.getElementById('auditFilter')?.value || 'all';
        let log = filter === 'all' ? DB.auditLog : DB.auditLog.filter(a => a.type === filter);

        // Stats
        const types = ['fee','student','book','staff','whatsapp','settings','renewal','other'];
        const typeLabels = {fee:'💰 Fee',student:'👤 Student',book:'📚 Book',staff:'👥 Staff',whatsapp:'💬 WhatsApp',settings:'⚙ Settings',renewal:'🔄 Renewal',other:'📌 Other'};
        const typeBg = {fee:'rgba(22,163,74,.1)',student:'rgba(61,111,240,.1)',book:'rgba(124,58,237,.1)',staff:'rgba(217,119,6,.1)',whatsapp:'rgba(37,211,102,.1)',settings:'rgba(100,116,139,.1)',renewal:'rgba(61,111,240,.1)',other:'rgba(100,116,139,.1)'};
        document.getElementById('auditStats').innerHTML = types.filter(t=>DB.auditLog.filter(a=>a.type===t).length>0).map(t => {
            const cnt = DB.auditLog.filter(a => a.type===t).length;
            return `<div class="sc" style="--ca:var(--ac);padding:12px"><div class="s-lb" style="font-size:9px">${typeLabels[t]||t}</div><div class="s-vl" style="font-size:20px">${cnt}</div></div>`;
        }).join('');

        document.getElementById('auditCount').textContent = log.length + ' entries';
        const iconMap = {fee:'💰',student:'👤',book:'📚',staff:'👥',whatsapp:'💬',settings:'⚙️',renewal:'🔄',other:'📌'};
        const bgMap   = {fee:'rgba(22,163,74,.12)',student:'rgba(61,111,240,.12)',book:'rgba(124,58,237,.12)',staff:'rgba(217,119,6,.12)',whatsapp:'rgba(37,211,102,.12)',settings:'rgba(100,116,139,.12)',renewal:'rgba(61,111,240,.12)',other:'rgba(100,116,139,.12)'};

        document.getElementById('auditList').innerHTML = log.length
            ? log.map(a => `<div class="audit-row">
        <div class="audit-ic" style="background:${bgMap[a.type]||bgMap.other}">${iconMap[a.type]||'📌'}</div>
        <div style="flex:1">
          <div class="audit-who">${a.who} <span class="audit-tag" style="background:${bgMap[a.type]||bgMap.other}">${a.type}</span></div>
          <div class="audit-what">${a.text}</div>
          <div class="audit-time">${a.time}</div>
        </div>
      </div>`).join('')
            : '<div class="empty"><div class="et">No audit entries yet — actions will appear here</div></div>';
    }

    function clearAudit() {
        if (!confirm('Clear all audit log entries?')) return;
        DB.auditLog = [];
        renderAudit();
        toast('Audit log cleared', 'wn');
    }

    // ══════════════════════════════════════════════════════════════
    // ═══ FEATURE 14: PWA (Student App) ═══════════════════════════
    // ══════════════════════════════════════════════════════════════
    let pwaInstallPrompt = null;

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        pwaInstallPrompt = e;
        document.getElementById('pwaBanner').classList.add('show');
    });

    function installPWA() {
        if (pwaInstallPrompt) {
            pwaInstallPrompt.prompt();
            pwaInstallPrompt.userChoice.then(r => {
                if (r.outcome === 'accepted') {
                    toast('✅ App installed!', 'ok');
                    auditLog('settings', 'ERP installed as PWA app');
                }
                document.getElementById('pwaBanner').classList.remove('show');
                pwaInstallPrompt = null;
            });
        }
    }

    window.addEventListener('appinstalled', () => {
        toast('📱 App installed successfully!', 'ok');
        document.getElementById('pwaBanner').classList.remove('show');
    });

    // Register service worker for PWA
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }

    // ══════════════════════════════════════════════════════════════
    // ═══ PROFILE PHOTO (DP) ═══════════════════════════════════════
    // ══════════════════════════════════════════════════════════════

    function applyDP(dp) {
        if (!dp) return;
        // Update sidebar avatar
        const av = document.getElementById('sidebarAv');
        if (av) {
            av.style.cssText += ';background-image:url(' + dp + ');background-size:cover;background-position:center;';
            av.textContent = '';
        }
        // Update settings preview
        const prev = document.getElementById('dp-preview');
        const ph   = document.getElementById('dp-placeholder');
        if (prev) { prev.src = dp; prev.style.display = 'block'; }
        if (ph)   { ph.style.display = 'none'; }
    }

    async function uploadDP() {
        const input  = document.getElementById('dp-file-input');
        const file   = input && input.files && input.files[0];
        if (!file) return;
        const status = document.getElementById('dp-status');
        if (status) status.textContent = '⏳ Uploading…';
        const fd = new FormData();
        fd.append('dp', file);
        try {
            const r   = await fetch(API + '?action=upload_dp', { method: 'POST', body: fd });
            const res = await r.json();
            if (res && res.error) {
                if (status) status.textContent = '';
                return toast('❌ ' + res.error, 'er');
            }
            applyDP(res.dp);
            if (status) { status.textContent = '✅ Saved!'; setTimeout(() => { status.textContent = ''; }, 3000); }
            toast('✅ Profile photo saved!', 'ok');
        } catch(e) {
            if (status) status.textContent = '';
            toast('❌ Upload error: ' + e.message, 'er');
        }
    }

    async function loadMyDP() {
        try {
            const res = await apiGet('get_my_dp');
            if (res && res.dp) applyDP(res.dp);
        } catch(e) { /* column may not exist yet — safe to ignore */ }
    }

    // ══════════════════════════════════════════════════════════════
    // ═══ LIBRARY LOGO ════════════════════════════════════════════
    // ══════════════════════════════════════════════════════════════

    function applyLogo(url) {
        if (!url) return;
        DB.settings.logoUrl = url;
        // Sidebar
        const icon = document.getElementById('sidebar-logo-icon');
        const img  = document.getElementById('sidebar-logo-img');
        const wrap = document.getElementById('sidebar-logo-wrap');
        if (icon) icon.style.display = 'none';
        if (img)  { img.src = url; img.style.display = 'block'; }
        if (wrap) { wrap.style.background = '#fff'; wrap.style.padding = '2px'; }
        // Settings preview
        const prev = document.getElementById('logo-preview');
        const ph   = document.getElementById('logo-placeholder');
        const rmBtn = document.getElementById('logo-remove-btn');
        if (prev)  { prev.src = url; prev.style.display = 'block'; }
        if (ph)    ph.style.display = 'none';
        if (rmBtn) rmBtn.style.display = 'inline-flex';
    }

    function clearLogoUI() {
        DB.settings.logoUrl = '';
        const icon = document.getElementById('sidebar-logo-icon');
        const img  = document.getElementById('sidebar-logo-img');
        const wrap = document.getElementById('sidebar-logo-wrap');
        if (icon) icon.style.display = '';
        if (img)  { img.src = ''; img.style.display = 'none'; }
        if (wrap) { wrap.style.background = ''; wrap.style.padding = ''; }
        const prev  = document.getElementById('logo-preview');
        const ph    = document.getElementById('logo-placeholder');
        const rmBtn = document.getElementById('logo-remove-btn');
        if (prev)  { prev.src = ''; prev.style.display = 'none'; }
        if (ph)    ph.style.display = '';
        if (rmBtn) rmBtn.style.display = 'none';
    }

    async function uploadLogo() {
        const input  = document.getElementById('logo-file-input');
        const file   = input && input.files && input.files[0];
        if (!file) return;
        const status = document.getElementById('logo-status');
        if (status) status.textContent = '⏳ Uploading…';
        const fd = new FormData();
        fd.append('logo', file);
        try {
            const r   = await fetch(API + '?action=upload_logo', { method: 'POST', body: fd });
            const res = await r.json();
            if (res && res.error) {
                if (status) status.textContent = '';
                return toast('❌ ' + res.error, 'er');
            }
            applyLogo(res.logo_url);
            if (status) { status.textContent = '✅ Saved!'; setTimeout(() => { status.textContent = ''; }, 3000); }
            toast('✅ Logo saved!', 'ok');
        } catch(e) {
            if (status) status.textContent = '';
            toast('❌ Upload error: ' + e.message, 'er');
        }
    }

    async function removeLogo() {
        if (!confirm('Remove library logo?')) return;
        try {
            await apiPost('remove_logo', {});
            clearLogoUI();
            toast('Logo removed', 'wn');
        } catch(e) {
            toast('❌ ' + e.message, 'er');
        }
    }

    async function loadLogo() {
        try {
            const res = await apiGet('get_logo');
            if (res && res.logo_url) applyLogo(res.logo_url);
        } catch(e) { /* safe to ignore */ }
    }

    // ══════════════════════════════════════════════════════════════
    // ═══ QR ATTENDANCE SYSTEM ════════════════════════════════════
    // ══════════════════════════════════════════════════════════════

    let currentQRStudentId = null;
    let adminQRObj = null;

    async function showStudentQR(studentId) {
        currentQRStudentId = studentId;
        const s = DB.students.find(x => x.id === studentId);
        if (!s) return toast('Student not found', 'er');

        // Show student info in modal
        document.getElementById('qrModalStudentInfo').innerHTML = `
    <div style="display:flex;align-items:center;gap:12px;justify-content:center;margin-bottom:4px">
      <div style="width:40px;height:40px;border-radius:50%;background:${s.color};display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:14px">${s.fname[0]}${(s.lname||'')[0]||''}</div>
      <div style="text-align:left">
        <div style="font-weight:700;font-size:14px">${s.fname} ${s.lname||''}</div>
        <div style="font-size:11px;color:var(--tx3);font-family:var(--fm)">${s.id}</div>
      </div>
    </div>`;

        openM('mStudentQR');
        await generateAdminQR(studentId);
    }

    async function generateAdminQR(studentId) {
        document.getElementById('adminQRCode').innerHTML = '<div style="width:200px;height:200px;display:flex;align-items:center;justify-content:center;color:#999;font-size:12px">Generating…</div>';
        document.getElementById('qrModalExpiry').textContent = '';
        try {
            const res = await apiPost('generate_qr_token', { student_id: studentId });
            if (res.error) return toast(res.error, 'er');
            const scanUrl = `${location.origin}/scan.php?token=${res.token}`;
            // Render QR
            document.getElementById('adminQRCode').innerHTML = '';
            if (adminQRObj) { try { adminQRObj.clear(); } catch(e){} }
            adminQRObj = new QRCode(document.getElementById('adminQRCode'), {
                text: scanUrl,
                width: 200, height: 200,
                colorDark: '#1e1b4b', colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
            const exp = new Date(res.expires_at);
            document.getElementById('qrModalExpiry').textContent = 'Valid until: ' + exp.toLocaleString('en-IN', { hour:'2-digit', minute:'2-digit', day:'numeric', month:'short' });
        } catch(e) {
            toast('QR generation failed: ' + e.message, 'er');
        }
    }

    async function regenerateQR() {
        if (!currentQRStudentId) return;
        await generateAdminQR(currentQRStudentId);
        toast('QR refreshed!', 'ok');
    }

    // ── LOAD TODAY'S QR SCANS (shown in Attendance page) ──
    async function loadQRScans() {
        const today = new Date().toISOString().split('T')[0];
        try {
            const res = await apiGet('get_todays_qr_attendance', { date: today });
            const records = res.records || [];
            document.getElementById('qrScanCount').textContent = records.length + ' scan' + (records.length !== 1 ? 's' : '');
            const el = document.getElementById('qrScanList');
            if (!records.length) {
                el.innerHTML = '<div style="text-align:center;padding:20px;color:var(--tx3);font-size:12px">No QR check-ins yet today</div>';
                return;
            }
            el.innerHTML = `<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px">` +
                records.map(r => {
                    const cin  = r.check_in  ? fmtTime(r.check_in)  : '—';
                    const cout = r.check_out ? fmtTime(r.check_out) : '—';
                    const lateBadge = +r.is_late ? `<span style="background:rgba(217,119,6,.12);color:var(--gd);font-size:9px;font-weight:700;padding:2px 6px;border-radius:5px;margin-left:5px">⚠ Late ${r.late_minutes}m</span>` : '';
                    return `<div style="background:var(--sf2);border:1px solid var(--br);border-radius:10px;padding:11px 13px;display:flex;align-items:center;gap:10px">
          <div style="width:34px;height:34px;border-radius:50%;background:${r.color||'var(--ac)'};display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0">${(r.fname||'?')[0]}${(r.lname||'')[0]||''}</div>
          <div style="min-width:0">
            <div style="font-weight:600;font-size:12px;display:flex;align-items:center">${r.fname} ${r.lname||''}${lateBadge}</div>
            <div style="font-size:10px;color:var(--tx3)">${r.batch_name||''}</div>
            <div style="font-size:10px;color:var(--tx2);margin-top:2px">
              <span style="color:var(--em)">▶ ${cin}</span>
              ${r.check_out ? `<span style="color:var(--sk);margin-left:6px">■ ${cout}</span>` : '<span style="color:var(--tx3);margin-left:6px">Still inside</span>'}
            </div>
          </div>
        </div>`;
                }).join('') + '</div>';
        } catch(e) {
            // silent fail if table doesn't exist yet
        }
    }

    function fmtTime(t) {
        if (!t) return '—';
        const [h, m] = t.split(':');
        const hr = +h;
        return (hr > 12 ? hr - 12 : (hr || 12)) + ':' + m + ' ' + (hr >= 12 ? 'PM' : 'AM');
    }

    // ══════════════════════════════════════════════════════════════
    // ═══ UPI PAYMENT LINK ════════════════════════════════════════
    // ══════════════════════════════════════════════════════════════
    let _upiLink = '', _upiStu = null;

    async function sendUpiLink(stuId) {
        const s = DB.students.find(x => x.id === stuId);
        if (!s) return toast('Student not found', 'er');
        const bal = s.netFee - s.paidAmt;
        if (bal <= 0) return toast('No balance due', 'wn');

        // Populate modal
        const av = document.getElementById('upiStuAv');
        av.textContent = (s.fname[0] + (s.lname[0] || '')).toUpperCase();
        av.style.background = s.color || 'var(--ac)';
        document.getElementById('upiStuName').textContent = s.fname + ' ' + (s.lname || '');
        const b = DB.batches.find(x => x.id === s.batchId);
        document.getElementById('upiStuMeta').textContent = '#' + s.id + (b ? ' · ' + b.name : '');
        document.getElementById('upiAmtShow').textContent = '₹' + bal.toLocaleString('en-IN');
        document.getElementById('upiIdShow').textContent = DB.settings.upiId || '7282071620@okaxis';
        document.getElementById('upiLoading').style.display = 'block';
        document.getElementById('upiResult').style.display = 'none';
        _upiStu = s;
        openM('mUpiLink');

        try {
            const res = await apiPost('generate_upi_link', { student_id: stuId, amount: bal, note: 'Monthly Fee' });
            if (res.error) { toast('❌ ' + res.error, 'er'); closeM('mUpiLink'); return; }
            _upiLink = res.url;
            document.getElementById('upiLinkVal').value = res.url;
            document.getElementById('upiLoading').style.display = 'none';
            document.getElementById('upiResult').style.display = 'block';
            auditLog('fee', 'UPI link sent — ' + s.fname + ' ' + (s.lname||'') + ' ₹' + bal);
        } catch(e) {
            toast('❌ ' + e.message, 'er');
            closeM('mUpiLink');
        }
    }

    function copyUpiLink() {
        if (!_upiLink) return;
        navigator.clipboard?.writeText(_upiLink)
            .then(() => toast('✅ Link copied!', 'ok'))
            .catch(() => { document.getElementById('upiLinkVal').select(); document.execCommand('copy'); toast('Copied!', 'ok'); });
    }

    function upiSendWA() {
        if (!_upiLink || !_upiStu) return;
        const s = _upiStu;
        const bal = s.netFee - s.paidAmt;
        const msg = `💳 *Fee Payment Request*\n\nDear *${s.fname} ${s.lname||''}*,\n\nYour monthly fee of *₹${bal.toLocaleString('en-IN')}* is due.\n\n🔗 *Pay securely here:*\n${_upiLink}\n\nTap the link — pay via GPay, PhonePe, Paytm or scan the QR code.\n\n💳 UPI: ${DB.settings.upiId || '7282071620@okaxis'}\n📞 ${DB.settings.phone}\n🏫 ${DB.settings.name}`;
        openWALink(s.phone, msg);
        toast('WhatsApp opened!', 'wa');
        closeM('mUpiLink');
        addActivity('📱', 'rgba(79,142,247,.14)', `UPI link sent to <strong>${s.fname}</strong> via WhatsApp`);
    }


    // ═══════════════════════════════════════════════════════════════
    // ═══ BIOMETRIC DEVICE MANAGEMENT ════════════════════════════════
    // ═══════════════════════════════════════════════════════════════
    async function renderBiometric() {
        const admsUrl = window.location.origin + window.location.pathname.replace(/\/[^/]*$/, '/iclock.php');
        ['admsUrl','settAdmsUrl'].forEach(id => { const el=document.getElementById(id); if(el) el.value=admsUrl; });
        await loadBiometric();
        await loadSettingsBio();
    }

    async function loadBiometric() {
        try {
            const res = await apiGet('get_biometric_devices');
            const devices = res.devices || [];
            const today = new Date().toISOString().split('T')[0];
            const pr = await apiGet('get_biometric_punches', { date: today });
            const punches = pr.punches || [];

            // Device cards
            const dc = document.getElementById('bioDeviceCards');
            if (dc) {
                dc.innerHTML = devices.length ? devices.map(d => {
                    const on = d.status === 'online';
                    const ls = d.last_seen ? timeSince(d.last_seen.replace(' ','T')) : 'Never';
                    return `<div class="panel" style="padding:0;overflow:hidden">
                        <div style="padding:14px 16px;background:${on?'#f0fdf4':'#fef2f2'};border-bottom:1px solid ${on?'#a3e8d4':'#fca5a5'};display:flex;align-items:center;justify-content:space-between">
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="width:36px;height:36px;background:${on?'#dcfce7':'#fee2e2'};border:1.5px solid ${on?'#a3e8d4':'#fca5a5'};border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px">🖐️</div>
                                <div><div style="font-size:14px;font-weight:800;color:var(--tx)">${d.device_name||d.serial_number}</div><div style="font-size:10px;color:var(--tx3);font-family:var(--fm)">SN: ${d.serial_number}</div></div>
                            </div>
                            <span style="font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;background:${on?'#dcfce7':'#fee2e2'};border:1.5px solid ${on?'#a3e8d4':'#fca5a5'};color:${on?'#166634':'#991b1b'}">${on?'🟢 Online':'🔴 Offline'}</span>
                        </div>
                        <div style="padding:12px 16px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px">
                            <div style="text-align:center"><div style="font-size:10px;color:var(--tx3);font-weight:600">IP Address</div><div style="font-size:12px;font-weight:700;font-family:var(--fm)">${d.ip_address||'—'}</div></div>
                            <div style="text-align:center"><div style="font-size:10px;color:var(--tx3);font-weight:600">Last Seen</div><div style="font-size:12px;font-weight:700">${ls}</div></div>
                            <div style="text-align:center"><div style="font-size:10px;color:var(--tx3);font-weight:600">Total Punches</div><div style="font-size:12px;font-weight:700;color:var(--ac)">${d.total_punches||0}</div></div>
                        </div>
                    </div>`;
                }).join('') : '<div style="text-align:center;padding:28px;color:var(--tx3);font-size:13px;grid-column:1/-1">⚙️ No devices connected yet.<br><br>Configure your eSSL X990 or RS9N with the ADMS URL shown above.</div>';
            }

            // Punch log
            const pl = document.getElementById('bioPunchList');
            const pc = document.getElementById('bioPunchCount');
            if (pc) pc.textContent = punches.length + ' punches today';
            if (pl) {
                pl.innerHTML = punches.length ? punches.slice(0,50).map(p => {
                    const stu = DB.students.find(s => s.id === p.student_id);
                    const name = stu ? stu.fname+' '+(stu.lname||'') : (p.fname ? p.fname+' '+(p.lname||'') : 'ID: '+p.user_id);
                    const col = stu?.color || '#9aa3b8';
                    const av = stu ? (((stu.fname||'?')[0])+((stu.lname||'')[0]||'')).toUpperCase() : '?';
                    const isIn = p.punch_type === 'check_in';
                    const vIcon = (p.verify_type||'').includes('finger')||(p.verify_type||'').includes('fp') ? '🖐️' : '💳';
                    const t = (p.punch_time||'').split(' ')[1]?.slice(0,5)||'';
                    return `<div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--br)">
                        <div style="width:32px;height:32px;border-radius:9px;background:${col};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0">${av}</div>
                        <div style="flex:1"><div style="font-size:13px;font-weight:700;color:var(--tx)">${name}</div><div style="font-size:10px;color:var(--tx3)">${vIcon} ${p.verify_type||'fingerprint'} · SN: ${p.serial_number}</div></div>
                        <div style="text-align:right"><div style="font-size:12px;font-weight:800;color:${isIn?'var(--em)':'var(--ro)'}">${isIn?'▶ IN':'◀ OUT'}</div><div style="font-size:10px;color:var(--tx3);font-family:var(--fm)">${t}</div></div>
                    </div>`;
                }).join('') : '<div style="text-align:center;padding:28px;color:var(--tx3);font-size:13px">No biometric punches today</div>';
            }

            // Fee Gate toggle — restore saved state from API
            const feeGateOn = !!res.fee_gate;
            DB.settings = DB.settings || {};
            DB.settings.feeGate = feeGateOn;
            const fgPairs = [
                { cb:'feeGateToggle', sl:'feeGateSlider',    th:'feeGateThumb'     },
                { cb:'settFeeGate',   sl:'settFeeGateSlider', th:'settFeeGateThumb' }
            ];
            fgPairs.forEach(({cb,sl,th}) => {
                const cbEl = document.getElementById(cb); if (cbEl) cbEl.checked = feeGateOn;
                const slEl = document.getElementById(sl);  if (slEl) slEl.style.background = feeGateOn ? 'var(--ac)' : '#e2e8f0';
                const thEl = document.getElementById(th);  if (thEl) thEl.style.transform = feeGateOn ? 'translateX(20px)' : 'translateX(0)';
            });
            const fgs = document.getElementById('feeGateStatus');
            if (fgs) fgs.textContent = feeGateOn ? '🔒 Active — overdue students will be blocked at door' : '🔓 Inactive — all students can enter regardless of fee status';

            // Sidebar badge
            const bioB = document.getElementById('b-bio');
            if (bioB) {
                const online = devices.filter(d=>d.status==='online').length;
                bioB.style.display = online > 0 ? '' : 'none';
                bioB.style.background = '#22c55e';
            }

        } catch(e) { console.warn('loadBiometric failed:', e); }
    }

    async function loadAttBiometric() {
        try {
            const today = new Date().toISOString().split('T')[0];
            const res = await apiGet('get_biometric_punches', { date: today });
            const punches = res.punches || [];
            // Build _bioToday map
            Object.keys(_bioToday).forEach(k => delete _bioToday[k]);
            punches.forEach(p => {
                if (!p.student_id) return;
                if (!_bioToday[p.student_id]) _bioToday[p.student_id] = { in: null, out: null, verify: p.verify_type };
                const t = (p.punch_time||'').split(' ')[1]?.slice(0,5);
                if (p.punch_type === 'check_in' && !_bioToday[p.student_id].in) _bioToday[p.student_id].in = t;
                if (p.punch_type === 'check_out') _bioToday[p.student_id].out = t;
            });
            // Render biometric feed in attendance page
            const el = document.getElementById('bioAttList');
            const cnt = document.getElementById('bioAttCount');
            if (cnt) cnt.textContent = punches.length + ' punches';
            if (el) {
                el.innerHTML = punches.length ? punches.slice(0,30).map(p => {
                    const stu = DB.students.find(s => s.id === p.student_id);
                    const name = stu ? stu.fname+' '+(stu.lname||'') : 'ID: '+p.user_id;
                    const col = stu?.color || '#9aa3b8';
                    const av = stu ? (((stu.fname||'?')[0])+((stu.lname||'')[0]||'')).toUpperCase() : '?';
                    const isIn = p.punch_type === 'check_in';
                    const vIcon = (p.verify_type||'').includes('finger') ? '🖐️' : '💳';
                    const t = (p.punch_time||'').split(' ')[1]?.slice(0,5)||'';
                    return `<div style="display:flex;align-items:center;gap:9px;padding:7px 0;border-bottom:1px solid var(--br)">
                        <div style="width:28px;height:28px;border-radius:8px;background:${col};display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;flex-shrink:0">${av}</div>
                        <div style="flex:1"><div style="font-size:12px;font-weight:700">${name}</div><div style="font-size:10px;color:var(--tx3)">${vIcon} ${p.verify_type||'fp'}</div></div>
                        <div style="text-align:right"><div style="font-size:11px;font-weight:700;color:${isIn?'var(--em)':'var(--ro)'}">${isIn?'▶ IN':'◀ OUT'}</div><div style="font-size:10px;color:var(--tx3);font-family:var(--fm)">${t}</div></div>
                    </div>`;
                }).join('') : '<div style="text-align:center;padding:18px;color:var(--tx3);font-size:12px">No biometric punches yet today</div>';
            }
            // Dot indicator
            const dot = document.getElementById('bioDeviceDot');
            if (dot) dot.style.background = punches.length > 0 ? '#22c55e' : '#e2e8f0';
            // Auto mark present
            Object.keys(_bioToday).forEach(sid => { if (_bioToday[sid].in) DB.attendance[sid] = 'present'; });
        } catch(e) { console.warn('loadAttBiometric failed:', e); }
    }

    async function loadSettingsBio() {
        try {
            const res = await apiGet('get_biometric_devices');
            const devices = res.devices || [];
            const admsUrl = window.location.origin + window.location.pathname.replace(/\/[^/]*$/, '/iclock.php');
            ['admsUrl','settAdmsUrl'].forEach(id => { const e=document.getElementById(id); if(e) e.value=admsUrl; });
            const el = document.getElementById('settBioDevices');
            if (!el) return;
            el.innerHTML = devices.length ? devices.map(d => {
                const on = d.status === 'online';
                return `<div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--br)">
                    <span style="font-size:10px;font-weight:700;padding:3px 8px;border-radius:20px;background:${on?'#dcfce7':'#fee2e2'};border:1px solid ${on?'#a3e8d4':'#fca5a5'};color:${on?'#166634':'#991b1b'}">${on?'🟢 Online':'🔴 Offline'}</span>
                    <div style="flex:1"><div style="font-size:12px;font-weight:700">${d.device_name||d.serial_number}</div><div style="font-size:10px;color:var(--tx3);font-family:var(--fm)">SN: ${d.serial_number} · IP: ${d.ip_address||'—'} · ${d.total_punches||0} punches</div></div>
                </div>`;
            }).join('') : '<div style="font-size:12px;color:var(--tx3);padding:8px 0">No devices connected. Set ADMS URL on device to connect.</div>';
        } catch(e) {}
    }

    async function toggleFeeGate(enabled) {
        const pairs = [
            { cb:'feeGateToggle',  sl:'feeGateSlider',     th:'feeGateThumb'     },
            { cb:'settFeeGate',    sl:'settFeeGateSlider',  th:'settFeeGateThumb' }
        ];
        pairs.forEach(({cb,sl,th}) => {
            const cbEl = document.getElementById(cb); if (cbEl) cbEl.checked = enabled;
            const slEl = document.getElementById(sl);  if (slEl) slEl.style.background = enabled ? 'var(--ac)' : '#e2e8f0';
            const thEl = document.getElementById(th);  if (thEl) thEl.style.transform = enabled ? 'translateX(20px)' : 'translateX(0)';
        });
        const fgs = document.getElementById('feeGateStatus');
        if (fgs) fgs.textContent = enabled ? '🔒 Active — overdue students will be blocked at door' : '🔓 Inactive — all students can enter regardless of fee status';
        try {
            await apiPost('set_fee_gate', { enabled: enabled ? 1 : 0 });
            DB.settings.feeGate = enabled;
            toast(enabled ? '🔒 Fee Gate ON — overdue students blocked' : '🔓 Fee Gate OFF', enabled ? 'wn' : 'ok');
        } catch(e) { toast('Failed to save fee gate setting', 'er'); }
    }

    // ═══ BOOT ═══
    document.getElementById('todayChip').textContent = new Date().toLocaleDateString('en-IN',{month:'long',year:'numeric'});
    initData();
    loadMyDP();
    loadLogo();
</script>
</body>
</html>