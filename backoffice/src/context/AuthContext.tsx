import { createContext, useContext, useState } from 'react'
import type { ReactNode } from 'react'

interface User {
  id: number
  name: string
  email: string
}

interface AuthContextType {
  user: User | null
  token: string | null
  roles: string[]
  login: (token: string, user: User, roles: string[]) => void
  logout: () => void
  isAuthenticated: boolean
  hasRole: (role: string) => boolean
}

const AuthContext = createContext<AuthContextType | null>(null)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [token, setToken] = useState<string | null>(localStorage.getItem('token'))
  const [roles, setRoles] = useState<string[]>([])

  const login = (token: string, user: User, roles: string[]) => {
    localStorage.setItem('token', token)
    setToken(token)
    setUser(user)
    setRoles(roles)
  }

  const logout = () => {
    localStorage.removeItem('token')
    setToken(null)
    setUser(null)
    setRoles([])
  }

  const hasRole = (role: string) => roles.includes(role)

  return (
    <AuthContext.Provider value={{
      user, token, roles, login, logout,
      isAuthenticated: !!token,
      hasRole
    }}>
      {children}
    </AuthContext.Provider>
  )
}

export const useAuth = () => {
  const context = useContext(AuthContext)
  if (!context) throw new Error('useAuth must be used within AuthProvider')
  return context
}