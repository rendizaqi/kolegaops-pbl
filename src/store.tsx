import { createContext, useContext, useState, type Dispatch, type ReactNode, type SetStateAction } from 'react';
import { MENU, STOK, type MenuItem, type StokItem } from './data';

/** Order lines keyed by menu id → quantity. */
export type Order = Record<number, number>;

interface Store {
  menu: MenuItem[];
  setMenu: Dispatch<SetStateAction<MenuItem[]>>;
  stok: StokItem[];
  setStok: Dispatch<SetStateAction<StokItem[]>>;
  order: Order;
  setOrder: Dispatch<SetStateAction<Order>>;
  trxNo: number;
  setTrxNo: Dispatch<SetStateAction<number>>;
}

const StoreContext = createContext<Store | null>(null);

// Shared in-memory state so pages stay in sync (e.g. Dashboard's "stok kritis"
// follows edits on Manajemen Stok, and deactivated menu items leave the Kasir grid).
export function StoreProvider({ children }: { children: ReactNode }) {
  const [menu, setMenu] = useState(MENU);
  const [stok, setStok] = useState(STOK);
  const [order, setOrder] = useState<Order>({ 2: 2, 8: 1 });
  const [trxNo, setTrxNo] = useState(142);
  return (
    <StoreContext.Provider value={{ menu, setMenu, stok, setStok, order, setOrder, trxNo, setTrxNo }}>
      {children}
    </StoreContext.Provider>
  );
}

export function useStore() {
  const s = useContext(StoreContext);
  if (!s) throw new Error('useStore must be used inside StoreProvider');
  return s;
}
