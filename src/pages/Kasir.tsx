import { useState } from 'react';
import { CATEGORIES, rp, todayLabel, type Category } from '../data';
import { useStore } from '../store';

const parseRp = (s: string) => parseInt(s.replace(/\D/g, ''), 10) || 0;

export default function Kasir() {
  const { menu, order, setOrder, trxNo, setTrxNo } = useStore();
  const [cat, setCat] = useState<'Semua' | Category>('Semua');
  const [query, setQuery] = useState('');
  const [bayarStr, setBayarStr] = useState('');
  const [paidMsg, setPaidMsg] = useState('');

  const q = query.trim().toLowerCase();
  const cards = menu.filter(
    (m) => m.active && (cat === 'Semua' || m.cat === cat) && (!q || m.name.toLowerCase().includes(q)),
  );

  const lines = Object.entries(order).flatMap(([id, qty]) => {
    const m = menu.find((x) => x.id === Number(id));
    return m ? [{ ...m, qty }] : [];
  });
  const total = lines.reduce((a, l) => a + l.price * l.qty, 0);
  const itemCount = lines.reduce((a, l) => a + l.qty, 0);
  const bayar = parseRp(bayarStr);
  const kurang = bayar > 0 && bayar < total;
  const canPay = total > 0 && bayar >= total;

  const add = (id: number) => {
    setOrder((o) => ({ ...o, [id]: (o[id] || 0) + 1 }));
    setPaidMsg('');
  };
  const setQty = (id: number, qty: number) =>
    setOrder((o) => {
      const next = { ...o };
      if (qty <= 0) delete next[id];
      else next[id] = qty;
      return next;
    });
  const setBayar = (n: number) => {
    setBayarStr(n ? n.toLocaleString('id-ID') : '');
    setPaidMsg('');
  };
  const proses = () => {
    setPaidMsg(`Pembayaran ${rp(total)} berhasil · kembalian ${rp(bayar - total)}`);
    setOrder({});
    setBayarStr('');
    setTrxNo((n) => n + 1);
  };

  const quickCash: [string, number][] = [
    ['Uang pas', total],
    ['20.000', 20000],
    ['50.000', 50000],
    ['100.000', 100000],
  ];

  return (
    <div className="kasir">
      <section className="kasir-menu">
        <header className="page-header">
          <h1 className="page-title">Kasir</h1>
          <div className="kasir-header-right">
            <input
              className="input kasir-search"
              placeholder="Cari menu…"
              value={query}
              onChange={(e) => setQuery(e.target.value)}
            />
            <span className="meta">{todayLabel()}</span>
          </div>
        </header>
        <div className="kasir-cats">
          {(['Semua', ...CATEGORIES] as const).map((c) => (
            <button key={c} className={'chip' + (cat === c ? ' is-active' : '')} onClick={() => setCat(c)}>
              {c}
            </button>
          ))}
        </div>
        <div className="kasir-grid">
          {cards.map((m) => (
            <button key={m.id} className="menu-card" onClick={() => add(m.id)}>
              <div className="photo menu-card-photo">foto</div>
              <div className="menu-card-name">{m.name}</div>
              <div className="menu-card-foot">
                <span className="menu-card-price">{rp(m.price)}</span>
                <span className="menu-card-plus">+</span>
              </div>
            </button>
          ))}
          {cards.length === 0 && <div className="empty">Tidak ada menu yang cocok.</div>}
        </div>
      </section>

      <aside className="order">
        <div className="order-head">
          <h2 className="order-title">Pesanan</h2>
          <span className="meta">#TRX-{String(trxNo).padStart(4, '0')} · Meja 4</span>
        </div>
        <div className="order-lines">
          {lines.length === 0 && <div className="empty">Belum ada item. Klik menu di kiri untuk menambahkan.</div>}
          {lines.map((l) => (
            <div key={l.id} className="order-line">
              <div className="order-line-info">
                <span className="name">{l.name}</span>
                <span className="meta">{rp(l.price)} / item</span>
              </div>
              <div className="stepper">
                <button onClick={() => setQty(l.id, l.qty - 1)} aria-label={`Kurangi ${l.name}`}>−</button>
                <span>{l.qty}</span>
                <button onClick={() => setQty(l.id, l.qty + 1)} aria-label={`Tambah ${l.name}`}>+</button>
              </div>
              <span className="order-line-sub">{rp(l.price * l.qty)}</span>
              <button className="order-line-remove" onClick={() => setQty(l.id, 0)} title="Hapus item" aria-label={`Hapus ${l.name}`}>
                ×
              </button>
            </div>
          ))}
        </div>
        <div className="order-foot">
          <div className="order-subtotal">
            <span>Subtotal ({itemCount} item)</span>
            <span>{rp(total)}</span>
          </div>
          <div className="order-total">
            <span className="order-total-label">TOTAL</span>
            <span className="order-total-value">{rp(total)}</span>
          </div>
          <label className="field">
            <span className="label">Nominal bayar</span>
            <input
              className="input input-strong"
              value={bayarStr}
              onChange={(e) => setBayar(parseRp(e.target.value))}
              placeholder="0"
              inputMode="numeric"
            />
          </label>
          <div className="quick-cash">
            {quickCash.map(([label, v]) => (
              <button key={label} onClick={() => setBayar(v)}>
                {label}
              </button>
            ))}
          </div>
          <div className="kembalian">
            <span className="kembalian-label">{kurang ? 'Kurang' : 'Kembalian'}</span>
            <span className="kembalian-value">{kurang ? rp(total - bayar) : rp(canPay ? bayar - total : 0)}</span>
          </div>
          <button className="btn btn-primary pay-btn" disabled={!canPay} onClick={proses}>
            <span>Proses Bayar</span>
            <span>→</span>
          </button>
          {paidMsg && <div className="paid-msg">✓ {paidMsg}</div>}
        </div>
      </aside>
    </div>
  );
}
