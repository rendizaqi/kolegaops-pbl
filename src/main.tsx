import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { StoreProvider } from './store';
import Layout from './components/Layout';
import Dashboard from './pages/Dashboard';
import Kasir from './pages/Kasir';
import ManajemenMenu from './pages/ManajemenMenu';
import ManajemenStok from './pages/ManajemenStok';
import Laporan from './pages/Laporan';
import './styles.css';

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <StoreProvider>
      <BrowserRouter>
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
      </BrowserRouter>
    </StoreProvider>
  </StrictMode>,
);
