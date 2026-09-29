import { useEffect, useState } from 'react';
import { stokStatus, type StokItem } from '../data';
import { useStore } from '../store';

const COLS = { gridTemplateColumns: '2fr 1.6fr 0.8fr 1fr 1fr 110px' };
const MODES = ['Tambah', 'Kurangi', 'Set jumlah'] as const;
type Mode = (typeof MODES)[number];

const BAR = { kritis: 'var(--danger)', menipis: 'var(--warn-line)', aman: 'var(--blue)' };
const fmt = (n: number) => n.toLocaleString('id-ID');

export default function ManajemenStok() {
  const { stok, setStok } = useStore();
  const [editing, setEditing] = useState<StokItem | null>(null);

  const count = (st: string) => stok.filter((s) => stokStatus(s) === st).length;
  const save = (id: number, qty: number) => {
    setStok((all) => all.map((s) => (s.id === id ? { ...s, qty } : s)));
    setEditing(null);
  };

  return (
    <>
      <header className="page-header">
        <div className="page-heading">
          <h1 className="page-title">Manajemen Stok</h1>
          <span className="meta">Bahan baku · terakhir diperbarui 07:45</span>
        </div>
        <button className="btn btn-primary">
          <span>+</span>
          <span>Tambah Bahan</span>
        </button>
      </header>
      <div className="stok-summary">
        <div>
          <span className="stat-label">Total bahan</span>
          <span className="stok-summary-value">{stok.length}</span>
        </div>
        <div className="is-warn">
          <span className="stat-label">Menipis</span>
          <span className="stok-summary-value">{count('menipis')}</span>
        </div>
        <div className="is-danger">
          <span className="stat-label">Kritis</span>
          <span className="stok-summary-value">{count('kritis')}</span>
        </div>
      </div>
      <div className="panel table-wrap" style={{ marginTop: 16 }}>
        <div className="trow thead" style={COLS}>
          <span>Nama bahan</span>
          <span>Stok saat ini</span>
          <span>Satuan</span>
          <span>Batas min.</span>
          <span>Status</span>
          <span>Aksi</span>
        </div>
        {stok.map((s) => {
          const st = stokStatus(s);
          return (
            <div key={s.id} className="trow" style={COLS}>
              <span className="name">{s.name}</span>
              <span className="stok-level">
                <span className="strong stok-qty">{fmt(s.qty)}</span>
                <span className="stok-bar">
                  <span style={{ width: Math.min(100, (s.qty / (s.min * 3)) * 100) + '%', background: BAR[st] }} />
                </span>
              </span>
              <span className="muted">{s.unit}</span>
              <span className="muted">{fmt(s.min)}</span>
              <span>
                {st === 'kritis' && <span className="badge badge-status badge-kritis">● Kritis</span>}
                {st === 'menipis' && <span className="badge badge-status badge-menipis">● Menipis</span>}
                {st === 'aman' && <span className="badge badge-status badge-aman">Aman</span>}
              </span>
              <button className="btn btn-outline btn-sm" onClick={() => setEditing(s)}>
                Update
              </button>
            </div>
          );
        })}
      </div>
      {editing && <UpdateStokDialog item={editing} onClose={() => setEditing(null)} onSave={(qty) => save(editing.id, qty)} />}
    </>
  );
}

function UpdateStokDialog({ item, onClose, onSave }: { item: StokItem; onClose: () => void; onSave: (qty: number) => void }) {
  const [mode, setMode] = useState<Mode>('Tambah');
  const [input, setInput] = useState('');
  const [note, setNote] = useState('');

  useEffect(() => {
    const onKey = (e: KeyboardEvent) => e.key === 'Escape' && onClose();
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, [onClose]);

  const submit = () => {
    const v = parseFloat(input.replace(',', '.')) || 0;
    const next = mode === 'Tambah' ? item.qty + v : mode === 'Kurangi' ? item.qty - v : v;
    onSave(Math.max(0, +next.toFixed(2)));
  };

  return (
    <div className="dialog-backdrop" onClick={(e) => e.target === e.currentTarget && onClose()}>
      <form
        className="dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="stok-dialog-title"
        onSubmit={(e) => {
          e.preventDefault();
          submit();
        }}
      >
        <div className="dialog-head">
          <h2 className="dialog-title" id="stok-dialog-title">Update Stok</h2>
          <span className="muted" style={{ fontSize: 13 }}>
            {item.name} · stok saat ini {fmt(item.qty)} {item.unit}
          </span>
        </div>
        <div className="dialog-body">
          <div className="mode-picker">
            {MODES.map((m) => (
              <button type="button" key={m} className={'chip' + (mode === m ? ' is-active' : '')} onClick={() => setMode(m)}>
                {m}
              </button>
            ))}
          </div>
          <label className="field">
            <span className="label">Jumlah ({item.unit})</span>
            <input className="input input-strong" autoFocus value={input} onChange={(e) => setInput(e.target.value)} inputMode="decimal" />
          </label>
          <textarea className="input" placeholder="Catatan (opsional) — mis. belanja pasar pagi" value={note} onChange={(e) => setNote(e.target.value)} />
        </div>
        <div className="dialog-actions">
          <button type="button" className="btn btn-plain btn-md" onClick={onClose}>
            Batal
          </button>
          <button type="submit" className="btn btn-primary btn-md" style={{ paddingInline: 18 }}>
            Simpan
          </button>
        </div>
      </form>
    </div>
  );
}
