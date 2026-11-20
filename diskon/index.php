<?php
date_default_timezone_set('Asia/Jakarta');

// Persentase diskon berdasarkan total belanja
function persenDiskon(int $totalBelanja): int
{
    if ($totalBelanja >= 100000) return 10;
    if ($totalBelanja >= 50000)  return 5;
    return 0;
}

// Prosedur utama: menerima total belanja, mengembalikan nilai diskon (rupiah)
function hitungDiskon(int $totalBelanja): int
{
    return (int) round($totalBelanja * persenDiskon($totalBelanja) / 100);
}

function rupiah(int $angka): string
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

$input = '';
$error = '';
$hasil = null;
$posisi = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = is_string($_POST['total_belanja'] ?? null) ? $_POST['total_belanja'] : '';
    $angka = preg_replace('/\D/', '', $input);

    if ($angka === '' || strlen($angka) > 12) {
        $error = 'Isi total belanja dengan angka, misalnya 75000.';
    } else {
        $total  = (int) $angka;
        $diskon = hitungDiskon($total);

        $hasil = [
            'total'  => $total,
            'persen' => persenDiskon($total),
            'diskon' => $diskon,
            'bayar'  => $total - $diskon,
            'kurang' => 0,
            'target' => 0,
        ];
        if ($total < 50000) {
            $hasil['kurang'] = 50000 - $total;
            $hasil['target'] = 5;
        } elseif ($total < 100000) {
            $hasil['kurang'] = 100000 - $total;
            $hasil['target'] = 10;
        }

        // Posisi penanda pada tangga diskon (0 sampai 100 persen lebar)
        if ($total < 50000) {
            $posisi = ($total / 50000) / 3;
        } elseif ($total < 100000) {
            $posisi = 1 / 3 + (($total - 50000) / 50000) / 3;
        } else {
            $posisi = 2 / 3 + min(($total - 100000) / 100000, 1) / 3;
        }
        $posisi = round($posisi * 100, 2);
    }
}
$aktif = $hasil['persen'] ?? -1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MENGHITUNG DISKON</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;800&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --biru:   #2340d8;
            --kertas: #fbfbf6;
            --tinta:  #14142b;
            --sorot:  #ffe14d;
            --cap:    #e8344e;
            --judul:  "Bricolage Grotesque", "Trebuchet MS", Verdana, sans-serif;
            --struk:  "Courier Prime", "Courier New", monospace;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--biru);
            color: var(--kertas);
            font-family: var(--judul);
            font-weight: 500;
            line-height: 1.5;
        }
        .halaman {
            max-width: 1040px;
            margin: 0 auto;
            padding: 56px 24px 72px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 56px;
            align-items: start;
        }

        /* ---------- Sisi kiri ---------- */
        h1 {
            margin: 0 0 12px;
            font-size: clamp(2.3rem, 5.2vw, 3.7rem);
            font-weight: 800;
            line-height: 1.04;
            letter-spacing: -0.02em;
        }
        .sub { margin: 0 0 36px; font-size: 1.1rem; max-width: 34ch; opacity: .85; }

        label { display: block; margin-bottom: 4px; font-size: 1rem; }
        .isian {
            display: flex;
            align-items: baseline;
            gap: 14px;
            border-bottom: 3px solid var(--sorot);
        }
        .isian span { font-size: 1.6rem; font-weight: 800; color: var(--sorot); }
        .isian input {
            width: 100%;
            padding: 4px 0 8px;
            border: 0;
            background: transparent;
            color: var(--kertas);
            font: 800 2.8rem/1.1 var(--judul);
            letter-spacing: -0.01em;
        }
        .isian input::placeholder { color: rgba(255,255,255,.35); }
        .isian input:focus { outline: none; }
        .isian:focus-within { border-bottom-color: var(--kertas); }

        .galat {
            margin: 12px 0 0;
            padding: 8px 12px;
            background: var(--sorot);
            color: var(--tinta);
            font-weight: 800;
            border-radius: 4px;
        }

        .cepat { display: flex; flex-wrap: wrap; gap: 8px; margin: 20px 0 28px; }

        .cetak {
    margin-top: 28px;   /* baris baru ini yang memberi jarak */
    padding: 14px 28px;
    /* sisanya tetap */
}
        .cepat button {
            padding: 6px 16px;
            border: 2px solid rgba(255,255,255,.55);
            border-radius: 999px;
            background: transparent;
            color: var(--kertas);
            font: inherit;
            cursor: pointer;
        }
        .cepat button:hover { background: var(--kertas); color: var(--biru); border-color: var(--kertas); }

        .cetak {
            padding: 14px 28px;
            border: 0;
            border-radius: 6px;
            background: var(--sorot);
            color: var(--tinta);
            font: 800 1.1rem var(--judul);
            box-shadow: 4px 4px 0 var(--tinta);
            cursor: pointer;
            transition: transform .08s, box-shadow .08s;
        }
        .cetak:active { transform: translate(4px, 4px); box-shadow: 0 0 0 var(--tinta); }
        button:focus-visible, input:focus-visible { outline: 3px solid var(--kertas); outline-offset: 3px; }

        /* Tangga diskon */
        .tangga { margin-top: 56px; max-width: 460px; }
        .tangga h2 { margin: 0 0 18px; font-size: 1rem; font-weight: 800; }
        .lintasan {
            position: relative;
            height: 10px;
            border-radius: 5px;
            background: rgba(255,255,255,.25);
        }
        .lintasan .isi {
            position: absolute; inset: 0 auto 0 0;
            width: var(--pos, 0%);
            border-radius: 5px;
            background: var(--sorot);
            transition: width .6s cubic-bezier(.2,.8,.2,1);
        }
        .lintasan .penanda {
            position: absolute; top: 50%;
            left: var(--pos, 0%);
            width: 22px; height: 22px;
            margin: -11px 0 0 -11px;
            border: 4px solid var(--biru);
            border-radius: 50%;
            background: var(--kertas);
            box-shadow: 0 0 0 3px var(--sorot);
            transition: left .6s cubic-bezier(.2,.8,.2,1);
        }
        .tangga:not(.terisi) .penanda { display: none; }
        .batas { position: relative; height: 22px; margin-top: 8px; font-size: .85rem; opacity: .8; }
        .batas span { position: absolute; transform: translateX(-50%); white-space: nowrap; }
        .tingkat { display: grid; grid-template-columns: repeat(3, 1fr); text-align: center; font-size: .95rem; }
        .tingkat span { opacity: .55; }
        .tingkat span.on { opacity: 1; font-weight: 800; color: var(--sorot); }

        /* ---------- Struk ---------- */
        .kanan { padding-top: 8px; }
        .struk {
            position: relative;
            padding: 30px 26px 34px;
            background: var(--kertas);
            color: var(--tinta);
            font-family: var(--struk);
            transform: rotate(-1.5deg);
            filter: drop-shadow(7px 9px 0 rgba(8, 14, 70, .55));
        }
        .struk::after {
            content: "";
            position: absolute; left: 0; right: 0; top: 100%;
            height: 9px;
            background: conic-gradient(from -45deg at bottom, #0000, var(--kertas) 1deg 89deg, #0000 90deg) 50% / 18px 100%;
        }
        .struk h2 { margin: 0; font: 800 1.5rem var(--judul); letter-spacing: -0.01em; }
        .meta { margin: 2px 0 18px; font-size: .9rem; opacity: .7; }
        .struk dl { margin: 0; padding: 14px 0; border-top: 2px dashed var(--tinta); border-bottom: 2px dashed var(--tinta); }
        .struk dl div { display: flex; justify-content: space-between; gap: 12px; padding: 3px 0; }
        .struk dt, .struk dd { margin: 0; }
        .sorot { background: linear-gradient(transparent 15%, var(--sorot) 15% 90%, transparent 90%); padding: 0 4px; font-weight: 700; }
        .bayar { padding: 16px 0 6px; }
        .bayar span { display: block; font-size: .95rem; }
        .bayar strong { display: block; font: 800 2.3rem/1.1 var(--judul); letter-spacing: -0.02em; word-break: break-all; }
        .catatan { margin: 14px 0 0; font-size: .92rem; }
        .kosong-teks { margin: 8px 0 0; padding: 14px 0; border-top: 2px dashed var(--tinta); }

        .stempel {
            position: absolute; right: 18px; bottom: 54px;
            padding: 6px 14px;
            border: 3px double var(--cap);
            border-radius: 6px;
            color: var(--cap);
            font: 800 1rem/1.2 var(--judul);
            text-align: center;
            transform: rotate(-12deg);
            opacity: .92;
        }
        .stempel b { display: block; font-size: 1.25rem; }

        .baru { animation: keluar .7s cubic-bezier(.2,.8,.2,1) both; }
        .baru .stempel { animation: cap .35s .6s cubic-bezier(.3,1.6,.5,1) both; }
        @keyframes keluar {
            from { opacity: 0; transform: translateY(-36px) rotate(-1.5deg); }
            to   { opacity: 1; transform: translateY(0) rotate(-1.5deg); }
        }
        @keyframes cap {
            from { opacity: 0; transform: scale(2.2) rotate(-12deg); }
            to   { opacity: .92; transform: scale(1) rotate(-12deg); }
        }
        @media (prefers-reduced-motion: reduce) {
            .baru, .baru .stempel { animation: none; }
            .lintasan .isi, .lintasan .penanda { transition: none; }
        }

        @media (max-width: 820px) {
            .halaman { grid-template-columns: 1fr; gap: 44px; padding-top: 36px; }
            .tangga { margin-top: 40px; }
        }
    </style>
</head>
<body>
<main class="halaman">

    <section class="kiri">
        <h1>Menghitung total diskon</h1>
        <p class="sub">Ketik totalnya, struknya langsung tercetak.</p>

        <form method="post" action="" novalidate>
            <label for="total">Total belanja</label>
            <div class="isian">
                <span>Rp</span>
                <input id="total" name="total_belanja" type="text" inputmode="numeric"
                       autocomplete="off" maxlength="16" placeholder="0"
                       value="<?= htmlspecialchars($input) ?>" autofocus>
            </div>

            <?php if ($error): ?>
                <p class="galat" role="alert"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>


            <button class="cetak" type="submit">Cetak struk</button>
        </form>

        <div class="tangga <?= $hasil ? 'terisi' : '' ?>" style="--pos: <?= $posisi ?>%">
            <h2>Tangga diskon</h2>
            <div class="lintasan">
                <div class="isi"></div>
                <div class="penanda"></div>
            </div>
            <div class="batas" aria-hidden="true">
                <span style="left: 33.33%">Rp 50.000</span>
                <span style="left: 66.66%">Rp 100.000</span>
            </div>
            <div class="tingkat">
                <span class="<?= $aktif === 0 ? 'on' : '' ?>">tanpa diskon</span>
                <span class="<?= $aktif === 5 ? 'on' : '' ?>">diskon 5%</span>
                <span class="<?= $aktif === 10 ? 'on' : '' ?>">diskon 10%</span>
            </div>
        </div>
    </section>

    <section class="kanan" aria-live="polite">
        <article class="struk <?= $hasil ? 'baru' : '' ?>">
            <h2>Toko Hemat</h2>
            <p class="meta"><?= date('d/m/Y H:i') ?> WIB</p>

            <?php if ($hasil): ?>
                <dl>
                    <div>
                        <dt>Total belanja</dt>
                        <dd><?= rupiah($hasil['total']) ?></dd>
                    </div>
                    <div>
                        <dt>Diskon <?= $hasil['persen'] ?>%</dt>
                        <dd class="sorot"><?= $hasil['diskon'] > 0 ? '- ' : '' ?><?= rupiah($hasil['diskon']) ?></dd>
                    </div>
                </dl>

                <div class="bayar">
                    <span>Total bayar</span>
                    <strong><?= rupiah($hasil['bayar']) ?></strong>
                </div>

                <p class="catatan">
                    <?php if ($hasil['target'] > 0): ?>
                        Tambah <?= rupiah($hasil['kurang']) ?> lagi, diskon naik jadi <?= $hasil['target'] ?>%.
                    <?php else: ?>
                        Diskon sudah maksimal. Terima kasih sudah belanja!
                    <?php endif; ?>
                </p>

                <?php if ($hasil['diskon'] > 0): ?>
                    <div class="stempel">Hemat<b><?= rupiah($hasil['diskon']) ?></b></div>
                <?php endif; ?>
            <?php else: ?>
                <p class="kosong-teks">Belum ada belanjaan. Isi total di sebelah kiri, lalu klik Cetak struk.</p>
            <?php endif; ?>
        </article>
    </section>

</main>

<script>
    const kolom = document.getElementById('total');
    const titik = (teks) => teks.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    kolom.value = titik(kolom.value);
    kolom.addEventListener('input', () => { kolom.value = titik(kolom.value); });

    document.querySelectorAll('[data-nilai]').forEach((tombol) => {
        tombol.addEventListener('click', () => {
            kolom.value = titik(tombol.dataset.nilai);
            kolom.focus();
        });
    });
</script>
</body>
</html>
