import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, MemoryRouter, Navigate, Route, Routes } from 'react-router-dom';
import { StoreProvider } from './store';
import Layout from './components/Layout';
import Dashboard from './pages/Dashboard';
import Kasir from './pages/Kasir';
import ManajemenMenu from './pages/ManajemenMenu';
import ManajemenStok from './pages/ManajemenStok';
import Laporan from './pages/Laporan';
import './styles.css';

// VITE_MEMORY_ROUTER=1 builds a self-contained preview (e.g. for embedding) that doesn't touch the URL.
const Router = import.meta.env.VITE_MEMORY_ROUTER ? MemoryRouter : BrowserRouter;

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <StoreProvider>
      <Router>
        <Routes>
          <Route element={<Layout />}>
            <Route index element={<Navigate to="/dashboard" replace />} />
            <Route path="dashboard" element={<Dashboard />} />
            <Route path="kasir" element={<Kasir />} />
            <Route path="menu" element={<ManajemenMenu />} />
            <Route path="stok" element={<ManajemenStok />} />
            <Route path="laporan" element={<Laporan />} />
            <Route path="*" element={<Navigate to="/dashboard" replace />} />
          </Route>
        </Routes>
      </Router>
    </StoreProvider>
  </StrictMode>,
);
