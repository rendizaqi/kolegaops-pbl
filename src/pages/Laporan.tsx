import { useState } from 'react';
import { SHIFTS, TRX, rp, type Transaksi } from '../data';

const COLS = { gridTemplateColumns: '1fr 1.2fr 1fr 1fr 1fr 1fr' };
const KASIR = [...new Set(TRX.map((t) => t.kasir))];

const todayIso = () => {
  const d = new Date();
  return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
};

function exportCsv(rows: Transaksi[], date: string) {
  const lines = [
    ['Waktu', 'No. transaksi', 'Kasir', 'Shift', 'Total', 'Status'],
    ...rows.map((t) => [t.time, t.id, t.kasir, t.shift, String(t.total), t.ok ? 'Lunas' : 'Dibatalkan']),
  ];
  const blob = new Blob([lines.map((l) => l.join(',')).join('\n')], { type: 'text/csv' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `laporan-${date}.csv`;
  a.click();
  URL.revokeObjectURL(a.href);
}

export default function Laporan() {
  // Placeholder data has no dates, so the date picker doesn't filter yet.
  const [date, setDate] = useState(todayIso);
  const [shift, setShift] = useState('Semua');
  const [kasir, setKasir] = useState('');

  const rows = TRX.filter((t) => (shift === 'Semua' || t.shift === shift) && (!kasir || t.kasir === kasir));
  const paid = rows.filter((t) => t.ok);
  const sum = paid.reduce((a, t) => a + t.total, 0);

  return (
    <>
      <header className="page-header">
        <h1 className="page-title">Laporan Transaksi Harian</h1>
        <button className="btn btn-outline" onClick={() => exportCsv(rows, date)}>
          Ekspor CSV
        </button>
      </header>
      <div className="filters">
        <label className="field">
          <span className="label">Tanggal</span>
          <input type="date" className="input" style={{ fontWeight: 600, paddingInline: 10 }} value={date} onChange={(e) => setDate(e.target.value)} />
        </label>
        <div className="field">
          <span className="label">Shift</span>
          <div className="seg">
            {['Semua', ...SHIFTS].map((s) => (
              <button key={s} className={'seg-opt' + (shift === s ? ' is-active' : '')} onClick={() => setShift(s)}>
                {s}
              </button>
            ))}
          </div>
        </div>
        <label className="field">
          <span className="label">Kasir</span>
          <select className="input" style={{ paddingLeft: 10, minWidth: 170 }} value={kasir} onChange={(e) => setKasir(e.target.value)}>
            <option value="">Semua kasir</option>
            {KASIR.map((k) => (
              <option key={k}>{k}</option>
            ))}
          </select>
        </label>
      </div>
      <div className="stats">
        <div className="stat stat-primary">
          <span className="stat-label">Total pendapatan</span>
          <span className="stat-value">{rp(sum)}</span>
        </div>
        <div className="stat">
          <span className="stat-label">Total transaksi</span>
          <span className="stat-value">{rows.length}</span>
        </div>
        <div className="stat">
          <span className="stat-label">Rata-rata / transaksi</span>
          <span className="stat-value">{rp(paid.length ? sum / paid.length : 0)}</span>
        </div>
      </div>
      <div className="panel table-wrap" style={{ marginTop: 16 }}>
        <div className="trow thead" style={COLS}>
          <span>Waktu</span>
          <span>No. transaksi</span>
          <span>Kasir</span>
          <span>Shift</span>
          <span>Total</span>
          <span>Status</span>
        </div>
        {rows.map((t) => (
          <div key={t.id} className="trow" style={{ ...COLS, paddingBlock: 11 }}>
            <span className="strong">{t.time}</span>
            <span className="mono">{t.id}</span>
            <span>{t.kasir}</span>
            <span className="muted">{t.shift}</span>
            <span className="strong">{rp(t.total)}</span>
            <span>{t.ok ? <span className="badge badge-ok">Lunas</span> : <span className="badge badge-bad">Dibatalkan</span>}</span>
          </div>
        ))}
        {rows.length === 0 && <div className="empty">Tidak ada transaksi.</div>}
      </div>
    </>
  );
}
