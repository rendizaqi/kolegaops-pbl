import { NavLink, Outlet, useLocation } from 'react-router-dom';

const NAV = [
  { to: '/dashboard', label: 'Dashboard' },
  { to: '/kasir', label: 'Kasir' },
  { to: '/menu', label: 'Manajemen Menu' },
  { to: '/stok', label: 'Manajemen Stok' },
  { to: '/laporan', label: 'Laporan Transaksi' },
];

// Placeholder session: the cashier view shows the on-shift cashier, every other page the owner.
const USERS = {
  kasir: { name: 'Rina', role: 'Kasir · Shift Pagi' },
  owner: { name: 'Pak Dimas', role: 'Pemilik' },
};

export default function Layout() {
  const { pathname } = useLocation();
  const user = pathname.startsWith('/kasir') ? USERS.kasir : USERS.owner;
  return (
    <div className="app">
      <aside className="sidebar">
        <div className="brand">
          <div className="brand-logo">
            Kolega<span>Ops</span>
          </div>
          <div className="brand-sub">Warkop Kolega</div>
        </div>
        <div className="sidebar-rule" />
        <nav className="nav">
          {NAV.map((n) => (
            <NavLink key={n.to} to={n.to} className="nav-item">
              <span className="nav-mark" />
              {n.label}
            </NavLink>
          ))}
        </nav>
        <div className="sidebar-user">
          <span className="avatar" />
          <div className="sidebar-user-text">
            <span className="sidebar-user-name">{user.name}</span>
            <span className="sidebar-user-role">{user.role}</span>
          </div>
        </div>
      </aside>
      <main className="main">
        <Outlet />
      </main>
    </div>
  );
}
