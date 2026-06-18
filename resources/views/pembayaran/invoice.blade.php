<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $pembayaran->payment_method === 'cash' ? 'Kwitansi Pembayaran Tunai' : 'Invoice Pembayaran' }}</title>

<style>
	:root {
		--ink: #0f172a;
		--muted: #6b7280;
		--line: #e5e7eb;
		--accent: #185ea8;
		--accent-soft: #dbeafe;
	}

	* { box-sizing: border-box; }

	body {
		font-family: Arial, Helvetica, sans-serif;
		background: #f3f4f6;
		margin: 0;
		padding: 24px;
		color: var(--ink);
	}

	.sheet {
		max-width: 760px;
		margin: 0 auto;
		background: #fff;
		border: 1px solid var(--line);
		border-radius: 14px;
		box-shadow: 0 12px 32px rgba(15, 23, 42, .10);
		overflow: hidden;
	}

	.top-band {
		background: linear-gradient(135deg, #0f766e, #0d9488);
		color: #fff;
		padding: 18px 22px;
		display: flex;
		justify-content: space-between;
		align-items: center;
		gap: 12px;
		flex-wrap: wrap;
	}

	.top-band h1 {
		margin: 0;
		font-size: 22px;
		letter-spacing: .2px;
	}

	.top-band p {
		margin: 4px 0 0;
		color: var(--accent-soft);
		font-size: 13px;
	}

	.receipt-no {
		background: rgba(255,255,255,.12);
		border: 1px solid rgba(255,255,255,.35);
		border-radius: 10px;
		padding: 8px 12px;
		font-size: 13px;
		font-weight: 700;
	}

	.content {
		padding: 22px;
	}

	.org-header {
		display: flex;
		align-items: center;
		gap: 14px;
		padding-bottom: 14px;
		border-bottom: 2px solid var(--line);
		margin-bottom: 18px;
	}

	.org-header img {
		width: 56px;
		height: 56px;
		object-fit: contain;
	}

	.org-title {
		margin: 0;
		font-size: 16px;
		font-weight: 800;
		text-transform: uppercase;
	}

	.org-sub {
		margin: 2px 0 0;
		color: var(--muted);
		font-size: 13px;
	}

	.grid {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 10px 18px;
		margin-bottom: 16px;
	}

	.field {
		border: 1px solid var(--line);
		border-radius: 10px;
		padding: 10px 12px;
	}

	.field .label {
		display: block;
		font-size: 11px;
		text-transform: uppercase;
		letter-spacing: .3px;
		color: var(--muted);
		margin-bottom: 4px;
		font-weight: 700;
	}

	.field .value {
		font-size: 14px;
		font-weight: 700;
		color: var(--ink);
	}

	.summary {
		border: 1px dashed #9ca3af;
		border-radius: 12px;
		padding: 12px 14px;
		margin: 8px 0 14px;
	}

	.row {
		display: flex;
		justify-content: space-between;
		gap: 10px;
		margin: 7px 0;
		font-size: 14px;
	}

	.row.total {
		border-top: 1px solid var(--line);
		margin-top: 10px;
		padding-top: 10px;
		font-size: 18px;
		font-weight: 800;
	}

	.badge {
		display: inline-block;
		padding: 5px 10px;
		border-radius: 999px;
		font-size: 12px;
		font-weight: 700;
	}

	.badge.paid {
		background: #dbeafe;
		color: #215d90;
	}

	.badge.pending {
		background: #fef3c7;
		color: #92400e;
	}

	.notes {
		margin-top: 10px;
		border: 1px solid var(--line);
		border-radius: 10px;
		padding: 10px 12px;
		font-size: 13px;
		white-space: pre-line;
	}

	.signatures {
		margin-top: 24px;
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 24px;
	}

	.sign-box {
		border-top: 1px solid var(--line);
		padding-top: 10px;
		min-height: 86px;
		font-size: 13px;
		color: var(--muted);
	}

	.sign-box b {
		color: var(--ink);
	}

	.actions {
		margin-top: 20px;
		display: flex;
		gap: 10px;
		flex-wrap: wrap;
	}

	.btn {
		display: inline-flex;
		align-items: center;
		padding: 10px 14px;
		border-radius: 10px;
		text-decoration: none;
		font-size: 14px;
		font-weight: 700;
		border: 1px solid transparent;
		cursor: pointer;
	}

	.btn.back {
		background: #fff;
		color: #374151;
		border-color: #d1d5db;
	}

	.btn.print {
		background: #0f766e;
		color: #fff;
	}

	.footer {
		margin-top: 18px;
		color: var(--muted);
		font-size: 12px;
		text-align: center;
	}

	@media (max-width: 700px) {
		.grid,
		.signatures {
			grid-template-columns: 1fr;
		}
	}

	@media print {
		body {
			background: #fff;
			padding: 0;
		}

		.sheet {
			border: none;
			border-radius: 0;
			box-shadow: none;
			max-width: 100%;
		}

		.actions {
			display: none;
		}
	}
</style>
</head>
<body>
<div class="sheet">
	<div class="top-band">
		<div>
			<h1>{{ $pembayaran->payment_method === 'cash' ? 'KWITANSI PEMBAYARAN TUNAI' : 'INVOICE PEMBAYARAN' }}</h1>
			<p>Bukti resmi transaksi Sistem Pembayaran Desa</p>
		</div>
		<div class="receipt-no">No. {{ $pembayaran->invoice ?? '-' }}</div>
	</div>

	<div class="content">
		<div class="org-header">
			<img src="{{ asset('logo-simp-mld.png') }}" alt="Logo SIMP-MLD">
			<div>
				<p class="org-title">Pemerintah Desa</p>
				<p class="org-sub">Unit Pelayanan Pembayaran Warga</p>
			</div>
		</div>

		<div class="grid">
			<div class="field">
				<span class="label">Nama Warga</span>
				<span class="value">{{ $pembayaran->warga->nama ?? '-' }}</span>
			</div>
			<div class="field">
				<span class="label">Jenis Pembayaran</span>
				<span class="value">{{ $pembayaran->jenisPembayaran->nama ?? '-' }}</span>
			</div>
			<div class="field">
				<span class="label">Tanggal Bayar</span>
				<span class="value">{{ $pembayaran->tanggal_bayar ? \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->translatedFormat('d F Y') : '-' }}</span>
			</div>
			<div class="field">
				<span class="label">Metode</span>
				<span class="value">{{ $pembayaran->payment_method === 'cash' ? 'Tunai' : 'Online' }}</span>
			</div>
		</div>

		<div class="summary">
			@if($pembayaran->payment_method === 'cash')
				<div class="row"><span>Uang Diterima</span><b>Rp {{ number_format((int) ($pembayaran->cash_received_amount ?? 0), 0, ',', '.') }}</b></div>
				<div class="row"><span>Kembalian</span><b>Rp {{ number_format((int) ($pembayaran->cash_change_amount ?? 0), 0, ',', '.') }}</b></div>
				<div class="row"><span>Petugas</span><b>{{ $pembayaran->paidByUser->name ?? '-' }}</b></div>
			@endif
			<div class="row total"><span>Total Pembayaran</span><span>Rp {{ number_format((int) $pembayaran->jumlah, 0, ',', '.') }}</span></div>
		</div>

		<div class="row" style="margin-top:2px;">
			<span>Status Transaksi</span>
			@if($pembayaran->status === 'paid')
				<span class="badge paid">Lunas</span>
			@else
				<span class="badge pending">Pending</span>
			@endif
		</div>

		<div class="notes">
			<b>Keterangan:</b>
			{{ $pembayaran->keterangan ?: '-' }}
		</div>

		<div class="signatures">
			<div class="sign-box">
				Mengetahui,<br>
				<b>Petugas Pembayaran</b><br><br><br>
				( {{ $pembayaran->paidByUser->name ?? '........................' }} )
			</div>
			<div class="sign-box">
				Diterima oleh,<br>
				<b>Warga</b><br><br><br>
				( {{ $pembayaran->warga->nama ?? '........................' }} )
			</div>
		</div>

		<div class="actions">
			<a href="{{ route('pembayaran.index') }}" class="btn back">Kembali</a>
			<button onclick="window.print()" class="btn print">Cetak</button>
		</div>

		<div class="footer">
			Dokumen ini dicetak otomatis oleh Sistem Pembayaran Desa.
		</div>
	</div>
</div>
</body>
</html>