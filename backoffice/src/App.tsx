import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { AuthProvider, useAuth } from './context/AuthContext'
import MainLayout from './layouts/MainLayout'
import Login from './pages/Login'
import Agences from './pages/Agences'
import Clients from './pages/Clients'
import Commandes from './pages/Commandes'
import Employes from './pages/Employes'
import Pointages from './pages/Pointages'
import Factures from './pages/Factures'
import Fidelisation from './pages/Fidelisation'
import Dashboard from './pages/Dashboard'
import Livraisons from './pages/Livraisons'
import Notifications from './pages/Notifications'
import Catalogue from './pages/Catalogue'
import Rapports from './pages/Rapports'
import AuditLogs from './pages/AuditLogs'
import Plannings from './pages/Plannings';

const queryClient = new QueryClient()

function ProtectedRoute({ children }: { children: React.ReactNode }) {
  const { isAuthenticated } = useAuth()
  return isAuthenticated ? <>{children}</> : <Navigate to="/login" />
}

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        <BrowserRouter>
          <Routes>
            <Route path="/login" element={<Login />} />
            <Route
              path="/"
              element={
                <ProtectedRoute>
                  <MainLayout />
                </ProtectedRoute>
              }
            >
              <Route index element={<Navigate to="/dashboard" />} />
              <Route path="dashboard" element={<Dashboard />} />
              <Route path="agences" element={<Agences />} />
              <Route path="commandes" element={<Commandes />} />
              <Route path="clients" element={<Clients />} />
              <Route path="employes" element={<Employes />} />
              <Route path="pointages" element={<Pointages />} />
              <Route path="factures" element={<Factures />} />
              <Route path="catalogue" element={<Catalogue />} />
              <Route path="fidelisation" element={<Fidelisation />} />
              <Route path="livraisons" element={<Livraisons />} />
              <Route path="rapports" element={<Rapports />} />
              <Route path="audit-logs" element={<AuditLogs />} />
              <Route path="/plannings" element={<Plannings />} />
              <Route path="notifications" element={<Notifications />} />
              
            </Route>
          </Routes>
        </BrowserRouter>
      </AuthProvider>
    </QueryClientProvider>
  )
}

export default App