import { Link } from 'react-router-dom';
import { TRX, WEEK, rp, stokStatus, todayLabel } from '../data';
import { useStore } from '../store';

const COLS = { gridTemplateColumns: '0.8fr 1fr 1fr 0.9fr' };

export default function Dashboard() {
  const { stok } = useStore();
  const kritis = stok.filter((s) => stokStatus(s) === 'kritis').length;
  const max = Math.max(...WEEK.map((w) => w[1]));
  const weekTotal = WEEK.reduce((a, w) => a + w[1], 0);
  const recent = TRX.slice().reverse().slice(0, 8);

  return (
    <>
      <header className="page-header">
        <div className="page-heading">
          <h1 className="page-title">Dashboard</h1>
          <span className="meta">Ringkasan hari ini · {todayLabel()}</span>
        </div>
        <span className="meta">Diperbarui 14:32</span>
      </header>
      <div className="stats">
        <div className="stat stat-primary">
          <span className="stat-label">Total penjualan</span>
          <span className="stat-value">Rp 2.845.000</span>
          <span className="stat-note">▲ 12% vs kemarin</span>
        </div>
        <div className="stat">
          <span className="stat-label">Jumlah transaksi</span>
          <span className="stat-value">142</span>
          <span className="stat-note">Rata-rata Rp 20.035</span>
        </div>
        <div className="stat stat-danger">
          <span className="stat-label">Stok kritis</span>
          <span className="stat-value">{kritis} bahan</span>
          <Link to="/stok">Lihat stok →</Link>
        </div>
      </div>
      <div className="dash-grid">
        <section className="panel dash-card">
          <div className="dash-card-head">
            <h2 className="dash-card-title">Pendapatan 7 hari terakhir</h2>
            <span className="meta">Total {rp(weekTotal)}</span>
          </div>
          <div className="chart">
            {WEEK.map(([day, v], i) => (
              <div key={day} className="chart-col" title={`${day}: ${rp(v)}`}>
                <span className="chart-value">{(v / 1e6).toFixed(1)}jt</span>
                <div className="chart-bar-area">
                  <div className={'chart-bar' + (i === WEEK.length - 1 ? ' is-today' : '')} style={{ height: Math.round((v / max) * 100) + '%' }} />
                </div>
              </div>
            ))}
          </div>
          <div className="chart-days">
            {WEEK.map(([day]) => (
              <span key={day}>{day}</span>
            ))}
          </div>
        </section>
        <section className="panel dash-card">
          <div className="dash-card-head">
            <h2 className="dash-card-title">Transaksi terbaru</h2>
            <Link to="/laporan" className="strong" style={{ fontSize: 12 }}>
              Semua →
            </Link>
          </div>
          <div className="trow thead dash-thead" style={COLS}>
            <span>Waktu</span>
            <span>Kasir</span>
            <span>Total</span>
            <span>Status</span>
          </div>
          <div className="dash-rows">
            {recent.map((t) => (
              <div key={t.id} className="trow dash-row" style={COLS}>
                <span className="strong">{t.time}</span>
                <span>{t.kasir}</span>
                <span className="strong">{rp(t.total)}</span>
                <span>{t.ok ? <span className="badge badge-ok badge-xs">Lunas</span> : <span className="badge badge-bad badge-xs">Batal</span>}</span>
              </div>
            ))}
          </div>
        </section>
      </div>
    </>
  );
}
