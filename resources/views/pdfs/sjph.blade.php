<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>SJPH — {{ $facility->name }}</title>
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; line-height: 1.5; color: #222; margin: 0; padding: 20px; }
    h1 { text-align: center; font-size: 16pt; margin-bottom: 2px; color: #166534; }
    h2 { font-size: 12pt; border-bottom: 2px solid #166534; padding-bottom: 3px; margin-top: 25px; color: #166534; }
    h3 { font-size: 10pt; margin-top: 15px; color: #333; }
    .subtitle { text-align: center; font-size: 10pt; color: #555; margin-bottom: 10px; }
    .meta { text-align: center; font-size: 9pt; color: #777; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0 15px; font-size: 9pt; }
    th { background: #166534; color: #fff; padding: 6px 8px; text-align: left; }
    td { padding: 5px 8px; border-bottom: 1px solid #ddd; }
    .policy-box { border: 1px solid #ddd; padding: 15px; margin: 10px 0; background: #fafafa; }
    .sig-block { margin-top: 30px; }
    .sig-line { width: 180px; border-bottom: 1px solid #333; margin-top: 50px; }
    .stat { display: inline-block; margin-right: 15px; font-size: 9pt; }
    .stat b { color: #166534; }
    ol li { margin-bottom: 5px; }
    .footer { text-align: center; font-size: 8pt; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; }
    .cert-valid { color: #166534; font-weight: bold; }
    .cert-missing { color: #dc2626; font-weight: bold; }
    .cert-na { color: #6b7280; }
    .page-break { page-break-before: always; }
</style>
</head>
<body>

<h1>SISTEM JAMINAN PRODUK HALAL (SJPH)</h1>
<p class="subtitle">{{ $company->name }}</p>
<p class="meta">
    Fasilitas: {{ $facility->name }} ({{ $facility->code ?? '-' }})<br>
    Alamat: {{ $facilityAddress }}<br>
    NPWP: {{ $company->npwp ?? '________________' }}<br>
    Tanggal: {{ now()->format('d/m/Y') }}
</p>

<hr>

{{-- 1. KEBIJAKAN HALAL --}}
<h2>1. Kebijakan Halal</h2>
<div class="policy-box">
    <p><strong>{{ $company->name }}</strong> berkomitmen untuk:</p>
    <ol>
        <li>Memproduksi, menyimpan, dan mendistribusikan produk yang memenuhi persyaratan halal sesuai dengan syariat Islam dan peraturan perundang-undangan yang berlaku.</li>
        <li>Menjamin bahwa seluruh bahan yang digunakan dalam proses produksi telah memiliki status halal yang valid dan terdokumentasi.</li>
        <li>Memastikan fasilitas produksi, peralatan, dan proses produksi terbebas dari kontaminasi bahan non-halal.</li>
        <li>Melakukan perbaikan berkelanjutan terhadap Sistem Jaminan Produk Halal (SJPH) perusahaan.</li>
        <li>Mematuhi seluruh regulasi terkait Jaminan Produk Halal sebagaimana diatur dalam UU No. 33/2014 dan PP No. 42/2024.</li>
    </ol>
    <p>Kebijakan ini berlaku untuk seluruh karyawan, pemasok, dan pihak terkait.</p>
</div>
<div class="sig-block">
    <p>Direktur/Pemilik,</p>
    @if($signaturePath)
        <img src="{{ $signaturePath }}" alt="Signature" style="height: 60px; margin: 10px 0;" />
    @else
        <div class="sig-line"></div>
    @endif
    <p><strong>{{ $company->pic_name ?? '(Nama belum diisi)' }}</strong></p>
</div>

{{-- 2. TIM MANAJEMEN HALAL --}}
<h2>2. Tim Manajemen Halal</h2>
@if(count($teamMembers) > 0)
<table>
    <tr><th>No</th><th>Nama</th><th>Jabatan</th><th>Peran dalam Tim Halal</th></tr>
    @foreach($teamMembers as $i => $member)
    <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $member['name'] ?? '-' }}</td>
        <td>{{ $member['position'] ?? '-' }}</td>
        <td>{{ $member['role'] ?? '-' }}</td>
    </tr>
    @endforeach
</table>
@else
<p style="color: #999; text-align: center;">Belum diisi</p>
@endif

{{-- 3. PELATIHAN DAN EDUKASI --}}
<h2>3. Pelatihan dan Edukasi</h2>
@if(count($trainings) > 0)
<table>
    <tr><th>No</th><th>Tanggal</th><th>Materi</th><th>Penyelenggara</th><th>Peserta</th></tr>
    @foreach($trainings as $i => $training)
    <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $training['date'] ?? '-' }}</td>
        <td>{{ $training['topic'] ?? '-' }}</td>
        <td>{{ $training['provider'] ?? '-' }}</td>
        <td>{{ $training['attendees'] ?? '-' }}</td>
    </tr>
    @endforeach
</table>
@else
<p style="color: #999; text-align: center;">Belum diisi</p>
@endif

<div class="page-break"></div>

{{-- 4. BAHAN --}}
<h2>4. Bahan</h2>
<p>
    <span class="stat">Total bahan: <b>{{ $ingredients->count() }}</b></span>
    <span class="stat">Bersertifikat: <b>{{ $ingredients->filter(fn($i) => !empty($i->sh_number))->count() }}</b></span>
    <span class="stat">Alami halal: <b>{{ $ingredients->where('halal_risk_level', 'no_risk')->count() }}</b></span>
    <span class="stat">Belum ada: <b>{{ $ingredients->count() - $ingredients->filter(fn($i) => !empty($i->sh_number))->count() - $ingredients->where('halal_risk_level', 'no_risk')->count() }}</b></span>
</p>
<table>
    <tr><th>No</th><th>Nama Bahan</th><th>Merek/Produsen</th><th>Tingkat Risiko</th><th>No SH</th><th>Status</th></tr>
    @foreach($ingredients as $i => $ing)
    @php
        $risk = match($ing->halal_risk_level) {
            'no_risk' => 'Tanpa Risiko', 'low_risk' => 'Risiko Rendah',
            'medium_risk' => 'Risiko Sedang', 'high_risk' => 'Risiko Tinggi', default => '-',
        };
        $status = $ing->sh_number ? 'Bersertifikat' : ($ing->halal_risk_level === 'no_risk' ? 'Tidak Diperlukan' : 'BELUM ADA');
        $statusClass = $ing->sh_number ? 'cert-valid' : ($ing->halal_risk_level === 'no_risk' ? 'cert-na' : 'cert-missing');
    @endphp
    <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $ing->name }}</td>
        <td>{{ $ing->brand ?? '-' }}</td>
        <td>{{ $risk }}</td>
        <td style="font-family: monospace; font-size: 8pt;">{{ $ing->sh_number ?? '-' }}</td>
        <td class="{{ $statusClass }}">{{ $status }}</td>
    </tr>
    @endforeach
</table>

{{-- 5. PRODUK --}}
<h2>5. Produk</h2>
<table>
    <tr><th>No</th><th>Nama Produk</th><th>Kode</th><th>Skor Halal</th><th>Status</th><th>Bahan</th></tr>
    @foreach($products as $i => $product)
    @php
        $prodStatus = match($product->halal_status) {
            'compliant' => '✓ Sesuai', 'at_risk' => '⚠ Berisiko', 'non_compliant' => '✗ Tidak Sesuai', default => 'Pending',
        };
        $ingredientNames = $product->ingredients->pluck('name')->implode(', ');
    @endphp
    <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $product->name }}</td>
        <td>{{ $product->code ?? '-' }}</td>
        <td>{{ $product->halal_health_score ?? 0 }}/100</td>
        <td>{{ $prodStatus }}</td>
        <td style="font-size: 8pt;">{{ $ingredientNames }}</td>
    </tr>
    @endforeach
</table>

<div class="page-break"></div>

{{-- 6. FASILITAS PRODUKSI --}}
<h2>6. Fasilitas Produksi</h2>
<table>
    <tr><th>Item</th><th>Detail</th></tr>
    <tr><td>Nama Fasilitas</td><td>{{ $facility->name }}</td></tr>
    <tr><td>Kode</td><td>{{ $facility->code ?? '-' }}</td></tr>
    <tr><td>Alamat</td><td>{{ $facilityAddress }}</td></tr>
    <tr><td>Kapasitas Produksi</td><td>{{ $facility->production_capacity ?? '-' }}</td></tr>
    <tr><td>Penanggung Jawab</td><td>{{ $facility->pic_name ?? '-' }}</td></tr>
</table>

{{-- 7. PROSEDUR AKTIVITAS KRITIS --}}
<h2>7. Prosedur Aktivitas Kritis</h2>

<h3>7.1 Penerimaan Bahan</h3>
<ol>
    <li>Periksa label halal pada kemasan bahan</li>
    <li>Cocokkan dengan Daftar Bahan yang disetujui dan nomor SH</li>
    <li>Tolak bahan yang tidak sesuai atau tidak memiliki sertifikat halal</li>
    <li>Catat dalam logbook penerimaan bahan</li>
</ol>

<h3>7.2 Penyimpanan</h3>
<ol>
    <li>Simpan bahan halal terpisah dari bahan non-halal (jika ada)</li>
    <li>Beri label/identifikasi yang jelas pada setiap bahan</li>
    <li>Terapkan sistem FIFO (First In First Out)</li>
</ol>

<h3>7.3 Produksi</h3>
<ol>
    <li>Pastikan peralatan bersih sebelum digunakan</li>
    <li>Gunakan hanya bahan yang ada dalam Daftar Bahan yang disetujui</li>
    <li>Hindari kontaminasi silang dengan produk/bahan non-halal</li>
</ol>

<h3>7.4 Pengemasan</h3>
<ol>
    <li>Pastikan kemasan dalam kondisi bersih</li>
    <li>Cantumkan label halal sesuai ketentuan BPJPH</li>
    <li>Cantumkan informasi batch, tanggal produksi, dan tanggal kedaluwarsa</li>
</ol>

{{-- 8. KEMAMPUAN TELUSUR --}}
<h2>8. Kemampuan Telusur</h2>
<ol>
    <li><strong>Sistem Batch:</strong> Setiap lot produksi memiliki nomor batch unik yang mencatat seluruh bahan yang digunakan.</li>
    <li><strong>Alur Telusur:</strong> Bahan Baku (lot pemasok) → Proses Produksi (batch) → Produk Jadi (label) → Distribusi (surat jalan).</li>
    <li><strong>Target:</strong> Seluruh bahan hingga produk jadi dapat ditelusuri dalam waktu 2 jam.</li>
    <li><strong>Masa Simpan Dokumen:</strong> Minimal 2 tahun untuk seluruh catatan produksi, penerimaan, dan distribusi.</li>
</ol>

{{-- 9. PENANGANAN PRODUK TIDAK HALAL --}}
<h2>9. Penanganan Produk Tidak Memenuhi Kriteria Halal</h2>
<ol>
    <li><strong>Identifikasi:</strong> Produk/bahan yang terindikasi tidak halal segera diidentifikasi dan dicatat.</li>
    <li><strong>Karantina:</strong> Produk dipisahkan, diberi label "KARANTINA", disimpan di area terpisah.</li>
    <li><strong>Investigasi:</strong> Tim halal melakukan investigasi penyebab ketidaksesuaian.</li>
    <li><strong>Tindakan:</strong> Produk tidak halal dimusnahkan. Peralatan terkontaminasi dibersihkan.</li>
    <li><strong>Korektif:</strong> Identifikasi akar masalah, perbaiki prosedur, lakukan pelatihan ulang.</li>
    <li><strong>Recall:</strong> Jika produk sudah didistribusikan, lakukan penarikan dan hubungi seluruh penerima.</li>
</ol>

{{-- 10. AUDIT INTERNAL --}}
<h2>10. Audit Internal</h2>
<p>Audit internal halal dilaksanakan minimal <strong>1 kali per 6 bulan</strong> oleh tim halal internal atau pihak ketiga yang kompeten.</p>
<p>Ruang lingkup audit meliputi seluruh aspek SJPH: kebijakan, bahan, proses produksi, fasilitas, ketelusuran, dan dokumentasi.</p>
<p>Hasil audit didokumentasikan dalam laporan yang mencakup temuan, kategori (mayor/minor), tindakan korektif, dan target penyelesaian.</p>

{{-- 11. KAJI ULANG MANAJEMEN --}}
<h2>11. Kaji Ulang Manajemen</h2>
<p>Kaji ulang manajemen dilaksanakan minimal <strong>1 kali per tahun</strong> dengan agenda:</p>
<ol>
    <li>Evaluasi pelaksanaan SJPH periode sebelumnya</li>
    <li>Hasil audit internal halal dan status tindakan korektif</li>
    <li>Evaluasi status sertifikat halal bahan</li>
    <li>Keluhan pelanggan terkait kehalalan produk</li>
    <li>Perubahan regulasi halal yang mempengaruhi perusahaan</li>
    <li>Rencana perbaikan dan peningkatan SJPH</li>
</ol>
<div class="sig-block">
    <p>Mengetahui,</p>
    @if($signaturePath)
        <img src="{{ $signaturePath }}" alt="Signature" style="height: 60px; margin: 10px 0;" />
    @else
        <div class="sig-line"></div>
    @endif
    <p><strong>{{ $company->pic_name ?? '(Nama belum diisi)' }}</strong><br>Direktur/Pemilik</p>
</div>

<div class="footer">
    Dokumen ini dihasilkan oleh HalalPass 2026 pada {{ now()->format('d/m/Y') }}<br>
    Berdasarkan UU No. 33/2014 dan PP No. 42/2024
</div>

</body>
</html>