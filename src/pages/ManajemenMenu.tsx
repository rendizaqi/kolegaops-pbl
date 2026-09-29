import { useState } from 'react';
import { CATEGORIES, rp } from '../data';
import { useStore } from '../store';

const COLS = { gridTemplateColumns: '56px 2fr 1fr 1fr 1fr 160px' };

export default function ManajemenMenu() {
  const { menu, setMenu } = useStore();
  const [query, setQuery] = useState('');
  const [cat, setCat] = useState('');
  const [status, setStatus] = useState('');

  const q = query.trim().toLowerCase();
  const rows = menu.filter(
    (m) =>
      (!q || m.name.toLowerCase().includes(q)) &&
      (!cat || m.cat === cat) &&
      (!status || (status === 'aktif') === m.active),
  );
  const toggle = (id: number) => setMenu((ms) => ms.map((m) => (m.id === id ? { ...m, active: !m.active } : m)));
  const remove = (id: number) => setMenu((ms) => ms.filter((m) => m.id !== id));

  return (
    <>
      <header className="page-header">
        <div className="page-heading">
          <h1 className="page-title">Manajemen Menu</h1>
          <span className="meta">
            {menu.length} produk · {menu.filter((m) => m.active).length} aktif
          </span>
        </div>
        {/* TODO: form tambah/edit produk belum didesain */}
        <button className="btn btn-primary">
          <span>+</span>
          <span>Tambah Produk</span>
        </button>
      </header>
      <div className="toolbar">
        <input className="input" style={{ width: 300 }} placeholder="Cari produk…" value={query} onChange={(e) => setQuery(e.target.value)} />
        <select className="input" value={cat} onChange={(e) => setCat(e.target.value)}>
          <option value="">Semua kategori</option>
          {CATEGORIES.map((c) => (
            <option key={c}>{c}</option>
          ))}
        </select>
        <select className="input" value={status} onChange={(e) => setStatus(e.target.value)}>
          <option value="">Semua status</option>
          <option value="aktif">Aktif</option>
          <option value="nonaktif">Nonaktif</option>
        </select>
      </div>
      <div className="panel table-wrap">
        <div className="trow thead" style={COLS}>
          <span />
          <span>Nama produk</span>
          <span>Kategori</span>
          <span>Harga</span>
          <span>Status</span>
          <span>Aksi</span>
        </div>
        {rows.map((m) => (
          <div key={m.id} className="trow" style={{ ...COLS, paddingBlock: 10 }}>
            <span className="photo thumb" />
            <span className="name">{m.name}</span>
            <span className="muted">{m.cat}</span>
            <span className="strong">{rp(m.price)}</span>
            <span>
              <button className={'switch' + (m.active ? ' is-on' : '')} onClick={() => toggle(m.id)} aria-pressed={m.active}>
                <span className="switch-track">
                  <span className="switch-knob" />
                </span>
                {m.active ? 'Aktif' : 'Nonaktif'}
              </button>
            </span>
            <span className="row-actions">
              <button className="btn btn-outline btn-sm">Edit</button>
              <button className="btn btn-danger btn-sm" onClick={() => remove(m.id)}>
                Hapus
              </button>
            </span>
          </div>
        ))}
        {rows.length === 0 && <div className="empty">Tidak ada produk.</div>}
      </div>
    </>
  );
}
