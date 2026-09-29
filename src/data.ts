// Placeholder data from the Claude Design wireframe. Swap for API calls later.

export const rp = (n: number) => 'Rp ' + Math.round(n).toLocaleString('id-ID');

export type Category = 'Kopi' | 'Non-Kopi' | 'Makanan' | 'Snack';
export const CATEGORIES: Category[] = ['Kopi', 'Non-Kopi', 'Makanan', 'Snack'];

export interface MenuItem {
  id: number;
  name: string;
  cat: Category;
  price: number;
  active: boolean;
}

export const MENU: MenuItem[] = [
  { id: 1, name: 'Kopi Hitam', cat: 'Kopi', price: 5000 },
  { id: 2, name: 'Kopi Susu Kolega', cat: 'Kopi', price: 10000 },
  { id: 3, name: 'Es Kopi Gula Aren', cat: 'Kopi', price: 15000 },
  { id: 4, name: 'Kopi Tubruk', cat: 'Kopi', price: 6000 },
  { id: 5, name: 'Teh Tarik', cat: 'Non-Kopi', price: 10000 },
  { id: 6, name: 'Es Teh Manis', cat: 'Non-Kopi', price: 5000 },
  { id: 7, name: 'Es Jeruk', cat: 'Non-Kopi', price: 8000 },
  { id: 8, name: 'Indomie Goreng', cat: 'Makanan', price: 12000 },
  { id: 9, name: 'Indomie Rebus Telur', cat: 'Makanan', price: 14000 },
  { id: 10, name: 'Nasi Goreng Kolega', cat: 'Makanan', price: 18000 },
  { id: 11, name: 'Roti Bakar Coklat', cat: 'Snack', price: 13000 },
  { id: 12, name: 'Pisang Goreng', cat: 'Snack', price: 10000 },
].map((m) => ({ ...(m as Omit<MenuItem, 'active'>), active: m.id !== 7 }));

export interface StokItem {
  id: number;
  name: string;
  qty: number;
  unit: string;
  min: number;
}

export const STOK: StokItem[] = [
  { name: 'Biji kopi robusta', qty: 1.2, unit: 'kg', min: 2 },
  { name: 'Biji kopi arabika', qty: 3.5, unit: 'kg', min: 1.5 },
  { name: 'Susu kental manis', qty: 14, unit: 'kaleng', min: 10 },
  { name: 'Gula pasir', qty: 8, unit: 'kg', min: 3 },
  { name: 'Gula aren cair', qty: 0.8, unit: 'liter', min: 1 },
  { name: 'Teh celup', qty: 120, unit: 'pcs', min: 50 },
  { name: 'Indomie goreng', qty: 18, unit: 'bungkus', min: 24 },
  { name: 'Telur ayam', qty: 30, unit: 'butir', min: 20 },
  { name: 'Roti tawar', qty: 5, unit: 'bungkus', min: 4 },
  { name: 'Es batu', qty: 6, unit: 'kg', min: 10 },
].map((s, i) => ({ ...s, id: i + 1 }));

export type StokStatus = 'kritis' | 'menipis' | 'aman';

/** Kritis at or below the minimum; menipis within 1.5× of it. */
export const stokStatus = (s: StokItem): StokStatus =>
  s.qty <= s.min ? 'kritis' : s.qty <= s.min * 1.5 ? 'menipis' : 'aman';

export type Shift = 'Pagi' | 'Siang' | 'Malam';
export const SHIFTS: Shift[] = ['Pagi', 'Siang', 'Malam'];

export interface Transaksi {
  id: string;
  time: string;
  kasir: string;
  shift: Shift;
  total: number;
  ok: boolean;
}

export const TRX: Transaksi[] = (
  [
    ['06:12', 'Rina', 'Pagi', 23000, 1],
    ['06:40', 'Rina', 'Pagi', 15000, 1],
    ['07:05', 'Rina', 'Pagi', 42000, 1],
    ['08:18', 'Rina', 'Pagi', 10000, 0],
    ['09:30', 'Rina', 'Pagi', 31000, 1],
    ['11:02', 'Andi', 'Siang', 56000, 1],
    ['12:15', 'Andi', 'Siang', 28000, 1],
    ['13:40', 'Andi', 'Siang', 18000, 1],
    ['14:22', 'Andi', 'Siang', 64000, 1],
    ['18:05', 'Bayu', 'Malam', 37000, 1],
    ['19:30', 'Bayu', 'Malam', 48000, 1],
    ['21:10', 'Bayu', 'Malam', 22000, 0],
    ['22:45', 'Bayu', 'Malam', 71000, 1],
  ] as const
).map((r, i) => ({
  time: r[0],
  kasir: r[1],
  shift: r[2],
  total: r[3],
  ok: !!r[4],
  id: 'TRX-' + String(101 + i).padStart(4, '0'),
}));

export const WEEK: [string, number][] = [
  ['Rab', 2310000],
  ['Kam', 2580000],
  ['Jum', 3420000],
  ['Sab', 3890000],
  ['Min', 3150000],
  ['Sen', 2540000],
  ['Sel', 2845000],
];

export const todayLabel = () =>
  new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' });
