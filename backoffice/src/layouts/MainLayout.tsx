import { Layout, Menu, Avatar, Dropdown } from 'antd'
import {
  DashboardOutlined,
  ShopOutlined,
  ShoppingCartOutlined,
  UserOutlined,
  TeamOutlined,
  FileTextOutlined,
  ClockCircleOutlined,
  GiftOutlined,
  LogoutOutlined,
  CarOutlined,
} from '@ant-design/icons'
import { useNavigate, Outlet, useLocation } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { BellOutlined } from '@ant-design/icons'
const { Sider, Header, Content } = Layout

export default function MainLayout() {
  const navigate = useNavigate()
  const location = useLocation()
  const { user, logout } = useAuth()

  const menuItems = [
    { key: '/dashboard',    icon: <DashboardOutlined />,     label: 'Dashboard' },
    { key: '/agences',      icon: <ShopOutlined />,          label: 'Agences' },
    { key: '/commandes',    icon: <ShoppingCartOutlined />,  label: 'Commandes' },
    { key: '/clients',      icon: <UserOutlined />,          label: 'Clients' },
    { key: '/employes',     icon: <TeamOutlined />,          label: 'Employés' },
    { key: '/pointages',    icon: <ClockCircleOutlined />,   label: 'Pointages' },
    { key: '/factures',     icon: <FileTextOutlined />,      label: 'Factures' },
    { key: '/fidelisation', icon: <GiftOutlined />,          label: 'Fidélisation' },
    { key: '/livraisons',   icon: <CarOutlined />,           label: 'Livraisons' },
    { key: '/notifications', icon: <BellOutlined />, label: 'Notifications' },
  ]

  const userMenu = {
    items: [
      {
        key: 'logout',
        icon: <LogoutOutlined />,
        label: 'Déconnexion',
        onClick: logout,
        danger: true,
      }
    ]
  }

  return (
    <Layout style={{ minHeight: '100vh' }}>
      <Sider
        width={220}
        style={{
          background: '#001529',
          position: 'fixed',
          height: '100vh',
          left: 0,
          top: 0,
          overflow: 'auto',
        }}
      >
        <div style={{
          padding: '20px 16px',
          borderBottom: '1px solid rgba(255,255,255,0.1)',
          marginBottom: 8,
        }}>
          <div style={{
            color: '#1890ff',
            fontSize: 20,
            fontWeight: 'bold',
            letterSpacing: 1,
          }}>
            🧺 Masbanti
          </div>
          <div style={{ color: 'rgba(255,255,255,0.45)', fontSize: 11, marginTop: 4 }}>
            Gestion Franchise
          </div>
        </div>

        <Menu
          theme="dark"
          mode="inline"
          selectedKeys={[location.pathname]}
          items={menuItems}
          onClick={({ key }) => navigate(key)}
          style={{ borderRight: 0 }}
        />
      </Sider>

      <Layout style={{ marginLeft: 220 }}>
        <Header style={{
          background: 'white',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          padding: '0 24px',
          boxShadow: '0 1px 4px rgba(0,0,0,0.1)',
          position: 'sticky',
          top: 0,
          zIndex: 1,
        }}>
          <span style={{ fontSize: 16, fontWeight: 500, color: '#333' }}>
            {menuItems.find(m => m.key === location.pathname)?.label ?? 'Masbanti'}
          </span>

          <Dropdown menu={userMenu} placement="bottomRight">
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, cursor: 'pointer' }}>
              <Avatar style={{ background: '#1890ff' }}>
                {user?.name?.charAt(0).toUpperCase()}
              </Avatar>
              <span style={{ fontSize: 14, color: '#333' }}>{user?.name}</span>
            </div>
          </Dropdown>
        </Header>

        <Content style={{
          margin: 24,
          padding: 24,
          background: 'white',
          borderRadius: 8,
          minHeight: 'calc(100vh - 112px)',
        }}>
          <Outlet />
        </Content>
      </Layout>
    </Layout>
  )
}